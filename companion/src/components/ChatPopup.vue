<template>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <div v-if="showChat && person && canChat" class="chat-fullscreen">


    <!-- Header -->
    <div class="chat-header">
      <div class="chat-user" @click="openUserProfile">
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

      <!-- 3 Dots Menu -->
      <!-- 3 Dots Menu -->
<div class="chat-menu">
  <span class="menu-icon" @click="toggleMenu">⋮</span>
  <div v-if="showMenu" class="menu-dropdown">
    <div class="menu-item" @click="openWallpaperSelector">
      Change Wallpaper
    </div>
    <div class="menu-item delete-chat" @click="confirmClearChat">
      Clear All Chat
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
            <button class="reject" @click="rejectCall">
              <i class="fa fa-phone"></i>
            </button>
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
      <!-- Reply Preview Bar (WhatsApp style at bottom) -->
      <div v-if="replyToMessage" class="reply-preview-bar">
        <div class="reply-preview-content">
          <div class="reply-preview-line"></div>
          <div class="reply-preview-details">
            <div class="reply-preview-label">
              <i class="fa fa-reply"></i>
              <span>{{ replyToMessage.type === 'sender' ? 'You' : localPerson.first_name }}</span>
            </div>
            <div class="reply-preview-text">
              {{ replyToMessage.text || '📷 Image' }}
            </div>
          </div>
          <button class="cancel-reply-btn" @click="cancelReply">
            <i class="fa fa-times"></i>
          </button>
        </div>
      </div>

      <div
        v-for="(msg, index) in messages"
        :key="index"
        :class="['chat-bubble', msg.type === 'sender' ? 'sent' : 'received']"
        @touchstart="handleMessageLongPress(msg, $event)"
        @touchend="clearLongPress"
        @touchcancel="clearLongPress"
        @contextmenu="handleMessageContextMenu(msg, $event)"
      >
        <!-- Reply Quote (WhatsApp style) -->
        <div v-if="msg.reply_to" class="message-reply-quote" :class="msg.type === 'sender' ? 'sent-quote' : 'received-quote'">
          <div class="quote-line"></div>
          <div class="quote-content">
            <div class="quote-sender">
              {{ msg.reply_to.sender_name === 'You' ? 'You' : msg.reply_to.sender_name }}
            </div>
            <div class="quote-text">
              {{ msg.reply_to.text || '📷 Image' }}
            </div>
          </div>
        </div>

        <div class="chat-text">
          <!-- Text message -->
          <p v-if="msg.text">{{ msg.text }}</p>
          
          <!-- Image message -->
          <img v-if="msg.image" :src="msg.image" class="chat-image" @click="previewImage(msg.image)" />
        </div>
        
        <div class="chat-time">
          {{ formatTime(msg.created_at) }}
          
          <span v-if="msg.type === 'sender'" class="tick">
            <i v-if="!msg.delivered && !msg.seen" class="fa fa-check-circle"></i>
            <i v-else-if="msg.delivered && !msg.seen" class="fa fa-check-circle" style="color: grey;"></i>
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
      <div class="menu-item reply" @click="replyToSelectedMessage">
        <i class="fa fa-reply"></i>
        Reply
      </div>
      <div class="menu-item delete" @click="deleteMessage">
        <i class="fa fa-trash"></i>
        Delete
      </div>
    </div>

    <!-- ================= CHAT FOOTER ================= -->
    <div class="chat-footer">
      <!-- Emoji Button -->
      <button class="emoji-btn" @click="toggleEmojiPicker">😊</button>

      <!-- Image Button -->
      <button class="image-btn" @click="triggerImageUpload">📷</button>
      <input 
        type="file" 
        ref="imageInput" 
        accept="image/*" 
        style="display: none" 
        @change="sendImage"
      />

      <!-- Message Input -->
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
      <div v-if="isPlanOver" class="upgrade-container">
        <button class="upgrade-btn" @click="goToUpgrade">
          <span class="rocket">🚀</span>
          <span>Upgrade to Premium</span>
          <span class="premium-badge">⭐</span>
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
    
    <!-- Image Preview Modal -->
    <div v-if="showImagePreview" class="image-preview-modal" @click="closeImagePreview">
      <img :src="previewImageUrl" class="preview-image" />
    </div>
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
    canChat: { type: Boolean, default: false },
  },
  emits: ["close", "openProfile"],
  data() {
    return {
      isPlanOver: null,
      iceQueue: [],
      isInputActive: false,
      incomingCallData: null,
      showIncomingCall: false,
      peer: null,
      localStream: null,
      remoteStream: null,
      callActive: false,
      callMuted: false,
      callTimer: null,
      callDuration: 0,
      showEmojiPicker: false,
      emojis: ["😀","😂","😍","😎","🥰","😢","😡","👍","🙏","💖","🎉","🔥","✨","💀","👀","🐶","🐱","🍕","🍻","⚽","🏀"],
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
        "https://i.pinimg.com/1200x/b8/22/48/b82248f62d8f79081b8fc7558e054274.jpg",
        "https://i.pinimg.com/736x/15/ea/eb/15eaeb8bf1b51f6f8e171e5fe0e4cc55.jpg",
        "https://i.pinimg.com/736x/39/4f/66/394f66f401a5b3f5017e906f925f1b89.jpg",
        "https://i.pinimg.com/736x/28/89/42/288942effac549ef20ed90e5fe04efdb.jpg"
      ],
      defaultAvatar,
      messages: [],
      newMessage: "",
      refreshInterval: null,
      scrollAtBottom: true,
      replyToMessage: null,
      imageDB: null,
      showImagePreview: false,
      previewImageUrl: '',
      signalInterval: null,
      statusInterval: null,
      typingInterval: null,
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
        // this.showCustomToast(`User is now ${status}`);
      }
    },
    showChat(newVal) {
      if (newVal) this.startChat();
      else this.stopChat();
    },
    messages: {
      handler() {
        this.$nextTick(() => {
          if (this.scrollAtBottom) {
            this.scrollToBottom(true);
          }
        });
      },
      deep: true
    }
  },
  async mounted() {
    await this.initImageDatabase();
    this.startOnlinePing();
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
     confirmClearChat() {
    // Close the menu first
    this.showMenu = false;
    
    // Show confirmation dialog
    const confirmed = confirm(
      `⚠️ Clear all messages with ${this.localPerson.first_name}?\n\n` 
    );
    
    if (confirmed) {
      this.clearAllChat();
    }
  },
  
  async clearAllChat() {
    try {
      const token = localStorage.getItem("token");
      
      // Show loading toast
      this.showCustomToast("Clearing chat history...");
      
      // 1. Delete messages from server
      await axios.delete(
        `https://companion.ajaywatpade.in/api/messages/clear/${this.person.id}`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      
      // 2. Clear local messages array
      this.messages = [];
      
      // 3. Delete all images from IndexedDB for this conversation
      const conversationId = `${Math.min(Number(this.userId), Number(this.person.id))}_${Math.max(Number(this.userId), Number(this.person.id))}`;
      
      if (this.imageDB) {
        // Delete all messages from local storage
        const transaction = this.imageDB.transaction(['messages'], 'readwrite');
        const store = transaction.objectStore('messages');
        const index = store.index('conversationId');
        const request = index.getAllKeys(conversationId);
        
        request.onsuccess = () => {
          const keys = request.result;
          keys.forEach(key => {
            store.delete(key);
          });
        };
        
        // Delete all images from local storage
        const imageTransaction = this.imageDB.transaction(['images'], 'readwrite');
        const imageStore = imageTransaction.objectStore('images');
        const imageIndex = imageStore.index('conversationId');
        const imageRequest = imageIndex.getAllKeys(conversationId);
        
        imageRequest.onsuccess = () => {
          const imageKeys = imageRequest.result;
          imageKeys.forEach(key => {
            // Revoke blob URLs before deleting
            const getRequest = imageStore.get(key);
            getRequest.onsuccess = () => {
              if (getRequest.result && getRequest.result.url) {
                URL.revokeObjectURL(getRequest.result.url);
              }
              imageStore.delete(key);
            };
            getRequest.onerror = () => {
              imageStore.delete(key);
            };
          });
        };
      }
      
      // 4. Show success message
      this.showCustomToast("Chat cleared!");
      
      // 5. Scroll to top
      this.$nextTick(() => {
        const container = this.$refs.chatBody;
        if (container) {
          container.scrollTop = 0;
        }
      });
      
    } catch (err) {
      console.error("Failed to clear chat:", err);
      
      if (err.response?.status === 403) {
        this.showCustomToast("You don't have permission to clear this chat");
      } else if (err.response?.status === 404) {
        // If server endpoint doesn't exist, fall back to local-only clear
        this.clearChatLocally();
      } else {
        this.showCustomToast("Failed to clear chat. Please try again.");
      }
    }
  },
  
  // Fallback method for local-only clearing (if server endpoint isn't ready)
  async clearChatLocally() {
    try {
      const conversationId = `${Math.min(Number(this.userId), Number(this.person.id))}_${Math.max(Number(this.userId), Number(this.person.id))}`;
      
      // Clear local messages array
      this.messages = [];
      
      // Delete all messages from local storage
      if (this.imageDB) {
        const transaction = this.imageDB.transaction(['messages'], 'readwrite');
        const store = transaction.objectStore('messages');
        const index = store.index('conversationId');
        const request = index.getAllKeys(conversationId);
        
        request.onsuccess = () => {
          const keys = request.result;
          keys.forEach(key => {
            store.delete(key);
          });
        };
        
        // Delete all images
        const imageTransaction = this.imageDB.transaction(['images'], 'readwrite');
        const imageStore = imageTransaction.objectStore('images');
        const imageIndex = imageStore.index('conversationId');
        const imageRequest = imageIndex.getAllKeys(conversationId);
        
        imageRequest.onsuccess = () => {
          const imageKeys = imageRequest.result;
          imageKeys.forEach(key => {
            const getRequest = imageStore.get(key);
            getRequest.onsuccess = () => {
              if (getRequest.result && getRequest.result.url) {
                URL.revokeObjectURL(getRequest.result.url);
              }
              imageStore.delete(key);
            };
            getRequest.onerror = () => {
              imageStore.delete(key);
            };
          });
        };
      }
      
      this.showCustomToast("Chat cleared locally!");
      
    } catch (err) {
      console.error("Failed to clear chat locally:", err);
      this.showCustomToast("Failed to clear chat");
    }
  },
    previewImage(imageUrl) {
      this.previewImageUrl = imageUrl;
      this.showImagePreview = true;
    },
    closeImagePreview() {
      this.showImagePreview = false;
      this.previewImageUrl = '';
    },
    
    // Initialize IndexedDB for local image storage
    async initImageDatabase() {
      return new Promise((resolve, reject) => {
        const request = indexedDB.open('ChatImagesDB', 1);
        
        request.onerror = (event) => {
          console.error('IndexedDB error:', event.target.error);
          reject(event.target.error);
        };
        
        request.onsuccess = (event) => {
          this.imageDB = event.target.result;
          console.log('Image database opened successfully');
          resolve();
        };
        
        request.onupgradeneeded = (event) => {
          const db = event.target.result;
          
          // Create object store for images
          if (!db.objectStoreNames.contains('images')) {
            const store = db.createObjectStore('images', { keyPath: 'id' });
            store.createIndex('conversationId', 'conversationId', { unique: false });
            store.createIndex('timestamp', 'timestamp', { unique: false });
          }
          
          // Create object store for message metadata
          if (!db.objectStoreNames.contains('messages')) {
            const messageStore = db.createObjectStore('messages', { keyPath: 'id' });
            messageStore.createIndex('conversationId', 'conversationId', { unique: false });
            messageStore.createIndex('timestamp', 'timestamp', { unique: false });
          }
        };
      });
    },

    // Save image to IndexedDB
    async saveImageToLocal(imageBlob, messageId, conversationId) {
      if (!this.imageDB) {
        console.error('Image database not initialized');
        return null;
      }
      
      return new Promise((resolve, reject) => {
        const transaction = this.imageDB.transaction(['images'], 'readwrite');
        const store = transaction.objectStore('images');
        
        const imageData = {
          id: String(messageId),
          conversationId: conversationId,
          blob: imageBlob,
          url: URL.createObjectURL(imageBlob),
          timestamp: Date.now()
        };
        
        const request = store.put(imageData);
        
        request.onsuccess = () => {
          console.log('Image saved locally:', messageId);
          resolve(imageData.url);
        };
        
        request.onerror = (event) => {
          console.error('Failed to save image:', event.target.error);
          reject(event.target.error);
        };
      });
    },

    // Get image from IndexedDB
    async getImageFromLocal(messageId) {
      if (!this.imageDB) return null;
      
      return new Promise((resolve, reject) => {
        const transaction = this.imageDB.transaction(['images'], 'readonly');
        const store = transaction.objectStore('images');
        const request = store.get(String(messageId));
        
        request.onsuccess = () => {
          if (request.result) {
            resolve(request.result.url);
          } else {
            resolve(null);
          }
        };
        
        request.onerror = (event) => {
          reject(event.target.error);
        };
      });
    },

    // Delete image from local storage
    async deleteImageFromLocal(messageId) {
      if (!this.imageDB) return;
      
      return new Promise((resolve, reject) => {
        const transaction = this.imageDB.transaction(['images'], 'readwrite');
        const store = transaction.objectStore('images');
        
        const getRequest = store.get(String(messageId));
        getRequest.onsuccess = () => {
          if (getRequest.result && getRequest.result.url) {
            URL.revokeObjectURL(getRequest.result.url);
          }
          
          const deleteRequest = store.delete(String(messageId));
          deleteRequest.onsuccess = () => {
            console.log('Image deleted locally:', messageId);
            resolve();
          };
          deleteRequest.onerror = (event) => {
            reject(event.target.error);
          };
        };
        getRequest.onerror = (event) => {
          reject(event.target.error);
        };
      });
    },

    // Save message metadata locally
    async saveMessageLocally(message) {
      if (!this.imageDB) return;
      
      return new Promise((resolve, reject) => {
        const transaction = this.imageDB.transaction(['messages'], 'readwrite');
        const store = transaction.objectStore('messages');
        
        const messageData = {
          ...message,
          conversationId: `${Math.min(Number(message.sender_id), Number(message.receiver_id))}_${Math.max(Number(message.sender_id), Number(message.receiver_id))}`,
          timestamp: Date.now()
        };
        
        const request = store.put(messageData);
        request.onsuccess = () => resolve();
        request.onerror = (event) => reject(event.target.error);
      });
    },

    // Load messages from local storage first
    async loadMessagesFromLocal(conversationId) {
      if (!this.imageDB) return [];
      
      return new Promise((resolve, reject) => {
        const transaction = this.imageDB.transaction(['messages'], 'readonly');
        const store = transaction.objectStore('messages');
        const index = store.index('conversationId');
        const request = index.getAll(conversationId);
        
        request.onsuccess = async () => {
          const messages = request.result || [];
          
          for (const msg of messages) {
            if (msg.image && !msg.image.startsWith('blob:') && !msg.image.startsWith('http')) {
              const imageUrl = await this.getImageFromLocal(msg.id);
              if (imageUrl) {
                msg.image = imageUrl;
              }
            }
          }
          
          resolve(messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at)));
        };
        
        request.onerror = (event) => reject(event.target.error);
      });
    },

    triggerImageUpload() {
      this.$refs.imageInput.click();
    },

async sendImage(event) {
  const file = event.target.files[0];
  if (!file) return;
  
  if (!this.person?.id) {
    console.warn("Receiver ID missing");
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    this.showCustomToast("Image size should be less than 5MB");
    event.target.value = '';
    return;
  }

  if (!file.type.startsWith('image/')) {
    this.showCustomToast("Please select a valid image file");
    event.target.value = '';
    return;
  }

  const tempId = `temp_${Date.now()}_${Math.random()}`;
  
  try {
    const token = localStorage.getItem("token");
    
    // Create local blob URL for immediate display
    const localImageUrl = URL.createObjectURL(file);
    
    const newMsg = {
      id: tempId,
      sender_id: Number(this.userId),
      receiver_id: this.person.id,
      text: "",
      image: localImageUrl,
      type: "sender",
      seen: false,
      delivered: false,
      created_at: new Date().toISOString(),
      is_local: true,
      reply_to: this.replyToMessage ? {
        id: this.replyToMessage.id,
        text: this.replyToMessage.text || '📷 Image',
        sender_name: this.replyToMessage.type === 'sender' ? 'You' : this.localPerson.first_name
      } : null
    };

    // Add to UI immediately
    this.messages.push(newMsg);
    this.scrollToBottom(true);
    
    // Save temporary message locally
    await this.saveMessageLocally(newMsg);
    
    // Prepare form data for server
    const formData = new FormData();
    formData.append("receiver_id", this.person.id);
    formData.append("image", file);
    
    if (this.replyToMessage) {
      formData.append("reply_to_id", this.replyToMessage.id);
    }

    // Send to server
    const res = await axios.post(
      "https://companion.ajaywatpade.in/api/messages/image",
      formData,
      { 
        headers: { 
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        } 
      }
    );

    console.log("Server response:", res.data); // Debug log
    
    const serverImageUrl = res.data.image;
    
    const msgIndex = this.messages.findIndex(m => m.id === tempId);
    if (msgIndex !== -1) {
      const updatedMsg = {
        ...this.messages[msgIndex],
        id: res.data.id,
        image: serverImageUrl, // Use server URL
        is_local: false,
        delivered: true
      };
      
      this.messages[msgIndex] = updatedMsg;
      
      // Save to local storage
      await this.saveMessageLocally(updatedMsg);
      
      // Remove temporary message from storage
      if (this.imageDB) {
        const transaction = this.imageDB.transaction(['messages'], 'readwrite');
        const store = transaction.objectStore('messages');
        store.delete(tempId);
      }
    }
    
    this.cancelReply();
    this.showCustomToast("Image sent successfully");

  } catch (err) {
    console.error("Failed to send image:", err);
    
    // Remove the temporary message on failure
    const msgIndex = this.messages.findIndex(m => m.id === tempId);
    if (msgIndex !== -1) {
      // Revoke the blob URL to prevent memory leaks
      if (this.messages[msgIndex].image && this.messages[msgIndex].image.startsWith('blob:')) {
        URL.revokeObjectURL(this.messages[msgIndex].image);
      }
      this.messages.splice(msgIndex, 1);
      
      // Remove from local storage
      if (this.imageDB) {
        const transaction = this.imageDB.transaction(['messages'], 'readwrite');
        const store = transaction.objectStore('messages');
        store.delete(tempId);
      }
    }
    
    if (err.response?.status === 403) {
      this.isPlanOver = true;
      this.showCustomToast(err.response?.data?.message || "Message limit exceeded. Please upgrade to continue sending images 🚀");
    } else {
      this.showCustomToast(err.response?.data?.message || "Failed to send image. Please try again.");
    }
  }
  
  // Clear the input
  event.target.value = '';
},
async checkUserPlanForReceiver(receiverId) {
  try {
    const token = localStorage.getItem("token");
    const gender = localStorage.getItem("gender");

    if (gender === "female") {
      return { hasPlan: true, plan: 'free' };
    }

    const res = await axios.get(
      `https://companion.ajaywatpade.in/api/user/plan-status/${receiverId}`,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    return { hasPlan: !res.data.is_plan_over, plan: res.data.plan };
  } catch (err) {
    console.error("Failed to check plan status", err);
    return { hasPlan: false, plan: null };
  }
},
    fileToBlob(file) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
          fetch(e.target.result)
            .then(res => res.blob())
            .then(blob => resolve(blob))
            .catch(reject);
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
      });
    },

    openUserProfile() {
      this.$emit("openProfile", this.person);
    },

    handleMessageLongPress(msg, event) {
      if (this.longPressTimer) {
        clearTimeout(this.longPressTimer);
      }
      
      this.longPressTimer = setTimeout(() => {
        this.selectedMessage = msg;
        this.showMessageMenu = true;
        
        let x = 0, y = 0;
        
        if (event.touches && event.touches.length > 0) {
          x = event.touches[0].clientX;
          y = event.touches[0].clientY;
        } else {
          x = event.clientX;
          y = event.clientY;
        }
        
        const menuWidth = 150;
        const menuHeight = 80;
        
        this.menuX = Math.min(x, window.innerWidth - menuWidth - 10);
        this.menuY = Math.min(y, window.innerHeight - menuHeight - 10);
      }, 500);
    },

    handleMessageContextMenu(msg, event) {
      event.preventDefault();
      this.selectedMessage = msg;
      this.showMessageMenu = true;
      this.menuX = event.clientX;
      this.menuY = event.clientY;
      
      const menuWidth = 150;
      const menuHeight = 80;
      
      if (this.menuX + menuWidth > window.innerWidth) {
        this.menuX = window.innerWidth - menuWidth - 10;
      }
      if (this.menuY + menuHeight > window.innerHeight) {
        this.menuY = window.innerHeight - menuHeight - 10;
      }
    },

    clearLongPress() {
      if (this.longPressTimer) {
        clearTimeout(this.longPressTimer);
        this.longPressTimer = null;
      }
    },

    replyToSelectedMessage() {
      this.replyToMessage = {
        ...this.selectedMessage,
        sender_name: this.selectedMessage.type === 'sender' ? 'You' : this.localPerson.first_name
      };
      this.showMessageMenu = false;
      this.selectedMessage = null;
      
      this.$nextTick(() => {
        if (this.$refs.chatInput) {
          this.$refs.chatInput.focus();
        }
      });
    },

    cancelReply() {
      this.replyToMessage = null;
    },

    async deleteMessage() {
      if (!this.selectedMessage || !this.selectedMessage.id) return;
      
      try {
        const token = localStorage.getItem("token");
        const confirmed = confirm("Delete this message?");
        if (!confirmed) {
          this.showMessageMenu = false;
          return;
        }
        
        // Delete from server if it's not a local-only message
        if (!this.selectedMessage.is_local && this.selectedMessage.id.toString().indexOf('temp_') === -1) {
          await axios.delete(
            `https://companion.ajaywatpade.in/api/messages/${this.selectedMessage.id}`,
            { headers: { Authorization: `Bearer ${token}` } }
          );
        }
        
        // Delete image from local storage if it's an image message
        if (this.selectedMessage.image) {
          // Revoke blob URL if it's a blob
          if (this.selectedMessage.image.startsWith('blob:')) {
            URL.revokeObjectURL(this.selectedMessage.image);
          }
          await this.deleteImageFromLocal(this.selectedMessage.id);
        }
        
        // Delete message from local storage
        if (this.imageDB) {
          const transaction = this.imageDB.transaction(['messages'], 'readwrite');
          const store = transaction.objectStore('messages');
          store.delete(String(this.selectedMessage.id));
        }
        
        // Remove from UI
        this.messages = this.messages.filter(
          m => m.id !== this.selectedMessage.id
        );
        
        this.showCustomToast("Message deleted successfully");
        this.showMessageMenu = false;
        this.selectedMessage = null;
        
      } catch (err) {
        console.error("Delete failed:", err);
        if (err.response?.status === 403) {
          this.showCustomToast("You can only delete your own messages");
        } else {
          this.showCustomToast("Failed to delete message");
        }
        this.showMessageMenu = false;
      }
    },

 async checkPlanStatus() {
  try {
    const token = localStorage.getItem("token");
    const gender = localStorage.getItem("gender");

    // Women always have free access
    if (gender === "female") {
      this.isPlanOver = false;
      return false;
    }

    // Check plan status for men
    const res = await axios.get(
      "https://companion.ajaywatpade.in/api/user/plan-status",
      { headers: { Authorization: `Bearer ${token}` } }
    );

    this.isPlanOver = res.data.is_plan_over;
    return this.isPlanOver;

  } catch (err) {
    console.error("Failed to check plan status", err);
    // Default to false to allow functionality
    this.isPlanOver = false;
    return false;
  }
},
    
    goToUpgrade() {
      this.$router.push({
        path: '/upgrade',
        query: { targetUserId: this.person.id }
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
        const offer = new RTCSessionDescription({
          type: offerData.type,
          sdp: offerData.sdp
        });

        await this.initMedia();
        this.createPeer();
        await this.peer.setRemoteDescription(offer);
        
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
      this.callActive = false;
      this.sendSignal("reject", {});
    },
    
    incomingCall(signal) {
      this.incomingCallData = signal;
      this.showIncomingCall = true;
    },
    
    async fetchSignals() {
      try {
        const token = localStorage.getItem("token");
        const res = await axios.get(
          `https://companion.ajaywatpade.in/api/call/signals`,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        res.data.forEach(signal => {
          if (signal.type === "offer") this.incomingCall(signal);
          if (signal.type === "answer") this.handleAnswer(signal.data);
          if (signal.type === "ice") this.handleIce(signal.data);
          if (signal.type === "end-call") {
            this.onRemoteEndCall();
          }
        });
      } catch (err) {
        console.error("Fetch signals error:", err);
      }
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

      this.localStream.getTracks().forEach(track => {
        this.peer.addTrack(track, this.localStream);
      });

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
      if (this.$refs.chatInput) this.$refs.chatInput.blur();
      this.callActive = true;
      this.openCallUI();
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
      }).catch(err => console.error("Send signal error:", err));
    },

    openCallUI() {
      if (this.callTimer) return;
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

    handleScroll() {
      const el = this.$refs.chatBody;
      if (!el) return;
      this.scrollAtBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 20;
    },
    
    toggleEmojiPicker() {
      this.showEmojiPicker = !this.showEmojiPicker;
    },

    addEmoji(emoji) {
      this.newMessage += emoji;
      this.showEmojiPicker = false;
      this.$nextTick(() => {
        if (this.$refs.chatInput) this.$refs.chatInput.focus();
      });
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
          { headers: { Authorization: `Bearer ${token}` } }
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

      clearTimeout(this.typingTimer);
      this.typingTimer = setTimeout(() => {
        this.stopTyping();
      }, 1500);
    },
    
    startStatusRefresh() {
      this.statusInterval = setInterval(async () => {
        try {
          const token = localStorage.getItem("token");
          const res = await axios.get(
            `https://companion.ajaywatpade.in/api/users/${this.person.id}`,
            { headers: { Authorization: `Bearer ${token}` } }
          );
          if (res.data?.user?.last_active_at) {
            this.localPerson.last_active_at = new Date(res.data.user.last_active_at).toISOString();
          }
        } catch (err) {
          console.error("Failed to fetch user status:", err);
        }
      }, 5000);
    },

    stopStatusRefresh() {
      if (this.statusInterval) clearInterval(this.statusInterval);
    },

    startOnlinePing() {
      const token = localStorage.getItem("token");
      this.onlineInterval = setInterval(async () => {
        try {
          await axios.post(
            "https://companion.ajaywatpade.in/api/users/last-active",
            {},
            { headers: { Authorization: `Bearer ${token}` } }
          );
        } catch (err) {
          console.error("Ping failed", err);
        }
      }, 3000);
    },

    stopOnlinePing() {
      if (this.onlineInterval) clearInterval(this.onlineInterval);
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

    closeWallpaper() {
      this.showWallpaperModal = false;
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
      this.allowAutoFocus = false;
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
      if (isToday) return timeStr;
      const dateStr = date.toLocaleDateString([], { day: 'numeric', month: 'short' });
      return `${dateStr}, ${timeStr}`;
    },

    closeChat() {
      this.$emit("close");
    },

    async startChat() {
      await this.checkPlanStatus(); 
      this.localPerson = { ...this.person };
      await this.fetchMessages();
      await this.markMessagesSeen();
      this.scrollToBottom(false);
      this.typingInterval = setInterval(this.checkTyping, 2000);
      if (!this.refreshInterval) {
        this.refreshInterval = setInterval(this.fetchMessages, 3000);
      }
      if (!this.signalInterval) {
        this.signalInterval = setInterval(this.fetchSignals, 1000);
      }
      this.startOnlinePing();
      this.startStatusRefresh();
    },

    stopChat() {
      if (this.refreshInterval) clearInterval(this.refreshInterval);
      if (this.typingInterval) clearInterval(this.typingInterval);
      if (this.signalInterval) clearInterval(this.signalInterval);
      this.refreshInterval = null;
      this.typingInterval = null;
      this.signalInterval = null;
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
    const conversationId = `${Math.min(Number(this.userId), Number(this.person.id))}_${Math.max(Number(this.userId), Number(this.person.id))}`;
    
    // Load from local storage first for instant display
    const localMessages = await this.loadMessagesFromLocal(conversationId);
    
    if (localMessages.length > 0 && this.messages.length === 0) {
      this.messages = localMessages;
    }
    
    // Fetch from server to sync
    const res = await axios.get(
      `https://companion.ajaywatpade.in/api/messages/${this.person.id}`,
      { headers: { Authorization: `Bearer ${token}` } }
    );
    
    const serverMessages = Array.isArray(res.data) ? res.data : [];
    await this.mergeMessages(serverMessages, conversationId);
    
    if (!this.showWallpaperModal) {
      this.focusInput();
    }
    
  } catch (err) {
    console.error("Failed to fetch messages:", err);
  }
},

async mergeMessages(serverMessages, conversationId) {
  for (const serverMsg of serverMessages) {
    const existingMsg = this.messages.find(m => String(m.id) === String(serverMsg.id));
    
    if (!existingMsg) {
      let imageUrl = null;
      
      if (serverMsg.image) {
        // Fix image URL if needed
        imageUrl = this.fixImageUrl(serverMsg.image);
        
        // Cache image locally in background
        if (imageUrl && imageUrl.startsWith('http')) {
          this.cacheImageInBackground(imageUrl, serverMsg.id, conversationId);
        }
      }
      
      const processedMsg = {
        id: serverMsg.id,
        sender_id: Number(serverMsg.sender_id),
        receiver_id: Number(serverMsg.receiver_id),
        text: (serverMsg.text ?? "").trim(),
        image: imageUrl,
        type: serverMsg.type === 'sent' ? 'sender' : serverMsg.type,
        created_at: serverMsg.created_at,
        seen: Boolean(serverMsg.seen),
        delivered: Boolean(serverMsg.delivered),
        reply_to: serverMsg.reply_to ? {
          id: serverMsg.reply_to.id,
          text: serverMsg.reply_to.text || '📷 Image',
          sender_name: serverMsg.reply_to.sender_name || 
                      (serverMsg.reply_to.sender_id === Number(this.userId) ? 'You' : this.localPerson?.first_name || 'User')
        } : null
      };
      
      await this.saveMessageLocally(processedMsg);
      this.messages.push(processedMsg);
    } else {
      // Update existing message status
      let needsUpdate = false;
      if (serverMsg.seen && !existingMsg.seen) {
        existingMsg.seen = true;
        needsUpdate = true;
      }
      if (serverMsg.delivered && !existingMsg.delivered) {
        existingMsg.delivered = true;
        needsUpdate = true;
      }
      if (needsUpdate) {
        await this.saveMessageLocally(existingMsg);
      }
    }
  }
  
  // Sort messages by timestamp
  this.messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
},

// Helper function to fix image URLs
fixImageUrl(url) {
  if (!url) return null;
  
  // If it's already a full URL, return as is
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url;
  }
  
  // If it's a relative path, convert to full URL
  if (url.startsWith('/storage/')) {
    return `https://companion.ajaywatpade.in${url}`;
  }
  
  if (url.startsWith('storage/')) {
    return `https://companion.ajaywatpade.in/${url}`;
  }
  
  return url;
},

    async mergeMessages(serverMessages, conversationId) {
      for (const serverMsg of serverMessages) {
        const existingMsg = this.messages.find(m => String(m.id) === String(serverMsg.id));
        
        if (!existingMsg) {
          let imageUrl = null;
          
          if (serverMsg.image) {
            // Check if image is already cached locally
            const cachedImage = await this.getImageFromLocal(serverMsg.id);
            if (cachedImage) {
              imageUrl = cachedImage;
            } else if (serverMsg.image.startsWith('http')) {
              // Download and cache the image from server in background
              this.cacheImageInBackground(serverMsg.image, serverMsg.id, conversationId);
              imageUrl = serverMsg.image; // Use server URL temporarily
            } else {
              imageUrl = serverMsg.image;
            }
          }
          
          const processedMsg = {
            id: serverMsg.id,
            sender_id: Number(serverMsg.sender_id),
            receiver_id: Number(serverMsg.receiver_id),
            text: (serverMsg.text ?? "").trim(),
            image: imageUrl,
            type: serverMsg.type === 'sent' ? 'sender' : serverMsg.type,
            created_at: serverMsg.created_at,
            seen: Boolean(serverMsg.seen),
            delivered: Boolean(serverMsg.delivered),
            reply_to: serverMsg.reply_to ? {
              id: serverMsg.reply_to.id,
              text: serverMsg.reply_to.text || '📷 Image',
              sender_name: serverMsg.reply_to.sender_name || 
                          (serverMsg.reply_to.sender_id === Number(this.userId) ? 'You' : this.localPerson?.first_name || 'User')
            } : null
          };
          
          await this.saveMessageLocally(processedMsg);
          this.messages.push(processedMsg);
        } else {
          // Update existing message status
          let needsUpdate = false;
          if (serverMsg.seen && !existingMsg.seen) {
            existingMsg.seen = true;
            needsUpdate = true;
          }
          if (serverMsg.delivered && !existingMsg.delivered) {
            existingMsg.delivered = true;
            needsUpdate = true;
          }
          if (needsUpdate) {
            await this.saveMessageLocally(existingMsg);
          }
        }
      }
      
      // Sort messages by timestamp
      this.messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    },
    
    async cacheImageInBackground(imageUrl, messageId, conversationId) {
      try {
        const response = await fetch(imageUrl);
        const blob = await response.blob();
        await this.saveImageToLocal(blob, messageId, conversationId);
        
        // Update the message image URL in UI
        const msgIndex = this.messages.findIndex(m => String(m.id) === String(messageId));
        if (msgIndex !== -1) {
          const cachedUrl = await this.getImageFromLocal(messageId);
          if (cachedUrl) {
            this.messages[msgIndex].image = cachedUrl;
          }
        }
      } catch (err) {
        console.error('Failed to cache image in background:', err);
      }
    },

  async sendMessage() {
  if (!this.canChat) {
    this.showCustomToast("You can send messages only after matching 💕");
    return;
  }

  // Check plan status before sending
  const gender = localStorage.getItem("gender");
  if (gender !== "female") {
    const isPlanOver = await this.checkPlanStatus();
    if (isPlanOver) {
      this.showCustomToast("Upgrade your plan to continue sending messages 🚀");
      return;
    }
  }

  const trimmedMessage = this.newMessage.trim();
  if (!trimmedMessage || !this.person?.id) return;

  const tempId = `temp_msg_${Date.now()}_${Math.random()}`;
  
  // Create temporary message for instant display
  const tempMsg = {
    id: tempId,
    sender_id: Number(this.userId),
    receiver_id: this.person.id,
    text: trimmedMessage,
    type: "sender",
    delivered: false,
    seen: false,
    created_at: new Date().toISOString(),
    is_local: true,
    reply_to: this.replyToMessage ? {
      id: this.replyToMessage.id,
      text: this.replyToMessage.text || '📷 Image',
      sender_name: this.replyToMessage.type === 'sender' ? 'You' : this.localPerson.first_name
    } : null
  };

  this.messages.push(tempMsg);
  this.newMessage = "";
  this.cancelReply();
  this.scrollToBottom(true);
  
  // Save temporarily
  await this.saveMessageLocally(tempMsg);

  try {
    const token = localStorage.getItem("token");
    
    const payload = {
      receiver_id: this.person.id,
      text: trimmedMessage
    };
    
    if (this.replyToMessage) {
      payload.reply_to_id = this.replyToMessage.id;
    }

    const res = await axios.post(
      "https://companion.ajaywatpade.in/api/messages",
      payload,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    // Update temporary message with real data
    const msgIndex = this.messages.findIndex(m => m.id === tempId);
    if (msgIndex !== -1) {
      const updatedMsg = {
        ...this.messages[msgIndex],
        id: res.data.id,
        is_local: false,
        delivered: true
      };
      this.messages[msgIndex] = updatedMsg;
      await this.saveMessageLocally(updatedMsg);
      
      // Remove temporary version from storage
      if (this.imageDB) {
        const transaction = this.imageDB.transaction(['messages'], 'readwrite');
        const store = transaction.objectStore('messages');
        store.delete(tempId);
      }
    }

  } catch (err) {
    console.error("Failed to send message:", err);
    
    // Remove temporary message on failure
    const msgIndex = this.messages.findIndex(m => m.id === tempId);
    if (msgIndex !== -1) {
      this.messages.splice(msgIndex, 1);
      if (this.imageDB) {
        const transaction = this.imageDB.transaction(['messages'], 'readwrite');
        const store = transaction.objectStore('messages');
        store.delete(tempId);
      }
    }
    
    if (err.response?.status === 403) {
      this.isPlanOver = true;
      this.showCustomToast("Your plan has expired. Please upgrade to continue 💕");
    } else {
      this.showCustomToast("Failed to send message. Please try again.");
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
    },

    cleanupLocalStorage() {
      if (this.imageDB) {
        const transaction = this.imageDB.transaction(['images'], 'readwrite');
        const store = transaction.objectStore('images');
        const index = store.index('timestamp');
        const thirtyDaysAgo = Date.now() - (30 * 24 * 60 * 60 * 1000);
        const range = IDBKeyRange.upperBound(thirtyDaysAgo);
        
        const request = index.openCursor(range);
        request.onsuccess = (event) => {
          const cursor = event.target.result;
          if (cursor) {
            if (cursor.value.url) {
              URL.revokeObjectURL(cursor.value.url);
            }
            cursor.delete();
            cursor.continue();
          }
        };
      }
    }
  },
  beforeUnmount() {
    this.stopChat();
    this.cleanupLocalStorage();
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

/* ================= HEADER ================= */
.chat-header {
  margin-top: 59px;
  display: flex;
  align-items: center;
  padding: 9px;
  color: rgb(16, 12, 14);
  background: #fafafa;
  border-bottom: 1px solid #ddd;
  flex-shrink: 0;
}

.chat-user {
  display: flex;
  align-items: center;
  margin-left: 10px;
  cursor: pointer;
  flex: 1;
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
  display: flex;
  flex-direction: column;
}

/* ================= MESSAGE BUBBLES ================= */
.chat-bubble {
  padding: 8px 12px;
  margin-bottom: 8px;
  max-width: 70%;
  word-wrap: break-word;
  display: inline-block;
  position: relative;
  transition: all 0.2s ease;
}

/* Sent (Right) */
.sent {
  align-self: flex-end;
  background-color: #075E54;
  color: #ffffff;
  border-radius: 16px 8px 16px 16px;
  overflow-wrap: anywhere;
}

/* Received (Left) */
.received {
  align-self: flex-start;
  background-color: #ffffff;
  color: #000;
  border-radius: 8px 16px 16px 16px;
  overflow-wrap: anywhere;
}

/* ================= WHATSAPP-STYLE REPLY QUOTE ================= */
.message-reply-quote {
  display: flex;
  gap: 8px;
  margin-bottom: 6px;
  padding: 6px 8px;
  border-radius: 8px;
  font-size: 12px;
  background: rgba(0, 0, 0, 0.05);
}

.sent-quote {
  background: rgba(255, 255, 255, 0.1);
}

.received-quote {
  background: rgba(0, 0, 0, 0.05);
}

.quote-line {
  width: 3px;
  background: #00a884;
  border-radius: 2px;
}

.quote-content {
  flex: 1;
  overflow: hidden;
}

.quote-sender {
  font-weight: 600;
  font-size: 11px;
  color: #00a884;
  margin-bottom: 2px;
}

.quote-text {
  font-size: 11px;
  opacity: 0.8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Reply Preview Bar */
.reply-preview-bar {
  background: #e8e8e8;
  border-radius: 12px 12px 8px 8px;
  margin: 0 8px 8px 8px;
  overflow: hidden;
  animation: slideUp 0.2s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.reply-preview-content {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  gap: 10px;
  background: #f0f0f0;
  border-left: 4px solid #00a884;
}

.reply-preview-details {
  flex: 1;
}

.reply-preview-label {
  font-size: 11px;
  font-weight: 600;
  color: #00a884;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.reply-preview-label i {
  font-size: 11px;
}

.reply-preview-text {
  font-size: 12px;
  color: #555;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cancel-reply-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px 8px;
  border-radius: 50%;
  transition: all 0.2s ease;
  color: #999;
}

.cancel-reply-btn:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #333;
}

/* ================= TIME + TICKS ================= */
.chat-time {
  font-size: 10px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 4px;
  margin-top: 4px;
  opacity: 0.7;
}

.tick {
  display: flex;
  align-items: center;
}

.tick i {
  font-size: 11px;
  margin-left: 2px;
  color: rgba(255, 255, 255, 0.6);
}

.tick .blue-tick {
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
  bottom: 0;
  left: 0;
  width: 100%;
  z-index: 1001;
  box-sizing: border-box;
  align-items: center;
  flex-shrink: 0;
}

.chat-footer input {
  flex: 1;
  padding: 8px 12px;
  border-radius: 12px;
  border: 1px solid #ccc;
  outline: none;
  font-size: 14px;
}

.chat-footer button {
  padding: 7px 5px;
  border-radius: 12px;
  border: none;
  color: rgb(2, 0, 1);
  cursor: pointer;
}

.emoji-btn {
  font-size: 22px;
  background: transparent;
  padding: 0 8px;
}

.image-btn {
  font-size: 22px;
  background: transparent;
  font-weight: bold;
  padding: 0 8px;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.image-btn:hover {
  transform: scale(1.1);
}

.send-btn {
  background: transparent;
  font-size: 20px;
  color: #075E54;
  padding: 0 8px;
}

.send-btn:disabled {
  color: #ccc;
  cursor: not-allowed;
}

/* ================= IMAGE PREVIEW MODAL ================= */
.image-preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  cursor: pointer;
}

.preview-image {
  max-width: 90%;
  max-height: 90%;
  object-fit: contain;
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
  z-index: 2000;
}

.menu-item {
  padding: 8px 16px;
  cursor: pointer;
  font-size: 14px;
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
  background: rgba(0, 0, 0, 0.3);
}

.message-menu {
  position: fixed;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.25);
  z-index: 3000;
  min-width: 160px;
  animation: fadeIn 0.15s ease-in-out;
  overflow: hidden;
}

.message-menu .menu-item {
  padding: 12px 16px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s ease;
  display: flex;
  align-items: center;
  gap: 10px;
}

.message-menu .menu-item:hover {
  background: #f5f5f5;
}

.message-menu .delete {
  color: #ff2e44;
}

.message-menu .reply {
  color: #075E54;
}

@keyframes fadeIn {
  from { 
    opacity: 0; 
    transform: scale(0.95); 
  }
  to { 
    opacity: 1; 
    transform: scale(1); 
  }
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

/* ================= WALLPAPER MODAL ================= */
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

/* ================= TYPING INDICATOR ================= */
.typing {
  color: #08570b;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* ================= EMOJI PICKER ================= */
.emoji-picker {
  position: absolute;
  bottom: 70px;
  left: 10px;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 10px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  max-width: 280px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  z-index: 1002;
}

.emoji-picker span {
  cursor: pointer;
  font-size: 22px;
  padding: 4px;
  transition: transform 0.1s ease;
}

.emoji-picker span:hover {
  transform: scale(1.2);
}

/* ================= IMAGE STYLES ================= */
.chat-image {
  max-width: 200px;
  max-height: 200px;
  border-radius: 12px;
  margin-top: 5px;
  cursor: pointer;
  object-fit: cover;
  transition: transform 0.2s ease;
}

.chat-image:hover {
  transform: scale(1.02);
}

.chat-status {
  font-size: 12px;
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

.incoming-actions .reject {
  background: #f44336;
  transform: rotate(135deg);
}

.incoming-actions .accept {
  background: #4caf50;
  animation: pulse 1.6s infinite;
}

/* ================= UPGRADE BUTTON ================= */
.upgrade-container {
  flex: 1;
  padding: 0 4px;
}

.upgrade-btn {
  width: 100%;
  padding: 12px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white !important;
  font-weight: bold !important;
  font-size: 14px;
  border-radius: 30px;
  border: none;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  white-space: nowrap;
}

.upgrade-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.upgrade-btn .rocket {
  display: inline-block;
  animation: float 1s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-3px); }
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

/* Responsive adjustments */
@media (max-width: 480px) {
  .chat-bubble {
    max-width: 85%;
  }
  
  .upgrade-btn {
    font-size: 12px;
    padding: 10px 12px;
  }
  
  .emoji-picker {
    max-width: 260px;
    bottom: 65px;
  }
  
  .reply-preview-bar {
    margin: 0 8px 8px 8px;
  }
  
  .reply-preview-text {
    max-width: 150px;
  }
  
  .chat-image {
    max-width: 150px;
    max-height: 150px;
  }
}

</style>