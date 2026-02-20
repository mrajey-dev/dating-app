<template>
  <div>
    <!-- SKELETON LOADER -->
    <div v-if="loading" class="details-skeleton">
      <!-- Header -->
      <div class="skeleton-header"></div>

      <!-- Card -->
      <div class="skeleton-card">
        <!-- Insta Profile -->
        <div class="skeleton-insta">
          <div class="skeleton-avatar shimmer"></div>

          <div class="skeleton-right">
            <div class="skeleton-line name shimmer"></div>

            <div class="skeleton-stats">
              <div class="skeleton-stat shimmer"></div>
              <div class="skeleton-stat shimmer"></div>
              <div class="skeleton-stat shimmer"></div>
            </div>

            <div class="skeleton-btn shimmer"></div>
          </div>
        </div>

        <!-- Intro -->
        <div class="skeleton-intro">
          <div class="skeleton-line full shimmer"></div>
          <div class="skeleton-line full shimmer"></div>
          <div class="skeleton-line half shimmer"></div>
        </div>

        <!-- Habits -->
        <div class="skeleton-habit shimmer"></div>

        <!-- Gallery -->
        <div class="skeleton-gallery">
          <div class="skeleton-photo shimmer"></div>
          <div class="skeleton-photo shimmer"></div>
          <div class="skeleton-photo shimmer"></div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="skeleton-bottom">
        <div class="skeleton-price shimmer"></div>
        <div class="skeleton-action shimmer"></div>
      </div>
    </div>

    <!-- ACTUAL CONTENT -->
    <div v-else-if="person" class="details">
      <!-- Header -->
      <div class="details-header">
     
      </div>

      <!-- Card -->
      <div class="details-card">
        <div class="insta-profile">
          <!-- LEFT COLUMN -->
          <div class="insta-left">
            <img class="insta-avatar" :src="person.profile_photo" />
            <p class="insta-subtitle" v-if="person.subtitle">
              {{ person.subtitle }}
            </p>
          </div>

          <!-- RIGHT COLUMN -->
        <div class="insta-right">
  <div class="insta-name-row">
    <span class="insta-name">{{ person.first_name }}</span>
    <img
      v-if="person.verified_badge == 1"
      src="@/assets/verified1.png"
      class="verified"
    />
  </div>

  <div class="insta-stats">
    <div class="insta-stat">
      <strong>{{ person.photo_gallery?.length || 0 }}</strong>
      <span>Posts</span>
    </div>
    <div class="insta-stat">
      <strong>{{ person.followers_count || 0 }}</strong>
      <span>Followers</span>
    </div>
    <div class="insta-stat">
      <strong>{{ person.following_count || 0 }}</strong>
      <span>Following</span>
    </div>
  </div>

  <!-- Buttons Row -->
  <div class="insta-btn-row">
    <button
      v-if="isFollowing !== null"
      class="insta-follow-btn"
      :class="{ following: isFollowing }"
      @click="toggleFollow"
    >
      {{ isFollowing ? "Following" : "Follow" }}
    </button>

   <button
  class="insta-match-btn"
  :class="{
    requested: matchStatus === 'requested',
    matched: matchStatus === 'matched'
  }"
  :disabled="matchStatus === 'matched' || matchLoading"
  @click="handleMatch"
>
  <span v-if="matchStatus === null">Match</span>
  <span v-else-if="matchStatus === 'requested'">Requested</span>
  <span v-else>Matched</span>
</button>

  </div>
</div>

        </div>

        <!-- Self Introduction -->
        <div class="section intro-section">
          <div class="intro-card">
            <p class="intro-text">{{ introMessage }}</p>
          </div>
        </div>

        <!-- Stats -->
        <div class="stats">
          <div class="stat">
            <strong>{{ person.like_count || 0 }}</strong>
            <span>Likes ❤️</span>
          </div>
          <div class="stat">
            <strong>{{ person.comments || 0 }}</strong>
            <span>Comments 💬</span>
          </div>
          <div class="stat">
            <strong>{{ person.rating || '4.5' }}</strong>
            <span>Rating ⭐</span>
          </div>
        </div>

        <!-- Habits -->
        <div class="section" v-if="formattedHabits && formattedHabits.length">
          <h4 class="section-title">Habits</h4>
          <div class="habit-card">
            <span class="habit-text">🌿 {{ formattedHabits }}</span>
          </div>
        </div>

        <!-- Photo Gallery -->
        <div class="section">
          <h4 class="section-title">More Photos</h4>
          <div class="photo-gallery">
            <div
              v-for="(img, index) in person.photo_gallery"
              :key="index"
              class="photo-card"
              @click="openViewer(index)"
            >
              <img :src="img" />
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="bottom-bar">
        <div class="price">
          <strong></strong>
        </div>

        <!-- Only Button, Chat moved to separate component -->
       <button 
  class="messaging"
  :disabled="matchStatus !== 'matched'"
  :class="{ disabledBtn: matchStatus !== 'matched' }"
  @click="openChat"
>
  {{ matchStatus === 'matched' ? 'Start Messaging' : 'Match to Message' }}
</button>
 <button 
  class="dateplan"
  :disabled="matchStatus !== 'matched'"
  :class="{ disabledBtn: matchStatus !== 'matched' }"
    @click="$router.push(`/date-planner/${person.id}`)"
>
  {{ matchStatus === 'matched' ? 'Plan a Date ❤️' : 'Match to Date' }}
</button>

      </div>

      <!-- Image Viewer -->
      <div v-if="viewerOpen" class="viewer" @click="viewerOpen = false">
        <span class="close" @click="viewerOpen = false">✕</span>
        <img
          :src="person.photo_gallery[currentIndex]"
          class="viewer-img"
          @click.stop
        />
      </div>

      <!-- Success Overlay -->
     
    </div>

    <!-- Include Chat Popup Component -->
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
   components: {
    ChatPopup
  },

  data() {
    return {
      
       notificationStore: useNotificationStore(),
      showChat: false,         // controls chat popup
      isFollowing: null,       // follow state
      followLoading: false,    // follow/unfollow API loading
      person: null,            // user details
      loading: true,           // skeleton loader
      viewerOpen: false,       // photo viewer
      currentIndex: 0,          // photo viewer index
        // MATCH STATES
    matchStatus: null, // null | 'requested' | 'matched'
    matchLoading: false,
    refreshInterval: null,
      loggedInUserId: 0,
    }
  },

mounted() {
  this.loggedInUserId = Number(localStorage.getItem("user_id")) || 0
  this.fetchPerson()

  window.addEventListener("popstate", this.handleBack)
},

beforeUnmount() {
  window.removeEventListener("popstate", this.handleBack)
},
  computed: {
    // Format habits nicely
    formattedHabits() {
      if (!this.person || !this.person.habits) return null

      let habits = this.person.habits

      if (Array.isArray(habits)) {
        return habits.length ? habits.join(" • ") : null
      }

      if (typeof habits !== "string") return null

      habits = habits.trim()
      if (!habits || habits === "[]" || habits.toLowerCase() === "null") return null

      for (let i = 0; i < 2; i++) {
        try {
          habits = JSON.parse(habits)
        } catch {
          break
        }
      }

      if (Array.isArray(habits)) return habits.length ? habits.join(" • ") : null

      if (typeof habits === "string") {
        habits = habits.replace(/^"+|"+$/g, "")
        if (habits.includes(",")) {
          return habits
            .split(",")
            .map(h => h.trim())
            .filter(Boolean)
            .join(" • ")
        }
        return habits
      }

      return null
    },

    // Calculate age
    age() {
      if (!this.person || !this.person.dob) return null

      const dob = new Date(this.person.dob)
      const today = new Date()

      let age = today.getFullYear() - dob.getFullYear()
      const monthDiff = today.getMonth() - dob.getMonth()
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--
      }

      return age
    },

    // Auto-generated intro message
    introMessage() {
      if (!this.person) return ""

      const name = this.person.first_name || ""
      const city = this.person.city || ""
      const status = this.person.status || ""
      const age = this.age

      return `Hi, my name is ${name}. I’m ${age} years old and I live in ${city}. I’m ${status} and here to find a genuine and meaningful connection. Looking forward to meeting the right companion and seeing where things go 😊.`
    }
  },

  methods: {
    handleBack(event) {
  if (this.showChat) {
    this.showChat = false
  }
},

openChat() {
  if (this.matchStatus !== 'matched') {
    alert("You can message only after matching 💕")
    return
  }

  this.showChat = true

  // Push fake state into history
  window.history.pushState({ chat: true }, "")
},


async handleMatch() {
  if (this.matchLoading || !this.person) return
  this.matchLoading = true

  try {
    if (this.matchStatus === null) {
      // Send match request
      const res = await axios.post(
        "https://companion.ajaywatpade.in/api/send-match-request",
        { user_id: this.person.id },
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`
          }
        }
      )

      if (res.data.success) {
        this.matchStatus = 'requested'
        this.notificationStore.increment()
      }
    } else if (this.matchStatus === 'requested') {
      // Cancel match request
      const res = await axios.post(
        "https://companion.ajaywatpade.in/api/cancel-match-request",
        { user_id: this.person.id },
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`
          }
        }
      )

      if (res.data.success) {
        this.matchStatus = null
      }
    }
  } catch (e) {
    console.error("Match action failed", e)
  } finally {
    this.matchLoading = false
  }
},


    // Check match status
async checkMatchStatus() {
  if (!this.person) return
  try {
    const res = await axios.post(
      "https://companion.ajaywatpade.in/api/match-status",
      { user_id: this.person.id },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`
        }
      }
    )
  this.matchStatus = res.data.status ?? null
  } catch (e) {
    console.error("Match status failed", e)
  }
},

    // Fetch user details
    async fetchPerson() {
      try {
        const res = await axios.get(
          `https://companion.ajaywatpade.in/api/users/${this.$route.params.id}`,
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token")}`
            }
          }
        )

        const data = res.data
        if (data.success && data.user) {
          const user = data.user

          const profilePhoto = user.profile_photo
            ? `https://companion.ajaywatpade.in/${user.profile_photo}`
            : null

          let gallery = []
          if (Array.isArray(user.photo_gallery)) {
            gallery = user.photo_gallery.map(
              img => `https://companion.ajaywatpade.in/${img}`
            )
          }

          this.person = {
            ...user,
            profile_photo: profilePhoto,
            photo_gallery: gallery
          }

          await this.checkFollowStatus()
          await this.checkMatchStatus()
          this.loading = false
        }
      } catch (e) {
  console.error(e)
  alert("User not found")
  this.$router.back()
}

    },

    // Check if current user is following this person
    async checkFollowStatus() {
      if (!this.person) return
      try {
        const res = await axios.post(
          "https://companion.ajaywatpade.in/api/follow-status",
          { following_id: this.person.id },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token")}`
            }
          }
        )
        this.isFollowing = res.data.following
      } catch (e) {
        console.error("Follow status check failed", e)
      }
    },

    // Follow/Unfollow user
   async toggleFollow() {
  if (this.followLoading || !this.person) return
  this.followLoading = true

  const wasFollowing = this.isFollowing
  this.isFollowing = !wasFollowing

  if (!wasFollowing) {
    this.person.followers_count = (this.person.followers_count || 0) + 1
  } else {
    this.person.followers_count = Math.max(
      (this.person.followers_count || 1) - 1,
      0
    )
  }

  try {
    await axios.post(
      "https://companion.ajaywatpade.in/api/follow-toggle",
      { user_id: this.person.id },
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`
        }
      }
    )

    // 🔔 INCREMENT NOTIFICATION ONLY WHEN FOLLOWING
    if (!wasFollowing) {
      this.notificationStore.increment()
    }

  } catch (e) {
    // Rollback on failure
    this.isFollowing = wasFollowing
    if (!wasFollowing) this.person.followers_count--
    else this.person.followers_count++
    console.error("Follow failed", e)
  } finally {
    this.followLoading = false
  }
},


    // Open photo viewer
    openViewer(index) {
      this.currentIndex = index
      this.viewerOpen = true
    }
  }
}
</script>


<style scoped>
.details {
  min-height: 100vh;
  background: #ffffff;
  /* background-image: url(https://www.shutterstock.com/blog/wp-content/uploads/sites/5/2020/07/trendy-background-ideas-cover.jpg); */
  padding-bottom: 90px;
  font-family: 'Inter', sans-serif;
}

/* Header */
.details-header {
  height: 151px;
   /* background-image: url(https://static.vecteezy.com/system/resources/thumbnails/001/410/432/small/pink-fluid-dynamic-abstract-background-free-vector.jpg); */
  border-bottom-left-radius: 60px;
  border-bottom-right-radius: 60px;
  /* position: relative; */
}

.back {
  position: absolute;
  top: 55px;
  left: 16px;
  font-size: 32px;
  color: rgb(236, 3, 73);
  cursor: pointer;
}

/* Card */
.details-card {
  /* background: white; */
  border-radius: 28px;
  margin: -120px 16px 0;
  padding: 16px;
  /* box-shadow: 0 20px 40px rgba(0,0,0,0.15); */
  text-align: center;
}

/* Image */
.hero-img {
      width: 101px;
    height: 101px;
  border-radius: 171px;
  margin-top: -87px;
  border-bottom-style: inset;
}

/* Title */
.details-card h2 {
  margin-top: 14px;
  font-size: 20px;
  font-weight: 700;
  display: flex;
  justify-content: center;
  gap: 6px;
}

.verified {
      height: 18px;
    margin-top: 7px;
    width: 18px;
}

.meta {
  font-size: 13px;
  color: #666;
}

.rating {
  margin: 8px 0 14px;
  font-size: 13px;
  color: #444;
}

/* Features */
.features {
  display: flex;
  justify-content: space-around;
  margin: 16px 0;
  font-size: 13px;
}

/* Sections */
.section {
  margin-top: 14px;
  text-align: left;
}

.section h4 {
  font-size: 11px;
  margin-bottom: 6px;
  color: #444;
}

.box {
  background: #f6f7fb;
  padding: 14px;
  border-radius: 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
}

.bottom-bar {
  position: fixed;
  bottom: 0;
  left: 0;
      gap: 12px;
  width: 100%;
  background: #ffffff;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 -6px 20px rgba(0, 0, 0, 0.08);
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  z-index: 1000;
}

/* Price Section */
.price {
  display: flex;
  flex-direction: column;
}

.price span {
  font-size: 12px;
  color: #888;
  font-weight: 500;
}

.price strong {
  font-size: 13px;
  font-weight: 700;
  color: #111;
}

/* Checkout Button */
.messaging {
  background: linear-gradient(135deg, #ff4d6d, #aa0730);
  color: #fff;
  border: none;
  padding: 12px 20px;
  width: 100%;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  /* box-shadow: 0 8px 20px rgba(255, 46, 99, 0.35); */
  transition: all 0.2s ease;
}

.messaging:active {
  transform: scale(0.96);
}
.dateplan {
  background: linear-gradient(135deg, oklch(0.63 0.13 98.39), #e79e00);
  color: #fff;
  border: none;
  padding: 12px 20px;
  width: 100%;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  /* box-shadow: 0 8px 20px rgba(255, 46, 99, 0.35); */
  transition: all 0.2s ease;
}

.plandate:active {
  transform: scale(0.96);
}

/* Overlay */
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 999;
}

/* Payment Sheet */
.payment-sheet {
  position: fixed;
  bottom: -100%;
  left: 0;
  width: 100%;
  background: #fff;
  border-top-left-radius: 24px;
  border-top-right-radius: 24px;
  padding: 16px;
  z-index: 1000;
  transition: bottom 0.35s ease;
}

.payment-sheet.open {
  bottom: 0;
}

/* Handle */
.sheet-handle {
  width: 40px;
  height: 4px;
  background: #ddd;
  border-radius: 10px;
  margin: 6px auto 14px;
}

/* Title */
.payment-sheet h3 {
  text-align: center;
  font-size: 16px;
  margin-bottom: 14px;
}

/* Rows */
.pay-row {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  font-size: 14px;
  border-bottom: 1px solid #eee;
}

.pay-row.total {
  font-size: 16px;
  font-weight: 700;
  border-bottom: none;
  margin-top: 6px;
}

/* Pay Button */
.pay-btn {
  width: 100%;
  margin-top: 16px;
  padding: 14px;
  background: linear-gradient(135deg, #ff2e63, #2d010c);
  color: white;
  border: none;
  border-radius: 14px;
  font-size: 15px;
  font-weight: 600;
}

/* Cancel */
.cancel {
  text-align: center;
  margin-top: 10px;
  font-size: 13px;
  color: #888;
}
/* iOS Bounce Animation */
.payment-sheet {
  transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
  transform: translateY(100%);
}

.payment-sheet.open {
  transform: translateY(0);
}

/* Payment Methods */
.methods {
  display: flex;
  justify-content: space-between;
  margin: 16px 0;
}

.method {
  flex: 1;
  margin: 0 6px;
  padding: 12px;
  border-radius: 14px;
  background: #f6f7fb;
  text-align: center;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.method img {
  height: 26px;
  margin-bottom: 6px;
}

.method.active {
  background: #e8edff;
  border: 2px solid #bebebe;
  transform: scale(1.05);
}

/* Summary */
.pay-summary {
  background: #fafafa;
  padding: 14px;
  border-radius: 14px;
}

.pay-summary .row {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  font-size: 14px;
}

.pay-summary .total {
  font-size: 17px;
  font-weight: 700;
}

/* Price Animation */
.price-animate {
  transition: transform 0.2s ease;
}

/* Pay Button */
.pay-btn {
  width: 100%;
  margin-top: 18px;
  padding: 14px;
  background: linear-gradient(135deg, #ff2e63, #2d010c);
  color: #fff;
  border: none;
  border-radius: 14px;
  font-size: 15px;
  font-weight: 600;
  box-shadow: 0 10px 30px rgba(255, 91, 236, 0.4);
}

/* Cancel */
.cancel {
  text-align: center;
  margin-top: 10px;
  font-size: 13px;
  color: #777;
}

/* Duration Slider Box */
.duration-box {
  margin: 16px 0;
  padding: 14px;
  background: #f6f7fb;
  border-radius: 16px;
}

.duration-header {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 10px;
}

/* Slider */
.duration-slider {
  width: 100%;
  appearance: none;
  height: 6px;
  border-radius: 6px;
  background: linear-gradient(135deg, #ff2e63, #2d010c);
  outline: none;
}

/* Slider Thumb */
.duration-slider::-webkit-slider-thumb {
  appearance: none;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #fff;
  border: 3px solid #ba0982;
  box-shadow: 0 6px 12px rgba(0,0,0,0.2);
  cursor: pointer;
}

/* Scale */
.duration-scale {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #777;
  margin-top: 6px;
}

/* Photo Gallery */
.photo-gallery {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding-bottom: 6px;
  scroll-snap-type: x mandatory;
}

.photo-gallery::-webkit-scrollbar {
  display: none;
}

.photo-gallery img {
  flex: 0 0 auto;
  width: 120px;
  height: 160px;
  border-radius: 16px;
  object-fit: cover;
  scroll-snap-align: start;
  box-shadow: 0 10px 20px rgba(0,0,0,0.15);
  transition: transform 0.2s ease;
}

.photo-gallery img:active {
  transform: scale(0.95);
}
/* FULLSCREEN VIEWER */
.viewer {
  position: fixed;
  inset: 0;
  background: #000;
  z-index: 2000;
  display: flex;
  justify-content: center;
  align-items: center;
}

.viewer-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.close {
  position: absolute;
  top: 18px;
  right: 18px;
  font-size: 32px;
  /* background-color: #ff2e63;/ */
  
    justify-self: left;
  border-radius: 18px;

      width: 31px;
  color: rgb(236, 3, 73);
  cursor: pointer;
  z-index: 3000; /* 🔥 THIS FIXES IT */
}


/* Counter */
.counter {
  position: absolute;
  top: 20px;
  left: 20px;
  background: rgba(0,0,0,0.6);
  color: white;
  padding: 6px 10px;
  border-radius: 12px;
  font-size: 13px;
}

/* Dots */
.dots {
  position: absolute;
  bottom: 24px;
  display: flex;
  gap: 8px;
}

.dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255,255,255,0.4);
}

.dots span.active {
  background: white;
}

/* Navigation */
.nav {
  position: absolute;
  top: 0;
  width: 50%;
  height: 100%;
}

.nav.left {
  left: 0;
}

.nav.right {
  right: 0;
}
/* Success Overlay */
.success-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 3000;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Card */
.success-card {
  background: #fff;
  width: 86%;
  max-width: 320px;
  padding: 28px 20px;
  border-radius: 22px;
  text-align: center;
  animation: pop 0.35s ease;
}

/* Icon */
.success-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #00c853, #00e676);
  color: white;
  font-size: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 14px;
}

/* Text */
.success-card h2 {
  font-size: 20px;
  margin-bottom: 8px;
}

.success-card p {
  font-size: 14px;
  color: #555;
  line-height: 1.4;
}

/* Done Button */
.done-btn {
  margin-top: 18px;
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 14px;
  background: linear-gradient(135deg, #ff2e63, #2d010c);
  color: #fff;
  font-size: 15px;
  font-weight: 600;
}

/* Animation */
@keyframes pop {
  0% {
    transform: scale(0.8);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}
/* CONFETTI */
.confetti {
  position: fixed;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 3001;
}

.confetti span {
  position: absolute;
  top: -10px;
  width: 8px;
  height: 14px;
  background: red;
  opacity: 0.9;
  animation: fall 3s linear forwards;
}

/* Random colors */
.confetti span:nth-child(3n) { background: #ff4d6d; }
.confetti span:nth-child(3n+1) { background: #635bff; }
.confetti span:nth-child(3n+2) { background: #00e676; }

/* Random positions & delays */
.confetti span {
  left: calc(100% * var(--x));
  animation-delay: var(--delay);
  transform: rotate(var(--rotate));
}

/* Falling animation */
@keyframes fall {
  0% {
    transform: translateY(0) rotate(0deg);
  }
  100% {
    transform: translateY(110vh) rotate(360deg);
  }
}

@keyframes fall {
  0% {
    transform: translateY(-10px) rotate(0deg);
    opacity: 1;
  }
  100% {
    transform: translateY(110vh) rotate(360deg);
    opacity: 0;
  }
}
.photo-section {
  margin-top: 24px;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 12px;
  color: #222;
}

/* Grid layout */
.photo-gallery {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

/* Card wrapper */
.photo-card {
  border-radius: 14px;
  overflow: hidden;
  position: relative;
  background: #f5f5f5;
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

/* Image */
.photo-card img {
  width: 100%;
  height: 110px;
  object-fit: cover;
  display: block;
}

/* Tap / hover effect */
.photo-card:active {
  transform: scale(0.96);
}

.photo-card:hover {
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
}

/* Optional overlay icon */
.photo-card::after {
  content: "📸";
  position: absolute;
  bottom: 6px;
  right: 8px;
  font-size: 14px;
  opacity: 0.7;
}

.details-skeleton {
  min-height: 100vh;
  padding: 16px;
  background: #f0f2f5;
  font-family: 'Inter', sans-serif;
}

.skeleton-header {
  height: 200px;
  border-radius: 60px 60px 0 0;
  background: linear-gradient(90deg, #eee 25%, #ddd 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-card {
  background: white;
  border-radius: 28px;
  padding: 16px;
  margin-top: -80px;
}

.skeleton-img {
  height: 180px;
  border-radius: 22px;
  background: linear-gradient(90deg, #eee 25%, #ddd 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-text {
  height: 16px;
  border-radius: 8px;
  margin: 10px 0;
  background: linear-gradient(90deg, #eee 25%, #ddd 50%, #eee 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-text.title { width: 40%; margin: 16px auto 10px; }
.skeleton-text.meta { width: 30%; margin: 0 auto; }
.skeleton-text.rating { width: 20%; margin: 0 auto; }

.skeleton-features {
  display: flex;
  justify-content: space-around;
  margin: 16px 0;
}
.skeleton-feature {
  width: 80px;
  height: 20px;
  border-radius: 10px;
  background: linear-gradient(90deg, #eee 25%, #ddd 50%, #eee 75%);
  animation: shimmer 1.5s infinite;
}

.skeleton-gallery {
  display: flex;
  gap: 12px;
  margin-top: 14px;
}
.skeleton-photo {
  width: 120px;
  height: 160px;
  border-radius: 16px;
  background: linear-gradient(90deg, #eee 25%, #ddd 50%, #eee 75%);
  animation: shimmer 1.5s infinite;
}

.skeleton-bottom {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}
.skeleton-price { width: 80px; height: 20px; border-radius: 10px; background: #ddd; }
.skeleton-button { width: 120px; height: 36px; border-radius: 12px; background: #ddd; }

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

/* ===============================
   SUBTITLE
================================ */
.subtitle {
font-size: 14px;
    color: #555;
    margin-top: 2px;
    margin-bottom: 10px;
    line-height: 1.4;
}

/* ===============================
   LOCATION INFO CARD
================================ */
.info-card {
  background: #f6f7fb;
  padding: 12px 14px;
  border-radius: 16px;
  margin: 12px 0 16px;
  /* box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05); */
}

.info-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #333;
}

/* ===============================
   STATS (LIKES / COMMENTS / RATING)
================================ */
.stats {
  display: flex;
  justify-content: space-around;
  margin: 18px 0 10px;
}

.stat {
  text-align: center;
  font-size: 12px;
  color: #666;
}

.stat strong {
  display: block;
  font-size: 12px;
  /* font-weight: 700; */
  color: #111;
  margin: 2px 0;
}

.stat span {
  font-size: 11px;
}

/* ===============================
   HABITS SECTION
================================ */
.habits {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.habit-chip {
  padding: 6px 14px;
  background: linear-gradient(135deg, #ff4d6d, #ff2e63);
  color: #fff;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 500;
  box-shadow: 0 6px 14px rgba(255, 46, 99, 0.3);
  transition: transform 0.2s ease;
}

.habit-chip:active {
  transform: scale(0.95);
}

/* ===============================
   SECTION TITLE (REUSE)
================================ */
.section-title {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #222;
}

/* ===============================
   MOBILE POLISH
================================ */
@media (max-width: 480px) {
  .stats {
    margin-top: 14px;
  }

  .info-card {
    padding: 10px 12px;
  }
}
/* ===============================
   HABITS & LIFESTYLE (PREMIUM)
================================ */
.habit-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 18px;
  background: linear-gradient(135deg, #fff5f7, #ffeef2);
  /* box-shadow: 0 8px 20px rgba(255, 77, 109, 0.15); */
  margin-top: 10px;
}

.habit-icon {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  /* background: linear-gradient(135deg, #ff4d6d, #ff2e63); */
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #fff;
  flex-shrink: 0;
}

.habit-text {
  font-size: 12px;
  /* font-weight: 600; */
  color: #333;
  letter-spacing: 0.3px;
}

/* Mobile polish */
@media (max-width: 480px) {
  .habit-card {
    padding: 12px 14px;
  }

  .habit-text {
    font-size: 12px;
  }
}

/* ===============================
   HERO IMAGE WITH ANIMATED RINGS
================================ */
.hero-wrapper {
  position: relative;
  width: 110px;
  height: 110px;
  margin: -90px auto 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Profile Image */
.hero-img {
     width: 81px;
    height: 81px;
    border-radius: 50%;
    object-fit: cover;
    position: relative;
    z-index: 5;
    background: #fff;
    box-shadow: 0px 0px 20px 0px #ff2e633b;
    margin-top: -2px;
}

/* Animated Rings */
.ring {
  position: absolute;
  border-radius: 50%;
  border: 2px solid rgba(255, 46, 99, 0.5);
  animation: pulse 3s infinite ease-out;
  z-index: 1;
}

.ring-1 {
  width: 110px;
  height: 110px;
  animation-delay: 0s;
}

.ring-2 {
  width: 125px;
  height: 125px;
  animation-delay: 1s;
  opacity: 0.6;
}

.ring-3 {
  width: 145px;
  height: 145px;
  animation-delay: 2s;
  opacity: 0.4;
}

/* Pulse animation */
@keyframes pulse {
  0% {
    transform: scale(0.9);
    opacity: 0.8;
  }
  70% {
    transform: scale(1.1);
    opacity: 0;
  }
  100% {
    opacity: 0;
  }
}
/* ===============================
   HEART PULSE (VERIFIED USERS)
================================ */
.heart-pulse {
  position: absolute;
  width: 160px;
  height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 0;
}

.heart {
  position: absolute;
  width: 60px;
  height: 60px;
  background: #ff2e63;
  transform: rotate(-45deg);
  animation: heartPulse 3s infinite ease-out;
  opacity: 0.6;
}

.heart::before,
.heart::after {
  content: "";
  position: absolute;
  width: 60px;
  height: 60px;
  background: #ff2e63;
  border-radius: 50%;
}

.heart::before {
  top: -30px;
  left: 0;
}

.heart::after {
  left: 30px;
  top: 0;
}

/* Multiple pulse layers */
.heart:nth-child(1) {
  animation-delay: 0s;
}

.heart:nth-child(2) {
  animation-delay: 1s;
  opacity: 0.4;
  transform: scale(1.2) rotate(-45deg);
}

.heart:nth-child(3) {
  animation-delay: 2s;
  opacity: 0.25;
  transform: scale(1.4) rotate(-45deg);
}

/* Heart pulse animation */
@keyframes heartPulse {
  0% {
    transform: scale(0.7) rotate(-45deg);
    opacity: 0.8;
  }
  70% {
    transform: scale(1.6) rotate(-45deg);
    opacity: 0;
  }
  100% {
    opacity: 0;
  }
}
/* ===============================
   FOLLOW BUTTON (INSTAGRAM STYLE)
================================ */
.follow-btn {
  margin-top: 10px;
  padding: 8px 22px;
  border-radius: 999px;
  border: none;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s ease;
  background: linear-gradient(135deg, #ff4d6d, #ff2e63);
  color: #fff;
  box-shadow: 0 6px 14px rgba(255, 46, 99, 0.35);
}

.follow-btn:active {
  transform: scale(0.95);
}

/* Following state */
.follow-btn.following {
  background: #f1f1f1;
  color: #333;
  box-shadow: none;
  border: 1px solid #ddd;
}
/* ===============================
   INSTAGRAM PROFILE HEADER
================================ */
.insta-profile {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-top: -30px;
}

/* Avatar */
.insta-avatar img {
  width: 86px;
  height: 86px;
  margin-top: 6px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #ff2e63;
}

/* Right Section */
.insta-right {
  flex: 1;
  margin-top: -9px;
}

/* Stats Row */
.insta-stats {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.insta-stat {
  text-align: center;
  font-size: 12px;
}

.insta-stat strong {
  display: block;
  font-size: 14px;
  font-weight: 700;
  color: #111;
}

.insta-stat span {
  color: #666;
  font-size: 11px;
}

/* Follow Button */
.insta-follow-btn {
  width: 100%;
  padding: 8px 0;
  border-radius: 8px;
  border: none;
  font-size: 14px;
  font-weight: 600;
  
  background: linear-gradient(135deg, #ff4d6d, #ff2e63);
  color: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
}

.insta-follow-btn.following {
background: #ff346529;
    color: #ff3465;
    border: 1px solid #ff376694;
}

.insta-follow-btn:active {
  transform: scale(0.97);
}

/* Name */
.insta-name {
  margin-top: 8px;
  font-size: 15px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
}
.subtitle {
  text-align: left;
}
/* ===============================
   INSTAGRAM NAME ROW
================================ */
.insta-name-row {
  display: flex;
  align-items: center;
  gap: 6px;
     margin-top: -4px;
    margin-left: 19px;
}

.insta-name {
  font-size: 15px;
  font-weight: 600;
  color: #111;
}

/* Verified badge */
.verified {
  width: 16px;
  height: 16px;
}
/* LEFT COLUMN */
.insta-left {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center; /* 🔥 vertical centering */
  min-width: 120px;
}

/* Avatar */
.insta-avatar {
  width: 86px;
  height: 86px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #ff2e63;
  margin-bottom: 6px;
}

/* Name row */
.insta-name-row {
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 2px 0;
}

/* Name */
.insta-name {
  font-size: 15px;
  font-weight: 600;
  color: #111;
}

/* Subtitle */
.insta-subtitle {
  font-size: 12px;
  color: #666;
  text-align: center;
  max-width: 140px;
  line-height: 1.3;
}
.intro-section {
  margin-top: 16px;
}

.intro-text {
  font-size: 12px;
  line-height: 1.6;
  text-align: justify;
  color: #444;
  background: #f9f9f9;
  padding: 12px;
  border-radius: 10px;
}
.intro-section {
  margin-top: 18px;
}

.intro-card {
  background: linear-gradient(135deg, #fff, #fafafa);
  border-radius: 16px;
  padding: 4px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}

.intro-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.intro-header h4 {
  font-size: 16px;
  font-weight: 600;
}

.intro-icon {
  font-size: 18px;
}

.intro-text {
  line-height: 1.7;
  color: #333;
  margin-bottom: 12px;
}

.intro-tags {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.tag {
  background: #f1f3f5;
  padding: 6px 10px;
  border-radius: 20px;
  font-size: 12px;
  color: #555;
}
.chat-sheet {
  position: fixed;
  left: 0;
  bottom: -100%;
  width: 100%;
  height: 100vh;
  background: #fff;
  z-index: 1001;
  transition: bottom 0.35s ease;
  display: flex;
  flex-direction: column;
}

.chat-sheet.open {
  bottom: 0;
}

.chat-header {
  padding: 16px;
  text-align: center;
      margin-top: 54px;
  border-bottom: 1px solid #eee;
  position: relative;
}

.chat-header h3 {
  margin: 0;
  font-size: 15px;
}

.chat-header .close {
  position: absolute;
  left: 5px;
    top: 5px;

  font-size: 23px;
  cursor: pointer;
}

.chat-body {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  background: #fafafa;
}

.chat-bubble {
  max-width: 80%;
  padding: 12px 14px;
  margin-bottom: 12px;
  border-radius: 16px;
  font-size: 14px;
  line-height: 1.4;
}

.chat-bubble.received {
  background: #fff;
  border: 1px solid #eee;
  align-self: flex-start;
}

.chat-bubble.sent {
  background: #ff4d6d;
  color: #fff;
  justify-self: right;
  align-self: flex-end;
}

.chat-footer {
  padding: 12px;
  display: flex;
  gap: 8px;
  border-top: 1px solid #eee;
}

.chat-footer input {
  flex: 1;
  padding: 10px;
  border-radius: 20px;
  border: 1px solid #ddd;
}

.chat-footer button {
  padding: 10px 16px;
  border-radius: 20px;
  background: #ff4d6d;
  color: #fff;
  border: none;
  opacity: 0.6;
}

/* ===============================
   SKELETON BASE
================================ */
.details-skeleton {
  min-height: 100vh;
  background: #fff;
  padding-bottom: 90px;
}

.shimmer {
  background: linear-gradient(
    90deg,
    #eee 25%,
    #ddd 50%,
    #eee 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

/* ===============================
   HEADER
================================ */
.skeleton-header {
  height: 150px;
  border-bottom-left-radius: 60px;
  border-bottom-right-radius: 60px;
  background: #f2f2f2;
}

/* ===============================
   CARD
================================ */
.skeleton-card {
  margin: -120px 16px 0;
  padding: 16px;
  border-radius: 28px;
  background: #fff;
}

/* ===============================
   INSTAGRAM PROFILE
================================ */
.skeleton-insta {
  display: flex;
  gap: 18px;
  align-items: center;
}

.skeleton-avatar {
  width: 86px;
  height: 86px;
  border-radius: 50%;
}

.skeleton-right {
  flex: 1;
}

.skeleton-line.name {
  width: 120px;
  height: 14px;
  border-radius: 8px;
  margin-bottom: 10px;
}

.skeleton-stats {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
}

.skeleton-stat {
  width: 40px;
  height: 12px;
  border-radius: 6px;
}

.skeleton-btn {
  width: 100%;
  height: 32px;
  border-radius: 8px;
}

/* ===============================
   INTRO
================================ */
.skeleton-intro {
  margin-top: 18px;
}

.skeleton-line.full {
  width: 100%;
  height: 12px;
  border-radius: 8px;
  margin-bottom: 8px;
}

.skeleton-line.half {
  width: 60%;
  height: 12px;
  border-radius: 8px;
}

/* ===============================
   HABITS
================================ */
.skeleton-habit {
  height: 40px;
  border-radius: 18px;
  margin-top: 16px;
}

/* ===============================
   GALLERY
================================ */
.skeleton-gallery {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top: 16px;
}

.skeleton-photo {
  height: 110px;
  border-radius: 14px;
}

/* ===============================
   BOTTOM BAR
================================ */
.skeleton-bottom {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  padding: 14px 16px;
  display: flex;
  justify-content: space-between;
  background: #fff;
}

.skeleton-price {
  width: 80px;
  height: 14px;
  border-radius: 8px;
}

.skeleton-action {
  width: 140px;
  height: 36px;
  border-radius: 12px;
}

/* ===============================
   ANIMATION
================================ */
@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}
/* CHAT HEADER */
.insta-chat-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border-bottom: 1px solid #eee;
  background: #fff;
}

/* Back Arrow */
.insta-chat-header .close {
  font-size: 31px;
  cursor: pointer;
}

/* User Row */
.chat-user {
  display: flex;
  align-items: center;
      margin-left: 43px;
  gap: 10px;
}

/* Avatar */
.chat-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
}

/* Info */
.chat-user-info {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}

/* Name + badge */
.chat-name-row {
  display: flex;
  align-items: center;
  gap: 4px;
}

.chat-name {
  font-size: 14px;
  font-weight: 600;
  color: #111;
}

.chat-verified {
  width: 14px;
  height: 14px;
}

/* Status */
.chat-status {
  font-size: 11px;
  color: #8e8e8e;
}

.insta-btn-row {
  display: flex;
  gap: 8px;
  margin-top: 6px;
}

.insta-match-btn {
width: 100%;
  padding: 8px 0;
  border-radius: 8px;
  border: none;
  font-size: 14px;
  font-weight: 600;
  background: linear-gradient(135deg, #05c2da, #02acbc);
  color: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
}

.insta-match-btn.matched {
  background: #e0e0e0;
  color: #1e3cff;
  border: 1px solid #1e3cff;
  box-shadow: none;
}

.insta-match-btn:active {
  transform: scale(0.97);
}

.disabledBtn {
  background: #3a3737 !important;
  cursor: not-allowed;
  opacity: 0.7;
}

</style>
