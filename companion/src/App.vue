<template>
  <div class="app-container">
    <!-- Header -->
    <header class="app-header">
      <!-- Menu Icon -->
      <div class="menu-icon">
        +
      </div>

      <!-- App Name -->
      <div class="app-name">
      𑣲𝐼𝑛𝑡𝑖𝑚𝑎𝑡𝑒<strong>𝑀𝑎𝑡𝑐ℎ</strong>
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

export default {
  setup() {
    const notificationStore = useNotificationStore()
    const router = useRouter()   // ✅ use router to navigate

    // Fetch count on load
    notificationStore.fetchCount()

    const openNotifications = () => {
      notificationStore.reset()      // Reset count
      router.push('/notifications')  // Navigate to notifications page
    }

    return {
      notificationStore,
      openNotifications
    }
  },

  data() {
    return {
      loading: true
    }
  },

  mounted() {
    setTimeout(() => {
      this.loading = false;
    }, 1000);
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
  background: #fff; /* White background */
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  z-index: 9999; /* On top of everything */
  font-family: 'Inter', sans-serif;
}

/* Menu Icon */
.menu-icon {
  font-size: 31px;
  cursor: pointer;
}

/* App Name */
.app-name {
  font-size: 20px;
  font-weight: bold;
  text-align: center;
  flex: 1;
}

/* Heart notification icon */
.heart-notification {
  position: relative; /* For badge positioning */
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
border-radius: 20px;

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
  height: calc(100vh - 60px); /* Adjust for header height */
  background-color: #fff;
  z-index: 10;
  margin-top: 60px; /* Ensure it does not cover header */
}
.loader img {
  width: 200px;
  height: 200px;
}
strong {
  color: #ff4081; /* Pink color for "Match" */
  font-weight: bold;
}
</style>
