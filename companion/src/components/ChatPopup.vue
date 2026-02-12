<template>
  <div v-if="showChat && person" class="chat-fullscreen">
    <!-- Header -->
    <div class="chat-header">
      <span class="close" style="font-size: 22px;"  @click="closeChat">⬅</span>

      <div class="chat-user">
        <img :src="person.profile_photo || defaultAvatar" class="chat-avatar" />

        <div class="chat-user-info">
          <div class="chat-name-row">
            <span class="chat-name" style=" font-weight: 600!important;">{{ person.first_name || 'User' }}</span>
            <img
              v-if="person.verified_badge === 1"
              src="@/assets/verified1.png"
              class="chat-verified"
            />
          </div>
          <span class="chat-status">Online</span>
        </div>
      </div>
    </div>

    <!-- Chat Body -->
    <div class="chat-body">
      <div class="chat-bubble received">
        Hi {{ person.first_name || 'there' }} 😊  
        I came across your profile and felt we might vibe well.  
        Looking forward to getting to know you better 💕
      </div>

      <div class="chat-bubble sent">
        👋 Hey! Thanks for reaching out.  
        That’s really sweet — tell me more about yourself 🙂
      </div>
    </div>

    <!-- Chat Footer -->
    <div class="chat-footer">
      <input type="text" placeholder="Type your message..." disabled />
      <button disabled>Send</button>
    </div>

    <!-- Overlay -->
    <div class="overlay" @click="closeChat"></div>
  </div>
</template>

<script>
import defaultAvatar from '@/assets/user1.png'; // fallback avatar

export default {
  name: "ChatPopup",
  props: {
    person: {
      type: Object,
      default: () => ({}),
    },
    showChat: {
      type: Boolean,
      required: true,
    },
  },
  emits: ["close"],
  data() {
    return {
      defaultAvatar,
    };
  },
  methods: {
    closeChat() {
      this.$emit("close");
    },
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
  font-weight: 800!important;
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
}

.chat-bubble {
  padding: 10px 14px;
  border-radius: 16px;
  margin-bottom: 10px;
  max-width: 75%;
  word-wrap: break-word;
}

.received {
      border-radius: 0 16px 16px 16px;
  background: #ffffff;
  align-self: flex-start;
}

.sent {
      border-radius:  16px 0 16px 16px ;
     background: #f10265;
    align-self: flex-end;
    justify-self: right;
    color: white;
}

.chat-footer {
  display: flex;
  padding: 12px;
  border-top: 1px solid #ddd;
  gap: 8px;
  background: #fafafa;
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
</style>
