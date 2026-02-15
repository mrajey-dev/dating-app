<template>
  <div v-if="showChat && person" class="chat-fullscreen">
    <!-- Header -->
    <div class="chat-header">
      <span class="close" style="font-size: 22px;" @click="closeChat">⬅</span>

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
    </div>

    <!-- Chat Body -->
    <div class="chat-body" ref="chatBody">
      <div
        v-for="(msg, index) in messages"
        :key="index"
        :class="['chat-bubble', msg.type === 'sender' ? 'sent' : 'received']"
      >
        {{ msg.text }}
      </div>
    </div>

    <!-- Chat Footer -->
  <!-- Chat Footer -->
<div class="chat-footer">
  <input
    type="text"
    placeholder="Type your message..."
    v-model="newMessage"
    ref="chatInput"
    @keyup.enter.prevent="sendMessage"
  />
  <button @click="sendMessage">Send</button>
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
  },
  emits: ["close"],
  data() {
    return {
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
    closeChat() {
      this.$emit("close");
    },

    async startChat() {
      await this.fetchMessages();
      this.scrollAtBottom = false;

      // Scroll to bottom on open
      this.scrollToBottom(false);
      this.scrollAtBottom = true;

      // Always focus input to keep keyboard open
      this.focusInput();

      if (!this.refreshInterval) {
        this.refreshInterval = setInterval(this.fetchMessages, 1000);
      }
    },

    stopChat() {
      clearInterval(this.refreshInterval);
      this.refreshInterval = null;
    },

    focusInput() {
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

        this.messages = data
          .map(msg => ({
            id: msg.id,
            sender_id: Number(msg.sender_id),
            receiver_id: Number(msg.receiver_id),
            text: (msg.text ?? "").trim(),
            type: msg.type,
          }))
          .filter(msg => msg.text !== "");

        // Focus input again after fetching messages
        this.focusInput();
      } catch (err) {
        console.error("Failed to fetch messages:", err);
        this.messages = [];
      }
    },

   async sendMessage() {
  const trimmedMessage = this.newMessage.trim();
  if (!trimmedMessage || !this.person?.id) return;

  try {
    const token = localStorage.getItem("token");

    // Optimistically push message to chat before server response
    const sentMsg = {
      id: Date.now(),
      sender_id: Number(this.userId),
      receiver_id: this.person.id,
      text: trimmedMessage,
      type: 'sender',
    };
    this.messages.push(sentMsg);

    // Keep input focused (prevent keyboard from hiding)
    this.$refs.chatInput.focus();

    this.newMessage = "";

    // Scroll down smoothly
    this.scrollToBottom(true);

    // Send message to backend
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

.chat-body {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  background: #ffbade;
  height: auto;
  display: flex;
  flex-direction: column;
}

.chat-bubble {
  padding: 10px 14px;
  margin-bottom: 10px;
  max-width: 70%;
  word-wrap: break-word;
  display: inline-block;
}

/* Sent messages (right) */
.sent {
  align-self: flex-end;
    background-color: #ff2e44;
    color: #ffffff;
    border-radius: 16px 0 16px 16px;
}

/* Received messages (left) */
.received {
  align-self: flex-start;
  background-color: #ffffff;
  color: #000;
  border-radius: 0 16px 16px 16px;
}

.chat-footer input {
  flex: 1;
  padding: 8px 12px;
  border-radius: 20px;
  border: 1px solid #ccc;
}

.chat-footer button {
  padding: 8px 16px;
  border-radius: 20px;
  border: none;
  background: #f10265;
  color: white;
  cursor: pointer;
}

.chat-status {
  font-size: 12px;
  color: #f10265;
  margin-top: 4px;
}
.chat-message {
  max-width: 70%;
  padding: 8px 12px;
  margin: 4px 0;
  border-radius: 12px;
  word-wrap: break-word;
}

.chat-message.left {
  background-color: #f1f1f1;
  align-self: flex-start;
}

.chat-message.right {
  background-color: #4CAF50;
  color: white;
  align-self: flex-end;
}

/* Chat Footer fixed at bottom */
.chat-footer {
  display: flex;
  gap: 8px;
  padding: 12px;
  border-top: 1px solid #ddd;
  background: #fafafa;

  /* Fix at bottom */
  position: fixed;
  bottom: 20px;
  left: 0;
  width: 100%;
  z-index: 1001;

  /* Optional: prevent footer from shrinking */
  box-sizing: border-box;
}
.chat-body {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  background: #ffbade;
  margin-bottom: 70px; /* Give space for fixed footer */
  display: flex;
  flex-direction: column;
}

</style>
