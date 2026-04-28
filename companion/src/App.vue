<template>
  <div class="app-container">
    <!-- Header -->
    <AppToast />
    <header 
  v-if="$route.name !== 'AuthPage'" 
  class="app-header" 
  :class="{ 'scrolled': isScrolled }"
>
      <!-- Menu Icon with Animation -->
      <div
        v-if="$route.name !== 'AuthPage'"
        class="menu-icon"
        @click="toggleSidebar"
        @mouseenter="hoverMenu = true"
        @mouseleave="hoverMenu = false"
      >
        <div class="menu-icon-wrapper">
          <span class="menu-line" :class="{ 'rotate': hoverMenu }"></span>
          <span class="menu-line" :class="{ 'fade': hoverMenu }"></span>
          <span class="menu-line" :class="{ 'rotate-reverse': hoverMenu }"></span>
        </div>
      </div>

      <!-- App Name with Gradient Animation -->
    <div class="app-name" v-if="$route.name !== 'AuthPages'">
  <img 
    src="@/assets/logo.png" 
    alt="Heart Link Logo" 
    class="app-logo"
  />
</div>

      <!-- Notification Icon with Awesome Animations -->
      <div
        v-if="$route.name !== 'AuthPage'"
        class="heart-notification"
        @click="openNotifications"
        @mouseenter="hoverNotification = true"
        @mouseleave="hoverNotification = false"
      >
        <div class="notification-icon-wrapper" :class="{ 'ring': notificationStore.count > 0 && !notificationStore.seen, 'hover-bounce': hoverNotification }">
          <i class="fa-regular fa-bell" :class="{ 'fa-solid': notificationStore.count > 0 }"></i>
          <div class="ripple-effect" v-if="notificationStore.count > 0 && !notificationStore.seen"></div>
        </div>
        
        <span 
          v-if="notificationStore.count > 0"
          class="badge"
          :class="{ 'pulse': notificationStore.count > 0 }"
        >
          {{ notificationStore.count > 99 ? '99+' : notificationStore.count }}
        </span>
        
        <!-- Tooltip -->
        <div class="tooltip" v-if="hoverNotification">
          <span>{{ notificationStore.count > 0 ? `${notificationStore.count} new notification${notificationStore.count > 1 ? 's' : ''}` : 'No new notifications' }}</span>
        </div>
      </div>
    </header>

    <!-- Sidebar Menu -->
    <transition name="slide">
      <div v-if="sidebarOpen" class="sidebar-overlay" @click="closeSidebar">
        <div class="sidebar" @click.stop>
          <div class="sidebar-header">
            <div class="user-avatar">
              <img :src="userAvatar || '/default-avatar.png'" alt="User" />
            </div>
            <h3>{{ userName || 'Guest User' }}</h3>
            <p>{{ userEmail || 'user@example.com' }}</p>
          </div>
          <div class="sidebar-menu">
            <router-link to="/profile" @click="closeSidebar" class="sidebar-item">
              <i class="fa-solid fa-user"></i>
              <span>My Profile</span>
            </router-link>
            <router-link to="/settings" @click="closeSidebar" class="sidebar-item">
              <i class="fa-solid fa-gear"></i>
              <span>Settings</span>
            </router-link>
            <router-link to="/notifications" @click="closeSidebar" class="sidebar-item">
              <i class="fa-solid fa-bell"></i>
              <span>Notifications</span>
              <span v-if="notificationStore.count > 0" class="sidebar-badge">{{ notificationStore.count }}</span>
            </router-link>
            <div class="sidebar-divider"></div>
            <button @click="logout" class="sidebar-item logout">
              <i class="fa-solid fa-sign-out-alt"></i>
              <span>Logout</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Hidden audio for notifications -->
    <audio ref="notificationSound" src="/sounds/notification.mp3" preload="auto"></audio>

    <!-- Loader -->
    <div v-if="loading" class="loader">
      <div class="loader-content">
        <div class="heart-loader">❤️</div>
        <p>Loading...</p>
      </div>
    </div>

    <!-- Actual content -->
    <router-view v-else />
  </div>
</template>

<script>
import { PushNotifications } from '@capacitor/push-notifications';
import { useNotificationStore } from '@/stores/notification'
import { useRouter } from 'vue-router'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import AppToast from '@/components/AppToast.vue'
import { App as CapacitorApp } from '@capacitor/app'
import { StatusBar, Style } from '@capacitor/status-bar';
import { Capacitor } from '@capacitor/core';

// ✅ STATUS BAR FIX
if (Capacitor.isNativePlatform()) {
  StatusBar.setOverlaysWebView({ overlay: false });
  StatusBar.setBackgroundColor({ color: '#ffffff' });
  StatusBar.setStyle({ style: Style.Dark }); // ✅ FIXED
}

export default {
  components: { AppToast },
  setup() {
    const notificationStore = useNotificationStore()
    const router = useRouter()
    const notificationSound = ref(null)
    const previousCount = ref(0)
    const loading = ref(false)
    const isScrolled = ref(false)
    const sidebarOpen = ref(false)
    const hoverMenu = ref(false)
    const hoverNotification = ref(false)
    const userName = ref('')
    const userEmail = ref('')
    const userAvatar = ref('')

    const openNotifications = () => {
      notificationStore.reset()
      notificationStore.seen = true
      router.push('/notifications')
    }

    const toggleSidebar = () => {
      sidebarOpen.value = !sidebarOpen.value
      document.body.style.overflow = sidebarOpen.value ? 'hidden' : ''
    }

    const closeSidebar = () => {
      sidebarOpen.value = false
      document.body.style.overflow = ''
    }

    const logout = () => {
      localStorage.clear()
      router.push('/auth')
      closeSidebar()
    }

    const getUserInfo = () => {
      const user = JSON.parse(localStorage.getItem('user') || '{}')
      userName.value = user.first_name ? `${user.first_name} ${user.last_name || ''}` : 'Guest User'
      userEmail.value = user.email || ''
      userAvatar.value = user.profile_photo || ''
    }

    let backPressedOnce = false

    const handleScroll = () => {
      isScrolled.value = window.scrollY > 10
    }

    onMounted(() => {

      // 🔔 PUSH NOTIFICATIONS SETUP
      PushNotifications.requestPermissions().then(result => {
        if (result.receive === 'granted') {
          PushNotifications.register();
        }
      });

      // 🔑 TOKEN
      PushNotifications.addListener('registration', (token) => {
        console.log('FCM Token:', token.value);

        fetch('https://your-api.com/save-token', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
          },
          body: JSON.stringify({ fcm_token: token.value })
        }).catch(() => {});
      });

      // 📩 FOREGROUND NOTIFICATION
      PushNotifications.addListener('pushNotificationReceived', (notification) => {
        console.log('Received:', notification);

        notificationStore.count += 1
        notificationStore.seen = false

        if (notificationSound.value) {
          notificationSound.value.play().catch(() => {})
        }
      });

      // 👆 CLICK ACTION
      PushNotifications.addListener('pushNotificationActionPerformed', (action) => {
        const data = action.notification.data || {}

        if (data.type === 'message') {
          router.push(`/chat/${data.user_id}`)
        } else if (data.type === 'like') {
          router.push('/notifications')
        } else if (data.type === 'follow') {
          router.push('/profile')
        } else {
          router.push('/notifications')
        }
      });

      // 🔄 APP INIT
      loading.value = true
      getUserInfo()

      notificationStore.fetchCount().finally(() => loading.value = false)
      previousCount.value = notificationStore.count
      notificationStore.seen = false

      window.addEventListener('scroll', handleScroll)

      // 🔙 BACK BUTTON
      CapacitorApp.addListener('backButton', () => {
        if (sidebarOpen.value) {
          closeSidebar()
        } else if (router.currentRoute.value.path !== '/') {
          router.back()
        } else {
          if (backPressedOnce) {
            CapacitorApp.exitApp()
          } else {
            backPressedOnce = true
            showExitToast()
            setTimeout(() => backPressedOnce = false, 2000)
          }
        }
      })
    })

    const showExitToast = () => {
      const toast = document.createElement('div')
      toast.className = 'exit-toast'
      toast.innerHTML = 'Press back again to exit'
      document.body.appendChild(toast)
      setTimeout(() => toast.remove(), 2000)
    }

    watch(
      () => notificationStore.count,
      (newCount, oldCount) => {
        if (newCount > oldCount && notificationSound.value) {
          notificationSound.value.play().catch(() => {})
          notificationStore.seen = false
        }
        previousCount.value = newCount
      }
    )

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll)
      PushNotifications.removeAllListeners() // ✅ important
    })

    return {
      notificationStore,
      openNotifications,
      notificationSound,
      loading,
      isScrolled,
      sidebarOpen,
      hoverMenu,
      hoverNotification,
      userName,
      userEmail,
      userAvatar,
      toggleSidebar,
      closeSidebar,
      logout,
    }
  }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.app-container {
  position: relative;
  padding-top: 60px;
  min-height: 100vh;
}

/* Sticky Header with Glassmorphism */
.app-header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 70px;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  z-index: 9999;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
}

.app-header.scrolled {
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
  height: 65px;
}

/* Menu Icon with Animation */
.menu-icon {
  cursor: pointer;
  z-index: 10000;
  padding: 8px;
  display: none;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.menu-icon:hover {
  background: rgba(0, 0, 0, 0.05);
}

.menu-icon-wrapper {
  width: 24px;
  height: 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
}

.menu-line {
  width: 100%;
  height: 2.5px;
  background: linear-gradient(90deg, #fd5068, #ff8a5c);
  border-radius: 10px;
  transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.menu-line.rotate {
  transform: rotate(45deg) translate(5px, 5px);
}

.menu-line.fade {
  opacity: 0;
  transform: scaleX(0);
}

.menu-line.rotate-reverse {
  transform: rotate(-45deg) translate(5px, -5px);
}

/* App Name with Gradient */
.app-name {
  flex: 1;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.heart-icon {
  font-size: 28px;
  animation: heartbeat 1.5s ease infinite;
  display: inline-block;
}

@keyframes heartbeat {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

.app-title {
  font-size: 24px;
  letter-spacing: -0.5px;
}

.text-gradient {
  background: linear-gradient(135deg, #ff0058 0%, #ffc002 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.text-gradient-pink {
  background: linear-gradient(135deg, #ffc002 0%, #df004c 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

/* Notification Icon */
.heart-notification {
  position: relative;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.heart-notification:hover {
  background: rgba(0, 0, 0, 0.05);
}

.notification-icon-wrapper {
  position: relative;
  font-size: 24px;
  transition: transform 0.3s ease;
}

.notification-icon-wrapper.hover-bounce {
  animation: bounce 0.5s ease;
}

.notification-icon-wrapper.ring {
  animation: ring 0.5s ease;
}

@keyframes ring {
  0% {
    transform: rotate(0deg);
  }
  25% {
    transform: rotate(15deg);
  }
  50% {
    transform: rotate(-15deg);
  }
  75% {
    transform: rotate(5deg);
  }
  100% {
    transform: rotate(0deg);
  }
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}

.fa-bell {
  color: #333;
  transition: all 0.3s ease;
}

.fa-solid.fa-bell {
  color: #fd5068;
}

/* Ripple Effect */
.ripple-effect {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100%;
  height: 100%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  background: rgba(253, 80, 104, 0.3);
  animation: ripple 1.5s infinite;
  pointer-events: none;
}

@keyframes ripple {
  0% {
    width: 100%;
    height: 100%;
    opacity: 0.6;
  }
  100% {
    width: 200%;
    height: 200%;
    opacity: 0;
  }
}

/* Badge */
.badge {
  position: absolute;
  top: -2px;
  right: -5px;
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  color: white;
  font-size: 10px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
  box-shadow: 0 2px 5px rgba(253, 80, 104, 0.3);
}

.badge.pulse {
  animation: badgePulse 1s infinite;
}

@keyframes badgePulse {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 2px 5px rgba(253, 80, 104, 0.3);
  }
  50% {
    transform: scale(1.1);
    box-shadow: 0 2px 10px rgba(253, 80, 104, 0.6);
  }
}

/* Tooltip */
.tooltip {
  position: absolute;
  top: 45px;
  right: 0;
  background: #333;
  color: white;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  white-space: nowrap;
  z-index: 10001;
  animation: fadeInUp 0.2s ease;
}

.tooltip::before {
  content: '';
  position: absolute;
  top: -5px;
  right: 12px;
  width: 10px;
  height: 10px;
  background: #333;
  transform: rotate(45deg);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Sidebar */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 10001;
  animation: fadeIn 0.3s ease;
}

.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 280px;
  height: 100%;
  background: white;
  box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
  animation: slideIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  overflow-y: auto;
}

@keyframes slideIn {
  from {
    transform: translateX(-100%);
  }
  to {
    transform: translateX(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
}

.slide-enter-from .sidebar,
.slide-leave-to .sidebar {
  transform: translateX(-100%);
}

.sidebar-header {
  padding: 30px 20px;
  text-align: center;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

.user-avatar {
  width: 80px;
  height: 80px;
  margin: 0 auto 15px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid white;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.sidebar-header h3 {
  font-size: 18px;
  margin-bottom: 5px;
}

.sidebar-header p {
  font-size: 12px;
  opacity: 0.9;
}

.sidebar-menu {
  padding: 20px 0;
}

.sidebar-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #333;
  text-decoration: none;
  transition: all 0.3s ease;
  cursor: pointer;
  width: 100%;
  border: none;
  background: none;
  font-size: 14px;
  font-weight: 500;
}

.sidebar-item i {
  width: 24px;
  font-size: 18px;
}

.sidebar-item:hover {
  background: rgba(253, 80, 104, 0.1);
  color: #fd5068;
  padding-left: 25px;
}

.sidebar-item.logout {
  color: #ff4444;
}

.sidebar-item.logout:hover {
  background: rgba(255, 68, 68, 0.1);
  color: #ff4444;
}

.sidebar-divider {
  height: 1px;
  background: #e0e0e0;
  margin: 10px 20px;
}

.sidebar-badge {
  margin-left: auto;
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  color: white;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 10px;
}

/* Loader */
.loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: white;
  z-index: 10002;
}

.loader-content {
  text-align: center;
}

.heart-loader {
  font-size: 60px;
  animation: heartbeat 1s ease infinite;
  display: inline-block;
}

.loader p {
  margin-top: 15px;
  color: #666;
  font-size: 14px;
}

/* Exit Toast */
.exit-toast {
  position: fixed;
  bottom: 80px;
  left: 50%;
  transform: translateX(-50%);
  background: #333;
  color: white;
  padding: 12px 24px;
  border-radius: 50px;
  font-size: 14px;
  z-index: 10003;
  animation: fadeInUp 0.3s ease;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}

/* Responsive */
@media (max-width: 600px) {
  .app-header {
    padding: 0 16px;
    height: 60px;
  }
  
  .app-title {
    font-size: 20px;
            margin-right: -38px;
    font-weight: 700!important;
  }
  
  .heart-icon {
    font-size: 24px;
  }
  
  .sidebar {
    width: 260px;
  }
  
  .tooltip {
    display: none;
  }
}

/* Smooth Scroll */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #fd5068;
}
.app-logo {
  height: 82px;   /* adjust as needed */
  width: auto;
  object-fit: contain;
  margin-right: -33px;

}
</style>