<template>
  <div class="auth-page">
    <!-- FULLSCREEN ROMANTIC BANNER (Image Slider) -->
    <div class="fullscreen-banner">
      <div class="banner-overlay"></div>
      <div class="banner-slider" :style="sliderStyle">
        <div 
          v-for="(image, idx) in bannerImages" 
          :key="idx"
          class="banner-slide"
          :style="{ backgroundImage: `url(${image.url})` }"
        >
          <div class="slide-caption">
            <h3>{{ image.caption }}</h3>
            <p>{{ image.subtext }}</p>
          </div>
        </div>
      </div>
      <button class="banner-nav prev" @click="prevSlide">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
          <path d="M15 18L9 12L15 6" />
        </svg>
      </button>
      <button class="banner-nav next" @click="nextSlide">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
          <path d="M9 18L15 12L9 6" />
        </svg>
      </button>
      <div class="banner-dots">
        <span 
          v-for="(_, idx) in bannerImages" 
          :key="idx"
          :class="['dot', { active: currentSlide === idx }]"
          @click="currentSlide = idx"
        ></span>
      </div>
    </div>

    <!-- TRANSPARENT / GLASS AUTH CARD -->
    <div class="auth-card glass-card" :class="{ 'slide-up': activeTab === 'signup' }">
      <!-- Hero Header - More subtle -->
      <div class="hero-header glass-header">
        <div class="hero-circles">
          <div class="hc hc-1"></div>
          <div class="hc hc-2"></div>
          <div class="hc hc-3"></div>
        </div>
        <div class="brand-wrap">
          <div class="heart-badge glass-badge">
            <svg width="22" height="20" viewBox="0 0 22 20" fill="none">
              <path d="M11 18.5C11 18.5 1 12.5 1 6C1 3.2 3.2 1 6 1C8.1 1 9.9 2.3 11 4C12.1 2.3 13.9 1 16 1C18.8 1 21 3.2 21 6C21 12.5 11 18.5 11 18.5Z" fill="white"/>
            </svg>
          </div>
          <h1 class="brand-name"><span class="brand-heart">Heart</span>Link</h1>
          <p class="brand-tagline">Find your perfect match</p>
        </div>
      </div>

      <!-- Card Body - Glassmorphism -->
      <div class="card-body glass-body">

        <!-- Tab Switcher - Transparent style -->
        <div class="tab-switcher glass-tabs">
          <div class="tab-track">
            <div class="tab-thumb" :class="{ right: activeTab === 'signup' }"></div>
            <button class="tab-btn" :class="{ active: activeTab === 'login' }" @click="activeTab = 'login'">Login</button>
            <button class="tab-btn" :class="{ active: activeTab === 'signup' }" @click="activeTab = 'signup'">Sign Up</button>
          </div>
        </div>

        <!-- LOGIN FORM - Transparent inputs -->
        <transition name="fade-slide">
          <div v-if="activeTab === 'login'" key="login" class="form-section">
            
            <div class="field-group" :class="{ focused: focusedField === 'email', filled: login.email }">
              <label class="field-label glass-label">Email address</label>
              <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <rect x="1" y="3" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.3"/>
                  <path d="M1 6L8 10L15 6" stroke="currentColor" stroke-width="1.3"/>
                </svg>
                <input
                  type="email"
                  v-model="login.email"
                  placeholder="you@example.com"
                  @focus="focusedField = 'email'"
                  @blur="focusedField = ''"
                  autocomplete="email"
                  class="glass-input"
                />
              </div>
            </div>

            <div class="field-group" :class="{ focused: focusedField === 'password', filled: login.password }">
              <label class="field-label glass-label">Password</label>
              <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.3"/>
                  <path d="M5 7V5C5 3.3 6.3 2 8 2C9.7 2 11 3.3 11 5V7" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  v-model="login.password"
                  placeholder="Enter your password"
                  @focus="focusedField = 'password'"
                  @blur="focusedField = ''"
                  autocomplete="current-password"
                  class="glass-input"
                />
                <button class="eye-btn glass-icon-btn" @click="showPassword = !showPassword" tabindex="-1">
                  <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M1 8C1 8 3.5 3 8 3C12.5 3 15 8 15 8C15 8 12.5 13 8 13C3.5 13 1 8 1 8Z" stroke="currentColor" stroke-width="1.3"/>
                    <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.3"/>
                  </svg>
                  <svg v-else width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M2 2L14 14M6.5 6.6C6.2 6.9 6 7.4 6 8C6 9.1 6.9 10 8 10C8.6 10 9.1 9.8 9.4 9.5M4.3 4.4C2.8 5.4 1.8 6.9 1 8C1 8 3.5 13 8 13C9.8 13 11.3 12.3 12.5 11.4M7 3.1C7.3 3 7.7 3 8 3C12.5 3 15 8 15 8C14.6 8.7 14.1 9.4 13.5 10" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>

            <button class="forgot-btn glass-link" @click="forgotPassword">Forgot password?</button>

            <transition name="fade">
              <div v-if="error" class="error-banner glass-error">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.3"/>
                  <path d="M7 4V7M7 10H7.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                {{ error }}
              </div>
            </transition>

            <button class="btn-primary glass-primary-btn" @click="submitLogin" :disabled="loading || !login.email || !login.password">
              <span v-if="loading" class="btn-spinner"></span>
              <span v-else>Login to HeartLink</span>
              <svg v-if="!loading" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M3 8H13M9 4L13 8L9 12" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <!-- <div class="divider glass-divider">
              <span>or continue with</span>
            </div> -->

            <div class="social-row">
              <button class="social-btn glass-social">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M15.5 8.2C15.5 7.6 15.4 7 15.3 6.5H8V9.7H12.2C12 10.7 11.4 11.5 10.5 12.1V14.1H13.1C14.6 12.7 15.5 10.6 15.5 8.2Z" fill="#4285F4"/>
                  <path d="M8 16C10.1 16 11.9 15.3 13.1 14.1L10.5 12.1C9.8 12.6 8.9 12.9 8 12.9C5.9 12.9 4.1 11.5 3.5 9.5H0.8V11.6C2 13.9 4.8 16 8 16Z" fill="#34A853"/>
                  <path d="M3.5 9.5C3.3 8.9 3.2 8.2 3.2 7.5C3.2 6.8 3.3 6.1 3.5 5.5V3.4H0.8C0.3 4.5 0 5.7 0 7C0 8.3 0.3 9.5 0.8 10.6L3.5 8.5V9.5Z" fill="#FBBC05"/>
                  <path d="M8 3.1C9 3.1 9.9 3.5 10.6 4.1L13.2 1.5C11.9 0.4 10.1 0 8 0C4.8 0 2 2.1 0.8 4.4L3.5 6.5C4.1 4.5 5.9 3.1 8 3.1Z" fill="#EA4335"/>
                </svg>
                Google
              </button>
              <button class="social-btn glass-social">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M16 8C16 3.6 12.4 0 8 0C3.6 0 0 3.6 0 8C0 12 2.9 15.3 6.7 15.9V10.3H4.7V8H6.7V6.2C6.7 4.2 7.9 3.1 9.7 3.1C10.6 3.1 11.5 3.3 11.5 3.3V5.2H10.5C9.5 5.2 9.2 5.8 9.2 6.4V8H11.4L11 10.3H9.2V15.9C13 15.3 16 12 16 8Z" fill="#1877F2"/>
                </svg>
                Facebook
              </button>
            </div>

            <p class="switch-text glass-switch">
              New to HeartLink?
              <button class="link-btn glass-link-btn" @click="activeTab = 'signup'">Create account →</button>
            </p>

          </div>
        </transition>

        <!-- SIGNUP FORM (with glass styling) -->
        <transition name="fade-slide">
          <div v-if="activeTab === 'signup'" key="signup" class="form-section">
            <SignupForm />
            <!-- <p class="switch-text glass-switch">
              Already have an account?
              <button class="link-btn glass-link-btn" @click="activeTab = 'login'">← Back to Login</button>
            </p> -->
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import SignupForm from '@/components/CreateAccount.vue'

export default {
  name: 'AuthPage',
  components: { SignupForm },

  data() {
    return {
      activeTab: 'login',
      focusedField: '',
      showPassword: false,
      loading: false,
      login: {
        email: '',
        password: ''
      },
      error: '',
      currentSlide: 0,
      bannerImages: [
        {
          url: 'https://images.pexels.com/photos/1689731/pexels-photo-1689731.jpeg?auto=compress&cs=tinysrgb&w=1600',
          caption: 'Find Your Spark',
          subtext: 'Meaningful connections start here'
        },
        {
          url: 'https://images.pexels.com/photos/2253879/pexels-photo-2253879.jpeg?auto=compress&cs=tinysrgb&w=1600',
          caption: 'Real Love Stories',
          subtext: 'Thousands found their soulmate'
        },
        {
          url: 'https://images.pexels.com/photos/3823497/pexels-photo-3823497.jpeg?auto=compress&cs=tinysrgb&w=1600',
          caption: 'Adventure Awaits',
          subtext: 'Share your journey together'
        },
        {
          url: 'https://images.pexels.com/photos/3768131/pexels-photo-3768131.jpeg?auto=compress&cs=tinysrgb&w=1600',
          caption: 'Heartfelt Moments',
          subtext: 'Where chemistry meets destiny'
        }
      ],
      autoPlayInterval: null
    }
  },

  computed: {
    sliderStyle() {
      return {
        transform: `translateX(-${this.currentSlide * 100}%)`
      }
    }
  },

  mounted() {
    const token = localStorage.getItem('token')
    if (token) this.$router.push('/home')
    this.startAutoPlay()
  },

  beforeDestroy() {
    this.stopAutoPlay()
  },

  methods: {
    startAutoPlay() {
      this.autoPlayInterval = setInterval(() => {
        this.nextSlide()
      }, 5000)
    },
    stopAutoPlay() {
      if (this.autoPlayInterval) {
        clearInterval(this.autoPlayInterval)
        this.autoPlayInterval = null
      }
    },
    nextSlide() {
      this.currentSlide = (this.currentSlide + 1) % this.bannerImages.length
      this.resetAutoPlayTimer()
    },
    prevSlide() {
      this.currentSlide = (this.currentSlide - 1 + this.bannerImages.length) % this.bannerImages.length
      this.resetAutoPlayTimer()
    },
    resetAutoPlayTimer() {
      this.stopAutoPlay()
      this.startAutoPlay()
    },
    async submitLogin() {
      this.error = ''
      this.loading = true
      try {
        const res = await axios.post('https://companion.ajaywatpade.in/api/login', {
          email: this.login.email,
          password: this.login.password
        })
        localStorage.setItem('token', res.data.token)
        localStorage.setItem('user', JSON.stringify(res.data.user))
        this.$router.push('/home')
      } catch (err) {
        this.error = err.response?.data?.message || 'Login failed. Please try again.'
      } finally {
        this.loading = false
      }
    },
    forgotPassword() {
      alert('Reset link will be sent to your email.')
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Nunito', sans-serif;
  position: relative;
  overflow-x: hidden;
  padding: 20px;
}

/* ========= FULLSCREEN BANNER ========= */
.fullscreen-banner {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  overflow: hidden;
}

.banner-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.4) 100%);
  z-index: 1;
}

.banner-slider {
  display: flex;
  width: 100%;
  height: 100%;
  transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.banner-slide {
  min-width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  position: relative;
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.slide-caption {
  position: absolute;
  bottom: 15%;
  left: 0;
  right: 0;
  text-align: center;
  color: white;
  z-index: 2;
  text-shadow: 0 2px 12px rgba(0,0,0,0.3);
  animation: fadeUp 0.6s ease-out;
}

.slide-caption h3 {
  font-size: 28px;
  font-weight: 800;
  margin-bottom: 8px;
  letter-spacing: -0.3px;
}

.slide-caption p {
  font-size: 16px;
  font-weight: 400;
  opacity: 0.9;
}

@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.banner-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(8px);
  border: none;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 3;
  transition: all 0.2s ease;
  color: white;
}

.banner-nav:hover {
  background: rgba(255,255,255,0.4);
  transform: translateY(-50%) scale(1.05);
}

.banner-nav.prev { left: 20px; }
.banner-nav.next { right: 20px; }

.banner-dots {
  position: absolute;
  bottom: 30px;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  gap: 12px;
  z-index: 3;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255,255,255,0.5);
  cursor: pointer;
  transition: all 0.2s ease;
}

.dot.active {
  width: 28px;
  border-radius: 6px;
  background: #fd5068;
  box-shadow: 0 0 8px rgba(253,80,104,0.6);
}

/* ========= GLASS CARD (TRANSPARENT & BEAUTIFUL) ========= */
.auth-card {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 420px;
  transition: all 0.4s ease;
}

.glass-card {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(20px);
  border-top-right-radius: 54px;
  border-top-left-radius: 54px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 0.5px rgba(255,255,255,0.2) inset;
  overflow: hidden;
}

/* Hero Header Glass */
.hero-header {
  padding: 32px 24px 28px;
  text-align: center;
  position: relative;
  overflow: hidden;
  background: rgba(253, 80, 104, 0.2);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.hero-circles { position: absolute; inset: 0; pointer-events: none; }
.hc {
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,0.1);
}
.hc-1 { width: 180px; height: 180px; top: -60px; right: -40px; }
.hc-2 { width: 100px; height: 100px; bottom: -20px; left: -20px; }
.hc-3 { width: 60px; height: 60px; top: 20px; left: 30px; background: rgba(255,255,255,0.07); }

.brand-wrap { position: relative; z-index: 1; }

.heart-badge {
  width: 52px; height: 52px;
  border-radius: 18px;
  background: rgba(255,255,255,0.25);
  backdrop-filter: blur(12px);
  border: 1.5px solid rgba(255,255,255,0.4);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 12px;
  animation: heartPulse 2.5s ease-in-out infinite;
}
@keyframes heartPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.08); }
}

.brand-name {
  font-size: 28px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.5px;
  margin: 0 0 4px;
  text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
.brand-heart { color: #ffd6dc; }
.brand-tagline {
  font-size: 13px;
  color: rgba(255,255,255,0.9);
  font-weight: 500;
  margin: 0;
  text-shadow: 0 1px 4px rgba(0,0,0,0.1);
}

/* Card Body */
.card-body { padding: -1px 24px 32px; background: transparent; }

.tab-track {
  position: relative;
  display: none;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(8px);
  border-radius: 60px;
  padding: 4px;
  gap: 4px;
  border: 1px solid rgba(255,255,255,0.2);
}
.tab-thumb {
  position: absolute;
  top: 4px; left: 4px;
  width: calc(50% - 6px);
  height: calc(100% - 8px);
  background: rgba(255,255,255,0.95);
  border-radius: 60px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.1);
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  pointer-events: none;
}
.tab-thumb.right { transform: translateX(calc(100% + 4px)); }
.tab-btn {
  flex: 1;
  padding: 10px 0;
  border: none;
  background: transparent;
  border-radius: 60px;
  font-size: 14px;
  font-weight: 700;
  color: rgba(255,255,255,0.7);
  cursor: pointer;
  transition: all 0.3s;
  font-family: 'Nunito', sans-serif;
  position: relative;
  z-index: 1;
}
.tab-btn.active { color: #fd5068; background: transparent; }

/* Glass Form Fields */
.field-group { margin-bottom: 20px; padding: 8px;}
.field-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: rgba(255,255,255,0.7);
  margin-bottom: 6px;
  transition: color 0.2s;
}
.field-group.focused .field-label { color: #ffb6c1; }

.field-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.field-icon {
  position: absolute;
  left: 16px;
  color: rgba(255,255,255,0.6);
  transition: color 0.2s;
  pointer-events: none;
}
.field-group.focused .field-icon { color: #ffb6c1; }

.glass-input {
  width: 100%;
  height: 52px;
  border-radius: 30px;
  border: 1px solid rgba(255,255,255,0.25);
  background: rgba(255,255,255,0.12);
  backdrop-filter: blur(8px);
  padding: 0 48px 0 44px;
  font-size: 15px;
  font-family: 'Nunito', sans-serif;
  color: white;
  outline: none;
  transition: all 0.25s;
}
.glass-input::placeholder { color: rgba(255,255,255,0.5); }
.glass-input:focus {
  border-color: #fd5068;
  background: rgba(255,255,255,0.2);
  box-shadow: 0 0 0 3px rgba(253, 80, 104, 0.2);
}
.glass-input:-webkit-autofill,
.glass-input:-webkit-autofill:focus {
  -webkit-text-fill-color: white;
  -webkit-box-shadow: 0 0 0px 1000px rgba(255,255,255,0.15) inset;
  transition: background-color 5000s ease-in-out 0s;
}

.eye-btn {
  position: absolute;
  right: 16px;
  background: transparent;
  border: none;
  cursor: pointer;
  color: rgba(255,255,255,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
  transition: color 0.2s;
  border-radius: 50%;
}
.eye-btn:hover { color: #fd5068; background: rgba(255,255,255,0.1); }

.forgot-btn {
  background: transparent;
  border: none;
  font-size: 13px;
  font-weight: 600;
  color: rgba(255,255,255,0.8);
  cursor: pointer;
  font-family: 'Nunito', sans-serif;
  text-align-last: center;
  padding: 0;
  margin-bottom: 20px;
  display: block;
  text-align: right;
  width: 100%;
  transition: all 0.2s;
}
.forgot-btn:hover { color: #ffb6c1; text-shadow: 0 0 6px rgba(253,80,104,0.5); }

/* Error Banner */
.error-banner {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 240, 242, 0.9);
  backdrop-filter: blur(12px);
  border: 1px solid #ffd0d7;
  border-radius: 20px;
  padding: 12px 16px;
  font-size: 13px;
  color: #d44;
  margin-bottom: 16px;
  animation: shake 0.4s ease;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20% { transform: translateX(-6px); }
  40% { transform: translateX(6px); }
  60% { transform: translateX(-4px); }
  80% { transform: translateX(4px); }
}

/* Glass Primary Button */
.btn-primary {
  width: 100%;
  height: 54px;
  border-radius: 40px;
  border: none;
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: white;
  font-size: 15px;
  font-weight: 800;
  font-family: 'Nunito', sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.25s;
  box-shadow: 0 8px 24px rgba(253, 80, 104, 0.4);
  margin-bottom: 8px;
}
.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(253, 80, 104, 0.5);
}
.btn-primary:active:not(:disabled) { transform: translateY(0); }
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.btn-spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Divider */
.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 22px 0 18px;
  font-size: 12px;
  color: rgba(255,255,255,0.6);
}
.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(255,255,255,0.25);
}

/* Glass Social Buttons */
.social-row { display: none; gap: 12px; margin-bottom: 20px; }
.social-btn {
  flex: 1;
  height: 48px;
  border-radius: 40px;
  border: 1px solid rgba(255,255,255,0.3);
  background: rgba(255,255,255,0.12);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 13px;
  font-weight: 600;
  color: white;
  cursor: pointer;
  font-family: 'Nunito', sans-serif;
  transition: all 0.2s;
}
.social-btn:hover {
  border-color: #fd5068;
  background: rgba(255,255,255,0.25);
  transform: translateY(-1px);
}

/* Switch Text */
.switch-text {
  font-size: 13px;
  color: rgba(255,255,255,0.7);
  text-align: center;
  margin-top: 10px;
    margin-bottom: 15px;
}
.link-btn {
  background: none;
  border: none;
  font-size: 13px;
  font-weight: 700;
  color: #ffb6c1;
  cursor: pointer;
  font-family: 'Nunito', sans-serif;
  padding: 0;
  margin-left: 6px;
  transition: all 0.2s;
}
.link-btn:hover { color: white; text-shadow: 0 0 6px #fd5068; }

/* Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from { opacity: 0; transform: translateX(20px); }
.fade-slide-leave-to { opacity: 0; transform: translateX(-20px); position: absolute; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Responsive */
@media (max-width: 480px) {
  .auth-card { max-width: 100%; position: absolute; }
  .hero-header { padding: 24px 20px; }
  /* .card-body { padding: 24px 20px; } */
  .glass-input { height: 38px; font-size: 14px; }
  .banner-nav { width: 36px; height: 36px; }
  .slide-caption h3 { font-size: 22px; }
  .slide-caption p { font-size: 13px; }
}
</style>