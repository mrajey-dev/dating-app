<template>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <div class="notifications-page">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-bg"></div>
      <div class="floating-shapes">
        <span v-for="i in 8" :key="i" class="shape" :style="{ animationDelay: `${i * 0.3}s` }">💬</span>
      </div>
    </div>

    <div class="notifications-container">
      <!-- Header Section -->
     

      <!-- Notifications List -->
      <div v-if="notifications.length === 0" class="empty-state">
        <div class="empty-icon">🔔</div>
        <h3>No Notifications Yet</h3>
        <p>When someone interacts with you, you'll see it here</p>
      </div>

      <div v-else class="notifications-list">
        <transition-group name="notification-list">
          <div
            v-for="(notification, index) in notifications"
            :key="notification.id"
            class="notification-item"
            :class="{ 
              unread: !notification.read_at,
              'match-request': notification.type === 'match_request',
              'match-accepted': notification.type === 'match_accepted'
            }"
            :style="{ animationDelay: `${index * 0.03}s` }"
          >
            <!-- Animated Unread Indicator -->
            <div class="unread-indicator" v-if="!notification.read_at">
              <div class="pulse-dot"></div>
            </div>

            <!-- Avatar -->
            <div class="avatar-wrapper" @click="openUserProfile(notification.user?.id)">
              <img 
                :src="getProfilePhoto(notification.user?.profile_photo)" 
                class="avatar"
                :class="{ 'avatar-loading': !notification.user?.profile_photo }"
              />
              <div class="avatar-overlay">
                <i class="fa fa-user"></i>
              </div>
            </div>

            <!-- Content -->
            <div class="notification-content">
              <div class="notification-message">
                <template v-if="notification.type === 'match_request'">
                  <span class="user-name" @click="openUserProfile(notification.user?.id)">
                    {{ notification.user?.first_name || 'Someone' }}
                  </span>
                  <span class="action-text"> sent you a match request</span>
                  <span class="action-icon">💌</span>
                </template>

                <template v-else-if="notification.type === 'match_accepted'">
                  <span class="user-name" @click="openUserProfile(notification.user?.id)">
                    {{ notification.user?.first_name || 'Someone' }}
                  </span>
                  <span class="action-text"> accepted your match request</span>
                  <span class="action-icon">❤️</span>
                </template>

                <template v-else-if="notification.type === 'match_rejected'">
                  <span class="user-name" @click="openUserProfile(notification.user?.id)">
                    {{ notification.user?.first_name || 'Someone' }}
                  </span>
                  <span class="action-text"> rejected your request</span>
                  <span class="action-icon">💔</span>
                </template>

                <template v-else-if="notification.type === 'follow'">
                  <span class="user-name" @click="openUserProfile(notification.user?.id)">
                    {{ notification.user?.first_name || 'Someone' }}
                  </span>
                  <span class="action-text"> started following you</span>
                  <span class="action-icon">👥</span>
                </template>

                <template v-else>
                  {{ notification.message }}
                </template>

                <span class="time-ago">
                  <i class="fa fa-clock-o"></i> {{ timeAgo(notification.created_at) }}
                </span>
              </div>

              <!-- Match Request Actions -->
              <div v-if="notification.type === 'match_request' && notification.status === 'pending'" class="action-buttons">
                <button class="accept-btn" @click.stop="acceptMatch(notification.match_id)">
                  <i class="fa fa-check"></i> Accept
                </button>
                <button class="reject-btn" @click.stop="rejectMatch(notification.match_id)">
                  <i class="fa fa-times"></i> Decline
                </button>
              </div>

              <!-- Status Badges -->
              <div v-if="notification.type === 'match_request' && notification.status === 'accepted'" class="status-badge accepted">
                <i class="fa fa-check-circle"></i> Match Accepted
              </div>

              <div v-if="notification.type === 'match_request' && notification.status === 'rejected'" class="status-badge rejected">
                <i class="fa fa-times-circle"></i> Request Declined
              </div>

              <div v-if="notification.type === 'match_accepted'" class="status-badge matched">
                <i class="fa fa-heart"></i> You are now matched!
              </div>

              <div v-if="notification.type === 'match_rejected'" class="status-badge rejected">
                <i class="fa fa-frown-o"></i> Request Rejected
              </div>
            </div>
          </div>
        </transition-group>
      </div>

      <!-- Load More Button -->
      <div v-if="hasMore" class="load-more">
        <button @click="loadMore" class="load-more-btn">
          <i class="fa fa-refresh"></i> Load More
        </button>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition name="toast-fade">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <i :class="toast.type === 'success' ? 'fa fa-check-circle' : 'fa fa-exclamation-circle'"></i>
        {{ toast.message }}
      </div>
    </transition>

    <!-- BOTTOM NAVIGATION BAR -->
    <BottomNavBar :items="bottomNavItems" />
  </div>
</template>

<script>
import moment from "moment";
import axios from "@/axios";
import BottomNavBar from "@/components/BottomNavBar.vue";

export default {
  components: { BottomNavBar },
  data() {
    return {
      notifications: [],
      defaultAvatar: "/banner.png",
      notificationSound: new Audio("/notification.mp3"),
      lastNotificationCount: 0,
      page: 1,
      hasMore: true,
      loading: false,
      toast: {
        show: false,
        message: "",
        type: "success"
      }
    };
  },
  computed: {
    unreadCount() {
      return this.notifications.filter(n => !n.read_at).length;
    }
  },
  mounted() {
    this.safeFetchNotifications();
    this.startNotificationPolling();
  },
  watch: {
    $route() {
      this.safeFetchNotifications();
    }
  },
  beforeUnmount() {
    if (this.pollingInterval) clearInterval(this.pollingInterval);
  },
  methods: {
    showToast(message, type = "success") {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.show = true;
      setTimeout(() => { this.toast.show = false }, 3000);
    },
    startNotificationPolling() {
      this.pollingInterval = setInterval(() => {
        if (document.hidden) return;
        this.fetchNotifications(true);
      }, 30000);
    },
    safeFetchNotifications() {
      const token = localStorage.getItem("token");
      if (!token || this.$route.path === "/login") return;
      this.fetchNotifications();
    },
    openUserProfile(userId) {
      if (!userId) {
        console.warn("User ID is missing!");
        return;
      }
      this.$router.push({ name: "Details", params: { userId } }).catch(err => {
        if (err.name !== "NavigationDuplicated") console.error(err);
      });
    },
    async markAllAsRead() {
      try {
        await axios.post("/api/notifications/mark-all-read", {}, {
          headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
        });
        this.notifications.forEach(n => { n.read_at = new Date().toISOString(); });
        this.showToast("All notifications marked as read");
      } catch (error) {
        console.error("Mark all read failed:", error);
      }
    },
    async acceptMatch(matchId) {
      try {
        await axios.post("/api/accept-match", { match_id: matchId }, {
          headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
        });
        this.fetchNotifications();
        this.showToast("Match accepted! Start chatting now 💬", "success");
      } catch (error) {
        console.error("Accept failed:", error);
        this.showToast("Failed to accept match", "error");
      }
    },
    async rejectMatch(matchId) {
      try {
        await axios.post("/api/reject-match", { match_id: matchId }, {
          headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
        });
        this.fetchNotifications();
        this.showToast("Match request declined", "info");
      } catch (error) {
        console.error("Reject failed:", error);
        this.showToast("Failed to decline match", "error");
      }
    },
    getProfilePhoto(filename) {
      if (!filename) return this.defaultAvatar;
      if (filename.startsWith("http")) return filename;
      return `https://companion.ajaywatpade.in/dating-backend/public/storage/${filename}`;
    },
    async fetchNotifications(silent = false) {
      if (this.loading) return;
      this.loading = true;
      try {
        const res = await axios.get(`/api/notifications?page=${this.page}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
        });
        const newNotifications = res.data.notifications || [];
        this.hasMore = res.data.has_more || false;
        
        if (this.page === 1) {
          const oldCount = this.notifications.length;
          this.notifications = newNotifications;
          if (!silent && newNotifications.length > oldCount) {
            this.notificationSound.play().catch(() => {});
          }
        } else {
          this.notifications.push(...newNotifications);
        }
        this.lastNotificationCount = this.notifications.length;
      } catch (error) {
        console.error("Error fetching notifications:", error);
      } finally {
        this.loading = false;
      }
    },
    async loadMore() {
      if (!this.hasMore || this.loading) return;
      this.page++;
      await this.fetchNotifications();
    },
    timeAgo(time) {
      return moment(time).fromNow();
    }
  }
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.notifications-page {
  min-height: 100vh;
  background: linear-gradient(145deg, #ffdde5 0%, #fff0f3 100%);
  position: relative;
  overflow-x: hidden;
}

/* Animated Background */
.animated-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 0;
}

.gradient-bg {
  position: absolute;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%);
  animation: rotate 20s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.floating-shapes {
  position: absolute;
  width: 100%;
  height: 100%;
}

.shape {
  position: absolute;
  font-size: 24px;
  opacity: 0.15;
  animation: floatShape 12s infinite ease-in-out;
}

.shape:nth-child(1) { top: 10%; left: 5%; animation-delay: 0s; }
.shape:nth-child(2) { top: 70%; left: 85%; animation-delay: 2s; }
.shape:nth-child(3) { top: 40%; left: 15%; animation-delay: 4s; }
.shape:nth-child(4) { top: 80%; left: 25%; animation-delay: 6s; }
.shape:nth-child(5) { top: 20%; left: 70%; animation-delay: 8s; }
.shape:nth-child(6) { top: 55%; left: 45%; animation-delay: 3s; }
.shape:nth-child(7) { top: 85%; left: 60%; animation-delay: 5s; }
.shape:nth-child(8) { top: 15%; left: 40%; animation-delay: 7s; }

@keyframes floatShape {
  0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.1; }
  50% { transform: translateY(-40px) rotate(15deg); opacity: 0.2; }
}

/* Container */
.notifications-container {
  position: relative;
  z-index: 1;
  max-width: 600px;
  margin: 0 auto;
  padding: 20px 16px 40px;
}

/* Header Section */
.header-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  animation: slideDown 0.5s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.back-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(0,0,0,0.05);
  border: none;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.back-btn:hover {
  background: rgba(0,0,0,0.1);
  transform: scale(1.05);
}

.header-title {
  font-size: 22px;
  font-weight: 700;
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  display: flex;
  align-items: center;
  gap: 8px;
}

.mark-all-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  border: none;
  color: white;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mark-all-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 5px 15px rgba(253,80,104,0.4);
}

.stats-bar {
  display: flex;
  gap: 20px;
  justify-content: center;
  padding-top: 10px;
  border-top: 1px solid rgba(0,0,0,0.05);
}

.stat {
  text-align: center;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
  display: block;
}

.stat-label {
  font-size: 12px;
  color: #666;
}

/* Notifications List */
.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.notification-item {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 16px;
  display: flex;
  gap: 14px;
  position: relative;
  transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
  animation: fadeInUp 0.4s ease backwards;
  cursor: pointer;
}

.notification-item:hover {
  transform: translateX(5px);
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.notification-item.unread {
  /* background: linear-gradient(135deg, rgba(253,80,104,0.08), rgba(255,138,92,0.08)); */
  border-left: 4px solid #fd5068;
}

.notification-item.match-request {
  /* background: linear-gradient(135deg, rgba(255,193,7,0.1), rgba(255,138,92,0.05)); */
}

.notification-item.match-accepted {
  /* background: linear-gradient(135deg, rgba(40,167,69,0.1), rgba(32,201,151,0.05)); */
}

/* Unread Indicator */
.unread-indicator {
  position: absolute;
  left: -4px;
  top: 50%;
  transform: translateY(-50%);
}

.pulse-dot {
  width: 8px;
  height: 8px;
  background: #fd5068;
  border-radius: 50%;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.5); opacity: 0.5; }
}

/* Avatar */
.avatar-wrapper {
  position: relative;
  cursor: pointer;
}

.avatar {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  object-fit: cover;
  transition: all 0.3s;
  border: 2px solid transparent;
}

.avatar-wrapper:hover .avatar {
  transform: scale(1.05);
  border-color: #fd5068;
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.avatar-overlay {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
  color: white;
}

.avatar-wrapper:hover .avatar-overlay {
  opacity: 1;
}

/* Notification Content */
.notification-content {
  flex: 1;
}

.notification-message {
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: 8px;
}

.user-name {
  font-weight: 700;
  color: #fd5068;
  cursor: pointer;
  transition: color 0.2s;
}

.user-name:hover {
  text-decoration: underline;
  color: #ff8a5c;
}

.action-text {
  color: #1a1a2e;
}

.action-icon {
  margin-left: 5px;
  display: inline-block;
  animation: bounce 1s ease infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}

.time-ago {
  font-size: 11px;
  color: #999;
  margin-left: 8px;
  white-space: nowrap;
}

.time-ago i {
  margin-right: 3px;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 12px;
  margin-top: 10px;
}

.accept-btn, .reject-btn {
  padding: 8px 20px;
  border-radius: 30px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.accept-btn {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.accept-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(40,167,69,0.4);
}

.reject-btn {
  background: linear-gradient(135deg, #dc3545, #fd7e7e);
  color: white;
}

.reject-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(220,53,69,0.4);
}

/* Status Badges */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 30px;
  font-size: 11px;
  font-weight: 600;
  margin-top: 8px;
}

.status-badge.accepted, .status-badge.matched {
  background: linear-gradient(135deg, #d4edda, #c3e6cb);
  color: #155724;
}

.status-badge.rejected {
  background: linear-gradient(135deg, #f8d7da, #f5c6cb);
  color: #721c24;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(10px);
  border-radius: 30px;
  animation: fadeInUp 0.5s ease;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
  animation: shake 3s ease infinite;
}

@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  10%, 30%, 50%, 70%, 90% { transform: rotate(-10deg); }
  20%, 40%, 60%, 80% { transform: rotate(10deg); }
}

.empty-state h3 {
  font-size: 22px;
  color: #1a1a2e;
  margin-bottom: 10px;
}

.empty-state p {
  color: #666;
}

/* Load More */
.load-more {
  text-align: center;
  margin-top: 20px;
}

.load-more-btn {
  padding: 12px 30px;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(10px);
  border: none;
  border-radius: 40px;
  font-weight: 600;
  color: #fd5068;
  cursor: pointer;
  transition: all 0.3s;
}

.load-more-btn:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Toast */
.toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  border-radius: 50px;
  font-weight: 600;
  z-index: 10002;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.toast.success {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.toast.error {
  background: linear-gradient(135deg, #dc3545, #fd7e7e);
  color: white;
}

.toast.info {
  background: linear-gradient(135deg, #17a2b8, #5bc0de);
  color: white;
}

@keyframes toastSlide {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.3s;
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(20px);
}

/* List Transitions */
.notification-list-enter-active,
.notification-list-leave-active {
  transition: all 0.3s;
}

.notification-list-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.notification-list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* Responsive */
@media (max-width: 600px) {
  .notifications-container {
    padding: 16px 12px 40px;
  }
  
  .avatar {
    width: 48px;
    height: 48px;
  }
  
  .notification-message {
    font-size: 13px;
  }
  
  .time-ago {
    display: block;
    margin-left: 0;
    margin-top: 4px;
  }
  
  .action-buttons {
    gap: 8px;
  }
  
  .accept-btn, .reject-btn {
    padding: 6px 16px;
    font-size: 12px;
  }
}
</style>