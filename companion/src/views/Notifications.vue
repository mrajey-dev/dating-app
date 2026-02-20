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
  <!-- Avatar -->
  <div class="avatar">
    <img
      :src="getProfilePhoto(notification.user?.profile_photo)"
      alt="avatar"
    />
  </div>

 <!-- Content -->
<div class="content">
 <div class="message">
  <template v-if="notification.type === 'match_request'">
    <span
      class="clickable-name"
      @click="openUserProfile(notification.user?.id)"
    >
      {{ notification.user?.first_name || 'Someone' }}
    </span>
    sent you a match request!
  </template>

  <template v-else-if="notification.type === 'match_accepted'">
    <span
      class="clickable-name"
      @click="openUserProfile(notification.user?.id)"
    >
      {{ notification.user?.first_name || 'Someone' }}
    </span>
    accepted your match request!
  </template>

  <template v-else-if="notification.type === 'match_rejected'">
    <span
      class="clickable-name"
      @click="openUserProfile(notification.user?.id)"
    >
      {{ notification.user?.first_name || 'Someone' }}
    </span>
    rejected your request.
  </template>

  <template v-else-if="notification.type === 'follow'">
    <span
      class="clickable-name"
      @click="openUserProfile(notification.user?.id)"
    >
      {{ notification.user?.first_name || 'Someone' }}
    </span>
    started following you
  </template>

  <template v-else>
    {{ notification.message }}
  </template>

  <span class="time">• {{ timeAgo(notification.created_at) }}</span>
</div>



    <!-- MATCH REQUEST ACTIONS -->
    <div
      v-if="notification.type === 'match_request' && notification.status === 'pending'"
      class="action-buttons"
    >
      <button
        class="accept-btn"
        @click="acceptMatch(notification.match_id)"
      >
        Accept
      </button>

      <button
        class="reject-btn"
        @click="rejectMatch(notification.match_id)"
      >
        Reject
      </button>
    </div>

    <!-- Accepted -->
    <div
      v-if="notification.type === 'match_request' && notification.status === 'accepted'"
      class="matched-text"
    >
     {{ notification.user?.first_name }} and you are now matched!
    </div>

    <!-- Rejected -->
    <div
      v-if="notification.type === 'match_request' && notification.status === 'rejected'"
      class="rejected-text"
    >
       {{ notification.user?.first_name }}  rejected your request.
    </div>

    <div
  v-if="notification.type === 'match_accepted'"
  class="matched-text"
>
  {{ notification.user?.first_name }} and you are now matched!
</div>

<div
  v-if="notification.type === 'match_rejected'"
  class="rejected-text"
>
  {{ notification.user?.first_name }} rejected your request.
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
    defaultAvatar: "/banner.png",
    notificationSound: new Audio("/notification.mp3"),
    lastNotificationCount: 0,
    };
  },

  mounted() {
    this.fetchNotifications();
  },

  methods: {
  openUserProfile(userId) {
  if (!userId) {
    console.warn("User ID is missing!");
    return;
  }

  // Use router push
  this.$router.push({ name: "Details", params: { userId } }).catch(err => {
    if (err.name !== "NavigationDuplicated") {
      console.error(err);
    }
  });
},

async acceptMatch(matchId) {
  try {
    await axios.post(
      "/api/accept-match",
      { match_id: matchId },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      }
    );

    this.fetchNotifications();
  } catch (error) {
    console.error("Accept failed:", error);
  }
},


async rejectMatch(matchId) {
  try {
    await axios.post(
      "/api/reject-match",
      { match_id: matchId },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      }
    );

    this.fetchNotifications();
  } catch (error) {
    console.error("Reject failed:", error);
  }
},


     getProfilePhoto(filename) {
    if (!filename) return this.defaultAvatar;
    return `https://companion.ajaywatpade.in/dating-backend/public/storage/${filename}`;
  },
async fetchNotifications() {
  try {
    const res = await axios.get("/api/notifications", {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    });

    const newNotifications = res.data.notifications || [];

    // 🔔 Play sound if new notification added
    if (newNotifications.length > this.lastNotificationCount) {
      this.notificationSound.play().catch(() => {});
    }

    this.lastNotificationCount = newNotifications.length;
    this.notifications = newNotifications;

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
  color: #0d0707;
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
  background: #fee7f0;
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
  font-size: 12px;
  line-height: 1.4;
}

.time {
  font-size: 10px;
  color: #000000;
  margin-left: 6px;
}

.empty {
  text-align: center;
  padding: 60px 20px;
  color: #ffffff;
  font-size: 14px;
}
.action-buttons {
  margin-top: 8px;
  display: flex;
  gap: 10px;
}

.accept-btn {
  background: linear-gradient(45deg, #ff4d6d, #ff758f);
  border: none;
  padding: 6px 14px;
  border-radius: 9px;
  color: white;
  cursor: pointer;
  font-size: 13px;
}

.reject-btn {
  background: #ffffff;
  border: none;
  padding: 6px 14px;
  border-radius: 9px;
  cursor: pointer;
  font-size: 13px;
}

.matched-text {
  margin-top: 6px;
  color: #ff4d6d;
  font-size: 10px;
  font-weight: 600;
}

.rejected-text {
  margin-top: 6px;
  font-size: 10px;
  color: gray;
}
.clickable-name {
  color: #4f46e5; /* nice link color */
  font-weight: 600;
  cursor: pointer;
}
.clickable-name:hover {
  text-decoration: underline;
}

</style>