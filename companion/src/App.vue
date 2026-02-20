<template>
  <div class="app-container">
    <!-- Header -->
       <!-- 🔥 ADD THIS -->
    <AppToast />
    <header class="app-header">
      <!-- Menu Icon -->
      <div class="menu-icon">
        +
      </div>

      <!-- App Name -->
      <div class="app-name">
        𑣲𝐻𝑒𝒶𝓇𝓉 <strong>𝐿𝒾𝓃𝓀</strong>
      </div>

      <!-- Heart Notification Icon -->
      <div class="heart-notification" @click="openNotifications">
        <i class="fa-regular fa-bell"></i>

        <span 
          v-if="notificationStore.count > 0"
          class="badge"
        >
          {{ notificationStore.count > 99 ? '99+' : notificationStore.count }}
        </span>
      </div>
    </header>

    <!-- Hidden audio for notifications -->
    <audio ref="notificationSound" src="/sounds/notification.mp3" preload="auto"></audio>

    <!-- Loader -->
    <div v-if="loading" class="loader">
      <img src="https://img1.picmix.com/output/stamp/normal/6/9/9/5/2515996_a4e4e.gif" alt="Loading..." />
    </div>

    <!-- Actual content -->
    <router-view v-else />
  </div>
</template>

<script>
import { useNotificationStore } from '@/stores/notification'
import { useRouter } from 'vue-router'
import { onMounted, onUnmounted, ref, watch } from 'vue'
import AppToast from '@/components/AppToast.vue'
import { App as CapacitorApp } from '@capacitor/app'

export default {
   components: { AppToast },
  setup() {
    const notificationStore = useNotificationStore()
    const router = useRouter()
    const notificationSound = ref(null) // audio element
    const previousCount = ref(0) // track previous notification count
    const loading = ref(false) // optional loader, you may already have this reactive

    // Function to open notifications page
    const openNotifications = () => {
      notificationStore.reset()
      router.push('/notifications')
    }

    let intervalId = null
   let backPressedOnce = false

onMounted(() => {
  loading.value = true
  notificationStore.fetchCount().finally(() => loading.value = false)
  previousCount.value = notificationStore.count

  // Poll every 2 seconds
  intervalId = setInterval(() => {
    notificationStore.fetchCount()
  }, 2000)

  // 🔥 HANDLE ANDROID BACK BUTTON
  CapacitorApp.addListener('backButton', ({ canGoBack }) => {
    if (router.currentRoute.value.path !== '/') {
      router.back()
    } else {
      if (backPressedOnce) {
        CapacitorApp.exitApp()
      } else {
        backPressedOnce = true
        alert('Press back again to exit')
        setTimeout(() => {
          backPressedOnce = false
        }, 2000)
      }
    }
  })
})


    // Watch for new notifications
    watch(
      () => notificationStore.count,
      (newCount, oldCount) => {
        if (newCount > oldCount) {
          // Play notification sound
          if (notificationSound.value) {
            notificationSound.value.play().catch(() => {})
          }
        }
        previousCount.value = newCount
      }
    )

    onUnmounted(() => {
      if (intervalId) clearInterval(intervalId)
    })

    return {
      notificationStore,
      openNotifications,
      notificationSound,
      loading
    }
  }
}
</script>

<style scoped>
.app-container {
  position: relative;
  padding-top: 60px; /* Give space for sticky header */
}

/* Sticky Header */
.app-header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 60px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  z-index: 9999;
  font-family: 'Inter', sans-serif;
}

/* Menu Icon */
.menu-icon {
  font-size: 31px;
  color: black;
  cursor: pointer;
}

/* App Name */
.app-name {
  font-size: 20px;
  font-weight: bold;
  color: black;
  text-align: center;
  flex: 1;
}

/* Heart notification icon */
.heart-notification {
  position: relative;
  font-size: 24px;
  cursor: pointer;
}

/* Badge */
.heart-notification .badge {
  position: absolute;
  top: 0px;
  right: -3px;
  background: red;
  color: white;
  font-size: 10px;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10000;
}

/* Loader */
.loader {
  display: flex;
  justify-content: center;
  align-items: center;
  height: calc(100vh - 60px);
  background-color: #fff;
  z-index: 10;
  margin-top: 60px;
}
.loader img {
  width: 200px;
  height: 200px;
}

strong {
  color: #ff4081; /* Pink color for "Match" */
  font-weight: bold;
}
.fa-bell{
  color: rgb(0, 0, 0)!important;
}
</style>
