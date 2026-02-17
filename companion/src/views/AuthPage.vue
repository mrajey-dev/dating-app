<template>
  <div class="auth-page">
    <!-- Background Overlay -->
    <div class="bg-overlay"></div>

    <!-- Auth Card -->
    <div class="auth-card">
      <!-- Only show these when login tab is active -->
      <template v-if="activeTab === 'login'">
        <img src="/banner.png" alt="" class="login-img">
        <h1 class="logo"><strong>Heart<i class="fa fa-heart" aria-hidden="true" style="color: #cccccc; font-size: 18px;"></i></strong>Link</h1>

        <!-- Tabs -->
        <div class="tabs">
          <button
            :class="{ active: activeTab === 'login' }"
            @click="activeTab = 'login'"
          >
            Login
          </button>
          <button
            :class="{ active: activeTab === 'signup' }"
            @click="activeTab = 'signup'"
          >
            Sign Up
          </button>
        </div>
      </template>

      <!-- Login Tab -->
      <div v-if="activeTab === 'login'" class="tab-content">
        <input type="email" v-model="login.email" placeholder="Email" />
        <input type="password" v-model="login.password" placeholder="Password" />
        <button class="primary" @click="submitLogin">Login</button>
        <p class="small-text">Forgot password?</p>
        <p v-if="error" class="error-text">{{ error }}</p>
      </div>

      <!-- Signup Tab -->
      <div v-if="activeTab === 'signup'" class="tab-content">
        <SignupForm />
        <p class="switch-text" @click="activeTab = 'login'">← Back to Login</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import SignupForm from '@/components/CreateAccount.vue'

export default {
  components: { SignupForm },

  data() {
    return {
      activeTab: 'login',
      login: {
        email: '',
        password: ''
      },
      error: ''
    }
  },

  mounted() {
    // Redirect if already logged in
    const token = localStorage.getItem('token')
    if (token) {
      this.$router.push('/home')
    }
  },

  methods: {
    async submitLogin() {
      this.error = ''

      try {
        const res = await axios.post('https://companion.ajaywatpade.in/api/login', {
          email: this.login.email,
          password: this.login.password
        })

        // STORE TOKEN
        localStorage.setItem('token', res.data.token)

        // OPTIONAL: store user
        localStorage.setItem('user', JSON.stringify(res.data.user))

        // REDIRECT
        this.$router.push('/home')
      } catch (err) {
        this.error = err.response?.data?.message || 'Login failed'
      }
    }
  }
}
</script>


<style scoped>
/* Full page background */
.auth-page {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Helvetica Neue', sans-serif;
}

/* Gradient background overlay */
.bg-overlay {
  position: absolute;
  inset: 0;
  z-index: -1;
  filter: brightness(0.8);
}

/* Card */
.auth-card {
  background: #fff;
  border-radius: 30px;
  padding: 40px 20px;
  width: 100%;
  max-width: 400px;
  text-align: center;
  color: #333;
}

/* Logo */
.logo {
  font-size: 26px;
  font-weight: 700;
  color: #fd5068;
  margin-bottom: 30px;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 12px;
  margin-bottom: 30px;
  justify-content: center;
}

.tabs button {
  flex: 1;
  padding: 12px 0;
  border-radius: 3px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  background: #f1f1f1;
  transition: 0.3s;
}

.tabs button.active {
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: white;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

/* Inputs */
.tab-content input {
  width: 100%;
  padding: 14px 18px;
  margin-bottom: 18px;
  border-radius: 25px;
  border: 1px solid #ddd;
  outline: none;
  transition: 0.3s;
}

.tab-content input:focus {
  box-shadow: 0 2px 10px rgba(253, 80, 104, 0.3);
}

/* Buttons */
.primary {
  width: 100%;
  padding: 16px;
  border-radius: 25px;
  border: none;
  color: white;
  font-weight: 600;
  font-size: 16px;
  background: linear-gradient(135deg, #fd5068, #ff7854);
  cursor: pointer;
  transition: 0.3s;
}

.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(253, 80, 104, 0.4);
}

/* Small text */
.small-text {
  margin-top: 12px;
  font-size: 14px;
  color: #999;
  cursor: pointer;
  transition: 0.2s;
}

.small-text:hover {
  color: #fd5068;
}

.error-text {
  color: #ff4d4f;
  font-size: 14px;
  margin-top: 10px;
}

.login-img {
  width: 54%;
  margin-top: -23px;
  border-radius: 20px;
  margin-bottom: 0px;
}

strong {
  font-size: 26px;
  font-weight: 700;
  color: #ffa304;
  margin-bottom: 30px;
}

.switch-text {
  margin-top: 20px;
  font-size: 14px;
  color: #fd5068;
  cursor: pointer;
}
.switch-text:hover {
  text-decoration: underline;
}
</style>
