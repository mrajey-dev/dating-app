<template>
  <div class="notification-container">
    <div class="header">Notifications</div>

    <div v-if="notifications.length === 0" class="empty">
      No notifications yet
    </div>

    <div
      v-for="notification in notifications"
      :key="notification.id"
      class="notification-item"
      :class="{ unread: !notification.read_at }"
    >
<div class="avatar">
<img :src="getProfilePhoto(notification.user?.profile_photo)" alt="avatar" />


</div>



      <!-- Content -->
      <div class="content">
        <div class="message">
          {{ notification.message }}
          <span class="time">
            • {{ timeAgo(notification.created_at) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// import axios from "axios";
import moment from "moment";
import axios from "@/axios";

export default {
  data() {
    return {
      notifications: [],
      defaultAvatar: "/banner.png", // keep inside public folder
    };
  },

  mounted() {
    this.fetchNotifications();
  },

  methods: {
     getProfilePhoto(filename) {
    if (!filename) return this.defaultAvatar;
    return `https://companion.ajaywatpade.in/dating-backend/public/storage/profiles/${filename}`;
  },
  async fetchNotifications() {
  try {
    const res = await axios.get("/api/notifications", {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    });

    console.log("API Response:", res.data); // 👈 ADD THIS

    this.notifications = res.data.notifications || [];

  } catch (error) {
    console.error("Error fetching notifications:", error);
  }
},


    timeAgo(time) {
      return moment(time).fromNow();
    },
  },
};

</script>
<style scoped>

.notification-container {
    margin-top: -63px;
  max-width: 500px;
  background: #ffffff;
  color: #ffffff;
  min-height: 100vh;
}

.header {
  font-size: 20px;
  font-weight: 600;
  padding: 16px;
  border-bottom: 1px solid #262626;
}

.notification-item {
  display: flex;
  align-items: center;
  padding: 14px 16px;
  border-bottom: 1px solid #ffffff5c;
  cursor: pointer;
  transition: background 0.2s ease;
}



.notification-item.unread {
  background: #f10265;
}

.avatar img {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
}

.content {
  margin-left: 12px;
  flex: 1;
}

.message {
  font-size: 14px;
  line-height: 1.4;
}

.time {
  font-size: 12px;
  color: #fffdfd;
  margin-left: 6px;
}

.empty {
  text-align: center;
  padding: 60px 20px;
  color: #ffffff;
  font-size: 14px;
}
</style>