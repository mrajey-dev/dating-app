<template>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <div v-if="showChat && person && canChat" class="chat-fullscreen">


    <!-- Header -->
    <div class="chat-header">
      <span class="close" style="font-size: 22px;" @click="closeChat"><i class="fa fa-arrow-circle-left" style="font-size:18px; color:#E91E63"> </i></span>

      <div class="chat-user">
        <img :src="person.profile_photo || defaultAvatar" class="chat-avatar" />
        <div class="chat-user-info">
          <div class="chat-name-row">
            <span class="chat-name">{{ person.first_name || 'User' }}</span>
            <img
              v-if="person.verified_badge === 1"
              src="@/assets/verified1.png"
              class="chat-verified"
            />
          </div>
          <span class="chat-status">{{ isOnline ? 'Online' : 'Offline' }}</span>
        </div>
      </div>
      <!-- 3 Dots Menu -->
<div class="chat-menu">
  <span class="menu-icon" @click="toggleMenu">⋮</span>

  <div v-if="showMenu" class="menu-dropdown">
    <div class="menu-item" @click="openWallpaperSelector">
      Change Wallpaper
    </div>
  </div>
</div>

    </div>

    <!-- Chat Body -->
    <div
  class="chat-body"
  ref="chatBody"
  :style="selectedWallpaper ? {
    backgroundImage: `url(${selectedWallpaper})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center'
  } : {}"
>

<div
  v-for="(msg, index) in messages"
  :key="index"
  :class="['chat-bubble', msg.type === 'sender' ? 'sent' : 'received']"
>
  <div class="chat-text">{{ msg.text }}</div>

 <div class="chat-time">
  {{ formatTime(msg.created_at) }}

<span v-if="msg.type === 'sender'" class="tick">
  <template v-if="msg.seen == true">
    <i class="fa fa-check seen"></i>
  </template>
  <template v-else>
    <i class="fa fa-check"></i>
  </template>

</span>





</div>

</div>


</div>
<!-- Menu Overlay -->
<div 
  v-if="showMessageMenu" 
  class="menu-overlay"
  @click="showMessageMenu = false"
></div>

<div
  v-if="showMessageMenu"
  class="message-menu"
  :style="{ top: menuY + 'px', left: menuX + 'px' }"
>
  <div class="menu-item delete" @click="deleteMessage">Delete</div>
</div>


    <!-- Chat Footer -->
  <!-- Chat Footer -->
<div class="chat-footer">
  <input
  type="text"
  placeholder="Type your message..."
  v-model="newMessage"
  ref="chatInput"
  :disabled="!canChat"
  :placeholder="canChat ? 'Type your message...' : 'Match to start messaging 💕'"
  @keyup.enter.prevent="sendMessage"
/>
<button
  @click="sendMessage"
  :disabled="!canChat || !newMessage.trim()"
>
 <i class="fa fa-send-o" style="font-size:13px"></i> Send
</button>

</div>

<div v-if="showToast" class="chat-toast">
  {{ toastMessage }}
</div>

<!-- Wallpaper Modal -->
<div v-if="showWallpaperModal" class="wallpaper-modal">
  <div class="wallpaper-container">
    <div
      v-for="(img, index) in wallpapers"
      :key="index"
      class="wallpaper-item"
      @click="setWallpaper(img)"
    >
      <img :src="img" />
    </div>
  </div>

 <button
  class="close-wallpaper"
  @click="closeWallpaper"
>
  Close
</button>

</div>


    <div class="overlay" @click="closeChat"></div>
  </div>
</template>

<script>
import defaultAvatar from '@/assets/user1.png';
import axios from 'axios';

export default {
  name: "ChatPopup",
  props: {
  person: { type: Object, default: () => ({}) },
  showChat: { type: Boolean, required: true },
  userId: { type: [Number, String], required: true },
  canChat: { type: Boolean, default: false },   // 👈 ADD THIS
},

  emits: ["close"],
  data() {
    return {
       showToast: false,
  toastMessage: "",
      longPressTimer: null,
selectedMessage: null,
showMessageMenu: false,
menuX: 0,
menuY: 0,

      allowAutoFocus: true,
      showMenu: false,
  showWallpaperModal: false,
  selectedWallpaper: localStorage.getItem("chatWallpaper") || '',
  wallpapers: [
    "https://i.pinimg.com/1200x/9f/b5/bd/9fb5bdaec93d157a429a88fd7bf00b9d.jpg",
    "https://i.pinimg.com/736x/e2/2d/9b/e22d9b6b7cfe00993ba760e646ab0835.jpg",
    "https://i.pinimg.com/1200x/b8/22/48/b82248f62d8f79081b8fc7558e054976.jpg",
        "https://i.pinimg.com/736x/15/ea/eb/15eaeb8bf1b51f6f8e171e5fe0e4cc55.jpg",
    "https://i.pinimg.com/736x/39/4f/66/394f66f401a5b3f5017e906f925f1b89.jpg",
    "https://i.pinimg.com/736x/28/89/42/288942effac549ef20ed90e5fe04efdb.jpg"
  ],
      defaultAvatar,
      messages: [],
      newMessage: "",
      isOnline: true,
      refreshInterval: null,
      scrollAtBottom: false,
    };
  },
  watch: {
    showChat(newVal) {
      if (newVal) this.startChat();
      else this.stopChat();
    },
    messages() {
      if (this.scrollAtBottom) {
        this.scrollToBottom(true);
      }
    },
  },
  mounted() {
  if (this.showChat) this.$refs.chatInput.focus();
},
  methods: {
async markMessagesSeen() {
  try {
    const token = localStorage.getItem("token");

    await axios.post(
      `https://companion.ajaywatpade.in/api/messages/seen/${this.person.id}`,
      {},
      { headers: { Authorization: `Bearer ${token}` } }
    );

  } catch (err) {
    console.error("Failed to mark messages as seen:", err);
  }
},


showCustomToast(message) {
  this.toastMessage = message;
  this.showToast = true;

  setTimeout(() => {
    this.showToast = false;
  }, 2500);
},

async updateMessage() {
  if (!this.editText.trim()) return;

  try {
    const token = localStorage.getItem("token");

    await axios.put(
      `https://companion.ajaywatpade.in/api/messages/${this.editingMessageId}`,
      { text: this.editText },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    const msg = this.messages.find(
      m => m.id === this.editingMessageId
    );
   if (msg) {
  msg.text = this.editText;
  msg.is_edited = 1;
}


    this.editingMessageId = null;
    this.editText = "";

  } catch (err) {
    console.error("Edit failed", err);
  }
},
async deleteMessage() {
  try {
    const token = localStorage.getItem("token");

    await axios.delete(
      `https://companion.ajaywatpade.in/api/messages/${this.selectedMessage.id}`,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    this.messages = this.messages.filter(
      m => m.id !== this.selectedMessage.id
    );

    this.showMessageMenu = false;
  } catch (err) {
    console.error("Delete failed", err);
  }
},

handleTouchStart(msg, event) {
  if (msg.type !== 'sender') return;

  this.longPressTimer = setTimeout(() => {
    this.selectedMessage = msg;
    this.showMessageMenu = true;

    let x = 0;
    let y = 0;

    if (event.touches && event.touches.length > 0) {
      x = event.touches[0].clientX;
      y = event.touches[0].clientY;
    } else {
      x = event.clientX;
      y = event.clientY;
    }

    // Prevent overflow outside screen
    const menuWidth = 150;
    const menuHeight = 60;

    this.menuX = Math.min(x, window.innerWidth - menuWidth - 10);
    this.menuY = Math.min(y, window.innerHeight - menuHeight - 10);

  }, 500);
},



handleTouchEnd() {
  clearTimeout(this.longPressTimer);
},

    closeWallpaper() {
  this.showWallpaperModal = false;

  // enable auto focus again
  this.allowAutoFocus = true;
},
    toggleMenu() {
  this.showMenu = !this.showMenu;
},

openWallpaperSelector() {
  const input = this.$refs.chatInput;
  if (input) {
    input.blur();
  }

  this.allowAutoFocus = false; // 🔴 disable auto focus
  this.showWallpaperModal = true;
  this.showMenu = false;
},


setWallpaper(img) {
  this.selectedWallpaper = img;
  localStorage.setItem("chatWallpaper", img);
  this.showWallpaperModal = false;
},

  formatTime(timestamp) {
  if (!timestamp) return '';
  const date = new Date(timestamp);
  const now = new Date();

  const isToday = date.toDateString() === now.toDateString();
  const options = { hour: '2-digit', minute: '2-digit' };
  const timeStr = date.toLocaleTimeString([], options);

  if (isToday) return timeStr; // Only time for today's messages

  const dateStr = date.toLocaleDateString([], { day: 'numeric', month: 'short' });
  return `${dateStr}, ${timeStr}`; // Older messages: date + time
},

    closeChat() {
      this.$emit("close");
    },

async startChat() {

  await this.fetchMessages();

  await this.markMessagesSeen();

  await this.fetchMessages(); // 🔥 very important

  this.scrollToBottom(false);

  if (!this.refreshInterval) {
    this.refreshInterval = setInterval(this.fetchMessages, 1000);
  }
},


    stopChat() {
      clearInterval(this.refreshInterval);
      this.refreshInterval = null;
    },

   focusInput() {
  if (!this.allowAutoFocus) return;

  this.$nextTick(() => {
    const input = this.$refs.chatInput;
    if (input) {
      input.focus();
    }
  });
},
async fetchMessages() {
  if (!this.person?.id) return;

  try {
    const token = localStorage.getItem("token");

    const res = await axios.get(
      `https://companion.ajaywatpade.in/api/messages/${this.person.id}`,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    const data = Array.isArray(res.data) ? res.data : [];

    this.messages = data.map(msg => ({
      id: msg.id,
      sender_id: Number(msg.sender_id),
      receiver_id: Number(msg.receiver_id),
      text: (msg.text ?? "").trim(),
      type: msg.type,
      created_at: msg.created_at,
      seen: Boolean(msg.seen)

    }));
console.log("Fetched messages:", this.messages);

    if (!this.showWallpaperModal) {
      this.focusInput();
    }

  } catch (err) {
    console.error("Failed to fetch messages:", err);
    this.messages = [];
  }
},


  async sendMessage() {

if (!this.canChat) {
  this.showCustomToast("You can send messages only after matching 💕");
  return;
}


  const trimmedMessage = this.newMessage.trim();
  if (!trimmedMessage || !this.person?.id) return;

  try {
    const token = localStorage.getItem("token");

    // Optimistically push message
    const sentMsg = {
      id: Date.now(),
      sender_id: Number(this.userId),
      receiver_id: this.person.id,
      text: trimmedMessage,
      type: 'sender',
    };

    this.messages.push(sentMsg);

    this.$refs.chatInput?.focus();
    this.newMessage = "";
    this.scrollToBottom(true);

    await axios.post(
      "https://companion.ajaywatpade.in/api/messages",
      { receiver_id: this.person.id, text: trimmedMessage },
      { headers: { Authorization: `Bearer ${token}` } }
    );

  } catch (err) {
    console.error("Failed to send message:", err);
  }
},


    scrollToBottom(smooth = true) {
      this.$nextTick(() => {
        const container = this.$refs.chatBody;
        if (!container) return;

        if (!smooth) {
          container.scrollTop = container.scrollHeight;
        } else {
          const lastMsg = container.lastElementChild;
          if (lastMsg) {
            const offset = lastMsg.offsetHeight + 10;
            container.scrollBy({ top: offset, behavior: 'smooth' });
          }
        }
      });
    },
  },
  beforeUnmount() {
    this.stopChat();
  },
};
</script>


<!-- ----------------------------------------------------------------- -->

<style scoped>
.chat-fullscreen {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: #fff;
  display: flex;
  flex-direction: column;
  z-index: 1000;
}

/* ================= HEADER ================= */

.chat-header {
  margin-top: 59px;
  display: flex;
  align-items: center;
  padding: 9px;
  font-weight: 800 !important;
  color: rgb(16, 12, 14);
  background: #fafafa;
  border-bottom: 1px solid #ddd;
}

.chat-user {
  display: flex;
  align-items: center;
  margin-left: 10px;
}

.chat-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
}

.chat-user-info {
  margin-left: 12px;
}

.chat-name-row {
  display: flex;
  align-items: center;
  gap: 5px;
}

.chat-verified {
  width: 15px;
  height: 15px;
}

/* ================= BODY ================= */

.chat-body {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  background-image: url(https://i.pinimg.com/736x/5e/b0/5b/5eb05b30bd9d839c647cd548cfd85f3c.jpg);
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  margin-bottom: 70px;
  display: flex;
  flex-direction: column;
}

/* ================= MESSAGE BUBBLES ================= */

.chat-bubble {
  padding: 10px 14px;
  margin-bottom: 10px;
  max-width: 70%;
  word-wrap: break-word;
  display: inline-block;
}

/* Sent (Right) */
.sent {
  align-self: flex-end;
  background-color: #ff2e44;
  color: #ffffff;
  border-radius: 16px 0 16px 16px;
  overflow-wrap: anywhere;
}

/* Received (Left) */
.received {
  align-self: flex-start;
  background-color: #ffffff;
  color: #000;
  border-radius: 0 16px 16px 16px;
  overflow-wrap: anywhere;
}

/* ================= TIME + TICKS ================= */

.chat-time {
  font-size: 11px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 4px;
  margin-top: 4px;
}

/* Tick container */
.tick {
  display: flex;
  align-items: center;
}

/* Default single tick (grey) */
.tick i {
  font-size: 11px;
  margin-left: -2px;
  color: rgba(255, 255, 255, 0.6);
}

/* Double blue tick when seen */
.tick i.seen {
  color: #34b7f1 !important;
}

/* ================= FOOTER ================= */

.chat-footer {
  display: flex;
  gap: 8px;
  padding: 12px;
  border-top: 1px solid #ddd;
  background: #fafafa;
  position: fixed;
  bottom: 20px;
  left: 0;
  width: 100%;
  z-index: 1001;
  box-sizing: border-box;
}

.chat-footer input {
  flex: 1;
  padding: 8px 12px;
  border-radius: 12px;
  border: 1px solid #ccc;
}

.chat-footer button {
  padding: 8px 16px;
  border-radius: 12px;
  border: none;
  background: #f10265;
  color: white;
  cursor: pointer;
}

/* ================= MENU ================= */

.chat-menu {
  margin-left: auto;
  position: relative;
}

.menu-icon {
  font-size: 22px;
  cursor: pointer;
  padding: 5px;
}

.menu-dropdown {
  position: absolute;
  right: 0;
  top: 30px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  width: 150px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.menu-item {
  padding: 10px;
  cursor: pointer;
}

.menu-item:hover {
  background: #f5f5f5;
}

/* ================= MESSAGE CONTEXT MENU ================= */

.menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 2500;
}

.message-menu {
  position: fixed;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.25);
  z-index: 3000;
  min-width: 150px;
  animation: fadeIn 0.15s ease-in-out;
}

.message-menu .menu-item {
  padding: 12px 16px;
  font-size: 14px;
  font-weight: 500;
}

.message-menu .delete {
  color: #ff2e44;
}

/* ================= TOAST ================= */

.chat-toast {
  position: fixed;
  bottom: 110px;
  left: 50%;
  transform: translateX(-50%);
  background: #ff2e44;
  color: white;
  padding: 10px 18px;
  border-radius: 20px;
  font-size: 14px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  z-index: 5000;
  animation: fadeToast 0.3s ease;
}

@keyframes fadeToast {
  from { opacity: 0; transform: translateX(-50%) translateY(10px); }
  to { opacity: 1; transform: translateX(-50%) translateY(0); }
}

@keyframes fadeIn {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>

