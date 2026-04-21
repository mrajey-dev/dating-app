@ -1,1854 +1,1184 @@
<template>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <div v-if="showChat && person && canChat" class="chat-fullscreen">


    <!-- Header -->
    <div class="chat-header">
      <!-- <span class="close" style="font-size: 22px;" @click="closeChat"><i class="fa fa-arrow-circle-left" style="font-size:18px";> </i></span> -->

      <div class="chat-user">
        <img :src="localPerson.profile_photo || defaultAvatar" class="chat-avatar" />
        <div class="chat-user-info">
          <div class="chat-name-row">
            <span class="chat-name">{{ localPerson.first_name || 'User' }}</span>
            <img
              v-if="localPerson.verified_badge === 1"
              src="@/assets/verified1.png"
              class="chat-verified"
            />
          </div>
<span class="chat-status">
  <!-- <span :class="['online-dot', isOnline ? 'online' : 'offline']"></span> -->

 <span v-if="isTyping" class="typing">
  typing...
  <span class="dots">
    <i></i><i></i><i></i>
  </span>
</span>
  <span v-else>{{ isOnline ? 'Online' : lastSeenText }}</span>
</span>


        </div>
      </div>
<!-- ================= CALL UI ================= -->
<div v-if="callActive && !showIncomingCall" class="call-overlay">

  <div class="call-header">
    <img :src="localPerson.profile_photo || defaultAvatar" />
    <h3>{{ localPerson.first_name }}</h3>
    <p>Audio Call</p>
    <span class="call-time">{{ formatCallTime }}</span>
  </div>


  <!-- CONTROLS -->
  <div class="call-controls">
    <button @click="toggleMute">
      <i :class="callMuted ? 'fa fa-microphone-slash' : 'fa fa-microphone'"></i>
    </button>


    <button class="end-call" @click="endCall">
      <i class="fa fa-phone"></i>
    </button>
  </div>

</div>
      <!-- CALL ACTIONS -->
<!-- <div class="chat-actions">
  <i v-if="isPlanOver === false"
    class="fa fa-phone call-icon"
    title="Audio Call"
    @click="startAudioCall"
  ></i>


</div> -->

      <!-- 3 Dots Menu -->
<div class="chat-menu">
  <span class="menu-icon" @click="toggleMenu">⋮</span>

  <div v-if="showMenu" class="menu-dropdown">
    <div class="menu-item" @click="openWallpaperSelector">
      Change Wallpaper
    </div>
  </div>
</div>

    
<!-- ================= INCOMING CALL ================= -->
<div v-if="showIncomingCall" class="incoming-call">

  <div class="incoming-card">
    <img
      :src="localPerson.profile_photo || defaultAvatar"
      class="incoming-avatar"
    />

    <h2 class="incoming-name">
      {{ localPerson.first_name }}
    </h2>

  <p class="incoming-type">Incoming voice call</p>

    <div class="incoming-actions">
      <!-- Reject -->
      <button class="reject" @click="rejectCall">
        <i class="fa fa-phone"></i>
      </button>

      <!-- Accept -->
      <button class="accept" @click="acceptCall">
        <i class="fa fa-phone"></i>
      </button>
    </div>
  </div>

</div>
</div>

    <!-- Chat Body -->
<div
  class="chat-body"
  ref="chatBody"
  @scroll="handleScroll"
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
  <div class="chat-text">
  <!-- Text message -->
  <p v-if="msg.text">{{ msg.text }}</p>

  <!-- Image message -->
  <img v-if="msg.image" :src="msg.image" class="chat-image" />
</div>

 <div class="chat-time">
  {{ formatTime(msg.created_at) }}

<span v-if="msg.type === 'sender'" class="tick">
  <!-- Clock only if neither delivered nor seen -->
  <i v-if="!msg.delivered && !msg.seen" class="fa fa-check-circle"></i>

  <!-- Delivered only if delivered but not seen -->
  <i v-else-if="msg.delivered && !msg.seen" class="fa fa-check-circle" style="color: grey;"></i>

  <!-- Seen only if seen -->
  <i v-else-if="msg.seen" class="fa fa-check-circle blue-tick"></i>
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

<!-- ================= CHAT FOOTER ================= -->
<div class="chat-footer">
  <!-- Emoji Button -->
  <!-- <button class="emoji-btn" @click="toggleEmojiPicker">😊</button> -->

  <!-- Image Upload -->
  <input
    type="file"
    ref="imageInput"
    @change="sendImage"
    accept="image/*"
    style="display: none"
  />
  <button v-if="isPlanOver === false" class="image-btn" @click="$refs.imageInput.click()">
  +
  </button>

  <!-- Message Input -->
<!-- MESSAGE INPUT -->
<input
  v-if="!isPlanOver"
  type="text"
  placeholder="Type a message..."
  v-model="newMessage"
  ref="chatInput"
  :readonly="!isInputActive"
  :disabled="!canChat || callActive"
  @touchstart="activateInput"
  @mousedown="activateInput"
  @blur="deactivateInput"
  @input="handleTyping"
  @keyup.enter.prevent="sendMessage"
/>

<!-- SEND BUTTON -->
<button
  v-if="isPlanOver === false"
  class="send-btn"
  @click="sendMessage"
  :disabled="!canChat || !newMessage.trim()"
>
  <i class="fa fa-paper-plane"></i>
</button>

<!-- UPGRADE PLAN BUTTON -->
<div v-if="isPlanOver" class="upgrade-box">
  <button class="upgrade-btn" @click="goToUpgrade">
    Upgrade Plan 🚀
  </button>
</div>

  <!-- Emoji Picker -->
  <div v-if="showEmojiPicker" class="emoji-picker">
    <span v-for="emoji in emojis" :key="emoji" @click="addEmoji(emoji)">
      {{ emoji }}
    </span>
  </div>
</div>

<div v-if="showToast" class="chat-toast">
  {{ toastMessage }}
</div>
<div v-if="showWallpaperModal" class="wallpaper-modal">
  <div class="wallpaper-box">

    <div class="wallpaper-header">
      <span>Choose Chat Wallpaper</span>
      <button @click="closeWallpaper">✕</button>
    </div>

    <div class="wallpaper-grid">
      <div
        v-for="(img, index) in wallpapers"
        :key="index"
        class="wallpaper-card"
        @click="setWallpaper(img)"
      >
        <img :src="img" />
      </div>
    </div>

  </div>
</div>

    <div class="overlay" @click="closeChat"></div>
    <audio ref="remoteAudio" autoplay playsinline></audio>
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
      isPlanOver: null,
      iceQueue: [],
      isInputActive: false,
      incomingCallData: null,
showIncomingCall: false,
    // ===== WEBRTC =====
peer: null,
localStream: null,
remoteStream: null,
    callActive: false,

callMuted: false,

callTimer: null,
callDuration: 0,
       showEmojiPicker: false,
    emojis: ["😀","😂","😍","😎","🥰","😢","😡","👍","🙏","💖","🎉","🔥"],
      isTyping: false,
typingTimer: null,
       hasLoadedStatus: false,
       onlineInterval: null,
      localPerson: null,
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
  
      refreshInterval: null,
      scrollAtBottom: false,
    };
  },
watch: {
  isOnline(newVal, oldVal) {

    if (!this.hasLoadedStatus) {
      this.hasLoadedStatus = true;
      return;
    }

    if (newVal !== oldVal) {
      const status = newVal ? "online" : "offline";
      this.showCustomToast(`User is now ${status}`);
    }
  },

  showChat(newVal) {
    if (newVal) this.startChat();
    else this.stopChat();
  },

  messages() {
    this.$nextTick(() => {
      if (this.scrollAtBottom) {
        this.scrollToBottom(true);
      }
    });
  }
},
  mounted() {
    this.signalInterval = setInterval(this.fetchSignals, 1000);
     this.startOnlinePing();
    this.showCustomToast("Test toast works!");
  
},
computed: {
formatCallTime() {
  const m = Math.floor(this.callDuration / 60);
  const s = this.callDuration % 60;
  return `${m}:${s.toString().padStart(2, '0')}`;
},
  lastSeenText() {
  if (!this.localPerson || !this.localPerson.last_active_at) {
    return "Offline";
  }

  const last = new Date(this.localPerson.last_active_at.replace(" ", "T"));
  const now = new Date();

  const diff = Math.floor((now - last) / 1000);

  if (diff < 60) return "Last seen just now";

  const minutes = Math.floor(diff / 60);
  if (minutes < 60) return `Last seen ${minutes} min ago`;

  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `Last seen ${hours} hr ago`;

  const days = Math.floor(hours / 24);
  if (days === 1) return "Last seen yesterday";

  return `Last seen ${days} days ago`;
},
  isOnline() {
    if (!this.localPerson || !this.localPerson.last_active_at) return false;

    const formatted = this.localPerson.last_active_at.replace(" ", "T");
    const lastActive = new Date(formatted);
    const now = new Date();

    const diffSeconds = (now - lastActive) / 1000;

    return diffSeconds <= 10;
  }
},
  methods: {
async checkPlanStatus() {
  try {
    const token = localStorage.getItem("token");
    const gender = localStorage.getItem("gender"); // 👈 IMPORTANT

    // ✅ Female users NEVER see upgrade
    if (gender === "female") {
      this.isPlanOver = false;
      return;
    }

    const res = await axios.get(
      "https://companion.ajaywatpade.in/api/user/plan-status",
      { headers: { Authorization: `Bearer ${token}` } }
    );

    this.isPlanOver = res.data.is_plan_over;

  } catch (err) {
    console.error("Failed to check plan status", err);
    this.isPlanOver = false; // fail-safe (don't block user)
  }
},
goToUpgrade() {
  // Redirect to upgrade page and pass the chat user's ID
  this.$router.push({
    path: '/upgrade',
    query: { targetUserId: this.person.id } // 🔑 this is critical
  });
},
    resumeAudioContext() {
  const AudioContext = window.AudioContext || window.webkitAudioContext;
  if (!AudioContext) return;

  if (!this.audioContext) {
    this.audioContext = new AudioContext();
  }

  if (this.audioContext.state === "suspended") {
    this.audioContext.resume().then(() => {
      console.log("AudioContext resumed");
    });
  }
},
    activateInput() {
  if (this.callActive) return;

  this.isInputActive = true;

  this.$nextTick(() => {
    this.$refs.chatInput?.focus();
  });
},

deactivateInput() {
  this.isInputActive = false;
},
     onInputFocus() {
    // keyboard opens ONLY here
  },
  onInputBlur() {
    // nothing needed
  },
async acceptCall() {
  try {
    this.showIncomingCall = false;
    this.callActive = true;
    this.openCallUI();

    this.resumeAudioContext();

    const audioEl = this.$refs.remoteAudio;
    if (audioEl) {
      audioEl.muted = false;
      await audioEl.play().catch(() => {});
    }

    const offerData = this.incomingCallData.data;

    // 🔑 VERY IMPORTANT: recreate description
    const offer = new RTCSessionDescription({
      type: offerData.type,
      sdp: offerData.sdp
    });

    await this.initMedia();
    this.createPeer();

    await this.peer.setRemoteDescription(offer);
// 🔑 APPLY QUEUED ICE (CRITICAL)
this.iceQueue.forEach(c => {
  this.peer.addIceCandidate(new RTCIceCandidate(c));
});
this.iceQueue = [];
    const answer = await this.peer.createAnswer();
    await this.peer.setLocalDescription(answer);

    this.sendSignal("answer", {
      type: answer.type,
      sdp: answer.sdp
    });

  } catch (err) {
    console.error("ACCEPT CALL ERROR:", err);
  }
},
rejectCall() {
  this.showIncomingCall = false;
  this.incomingCallData = null;

  this.callActive = false; // ✅ make sure video is closed
  this.sendSignal("reject", {});
},
incomingCall(signal) {
  this.incomingCallData = signal;
  this.showIncomingCall = true;

  // 🚫 DO NOT start media
  // 🚫 DO NOT set callActive
},
async fetchSignals() {
  const token = localStorage.getItem("token");

  const res = await axios.get(
    `https://companion.ajaywatpade.in/api/call/signals`,
    { headers: { Authorization: `Bearer ${token}` } }
  );

  res.data.forEach(signal => {
    if (signal.type === "offer") this.incomingCall(signal);
    if (signal.type === "answer") this.handleAnswer(signal.data);
    if (signal.type === "ice") this.handleIce(signal.data);

    // 🔑 NEW: remote call end
    if (signal.type === "end-call") {
      this.onRemoteEndCall();
    }
  });
},
onRemoteEndCall() {
  console.log("Remote user ended the call");

  this.peer?.close();
  this.localStream?.getTracks().forEach(t => t.stop());

  this.peer = null;
  this.localStream = null;
  this.callActive = false;
  this.showIncomingCall = false;

  clearInterval(this.callTimer);

  this.showCustomToast("Call ended");
},
endCall() {
  this.sendSignal("end-call", {});

  this.peer?.close();
  this.localStream?.getTracks().forEach(t => t.stop());

  this.peer = null;
  this.localStream = null;
  this.callActive = false;

  clearInterval(this.callTimer);
  this.callTimer = null;
  this.callDuration = 0;
},
async initMedia() {
  this.localStream = await navigator.mediaDevices.getUserMedia({
    audio: {
      echoCancellation: true,
      noiseSuppression: true,
      autoGainControl: true
    },
    video: false
  });

  console.log("LOCAL AUDIO:", this.localStream.getAudioTracks());
},
createPeer() {
  this.peer = new RTCPeerConnection({
    iceServers: [{ urls: "stun:stun.l.google.com:19302" }]
  });

  // 🔑 ADD TRACKS FIRST
  this.localStream.getTracks().forEach(track => {
    this.peer.addTrack(track, this.localStream);
  });

  // 🔑 REMOTE AUDIO
this.peer.ontrack = (event) => {
  const audioEl = this.$refs.remoteAudio;
  if (audioEl) {
    audioEl.srcObject = event.streams[0];
    audioEl.play().catch(() => {});
  }
};

  this.peer.onicecandidate = e => {
    if (e.candidate) {
      this.sendSignal("ice", e.candidate);
    }
  };
},
async startAudioCall() {
   if (this.isPlanOver) {
    this.showCustomToast("Upgrade your plan to make calls 📞🚀");
    return;
  }
  this.isInputActive = false;
  this.$refs.chatInput?.blur();

  this.callActive = true;
  this.openCallUI(); // 🔑 START TIMER FOR CALLER
  this.resumeAudioContext();

  await this.initMedia();
  this.createPeer();

  const offer = await this.peer.createOffer();
  await this.peer.setLocalDescription(offer);

  this.sendSignal("offer", {
    type: offer.type,
    sdp: offer.sdp
  });
},


async startCall(video) {
  this.callActive = true;

  await this.initMedia(video);
  this.createPeer();

  const offer = await this.peer.createOffer();
  await this.peer.setLocalDescription(offer);

  this.sendSignal("offer", offer);
},
async handleAnswer(answerData) {
  if (!this.peer) return;

  const answer = new RTCSessionDescription({
    type: answerData.type,
    sdp: answerData.sdp
  });

  await this.peer.setRemoteDescription(answer);

  // 🔑 APPLY QUEUED ICE
  this.iceQueue.forEach(c => {
    this.peer.addIceCandidate(new RTCIceCandidate(c));
  });
  this.iceQueue = [];
},
async handleIce(candidate) {
  if (!this.peer) {
    console.log("ICE queued");
    this.iceQueue.push(candidate);
    return;
  }

  await this.peer.addIceCandidate(new RTCIceCandidate(candidate));
},
sendSignal(type, data) {
  const token = localStorage.getItem("token");

  axios.post("https://companion.ajaywatpade.in/api/call/signal", {
    receiver_id: this.person.id,
    type,
    data
  }, {
    headers: { Authorization: `Bearer ${token}` }
  });
},

openCallUI() {
  if (this.callTimer) return; // 🔒 prevent duplicate timers

  this.callActive = true;
  this.callDuration = 0;

  this.callTimer = setInterval(() => {
    this.callDuration++;
  }, 1000);
},

toggleMute() {
  this.callMuted = !this.callMuted;

  if (this.localStream) {
    this.localStream.getAudioTracks().forEach(track => {
      track.enabled = !this.callMuted;
    });
  }
},

toggleCamera() {
  this.cameraOff = !this.cameraOff;
},

   handleScroll() {
    const el = this.$refs.chatBody;
    if (!el) return;

    // user is near bottom (20px tolerance)
    this.scrollAtBottom =
      el.scrollTop + el.clientHeight >= el.scrollHeight - 20;
  },
     toggleEmojiPicker() {
    this.showEmojiPicker = !this.showEmojiPicker;
  },

  addEmoji(emoji) {
    this.newMessage += emoji;
    this.showEmojiPicker = false;
    this.$refs.chatInput.focus();
  },

async sendImage(event) {
  const file = event.target.files[0];
  if (!file) return;

  if (!this.person?.id) {
    console.warn("Receiver ID missing");
    return;
  }

  const formData = new FormData();
  formData.append("receiver_id", this.person.id);
  formData.append("image", file); // 🔑 key must match backend

  try {
    const token = localStorage.getItem("token");
    if (!token) throw new Error("No auth token");

    const res = await axios.post(
      "https://companion.ajaywatpade.in/api/messages/image",
      formData,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          // Do NOT set 'Content-Type'; Axios handles it automatically
        },
      }
    );

    // 🔑 Safely get image URL from backend response
    const imgUrl = res.data?.image || res.data?.data?.image || res.data?.url;
    if (!imgUrl) throw new Error("Image URL missing in response");

    // ✅ Push new message to chat
    const newMsg = {
      id: res.data.id || Date.now(),
      sender_id: Number(this.userId),
      receiver_id: this.person.id,
      text: "",
      image: imgUrl,
      type: "sender",
      seen: false,
      delivered: false,
      created_at: res.data.created_at || new Date().toISOString(),
    };

    this.messages.push(newMsg);
    this.scrollToBottom(true);

  } catch (err) {
  if (err.response?.status === 403) {
  this.isPlanOver = true;            // 👈 IMPORTANT
  this.showCustomToast("Upgrade your plan to continue 🚀");
} else {
    console.error("Failed to send image:", err);
    this.showCustomToast(
      err.response?.data?.message || "Failed to send image"
    );
  }
}
},
    async stopTyping() {

  const token = localStorage.getItem("token");

  try {
    await axios.post(
      "https://companion.ajaywatpade.in/api/chat/stop-typing",
      { receiver_id: this.person.id },
      { headers: { Authorization: `Bearer ${token}` } }
    );
  } catch (e) {}

},
async checkTyping() {
  const token = localStorage.getItem("token");

  try {
    const res = await axios.get(
      `https://companion.ajaywatpade.in/api/chat/typing/${this.person.id}`,
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    );

    this.isTyping = res.data.typing;

  } catch (e) {}
},
async handleTyping() {

  const token = localStorage.getItem("token");

  try {
    await axios.post(
      "https://companion.ajaywatpade.in/api/chat/typing",
      { receiver_id: this.person.id },
      { headers: { Authorization: `Bearer ${token}` } }
    );
  } catch (e) {}

  // stop typing after user stops typing
  clearTimeout(this.typingTimer);

  this.typingTimer = setTimeout(() => {
    this.stopTyping();
  }, 1500);
},
startStatusRefresh() {
    console.log("Status refresh started");
  this.statusInterval = setInterval(async () => {
    try {
      const token = localStorage.getItem("token");
      const res = await axios.get(
  `https://companion.ajaywatpade.in/api/users/${this.person.id}`,
  { headers: { Authorization: `Bearer ${token}` } }
);

console.log("API RESPONSE:", res.data);
     if (res.data?.user?.last_active_at) {
 this.localPerson.last_active_at = new Date(res.data.user.last_active_at).toISOString();
}
    } catch (err) {
      console.error("Failed to fetch user status:", err);
    }
  }, 5000); // every 5 seconds
},


stopStatusRefresh() {
  clearInterval(this.statusInterval);
},


  startOnlinePing() {

    const token = localStorage.getItem("token");

    this.onlineInterval = setInterval(async () => {
      try {

        await axios.post(
          "https://companion.ajaywatpade.in/api/users/last-active",
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`
            }
          }
        );

        console.log("last_active_at updated");

      } catch (err) {
        console.error("Ping failed", err);
      }

    }, 3000); // every 3 seconds
  },

stopOnlinePing() {
  clearInterval(this.onlineInterval);
},

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
   await this.checkPlanStatus(); 
  this.localPerson = { ...this.person };
  await this.fetchMessages();
  await this.markMessagesSeen();
  await this.fetchMessages(); 
  this.scrollToBottom(false);
this.typingInterval = setInterval(this.checkTyping, 2000);
  if (!this.refreshInterval) {
    this.refreshInterval = setInterval(this.fetchMessages, 1000);
  }

  this.startOnlinePing();      // ✅ update your own online status
  this.startStatusRefresh();   // ✅ fetch other user online status
},



    stopChat() {
      clearInterval(this.refreshInterval);
      clearInterval(this.typingInterval);
      this.refreshInterval = null;
        this.stopOnlinePing();
  this.stopStatusRefresh();
    },

focusInput() {
  if (!this.allowAutoFocus || this.callActive) return;

  this.$nextTick(() => {
    const input = this.$refs.chatInput;
    if (input) input.focus();
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
      image: msg.image ?? null,   // ✅ Add this line
      type: msg.type,
      created_at: msg.created_at,
      seen: Boolean(msg.seen),
      delivered: Boolean(msg.delivered),
    }));

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

    const res = await axios.post(
      "https://companion.ajaywatpade.in/api/messages",
      {
        receiver_id: this.person.id,
        text: trimmedMessage
      },
      {
        headers: { Authorization: `Bearer ${token}` }
      }
    );

    // ✅ push ONLY after success
    this.messages.push({
      id: res.data.id,
      sender_id: Number(this.userId),
      receiver_id: this.person.id,
      text: trimmedMessage,
      type: "sender",
      delivered: false,
      seen: false,
      created_at: res.data.created_at
    });

    this.newMessage = "";
    this.scrollToBottom(true);

  } catch (err) {
    if (err.response?.status === 403) {
  this.isPlanOver = true;            // 👈 IMPORTANT
  this.showCustomToast("Upgrade your plan to continue");
} else {
      console.error("Failed to send message:", err);
    }
  }
},


   scrollToBottom(smooth = true) {
  this.$nextTick(() => {
    const container = this.$refs.chatBody;
    if (!container) return;

    if (smooth) {
      container.scrollTo({
        top: container.scrollHeight,
        behavior: "smooth"
      });
    } else {
      container.scrollTop = container.scrollHeight;
    }
  });
}
  },
  beforeUnmount() {
    clearInterval(this.onlineInterval);
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
  font-size: 15px;
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
  padding-bottom: 80px;
  background-image: url(https://i.pinimg.com/736x/5e/b0/5b/5eb05b30bd9d839c647cd548cfd85f3c.jpg);
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  /* margin-bottom: 70px; */
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
  background-color: #07070742;
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
  padding: 7px 5px;
  border-radius: 12px;
  border: none;
  /* background: #f10265; */
  color: rgb(2, 0, 1);
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
.tick i {
  font-size: 12px;
  margin-left: 2px;
  color: rgb(255, 255, 255);
}

.tick .blue-tick {
  color: #0cfdff;
}
.online-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-right: 5px;
}

.online-dot.online {
  background: #4caf50;
}

.online-dot.offline {
  background: #ccc;
}

.wallpaper-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 4000;
}

.wallpaper-box {
  width: 92%;
  max-width: 420px;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 15px 40px rgba(0,0,0,0.3);
  animation: popup 0.25s ease;
}

.wallpaper-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  font-weight: 600;
  border-bottom: 1px solid #eee;
}

.wallpaper-header button {
  border: none;
  background: #f5f5f5;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
}

.wallpaper-grid {
  padding: 12px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.wallpaper-card {
  border-radius: 10px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s;
}

.wallpaper-card:hover {
  transform: scale(1.05);
}

.wallpaper-card img {
  width: 100%;
  height: 90px;
  object-fit: cover;
}

@keyframes popup {
  from {
    transform: scale(.9);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.typing {
  color: #08570b;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 4px;
}


.chat-footer {
  display: flex;
  align-items: center;
  gap: 0px;
  padding: 8px 12px;
  border-top: 1px solid #ddd;
  background: #fafafa;
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  z-index: 1001;
  box-sizing: border-box;
}

.chat-footer input {
  flex: 1;
  padding: 10px 14px;
  border-radius: 25px;
  border: 1px solid #ccc;
  outline: none;
  font-size: 14px;
}

.send-btn, .emoji-btn, .image-btn {
  border: none;
  background: transparent;
  font-size: 20px;
  cursor: pointer;
  color: #075E54;
}

.send-btn:disabled {
  color: #999;
  cursor: not-allowed;
}

.emoji-picker {
  position: absolute;
  bottom: 60px;
  left: 10px;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 10px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  max-width: 250px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.emoji-picker span {
  cursor: pointer;
  font-size: 18px;
}
.chat-image {
  max-width: 200px;        /* adjust as needed */
  border-radius: 12px;
  margin-top: 5px;
  cursor: pointer;
}
.chat-status{
  font-size: 12px;
}
.chat-actions {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-left: auto;
    margin-right: -78px;
}

.call-icon {
  font-size: 20px;
  cursor: pointer;
  margin-left: 83px;
    margin-top: 8px;
  color: #075E54;
}

.call-icon:hover {
  color: #128C7E;
}
/* ================= CALL UI ================= */

.call-overlay {
  position: fixed;
  inset: 0;
  background: #000;
  color: #fff;
  z-index: 6000;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.call-header {
  text-align: center;
  padding: 40px 20px;
      margin-top: 44px;
}

.call-header img {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 12px;
}

.call-header h3 {
  margin: 5px 0;
  font-size: 20px;
}

.call-time {
  font-size: 14px;
  opacity: 0.8;
}

.call-video {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.video-placeholder {
  width: 80%;
  height: 70%;
  background: #222;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #aaa;
}

.call-controls {
  display: flex;
  justify-content: center;
  gap: 30px;
  padding: 25px;
}

.call-controls button {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  border: none;
  font-size: 20px;
  cursor: pointer;
}

.call-controls button i {
  color: #fff;
}

.call-controls .end-call {
  background: #ff2e44;
}

.call-controls button:not(.end-call) {
  background: #333;
}
.incoming-call {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.85);
  z-index: 7000;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
}

.incoming-actions {
  display: flex;
  gap: 20px;
  margin-top: 20px;
}

.incoming-actions .accept {
  background: #4caf50;
  color: white;
  padding: 14px 22px;
  border-radius: 50%;
}

.incoming-actions .reject {
  background: #f44336;
  color: white;
  padding: 14px 22px;
  border-radius: 50%;
}

/* ================= WHATSAPP-LIKE INCOMING CALL ================= */

.incoming-call {
  position: fixed;
  inset: 0;
  background: radial-gradient(circle at center, #111 0%, #000 70%);
  z-index: 8000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.incoming-card {
  text-align: center;
  color: white;
  animation: fadeInScale 0.25s ease;
}

.incoming-avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 18px;
  box-shadow: 0 0 0 6px rgba(255,255,255,0.05);
}

.incoming-name {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 6px;
}

.incoming-type {
  font-size: 14px;
  opacity: 0.7;
  margin-bottom: 40px;
}

/* CALL BUTTONS */
.incoming-actions {
  display: flex;
  gap: 60px;
  justify-content: center;
}

.incoming-actions button {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  border: none;
  font-size: 26px;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Reject */
.incoming-actions .reject {
  background: #f44336;
  transform: rotate(135deg);
}

/* Accept */
.incoming-actions .accept {
  background: #4caf50;
  animation: pulse 1.6s infinite;
}

/* Animations */
@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(76,175,80,0.6); }
  70% { box-shadow: 0 0 0 20px rgba(76,175,80,0); }
  100% { box-shadow: 0 0 0 0 rgba(76,175,80,0); }
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.92);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.upgrade-box {
  width: 100%;
  text-align: center;
}

.upgrade-btn {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #ff416c, #ff4b2b);
  color: white!important;
  font-weight: 600!important;
  border-radius: 25px;
  border: none;
  font-size: 16px;
}
</style>
