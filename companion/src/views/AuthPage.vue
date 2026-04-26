<template>
  <div class="app-containers">
    <!-- WELCOME PAGE (LANDING) -->
    <transition name="fade-scale">
      <div v-if="showWelcome" class="welcome-page">
        <!-- Fullscreen background image slider with romantic couple photos -->
        <div class="hero-slider">
          <div v-for="(img, index) in romanticImages" 
               :key="index"
               class="slide"
               :class="{ active: currentSlide === index }"
               :style="{ backgroundImage: `url(${img})` }">
          </div>
          <div class="overlay-gradient"></div>
        </div>

        <!-- Content overlay -->
        <div class="welcome-content">
          <div class="brand-heart">
            <div class="logo-ring">
              <svg width="48" height="48" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 27C16 27 2 19 2 11C2 7.5 4.5 5 8 5C11.5 5 14 7.5 16 11C18 7.5 20.5 5 24 5C27.5 5 30 7.5 30 11C30 19 16 27 16 27Z" fill="currentColor"/>
              </svg>
            </div>
            <h1 class="welcome-title">Heart<span>Link</span></h1>
          </div>
          <p class="tagline-main">Where two hearts become one journey</p>
          <p class="description">Every great love story starts with a single step.<br>Begin yours today.</p>
          
          <!-- Bottom Action Buttons -->
          <div class="action-buttons">
            <button class="btn-primary" @click="navigateToLogin">
              <span>Login</span>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 3h6v6M14 10L21 3M10 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5" />
                <polyline points="16 15 21 15 21 20" />
                <line x1="9" y1="9" x2="21" y2="21" />
              </svg>
            </button>
            <button class="btn-secondary" @click="navigateToSignup">
              <span>Sign Up</span>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <line x1="19" y1="8" x2="19" y2="14" />
                <line x1="22" y1="11" x2="16" y2="11" />
              </svg>
            </button>
          </div>
          <div class="footer-note">
            <span>✨ The beginning of something beautiful ✨</span>
          </div>
        </div>

        <!-- Slide indicators -->
        <div class="slide-dots">
          <button v-for="(_, idx) in romanticImages" 
                  :key="idx"
                  class="dot"
                  :class="{ active: currentSlide === idx }"
                  @click="currentSlide = idx">
          </button>
        </div>
      </div>
    </transition>

    <!-- AUTH PAGE (LOGIN / SIGNUP CARD) -->
    <transition name="fade-slide-up">
      <div v-if="!showWelcome" class="auth-page-wrapper">
        <div class="auth-card">
          <!-- Back to welcome button -->
          <button class="back-to-welcome" @click="backToWelcome">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back
          </button>

          <!-- Brand Section -->
          <div class="brand-section">
            <div class="logo-wrapper">
              <div class="logo-icon">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M14 23.5C14 23.5 2 16.5 2 9.5C2 6.5 4.5 4 7.5 4C10.5 4 13 6.5 14 9.5C15 6.5 17.5 4 20.5 4C23.5 4 26 6.5 26 9.5C26 16.5 14 23.5 14 23.5Z" fill="currentColor"/>
                </svg>
              </div>
              <h1 class="brand-name">Heart<span>Link</span></h1>
            </div>
            <p class="brand-tagline">{{ activeTab === 'login' ? 'Welcome back. Sign in to continue your journey.' : 'Create your account and start your journey.' }}</p>
          </div>

          <!-- Tab Switcher -->
          <div class="tab-switcher">
            <div class="tab-track">
              <div class="tab-thumb" :class="{ right: activeTab === 'signup' }"></div>
              <button class="tab-btn" :class="{ active: activeTab === 'login' }" @click="activeTab = 'login'">Login</button>
              <button class="tab-btn" :class="{ active: activeTab === 'signup' }" @click="activeTab = 'signup'">Sign Up</button>
            </div>
          </div>

          <!-- Login Form with Floating Label Email Input -->
          <transition name="fade-slide">
            <form v-if="activeTab === 'login'" @submit.prevent="submitLogin" class="login-form" key="login">
              
              <!-- FLOATING LABEL EMAIL FIELD -->
              <div class="floating-group">
                <div class="input-floating-wrapper" :class="{ focused: focusedField === 'email', 'has-value': login.email.length > 0 }">
                  <input
                    id="floating-email"
                    type="email"
                    v-model="login.email"
                    @focus="focusedField = 'email'"
                    @blur="focusedField = ''"
                    class="floating-input"
                    :class="{ 'error-border': errors.email }"
                  />
                  <label for="floating-email" class="floating-placeholder">
                    Email address
                  </label>
                  <div class="input-accent-line"></div>
                </div>
                <span v-if="errors.email" class="error-message">{{ errors.email }}</span>
              </div>

              <!-- PASSWORD FIELD (standard with floating label) -->
              <div class="floating-group">
                <div class="input-floating-wrapper" :class="{ focused: focusedField === 'password', 'has-value': login.password.length > 0 }">
                  <input
                    id="floating-password"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="login.password"
                    @focus="focusedField = 'password'"
                    @blur="focusedField = ''"
                    class="floating-input"
                    :class="{ 'error-border': errors.password }"
                  />
                  <label for="floating-password" class="floating-placeholder">
                    Password
                  </label>
                  <button type="button" class="eye-toggle-floating" @click="showPassword = !showPassword">
                    <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M9.9 4.24C10.6 4.08 11.3 4 12 4C19 4 23 12 23 12C22.6 12.7 22.1 13.5 21.4 14.2M14.1 17.8C13.4 17.9 12.7 18 12 18C5 18 1 12 1 12C1.9 10.6 3 9.3 4.3 8.2M8 12C8 13.1 8.9 14 10 14C11.1 14 12 13.1 12 12"/>
                      <path d="M2 2L22 22"/>
                    </svg>
                  </button>
                  <div class="input-accent-line"></div>
                </div>
                <span v-if="errors.password" class="error-message">{{ errors.password }}</span>
              </div>

              <!-- Forgot Password & Remember -->
              <div class="form-footer">
                <label class="checkbox-label">
                  <input type="checkbox" v-model="rememberMe">
                  <span class="checkmark"></span>
                  <span>Remember me</span>
                </label>
                <button type="button" class="forgot-link" @click="forgotPassword">Forgot password?</button>
              </div>

              <!-- Error Banner -->
              <transition name="fade">
                <div v-if="error" class="error-banner">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                  </svg>
                  {{ error }}
                </div>
              </transition>

              <!-- Submit Button -->
              <button type="submit" class="submit-btn" :disabled="loading || !isFormValid">
                <span v-if="loading" class="spinner"></span>
                <span v-else>Sign in</span>
                <svg v-if="!loading" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="5" y1="12" x2="19" y2="12"/>
                  <polyline points="12 5 19 12 12 19"/>
                </svg>
              </button>

              <!-- Sign Up Link -->
              <p class="signup-prompt">
                Don't have an account?
                <button type="button" class="link-btn" @click="activeTab = 'signup'">Create account →</button>
              </p>
            </form>
          </transition>

          <!-- Signup Component -->
          <transition name="fade-slide">
            <div v-if="activeTab === 'signup'" key="signup" class="form-section">
              <SignupForm @switch-to-login="activeTab = 'login'" />
            </div>
          </transition>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios'
import SignupForm from '@/components/CreateAccount.vue'

export default {
  name: 'App',
  components: { SignupForm },
  data() {
    return {
      showWelcome: true,
      currentSlide: 0,
      slideInterval: null,
      romanticImages: [
        'https://img.freepik.com/premium-photo/beach-hug-happy-couple-celebrate-love-dating-marriage-sea-ocean-water-luxury-vacation-travel-man-woman-smile-date-celebration-surprise-joy-sunset-summer-holiday_590464-85391.jpg?semt=ais_hybrid&w=740&q=80',
        'https://www.shutterstock.com/image-photo/couple-love-laughing-embracing-closely-600nw-2586055147.jpg',
        'https://i.pinimg.com/736x/c1/f9/77/c1f977715f386ecd7e8f3df367184336.jpg',
        'https://img.freepik.com/premium-photo/together-beach-happiness-with-hug-holiday-having-fun-with-sunset-weekend-couple-vacation-laughing-with-face-ocean-travel-summer-relaxing-time_590464-186788.jpg?semt=ais_hybrid&w=740&q=80',
        'https://images.pexels.com/photos/30924512/pexels-photo-30924512/free-photo-of-romantic-black-and-white-couples-photography.jpeg',
        'https://images.pexels.com/photos/2808658/pexels-photo-2808658.jpeg',
      ],
      activeTab: 'login',
      focusedField: '',
      showPassword: false,
      loading: false,
      rememberMe: false,
      login: {
        email: '',
        password: ''
      },
      errors: {
        email: '',
        password: ''
      },
      error: ''
    }
  },
  computed: {
    isFormValid() {
      return this.login.email.trim() !== '' && this.login.password.trim() !== ''
    }
  },
  mounted() {
    this.startSlideShow();
    
    // CHECK IF USER IS ALREADY LOGGED IN
    const token = localStorage.getItem('token');
    const user = localStorage.getItem('user');
    
    // If user is already logged in, redirect to home page
    if (token && user) {
      // Check if router is available
      if (this.$router) {
        this.$router.push('/home');
      } else {
        // Fallback if router isn't available yet
        window.location.href = '/home';
      }
      return; // Stop further execution
    }
    
    // Only check for remembered email if not logged in
    const rememberedEmail = localStorage.getItem('rememberedEmail');
    if (rememberedEmail) {
      this.login.email = rememberedEmail;
      this.rememberMe = true;
    }
  },
  beforeUnmount() {
    if (this.slideInterval) clearInterval(this.slideInterval);
  },
  methods: {
    startSlideShow() {
      this.slideInterval = setInterval(() => {
        this.currentSlide = (this.currentSlide + 1) % this.romanticImages.length;
      }, 5000);
    },
    navigateToLogin() {
      // Double-check authentication before navigating
      const token = localStorage.getItem('token');
      const user = localStorage.getItem('user');
      
      if (token && user) {
        this.$router.push('/home');
        return;
      }
      
      this.showWelcome = false;
      this.activeTab = 'login';
    },
    navigateToSignup() {
      // Double-check authentication before navigating
      const token = localStorage.getItem('token');
      const user = localStorage.getItem('user');
      
      if (token && user) {
        this.$router.push('/home');
        return;
      }
      
      this.showWelcome = false;
      this.activeTab = 'signup';
    },
    backToWelcome() {
      this.showWelcome = true;
      this.error = '';
      this.login.password = '';
      this.errors = { email: '', password: '' };
    },
    validateEmail(email) {
      const re = /^[^\s@]+@([^\s@.,]+\.)+[^\s@.,]{2,}$/;
      return re.test(email);
    },
    validateForm() {
      let isValid = true;
      this.errors = { email: '', password: '' };

      if (!this.login.email.trim()) {
        this.errors.email = 'Email address is required';
        isValid = false;
      } else if (!this.validateEmail(this.login.email)) {
        this.errors.email = 'Please enter a valid email address';
        isValid = false;
      }

      if (!this.login.password) {
        this.errors.password = 'Password is required';
        isValid = false;
      } else if (this.login.password.length < 6) {
        this.errors.password = 'Password must be at least 6 characters';
        isValid = false;
      }
      return isValid;
    },
    async submitLogin() {
      this.error = '';
      if (!this.validateForm()) return;
      this.loading = true;
      try {
        const response = await axios.post('https://companion.ajaywatpade.in/api/login', {
          email: this.login.email,
          password: this.login.password
        });
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        if (this.rememberMe) {
          localStorage.setItem('rememberedEmail', this.login.email);
        } else {
          localStorage.removeItem('rememberedEmail');
        }
        if (this.$router) {
          this.$router.push('/home');
        } else {
          alert('Login successful! (Router not configured, you would be redirected to /home)');
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Invalid email or password. Please try again.';
        this.login.password = '';
      } finally {
        this.loading = false;
      }
    },
    forgotPassword() {
      if (this.login.email && this.validateEmail(this.login.email)) {
        alert(`✨ A romantic password reset link has been sent to ${this.login.email} ✨`);
      } else {
        alert('Please enter your email address to receive reset instructions 💌');
      }
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

.app-containers {
  width: 100%;
  min-height: 100vh;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  background: #ffffff;
  overflow-x: hidden;
  padding-top: 0!important;
}

/* ----- WELCOME PAGE ----- */
.welcome-page {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100vh;
  overflow: hidden;
  z-index: 100;
}

.hero-slider {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.slide {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  opacity: 0;
  transition: opacity 1.5s ease-in-out;
  z-index: 0;
}

.slide.active {
  opacity: 1;
  z-index: 1;
}

.overlay-gradient {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.5) 100%);
  z-index: 2;
  backdrop-filter: brightness(0.9);
}

.welcome-content {
  position: relative;
  z-index: 10;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  height: 100%;
  padding: 2rem;
  color: white;
  text-shadow: 0 2px 20px rgba(0,0,0,0.3);
}

.brand-heart {
  margin-bottom: 1rem;
  animation: fadeInUp 1s ease;
}

.logo-ring {
  display: inline-flex;
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(12px);
  border-radius: 60px;
  padding: 16px;
  margin-bottom: 1rem;
  box-shadow: 0 20px 35px -10px rgba(0,0,0,0.2);
}

.logo-ring svg {
  color: #ffb7c5;
  width: 48px;
  height: 48px;
  filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
}

.welcome-title {
  font-size: 3.8rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  background: linear-gradient(135deg, #FFFFFF, #FFD6E0);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.welcome-title span {
  background: linear-gradient(135deg, #FFB7C5, #FF8A9F);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.tagline-main {
  font-size: 1.5rem;
  font-weight: 500;
  margin-top: 0.5rem;
  letter-spacing: -0.3px;
  backdrop-filter: blur(4px);
  padding: 0.25rem 1rem;
  border-radius: 40px;
  display: inline-block;
  display: none;
}

.description {
  font-size: 1.05rem;
  margin-top: 1rem;
  opacity: 0.9;
  font-weight: 400;
  max-width: 500px;
  line-height: 1.5;
}

.action-buttons {
  display: flex;
  gap: 1.5rem;
  margin-top: 3rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 2.2rem;
  font-size: 1.1rem;
  font-weight: 600;
  border-radius: 60px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
  border: none;
  font-family: inherit;
  backdrop-filter: blur(8px);
}

.btn-primary {
  background: #E91E63;
  color: #ffffff;
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.btn-primary:hover {
  transform: translateY(-5px);
  /* background: white;
  box-shadow: 0 20px 30px -8px rgba(0,0,0,0.25); */
}

.btn-secondary {
  background: rgb(255, 255, 255);
  backdrop-filter: blur(12px);
  color: #E91E63;
  border: 1.5px solid rgba(255,255,255,0.6);

}

.btn-secondary:hover {
  /* background: rgba(255, 255, 255, 0.35); */
  transform: translateY(-5px);
  /* border-color: white; */
}

.footer-note {
  position: absolute;
  bottom: 2rem;
  font-size: 0.8rem;
  opacity: 0.7;
  letter-spacing: 0.5px;
}

.slide-dots {
  position: absolute;
  bottom: 2rem;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  gap: 12px;
  z-index: 20;
  display: none;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 10px;
  background: rgba(255,255,255,0.5);
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0;
}

.dot.active {
  width: 28px;
  background: white;
  box-shadow: 0 0 8px rgba(255,255,255,0.8);
}

/* ----- FLOATING LABEL STYLES (Key Feature) ----- */
.floating-group {
  margin-bottom: 1.5rem;
  width: 100%;
}

.input-floating-wrapper {
  position: relative;
  width: 100%;
}

.floating-input {
  width: 100%;
  padding: 1rem 1rem 0.5rem 1rem;
  font-size: 1rem;
  font-family: inherit;
  border: 1.5px solid #e2e8f0;
  border-radius: 18px;
  background: white;
  outline: none;
  transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
  /* height: 64px; */
  box-sizing: border-box;
}

.floating-input:focus {
  border-color: #ff9a9e;
  box-shadow: 0 0 0 3px rgba(255, 154, 158, 0.15);
}

.floating-input.error-border {
  border-color: #e74c3c;
}

.floating-placeholder {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1rem;
  color: #8e8ea8;
  pointer-events: none;
  transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
  background: transparent;
  padding: 0 0.25rem;
  line-height: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: calc(100% - 2rem);
}

/* FLOATING EFFECT - moves to border when focused OR has value */
.input-floating-wrapper.focused .floating-placeholder,
.input-floating-wrapper.has-value .floating-placeholder {
  top: 0;
  transform: translateY(-50%);
  font-size: 0.75rem;
  color: #c44569;
  background: white;
  left: 0.75rem;
  padding: 0 0.35rem;
  white-space: nowrap;
}

/* Accent line animation */
.input-accent-line {
  position: absolute;
  bottom: 0;
  display: none;
  left: 50%;
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, #ff9a9e, #fad0c4);
  transition: all 0.3s ease;
  border-radius: 2px;
}

.input-floating-wrapper.focused .input-accent-line {
  left: 0;
  width: 100%;
}

/* Eye toggle inside floating field */
.eye-toggle-floating {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #888;
  z-index: 5;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.eye-toggle-floating:hover {
  color: #c44569;
}

.error-message {
  font-size: 0.75rem;
  color: #e74c3c;
  margin-top: 0.35rem;
  margin-left: 0.5rem;
  display: block;
}

/* ----- AUTH PAGE STYLES ----- */
.auth-page-wrapper {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  /* padding: 2rem; */
  position: relative;
  min-height: 100vh;
}

.auth-card {
  width: 100%;
  max-width: 520px;
  background: white;
  border-radius: 2rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
  position: relative;
  transition: all 0.2s;
  padding: 2rem 2rem 2.5rem;
}

.back-to-welcome {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  background: transparent;
  border: none;
  display: none;
  align-items: center;
  gap: 6px;
  padding: 0.5rem 1rem;
  border-radius: 40px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  color: #555;
  transition: all 0.2s;
  font-family: inherit;
}

.back-to-welcome:hover {
  background: #e6e6e6;
  color: #c44569;
}

.brand-section {
  text-align: center;
  margin-bottom: 2rem;
  margin-top: 1rem;
}

.logo-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}

.logo-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #ff000a 0%, #871e00 100%);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.brand-name {
  font-size: 1.9rem;
  font-weight: 700;
  background: linear-gradient(135deg, #ff0048, #651300);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.brand-tagline {
  color: #6c5b7b;
  font-size: 0.8rem;
}

.tab-switcher {
  margin-bottom: 2rem;
  max-width: 280px;
  margin-left: auto;
  margin-right: auto;
}

.tab-track {
  position: relative;
  display: flex;
  background: #f1f2f6;
  border-radius: 50px;
  padding: 4px;
}

.tab-thumb {
  position: absolute;
  top: 4px;
  left: 4px;
  width: calc(50% - 6px);
  height: calc(100% - 8px);
  background: white;
  border-radius: 40px;
  transition: transform 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
}

.tab-thumb.right {
  transform: translateX(calc(100% + 4px));
}

.tab-btn {
  flex: 1;
  padding: 10px;
  background: transparent;
  border: none;
  font-weight: 600;
  cursor: pointer;
  z-index: 1;
  position: relative;
  color: #5f5f7a;
}

.tab-btn.active {
  color: #c44569;
}

.form-footer {
  display: flex;
  justify-content: space-between;
  margin: 0.5rem 0 1.5rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  cursor: pointer;
}

.forgot-link {
  background: none;
  border: none;
  color: #c44569;
  font-size: 0.85rem;
  cursor: pointer;
}

.submit-btn {
  width: 100%;
  background: linear-gradient(120deg, #ff9a9e, #fad0c4);
  border: none;
  padding: 0.9rem;
  border-radius: 40px;
  font-weight: 700;
  font-size: 1rem;
  color: #4a2b33;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.3s;
}

.submit-btn:hover:not(:disabled) {
  transform: scale(1.02);
  box-shadow: 0 10px 20px -5px rgba(196, 69, 105, 0.3);
}

.error-banner {
  background: #ffe3e3;
  border-radius: 40px;
  padding: 0.6rem 1rem;
  font-size: 0.8rem;
  color: #c0392b;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 1rem 0;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(0,0,0,0.1);
  border-top: 2px solid #c44569;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

.signup-prompt {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.85rem;
  color: #666;
}

.link-btn {
  background: none;
  border: none;
  color: #c44569;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.85rem;
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Transitions */
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.25s ease;
}
.fade-slide-enter-from { opacity: 0; transform: translateX(12px); }
.fade-slide-leave-to { opacity: 0; transform: translateX(-12px); }

.fade-scale-enter-active, .fade-scale-leave-active {
  transition: all 0.4s ease;
}
.fade-scale-enter-from, .fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.98);
}

.fade-slide-up-enter-active, .fade-slide-up-leave-active {
  transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
}
.fade-slide-up-enter-from { opacity: 0; transform: translateY(40px); }
.fade-slide-up-leave-to { opacity: 0; transform: translateY(20px); }

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@media (max-width: 640px) {
  .welcome-title { font-size: 2.5rem; }
  .action-buttons { flex-direction: column; width: 80%; margin-top: 2rem; gap: 1rem; }
  .btn-primary, .btn-secondary { justify-content: center; }
  /* .auth-card { padding: 1.5rem; } */
  .back-to-welcome { top: 0.5rem; left: 0.5rem; }
}
</style>