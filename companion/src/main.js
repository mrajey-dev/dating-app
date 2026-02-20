import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import '@fortawesome/fontawesome-free/css/all.css'
import { PushNotifications } from '@capacitor/push-notifications';
import axios from '@/axios'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

app.mount('#app')

// 🔔 PUSH SETUP
PushNotifications.requestPermissions().then(result => {
  if (result.receive === 'granted') {
    PushNotifications.register();
  }
});

// 📲 When device gets FCM token
PushNotifications.addListener('registration', (token) => {
  console.log('FCM Token:', token.value);

  // Save token in Laravel
  axios.post('/api/save-fcm-token', {
    token: token.value
  }, {
    headers: {
      Authorization: `Bearer ${localStorage.getItem("token")}`
    }
  }).catch(err => {
    console.log("Token save error:", err);
  });
});

// 📩 When notification received while app is open
PushNotifications.addListener('pushNotificationReceived', (notification) => {
  console.log('Push received:', notification);
});
