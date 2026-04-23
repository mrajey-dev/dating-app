<template>
  <div class="dating-app">
    <!-- PREMIUM TOAST NOTIFICATION -->
    <transition name="toast-slide">
      <div v-if="toast.show" :class="['premium-toast', toast.type]">
        <div class="toast-icon">
          <svg v-if="toast.type === 'success'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <span>{{ toast.message }}</span>
      </div>
    </transition>

    <!-- SKELETON LOADER - EXACT MATCH TO ACTUAL PROFILE LAYOUT -->
    <div v-if="loading" class="skeleton-wrapper">
      <!-- Hero Section Skeleton -->
      <div class="skeleton-hero-section">
        <div class="skeleton-hero-overlay"></div>
        <div class="skeleton-hero-back-btn shimmer"></div>
        <div class="skeleton-hero-badge shimmer"></div>
        <div class="skeleton-hero-info">
          <div class="skeleton-hero-name shimmer"></div>
          <div class="skeleton-hero-location shimmer"></div>
          <div class="skeleton-hero-quote shimmer"></div>
        </div>
      </div>

      <!-- Profile Card Skeleton -->
      <div class="skeleton-profile-card">
        <!-- Stats Row Skeleton -->
        <div class="skeleton-stats-row">
          <div class="skeleton-stat-item">
            <div class="skeleton-stat-value shimmer"></div>
            <div class="skeleton-stat-label shimmer"></div>
          </div>
          <div class="skeleton-stat-divider"></div>
          <div class="skeleton-stat-item">
            <div class="skeleton-stat-value shimmer"></div>
            <div class="skeleton-stat-label shimmer"></div>
          </div>
          <div class="skeleton-stat-divider"></div>
          <div class="skeleton-stat-item">
            <div class="skeleton-stat-value shimmer"></div>
            <div class="skeleton-stat-label shimmer"></div>
          </div>
        </div>

        <!-- Buttons Row Skeleton -->
        <div class="skeleton-buttons-row">
          <div class="skeleton-btn shimmer"></div>
          <div class="skeleton-btn shimmer"></div>
        </div>

        <!-- About Section Skeleton -->
        <div class="skeleton-about-section">
          <div class="skeleton-section-title shimmer"></div>
          <div class="skeleton-about-text shimmer"></div>
          <div class="skeleton-info-grid">
            <div class="skeleton-info-item shimmer"></div>
            <div class="skeleton-info-item shimmer"></div>
            <div class="skeleton-info-item shimmer"></div>
          </div>
        </div>

        <!-- Habits Section Skeleton -->
        <div class="skeleton-habits-section">
          <div class="skeleton-section-title shimmer"></div>
          <div class="skeleton-habits-container">
            <div class="skeleton-habit-pill shimmer"></div>
            <div class="skeleton-habit-pill shimmer"></div>
            <div class="skeleton-habit-pill shimmer"></div>
            <div class="skeleton-habit-pill shimmer"></div>
          </div>
        </div>

        <!-- Gallery Section Skeleton -->
        <div class="skeleton-gallery-section">
          <div class="skeleton-section-title shimmer"></div>
          <div class="skeleton-gallery-grid">
            <div class="skeleton-gallery-item grid-span-2 shimmer"></div>
            <div class="skeleton-gallery-item shimmer"></div>
            <div class="skeleton-gallery-item shimmer"></div>
            <div class="skeleton-gallery-item shimmer"></div>
            <div class="skeleton-gallery-item shimmer"></div>
          </div>
        </div>
      </div>

      <!-- Bottom Action Bar Skeleton -->
      <div class="skeleton-bottom-bar">
        <div class="skeleton-action-btn shimmer"></div>
        <div class="skeleton-action-btn shimmer"></div>
      </div>
    </div>

    <!-- MAIN PROFILE CONTENT -->
    <div v-else-if="person" class="profile-page">
      <!-- HERO SECTION WITH BACKGROUND IMAGE -->
      <div class="hero-section" :style="{ backgroundImage: `url(${person.profile_photo})` }">
        <div class="hero-overlay"></div>
        
        <!-- Back Button -->
        <div class="hero-back-btn" @click="$router.back()">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </div>
        
        <!-- Verified Badge -->
        <div class="hero-badge" v-if="person.verified_badge == 1">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          <span>Verified</span>
        </div>
        
        <!-- Hero Info -->
        <div class="hero-info">
          <h1 class="hero-name">{{ person.first_name }} <span class="hero-age">{{ age }}</span></h1>
          <div class="hero-location">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>{{ person.city || 'Finding Love' }}</span>
          </div>
          <div class="hero-quote">"{{ person.subtitle || 'Looking for something real' }}"</div>
        </div>
      </div>

      <!-- PROFILE CARD -->
      <div class="profile-card">
        <!-- Stats Row -->
        <div class="stats-row">
          <div class="stat-item">
            <div class="stat-value">{{ formatNumber(person.followers_count || 0) }}</div>
            <div class="stat-label">Followers</div>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <div class="stat-value">{{ formatNumber(person.following_count || 0) }}</div>
            <div class="stat-label">Following</div>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <div class="stat-value">{{ person.photo_gallery?.length || 0 }}</div>
            <div class="stat-label">Posts</div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="buttons-row">
          <button class="btn-follow" :class="{ 'active': isFollowing }" @click="toggleFollow">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="isFollowing ? 'white' : '#333'" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>{{ isFollowing ? 'Following' : 'Follow' }}</span>
          </button>
          <button class="btn-match" :class="matchClass" :disabled="matchLoading" @click="handleMatch">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span>{{ matchText }}</span>
          </button>
        </div>

        <!-- About Section -->
        <div class="about-section">
          <div class="section-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff4d6d" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <h3>About</h3>
          </div>
          <div class="about-text">
            <p>{{ introMessage }}</p>
          </div>
          <div class="info-grid">
            <div class="info-item" v-if="person.dob">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff4d6d" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>Born</span>
              <strong>{{ formatDate(person.dob) }}</strong>
            </div>
            <div class="info-item" v-if="person.status">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff4d6d" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              <span>Status</span>
              <strong>{{ person.status }}</strong>
            </div>
            <div class="info-item" v-if="person.city">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff4d6d" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <span>Lives in</span>
              <strong>{{ person.city }}</strong>
            </div>
          </div>
        </div>

        <!-- Habits Section -->
        <div class="habits-section" v-if="formattedHabits && formattedHabits.length">
          <div class="section-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff4d6d" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-2zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <h3>Habits & Interests</h3>
          </div>
          <div class="habits-container">
            <div v-for="habit in getHabitsArray" :key="habit" class="habit-pill">
              <span class="habit-dot"></span>
              {{ habit }}
            </div>
          </div>
        </div>

        <!-- Gallery Section -->
        <div class="gallery-section">
          <div class="section-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ff4d6d" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="2.5"/><polyline points="21 15 16 10 5 21"/></svg>
            <h3>Gallery</h3>
            <span class="gallery-count">{{ person.photo_gallery?.length || 0 }} photos</span>
          </div>
          <div class="gallery-grid">
            <div
              v-for="(img, idx) in person.photo_gallery.slice(0, 6)"
              :key="idx"
              class="gallery-item"
              :class="{ 'grid-span-2': idx === 0 && person.photo_gallery.length >= 3 }"
              @click="openViewer(idx)"
            >
              <img :src="img" :alt="`Gallery ${idx + 1}`" />
              <div class="gallery-hover">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BOTTOM ACTION BAR -->
      <div class="bottom-action-bar">
        <button class="action-chat" :disabled="matchStatus !== 'matched'" :class="{ 'disabled': matchStatus !== 'matched' }" @click="openChat">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>Message</span>
          <div class="action-glow" v-if="matchStatus === 'matched'"></div>
        </button>
        <button class="action-date" :disabled="matchStatus !== 'matched'" :class="{ 'disabled': matchStatus !== 'matched' }" @click="planDate">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/></svg>
          <span>Plan Date</span>
        </button>
      </div>

      <!-- IMAGE VIEWER MODAL -->
      <transition name="viewer-fade">
        <div v-if="viewerOpen" class="image-viewer" @click="viewerOpen = false">
          <div class="viewer-top">
            <button class="viewer-close" @click.stop="viewerOpen = false">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div class="viewer-counter">{{ currentIndex + 1 }} / {{ person.photo_gallery.length }}</div>
          </div>
          <div class="viewer-main">
            <button v-if="currentIndex > 0" class="viewer-nav prev" @click.stop="currentIndex--">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <img :src="person.photo_gallery[currentIndex]" class="viewer-image" @click.stop />
            <button v-if="currentIndex < person.photo_gallery.length - 1" class="viewer-nav next" @click.stop="currentIndex++">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
          </div>
          <div class="viewer-bottom">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#ff4d6d" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <span>{{ person.first_name }}'s moment</span>
          </div>
        </div>
      </transition>
    </div>

    <!-- CONFIRMATION MODAL -->
    <transition name="modal-pop">
      <div v-if="confirmDialog.show" class="modal-backdrop">
        <div class="confirm-modal">
          <div class="modal-heart">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="#ff4d6d" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          </div>
          <h3>Remove Connection?</h3>
          <p>{{ confirmDialog.message }}</p>
          <div class="modal-buttons">
            <button class="modal-cancel" @click="confirmDialog.show = false">Cancel</button>
            <button class="modal-confirm" @click="confirmDialog.onConfirm()">Yes, Remove</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- CHAT POPUP COMPONENT -->
    <ChatPopup
      :person="person"
      :showChat="showChat"
      :userId="loggedInUserId"
      :canChat="matchStatus === 'matched'"
      @close="showChat = false"
    />
  </div>
</template>

<script>
import axios from "axios"
import ChatPopup from '@/components/ChatPopup.vue'
import { useNotificationStore } from '@/stores/notification'

export default {
  name: "Details",
  components: { ChatPopup },

  beforeRouteEnter(to, from, next) {
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' })
    next()
  },

  beforeRouteUpdate(to, from, next) {
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' })
    next()
  },

  data() {
    return {
      confirmDialog: { show: false, message: "", onConfirm: null },
      toast: { show: false, message: "", type: "success" },
      notificationStore: useNotificationStore(),
      showChat: false,
      isFollowing: null,
      followLoading: false,
      person: null,
      loading: true,
      viewerOpen: false,
      currentIndex: 0,
      matchStatus: null,
      matchLoading: false,
      refreshInterval: null,
      loggedInUserId: 0,
    }
  },

  mounted() {
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' })
    
    this.loggedInUserId = Number(localStorage.getItem("user_id")) || 0
    this.fetchPerson()
    this.refreshInterval = setInterval(() => this.checkMatchStatus(), 5000)
    window.addEventListener("popstate", this.handleBack)
  },

  beforeUnmount() {
    window.removeEventListener("popstate", this.handleBack)
    clearInterval(this.refreshInterval)
  },

  computed: {
    getHabitsArray() {
      if (!this.formattedHabits) return []
      return this.formattedHabits.split(' • ').filter(h => h.trim())
    },

    formattedHabits() {
      if (!this.person || !this.person.habits) return null
      let habits = this.person.habits
      if (Array.isArray(habits)) return habits.length ? habits.join(" • ") : null
      if (typeof habits !== "string") return null
      habits = habits.trim()
      if (!habits || habits === "[]" || habits.toLowerCase() === "null") return null
      for (let i = 0; i < 2; i++) {
        try { habits = JSON.parse(habits) } catch { break }
      }
      if (Array.isArray(habits)) return habits.length ? habits.join(" • ") : null
      if (typeof habits === "string") {
        habits = habits.replace(/^"+|"+$/g, "")
        if (habits.includes(",")) {
          return habits.split(",").map(h => h.trim()).filter(Boolean).join(" • ")
        }
        return habits
      }
      return null
    },

    age() {
      if (!this.person || !this.person.dob) return null
      const dob = new Date(this.person.dob)
      const today = new Date()
      let age = today.getFullYear() - dob.getFullYear()
      const monthDiff = today.getMonth() - dob.getMonth()
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) age--
      return age
    },

    introMessage() {
      if (!this.person) return ""
      return `${this.person.first_name} is a ${this.age}-year-old soul seeking genuine connections. With a heart full of dreams and a spirit for adventure, they believe in the magic of authentic relationships.`
    },

    matchClass() {
      if (this.matchStatus === 'requested') return 'requested'
      if (this.matchStatus === 'matched') return 'matched'
      return ''
    },

    matchText() {
      if (this.matchStatus === null) return 'Connect'
      if (this.matchStatus === 'requested') return 'Requested'
      return 'Connected'
    }
  },

  methods: {
    formatNumber(num) {
      if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
      if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
      return num.toString()
    },

    formatDate(dateStr) {
      if (!dateStr) return ''
      const date = new Date(dateStr)
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    },

    showToast(message, type = "success") {
      this.toast = { show: true, message, type }
      setTimeout(() => { this.toast.show = false }, 3000)
    },

    handleBack(event) {
      if (this.showChat) this.showChat = false
    },

    openChat() {
      if (this.matchStatus !== 'matched') {
        this.showToast("Connect first to start messaging 💕", "error")
        return
      }
      this.showChat = true
      window.history.pushState({ chat: true }, "")
    },

    planDate() {
      if (this.matchStatus !== 'matched') {
        this.showToast("Connect to plan a date ❤️", "error")
        return
      }
      this.$router.push(`/date-planner/${this.person.id}`)
    },

    async handleMatch() {
      if (this.matchLoading || !this.person) return
      this.matchLoading = true

      try {
        if (this.matchStatus === null) {
          const res = await axios.post(
            "https://companion.ajaywatpade.in/api/send-match-request",
            { user_id: this.person.id },
            { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
          )
          if (res.data.success) {
            this.matchStatus = 'requested'
            this.notificationStore.increment()
            this.showToast(`✨ Request sent to ${this.person.first_name}`, "success")
          }
        } else if (this.matchStatus === 'requested') {
          const res = await axios.post(
            "https://companion.ajaywatpade.in/api/cancel-match-request",
            { user_id: this.person.id },
            { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
          )
          if (res.data.success) {
            this.matchStatus = null
            this.showToast("Request cancelled", "success")
          }
        } else if (this.matchStatus === 'matched') {
          this.confirmDialog = {
            show: true,
            message: `Remove ${this.person.first_name} from your connections?`,
            onConfirm: async () => {
              this.matchLoading = true
              try {
                const res = await axios.post(
                  "https://companion.ajaywatpade.in/api/remove-match",
                  { user_id: this.person.id },
                  { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
                )
                if (res.data.success) {
                  this.matchStatus = null
                  this.showToast(`${this.person.first_name} removed`, "success")
                }
              } catch (e) {
                this.showToast("Something went wrong", "error")
              } finally {
                this.matchLoading = false
                this.confirmDialog.show = false
              }
            }
          }
          return
        }
        
        await this.checkMatchStatus()
        
      } catch (e) {
        this.showToast("Action failed", "error")
      } finally {
        if (this.matchStatus !== 'matched') this.matchLoading = false
      }
    },

    async checkMatchStatus() {
      if (!this.person) return
      try {
        const res = await axios.post(
          "https://companion.ajaywatpade.in/api/match-status",
          { user_id: this.person.id },
          { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
        )
        const newStatus = res.data.status === 'accepted' ? 'matched' : (res.data.status ?? null)
        
        if (this.matchStatus !== newStatus) {
          this.matchStatus = newStatus
          if (newStatus === 'matched') {
            this.showToast(`✨ You matched with ${this.person.first_name}!`, "success")
          }
        }
      } catch (e) {
        console.error("Match status failed", e)
      }
    },

    async fetchPerson() {
      try {
        const res = await axios.get(
          `https://companion.ajaywatpade.in/api/users/${this.$route.params.id}`,
          { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
        )
        if (res.data.success && res.data.user) {
          const user = res.data.user
          this.person = {
            ...user,
            profile_photo: user.profile_photo ? `https://companion.ajaywatpade.in/${user.profile_photo}` : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400',
            photo_gallery: Array.isArray(user.photo_gallery) && user.photo_gallery.length 
              ? user.photo_gallery.map(img => `https://companion.ajaywatpade.in/${img}`)
              : ['https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=400', 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=400', 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400']
          }
          await this.checkFollowStatus()
          await this.checkMatchStatus()
          this.loading = false
          
          this.$nextTick(() => {
            window.scrollTo({ top: 0, left: 0, behavior: 'instant' })
            const app = document.querySelector('.dating-app')
            if (app) app.scrollTop = 0
            document.body.scrollTop = 0
            document.documentElement.scrollTop = 0
          })
          
          if (this.$route.query.chat && this.matchStatus === 'matched') this.openChat()
        }
      } catch (e) {
        console.error("Fetch error:", e)
        this.showToast("Profile not found", "error")
        this.$router.back()
      }
    },

    async checkFollowStatus() {
      if (!this.person) return
      try {
        const res = await axios.post(
          "https://companion.ajaywatpade.in/api/follow-status",
          { following_id: this.person.id },
          { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
        )
        this.isFollowing = res.data.following
      } catch (e) {
        console.error("Follow status failed", e)
      }
    },

    async toggleFollow() {
      if (this.followLoading || !this.person) return
      this.followLoading = true
      const wasFollowing = this.isFollowing
      this.isFollowing = !wasFollowing
      let count = Number(this.person.followers_count) || 0
      this.person.followers_count = wasFollowing ? Math.max(count - 1, 0) : count + 1

      try {
        await axios.post(
          "https://companion.ajaywatpade.in/api/follow-toggle",
          { user_id: this.person.id },
          { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
        )
        if (!wasFollowing) {
          this.notificationStore.increment()
          this.showToast(`Following ${this.person.first_name} ✨`, "success")
        }
      } catch (e) {
        this.isFollowing = wasFollowing
        this.person.followers_count = count
        this.showToast("Action failed", "error")
      } finally {
        this.followLoading = false
      }
    },

    openViewer(index) {
      this.currentIndex = index
      this.viewerOpen = true
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

.dating-app {
  max-width: 480px;
  margin: 0 auto;
  background: #ffffff;
  min-height: 100vh;
  position: relative;
  box-shadow: 0 0 40px rgba(0, 0, 0, 0.05);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

/* ========== TOAST ========== */
.premium-toast {
  position: fixed;
  top: 80px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10000;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  background: #1a1a2e;
  border-radius: 50px;
  color: white;
  font-size: 13px;
  font-weight: 500;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  white-space: nowrap;
}
.premium-toast.success { background: linear-gradient(135deg, #11998e, #38ef7d); }
.premium-toast.error { background: linear-gradient(135deg, #ff4d6d, #e63946); }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter, .toast-slide-leave-to { opacity: 0; transform: translateX(-50%) translateY(-20px); }

/* ========== SKELETON LOADER - EXACT MATCH TO PROFILE LAYOUT ========== */
.skeleton-wrapper {
  background: #ffffff;
  min-height: 100vh;
}

/* Hero Section Skeleton */
.skeleton-hero-section {
  height: 420px;
  background: linear-gradient(135deg, #e0e0e0 0%, #d0d0d0 100%);
  position: relative;
  border-radius: 0 0 48px 48px;
  overflow: hidden;
}
.skeleton-hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.5) 60%, rgba(0,0,0,0.7) 100%);
}
.skeleton-hero-back-btn {
  position: absolute;
  top: 50px;
  left: 20px;
  width: 40px;
  height: 40px;
  background: rgba(0,0,0,0.3);
  border-radius: 30px;
}
.skeleton-hero-badge {
  position: absolute;
  top: 50px;
  right: 20px;
  width: 90px;
  height: 34px;
  background: rgba(0,0,0,0.3);
  border-radius: 30px;
}
.skeleton-hero-info {
  position: absolute;
  bottom: 70px;
  left: 0;
  right: 0;
  padding: 0 24px;
}
.skeleton-hero-name {
  width: 180px;
  height: 36px;
  background: rgba(255,255,255,0.3);
  border-radius: 12px;
  margin-bottom: 12px;
}
.skeleton-hero-location {
  width: 120px;
  height: 18px;
  background: rgba(255,255,255,0.3);
  border-radius: 8px;
  margin-bottom: 10px;
}
.skeleton-hero-quote {
  width: 200px;
  height: 16px;
  background: rgba(255,255,255,0.3);
  border-radius: 8px;
}

/* Profile Card Skeleton */
.skeleton-profile-card {
  background: white;
  border-radius: 40px;
  margin: -30px 16px 20px;
  padding: 24px 20px;
  position: relative;
  z-index: 15;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

/* Stats Row Skeleton */
.skeleton-stats-row {
  display: flex;
  justify-content: space-around;
  align-items: center;
  background: #f8f9fc;
  border-radius: 60px;
  padding: 16px 20px;
  margin-bottom: 24px;
}
.skeleton-stat-item {
  flex: 1;
  text-align: center;
}
.skeleton-stat-value {
  width: 50px;
  height: 28px;
  background: #e8e8e8;
  border-radius: 8px;
  margin: 0 auto 6px;
}
.skeleton-stat-label {
  width: 50px;
  height: 12px;
  background: #e8e8e8;
  border-radius: 6px;
  margin: 0 auto;
}
.skeleton-stat-divider {
  width: 1px;
  height: 35px;
  background: #e0e0e0;
}

/* Buttons Row Skeleton */
.skeleton-buttons-row {
  display: flex;
  gap: 12px;
  margin-bottom: 28px;
}
.skeleton-btn {
  flex: 1;
  height: 48px;
  background: #e8e8e8;
  border-radius: 12px;
}

/* About Section Skeleton */
.skeleton-about-section {
  margin-bottom: 28px;
}
.skeleton-section-title {
  width: 80px;
  height: 20px;
  background: #e8e8e8;
  border-radius: 8px;
  margin-bottom: 16px;
}
.skeleton-about-text {
  height: 80px;
  background: #f0f0f0;
  border-radius: 24px;
  margin-bottom: 16px;
}
.skeleton-info-grid {
  display: flex;
  gap: 12px;
}
.skeleton-info-item {
  flex: 1;
  height: 70px;
  background: #f0f0f0;
  border-radius: 20px;
}

/* Habits Section Skeleton */
.skeleton-habits-section {
  margin-bottom: 28px;
}
.skeleton-habits-container {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.skeleton-habit-pill {
  width: 80px;
  height: 38px;
  background: #f0f0f0;
  border-radius: 40px;
}

/* Gallery Section Skeleton */
.skeleton-gallery-section {
  margin-bottom: 20px;
}
.skeleton-gallery-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
.skeleton-gallery-item {
  background: #f0f0f0;
  border-radius: 18px;
  aspect-ratio: 1;
}
.skeleton-gallery-item.grid-span-2 {
  grid-column: span 2;
  aspect-ratio: 16/10;
}

/* Bottom Action Bar Skeleton */
.skeleton-bottom-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  max-width: 480px;
  margin: 0 auto;
  display: flex;
  gap: 16px;
  padding: 16px 24px;
  background: rgba(255,255,255,0.96);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(255,77,109,0.15);
  z-index: 100;
}
.skeleton-action-btn {
  flex: 1;
  height: 50px;
  background: #e8e8e8;
  border-radius: 12px;
}

/* Shimmer Animation */
.shimmer {
  background: linear-gradient(
    90deg,
    #e0e0e0 0%,
    #f0f0f0 50%,
    #e0e0e0 100%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* ========== END SKELETON STYLES ========== */

/* ========== HERO SECTION ========== */
.hero-section {
  height: 420px;
  background-size: cover;
  background-position: center 30%;
  position: relative;
  border-radius: 0 0 48px 48px;
  overflow: hidden;
}
.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.5) 60%, rgba(0,0,0,0.7) 100%);
}
.hero-back-btn {
  position: absolute;
  top: 50px;
  left: 20px;
  width: 40px;
  height: 40px;
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(10px);
  border-radius: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  z-index: 10;
}
.hero-badge {
  position: absolute;
  top: 50px;
  right: 20px;
  background: rgba(255,215,0,0.95);
  padding: 8px 16px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
  color: #1a1a2e;
  display: flex;
  align-items: center;
  gap: 6px;
  z-index: 10;
}
.hero-info {
  position: absolute;
  bottom: 70px;
  left: 0;
  right: 0;
  padding: 0 24px;
  color: white;
  z-index: 10;
}
.hero-name {
  font-size: 36px;
  font-weight: 700;
  letter-spacing: -0.5px;
  display: flex;
  align-items: baseline;
  gap: 8px;
  flex-wrap: wrap;
}
.hero-age {
  font-size: 22px;
  font-weight: 500;
  opacity: 0.9;
}
.hero-location {
  font-size: 14px;
  margin-top: 6px;
  opacity: 0.85;
  display: flex;
  align-items: center;
  gap: 6px;
}
.hero-quote {
  font-size: 13px;
  font-style: italic;
  margin-top: 10px;
  opacity: 0.9;
}

/* ========== PROFILE CARD ========== */
.profile-card {
  background: white;
  border-radius: 40px;
  margin: -30px 16px 20px;
  padding: 24px 20px;
  position: relative;
  z-index: 15;
}

/* Stats Row */
.stats-row {
  display: flex;
  justify-content: space-around;
  align-items: center;
  background: #f8f9fc;
  border-radius: 60px;
  padding: 16px 20px;
  margin-bottom: 24px;
}
.stat-item { text-align: center; flex: 1; }
.stat-value { font-size: 22px; font-weight: 800; color: #ff4d6d; }
.stat-label { font-size: 11px; color: #888; margin-top: 4px; }
.stat-divider { width: 1px; height: 35px; background: #e0e0e0; }

/* Buttons */
.buttons-row {
  display: flex;
  gap: 12px;
  margin-bottom: 28px;
}
.btn-follow, .btn-match {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 0;
  border-radius: 10px;
  font-weight: 600;
  font-size: 14px;
  border: none;
  cursor: pointer;
  transition: all 0.25s ease;
}
.btn-follow {
  background: #f0f0f0;
  color: #333;
}
.btn-follow.active {
  background: linear-gradient(135deg, #ff4d6d, #ff2e63);
  color: white;
  box-shadow: 0 6px 20px rgba(255,46,99,0.3);
}
.btn-match {
  background: linear-gradient(135deg, #a8edea, #fed6e3);
  color: #2d6a4f;
}
.btn-match.requested {
  background: linear-gradient(135deg, #f9d423, #ff4e50);
  color: white;
}
.btn-match.matched {
  background: linear-gradient(135deg, #11998e, #38ef7d);
  color: white;
  box-shadow: 0 6px 20px rgba(56,239,125,0.4);
}
.btn-follow:hover, .btn-match:hover { transform: translateY(-2px); }

/* About Section */
.about-section { margin-bottom: 28px; }
.section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}
.section-title svg { flex-shrink: 0; }
.section-title h3 { font-size: 16px; font-weight: 700; color: #1a1a2e; }
.about-text {
  background: #fef8fa;
  border-radius: 24px;
  padding: 18px;
  margin-bottom: 16px;
  border: 1px solid #ffe2ea;
}
.about-text p { font-size: 14px; line-height: 1.6; color: #555; }
.info-grid {
  display: flex;
  gap: 12px;
  flex-wrap: nowrap;
}
.info-item {
  flex: 1;
  min-width: 100px;
  background: #f8f9fc;
  padding: 12px;
  border-radius: 20px;
  text-align: center;
}
.info-item svg { display: block; margin: 0 auto 6px; }
.info-item span { font-size: 10px; color: #999; display: block; }
.info-item strong { font-size: 12px; color: #333; margin-top: 4px; display: block; }

/* Habits */
.habits-section { margin-bottom: 28px; }
.habits-container { display: flex; flex-wrap: wrap; gap: 10px; }
.habit-pill {
  background: linear-gradient(135deg, #fff0f3, #ffffff);
  border: 1px solid #ffe2ea;
  padding: 10px 18px;
  border-radius: 40px;
  font-size: 13px;
  font-weight: 500;
  color: #ff4d6d;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.habit-dot { width: 6px; height: 6px; background: #ff4d6d; border-radius: 50%; display: inline-block; }

/* Gallery */
.gallery-section { margin-bottom: 20px; }
.gallery-count { margin-left: auto; font-size: 11px; color: #aaa; }
.gallery-grid {
  margin-bottom: 15%;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
.gallery-item {
  position: relative;
  border-radius: 18px;
  overflow: hidden;
  aspect-ratio: 1;
  cursor: pointer;
}
.grid-span-2 {
  grid-column: span 2;
  aspect-ratio: 16/10;
}
.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}
.gallery-item:hover img { transform: scale(1.05); }
.gallery-hover {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.25s;
}
.gallery-item:hover .gallery-hover { opacity: 1; }
.gallery-hover svg { color: white; }

/* Bottom Action Bar */
.bottom-action-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  max-width: 480px;
  margin: 0 auto;
  display: flex;
  gap: 16px;
  padding: 16px 24px;
  background: rgba(255,255,255,0.96);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(255,77,109,0.15);
  z-index: 100;
}
.action-chat, .action-date {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 0;
  border-radius: 10px;
  font-weight: 700;
  font-size: 14px;
  border: none;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.2s;
}
.action-chat {
  background: linear-gradient(135deg, #ff4d6d, #ff2e63);
  color: white;
}
.action-date {
  background: linear-gradient(135deg, #f9b43a, #fb8b2c);
  color: white;
}
.action-chat.disabled, .action-date.disabled {
  opacity: 0.5;
  filter: grayscale(0.2);
  cursor: not-allowed;
}
.action-glow {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
  animation: glowMove 2s infinite;
}
@keyframes glowMove { 0% { left: -100%; } 100% { left: 100%; } }

/* Image Viewer */
.image-viewer {
  position: fixed;
  inset: 0;
  background: #000;
  z-index: 10050;
  display: flex;
  flex-direction: column;
}
.viewer-top {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  z-index: 10;
}
.viewer-close {
  width: 44px;
  height: 44px;
  background: rgba(255,255,255,0.2);
  border: none;
  border-radius: 30px;
  color: white;
  cursor: pointer;
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
}
.viewer-counter {
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(10px);
  padding: 10px 20px;
  border-radius: 30px;
  color: white;
  font-size: 14px;
}
.viewer-main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
  padding: 0 15px;
}
.viewer-image {
  max-width: 85%;
  max-height: 80vh;
  border-radius: 20px;
  box-shadow: 0 0 0 3px white;
}
.viewer-nav {
  width: 48px;
  height: 48px;
  background: rgba(255,255,255,0.2);
  border: none;
  border-radius: 30px;
  color: white;
  cursor: pointer;
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
}
.viewer-bottom {
  position: absolute;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(10px);
  padding: 10px 24px;
  border-radius: 40px;
  color: white;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.viewer-fade-enter-active, .viewer-fade-leave-active { transition: opacity 0.25s; }
.viewer-fade-enter, .viewer-fade-leave-to { opacity: 0; }

/* Confirm Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 11000;
}
.confirm-modal {
  background: white;
  width: 85%;
  max-width: 320px;
  padding: 32px 24px;
  border-radius: 48px;
  text-align: center;
}
.modal-heart { margin-bottom: 12px; }
.confirm-modal h3 { font-size: 20px; margin-bottom: 8px; color: #1a1a2e; }
.confirm-modal p { font-size: 14px; color: #666; margin-bottom: 24px; }
.modal-buttons { display: flex; gap: 12px; }
.modal-cancel, .modal-confirm {
  flex: 1;
  padding: 12px 0;
  border-radius: 50px;
  font-weight: 600;
  font-size: 14px;
  border: none;
  cursor: pointer;
}
.modal-cancel { background: #f0f0f0; color: #666; }
.modal-confirm { background: linear-gradient(135deg, #ff4d6d, #e63946); color: white; }
.modal-pop-enter-active, .modal-pop-leave-active { transition: all 0.2s; }
.modal-pop-enter, .modal-pop-leave-to { opacity: 0; transform: scale(0.9); }
</style>