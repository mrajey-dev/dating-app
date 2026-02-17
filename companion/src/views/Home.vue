<template>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <div class="app">
    <!-- Side Menu Overlay -->
    <div class="overlay" v-if="isMenuOpen" @click="closeMenu"></div>

    <!-- Side Menu -->
    <div class="side-menu" :class="{ open: isMenuOpen }">
      <!-- Header -->



      <ul class="menu-list">
        <li @click="goToProfile">👤 My Profile</li>
        <li>❤️ Matches</li>
        <li>💬 Chats</li>
        <li @click="goToBookings">📅 Bookings</li>
        <li @click="showFavourites">❤️ Favorites</li>
        <li @click="showAllUsers">🌐 All Users</li>
        <li>⚙️ Settings</li>
        <li class="logout" @click="logout">🚪 Logout</li>
      </ul>
    </div>

    <!-- Header -->
    <!-- Header -->
<div class="header">
  <h1 class="page-title">Choose a Companion</h1>

  <div class="search-filter-wrapper">
   <input
  v-if="showSearchBar"
  ref="searchInput"
  type="text"
  v-model="search"
  placeholder="Search"
  class="search-input"
/>

   <div class="filter-icon" @click="toggleFilter">
<i class="fa fa-sliders" style="font-size:24px"></i>
</div>

  </div>
</div>


    <!-- FILTER POPUP -->
<transition name="slide-down">
  <div v-if="showFilter" class="filter-popup">
    <div class="filter-header">
      <h3>Advanced Filters</h3>
      <span class="close-btn" @click="toggleFilter">✖</span>
    </div>

    <div class="filter-body">

      <div class="filter-group">
        <label>Status</label>
        <input v-model="filters.status" placeholder="Enter status" />
      </div>

      <div class="filter-group">
        <label>Subtitle</label>
        <input v-model="filters.subtitle" placeholder="Enter subtitle" />
      </div>

      <div class="filter-group">
        <label>City</label>
        <input v-model="filters.city" placeholder="Enter city" />
      </div>

      <div class="filter-group">
        <label>State</label>
        <input v-model="filters.state" placeholder="Enter state" />
      </div>

      <div class="filter-group">
        <label>Verified Badge</label>
        <select v-model="filters.verified_badge">
          <option value="">All</option>
          <option value="1">Verified Only</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Habits</label>
        <input v-model="filters.habits" placeholder="Smoking, Gym, etc" />
      </div>

      <div class="filter-actions">
        <button class="clear-btn" @click="clearFilters"><i class="fa fa-remove" style="font-size:14px"></i> Clear</button>
       <button class="apply-btn" @click="closeFilter"><i class="fa fa-check" style="font-size:14px"></i> Done</button>

      </div>

    </div>
  </div>
</transition>


    <!-- Results -->
   <div class="content">

  <!-- If users exist -->
  <div v-if="people.length">

    <div
      class="deal-card"
      v-for="person in filteredPeople"
      :key="person.id"
      :data-id="person.id"
      ref="cards"
    >

      <!-- Top -->
      <div class="deal-top">
        <div class="deal-rating">🥇 {{ person.rating || 0 }}</div>

        <button
          class="fav-btn"
          :class="{ active: person.liked }"
          @click.stop="toggleFavourite(person)"
        >
          {{ person.liked ? "❤️" : "🤍" }}
        </button>
      </div>

      <!-- Media Section -->
      
      <!-- Show video if available -->
  <div v-if="person.introduction_video" class="video-wrapper"
     @click="showControls(person.id)">

 <video
    :ref="'video-' + person.id"
    class="deal-img no-fullscreen"
    :src="person.introduction_video"
    preload="metadata"
    loop
    controls
    playsinline
    controlsList="nodownload noplaybackrate noremoteplayback"
    disablePictureInPicture
    @click.stop="toggleVideo(person.id)"
  ></video>


  <!-- Controls Wrapper -->
  <div v-if="controlsVisible[person.id]" class="video-controls">

    <!-- Play Button -->
    <div class="play-btn" @click.stop="toggleVideo(person.id)">
      {{ playingVideo === person.id ? '||' : '▶' }}
    </div>

    <!-- Sound Button -->
    <div class="sound-btn" @click.stop="toggleSound(person.id)">
  {{ videoMuted[person.id] ? '🔇' : '🔊' }}
</div>


  </div>
</div>




      <!-- Otherwise show image -->
      <img
        v-else
        class="deal-img"
        :src="person.profile_photo || 'https://via.placeholder.com/200'"
        alt="Profile"
      />

      <!-- Content -->
      <div class="deal-content">
        <h3>
          {{ person.first_name || person.name }}

          <img
            v-if="person.verified_badge == 1"
            src="@/assets/verified1.png"
            class="verified"
            alt="Verified"
          />
        </h3>

        <p class="sub">
          {{ person.status || '' }}
        </p>
      </div>

      <!-- Arrow -->
      <button
        class="deal-next"
        @click="goToDetails(person.id)"
      >
        ››
      </button>

    </div>

    <!-- ✅ Loading more (ONLY when fetching next page) -->
    <div v-if="loadingMore" class="loading-more">
      Loading more companions...
    </div>

  </div>

  <!-- ✅ Empty state -->
  <div v-else class="empty-state">
    No companions available. Make sure you are logged in!
  </div>

</div>


    <!-- Instagram Style Footer -->
    <div class="bottom-footer">
      <div class="footer-item" @click="$router.push('/home')">
       <i class="fa fa-home" style="font-size:24px"></i>
      </div>
     <div class="footer-item" @click="openSearch">
 <i class="fa fa-search" style="font-size:20px"></i>
</div>

      <div class="footer-item">
        <img src="@/assets/golden.png" class="footer-icon" />
      </div>
      <div class="footer-item" @click="$router.push('/notifications')">
       <i class='fa fa-heartbeat' style='font-size:20px'></i>
      </div>
      <div class="footer-item" @click="$router.push('/profile')">
        <img :src="user.profile_photo" class="footer-avatar" />
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios"
import _ from "lodash"

export default {
  name: "Home",
  
  data() {
    return {
      showSearchBar: true,
      videoMuted: {},            // track mute state per video
      controlsVisible: {},       // track controls visibility per video
      controlTimeouts: {},       // timeout for controls hide
      playingVideo: null,        // currently playing video ID
      currentPage: 1,
      lastPage: 1,
      loadingMore: false,
      scrollHandler: null,
      showFilter: false,
      filters: {
        status: "",
        subtitle: "",
        city: "",
        state: "",
        verified_badge: "",
        habits: ""
      },
      showOnlyFavourites: false,
      isMenuOpen: false,
      refreshInterval: null,
      people: [],
      search: "",
      visibleUsers: new Set(),
      observer: null,
      user: { first_name: "", last_name: "", city: "", profile_photo: "" },
    }
  },

  async mounted() {
    await this.loadUser()
    await this.loadUsers(1)
    this.setupObserver()
    this.refreshVisibleUsers()

    // Infinite scroll listener
    this.scrollHandler = this.handleScroll
    window.addEventListener("scroll", this.scrollHandler)
  },

  beforeUnmount() {
    if (this.refreshInterval) clearInterval(this.refreshInterval)
    if (this.observer) this.observer.disconnect()
    window.removeEventListener("scroll", this.scrollHandler)
  },

  beforeRouteEnter(to, from, next) {
    next(vm => vm.loadUser())
  },

  beforeRouteUpdate(to, from, next) {
    this.loadUser()
    next()
  },

  computed: {
    filteredPeople() {
      return this.people
    }
  },

  watch: {
    search() {
      this.debounceSearch()
    },
    filters: {
      handler() {
        this.debounceFilter()
      },
      deep: true
    }
  },

  methods: {
openSearch() {
  this.showSearchBar = true

  this.$nextTick(() => {
    if (this.$refs.searchInput) {
      this.$refs.searchInput.focus()
    }
  })
},

    // Show video controls for 2 seconds
    showControls(id) {
      this.controlsVisible[id] = true
      if (this.controlTimeouts[id]) clearTimeout(this.controlTimeouts[id])
      this.controlTimeouts[id] = setTimeout(() => {
        this.controlsVisible[id] = false
      }, 2000)
    },

    // Toggle play/pause manually
    toggleVideo(id) {
      this.showControls(id)
      const currentRef = this.$refs['video-' + id]
      if (!currentRef) return
      const video = Array.isArray(currentRef) ? currentRef[0] : currentRef

      // Pause if same video clicked
      if (this.playingVideo === id) {
        video.pause()
        this.playingVideo = null
        return
      }

      // Pause all other videos
      Object.keys(this.$refs).forEach(key => {
        if (key.startsWith('video-')) {
          const refItem = this.$refs[key]
          const v = Array.isArray(refItem) ? refItem[0] : refItem
          if (v && typeof v.pause === 'function') {
            const otherId = Number(key.replace('video-', ''))
            if (otherId !== id) {
              v.pause()
              v.currentTime = 0
              this.videoMuted[otherId] = true
            }
          }
        }
      })

      // Play selected video
      video.play()
        .then(() => {
          this.playingVideo = id
          this.videoMuted[id] = video.muted
        })
        .catch(err => console.log("Play blocked:", err))
    },

    // Toggle sound
    toggleSound(id) {
      const currentRef = this.$refs['video-' + id]
      if (!currentRef) return
      const video = Array.isArray(currentRef) ? currentRef[0] : currentRef
      video.muted = !video.muted
      this.videoMuted[id] = video.muted
    },

    // Infinite scroll
    handleScroll() {
      const scrollTop = window.scrollY
      const windowHeight = window.innerHeight
      const fullHeight = document.documentElement.scrollHeight

      if (
        scrollTop + windowHeight >= fullHeight - 200 &&
        !this.loadingMore &&
        this.currentPage < this.lastPage
      ) {
        this.loadingMore = true
        this.loadUsers(this.currentPage + 1)
      }
    },

    toggleFilter() {
      this.showFilter = !this.showFilter
      if (!this.showFilter) this.loadUsers()
    },

    clearFilters() {
      this.filters = {
        status: "",
        subtitle: "",
        city: "",
        state: "",
        verified_badge: "",
        habits: ""
      }
    },

    closeFilter() {
      this.showFilter = false
    },

    debounceSearch: _.debounce(function () {
      this.currentPage = 1
      this.lastPage = 1
      this.loadUsers(1)
    }, 500),

    debounceFilter: _.debounce(function () {
      this.currentPage = 1
      this.lastPage = 1
      this.loadUsers(1)
    }, 500),

    showFavourites() {
      this.isMenuOpen = false
      this.showOnlyFavourites = true
    },

    showAllUsers() {
      this.showOnlyFavourites = false
    },

    // IntersectionObserver to auto-play top visible video
    setupObserver() {
      if (this.observer) this.observer.disconnect()

      this.observer = new IntersectionObserver(
        entries => {
          const visibleEntries = entries
            .filter(entry => entry.isIntersecting)
            .sort((a, b) => a.target.getBoundingClientRect().top - b.target.getBoundingClientRect().top)

          // Pause videos not visible
          entries.forEach(entry => {
            const id = Number(entry.target.dataset.id)
            const videoRef = this.$refs['video-' + id]
            const video = Array.isArray(videoRef) ? videoRef[0] : videoRef
            if (!entry.isIntersecting && video) {
              video.pause()
              video.currentTime = 0
              if (this.playingVideo === id) this.playingVideo = null
            }
          })

          // Auto-play top visible video if none is manually playing
          if (visibleEntries.length > 0 && this.playingVideo === null) {
            const topVisible = visibleEntries[0]
            const id = Number(topVisible.target.dataset.id)
            const videoRef = this.$refs['video-' + id]
            const video = Array.isArray(videoRef) ? videoRef[0] : videoRef
            if (video) {
              video.muted = false
              this.videoMuted[id] = false
              video.play()
                .then(() => { this.playingVideo = id })
                .catch(err => console.log("Auto-play blocked:", err))
            }
          }
        },
        { threshold: 0.5 }
      )

      this.$nextTick(() => {
        this.$refs.cards?.forEach(el => this.observer.observe(el))
      })
    },

    async refreshVisibleUsers() {
      if (!this.visibleUsers.size) return
      const token = localStorage.getItem("token")
      if (!token) return

      try {
        const res = await axios.get("https://companion.ajaywatpade.in/api/users", {
          headers: { Authorization: `Bearer ${token}` }
        })

        res.data.data.forEach(updated => {
          if (this.visibleUsers.has(updated.id)) {
            const index = this.people.findIndex(p => p.id === updated.id)
            if (index !== -1) {
              this.people.splice(index, 1, {
                ...this.people[index],
                rating: updated.rating,
                like_count: updated.like_count,
                status: updated.status,
                rate: updated.rate,
                liked: updated.liked
              })
            }
          }
        })

        this.$nextTick(() => {
          this.$refs.cards?.forEach(el => this.observer.observe(el))
        })
      } catch (e) {
        console.error("Visible refresh failed", e)
      }
    },

    goToProfile() {
      this.isMenuOpen = false
      this.$router.push("/profile")
    },

    async loadUsers(page = 1) {
      const token = localStorage.getItem("token")
      if (!token) return

      try {
        const res = await axios.get("https://companion.ajaywatpade.in/api/users", {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            page,
            search: this.search,
            status: this.filters.status,
            subtitle: this.filters.subtitle,
            city: this.filters.city,
            state: this.filters.state,
            verified_badge: this.filters.verified_badge,
            habits: this.filters.habits
          }
        })

        const newUsers = res.data.data.map(person => ({
          ...person,
          profile_photo: person.profile_photo
            ? `https://companion.ajaywatpade.in/${person.profile_photo}`
            : "https://via.placeholder.com/200",
          introduction_video: person.introduction_video
            ? `https://companion.ajaywatpade.in/dating-backend/public/storage/${person.introduction_video}`
            : null,
          liked: person.liked || false
        }))

        if (page === 1) this.people = newUsers
        else this.people = [...this.people, ...newUsers]

        this.currentPage = res.data.current_page
        this.lastPage = res.data.last_page

      } catch (err) {
        console.error("Failed to load users:", err)
      } finally {
        this.loadingMore = false
      }
    },

    logout() {
      axios.post("https://companion.ajaywatpade.in/api/logout", {}, {
        headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
      }).catch(() => {})

      localStorage.removeItem("token")
      localStorage.removeItem("user")
      this.isMenuOpen = false
      this.$router.replace("/")
    },

    async loadUser() {
      const storedUser = JSON.parse(localStorage.getItem("user"))
      if (!storedUser || !localStorage.getItem("token")) {
        this.$router.replace("/")
        return
      }

      this.user = {
        first_name: storedUser.first_name || "",
        last_name: storedUser.last_name || "",
        city: storedUser.city || "Unknown",
        profile_photo: storedUser.profile_photo
          ? `https://companion.ajaywatpade.in/dating-backend/public/storage/${storedUser.profile_photo}`
          : "https://via.placeholder.com/100",
      }
    },

    async toggleFavourite(person) {
      const token = localStorage.getItem("token")
      if (!token) return

      try {
        const res = await axios.post(
          `https://companion.ajaywatpade.in/api/users/${person.id}/toggle-like`,
          {},
          { headers: { Authorization: `Bearer ${token}` } }
        )

        person.liked = res.data.liked
        person.like_count = res.data.like_count
      } catch (err) {
        console.error(err)
      }
    },

    goToDetails(id) {
      this.$router.push(`/details/${id}`)
    },

    goToBookings() {
      this.isMenuOpen = false
      this.$router.push("/my-bookings")
    },

    toggleMenu() {
      this.isMenuOpen = true
    },

    closeMenu() {
      this.isMenuOpen = false
    },
  }
}
</script>




        <style scoped>
        /* App Wrapper */
        .app {
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
        position: relative;
        overflow-x: hidden;
        }

        /* Header with waves */
        .header {
        /* background-image: url(https://img.freepik.com/free-vector/wave-gradient-blue-background-modern-design_343694-3814.jpg); */
        background-image: url(https://png.pngtree.com/thumb_back/fh260/background/20241126/pngtree-abstract-pink-background-vector-image_16667423.jpg);
        padding: 7px 48px 63px;
        border-bottom-left-radius: 40px;
        border-bottom-right-radius: 40px;
        color: white;
        position: relative;
        }

        /* Top bar */
        .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        }

        .menu {
        font-size: 22px;
        }

        .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        }

        /* Title */
        .header h1 {
        margin-top: 20px;
        font-size: 22px;
        font-weight: 600;
        }

    

        /* Content */
        .content {
        padding: 20px;
        margin-top: -101px;
        }

        .results {
        font-size: 14px;
        color: #444;
        margin-bottom: 14px;
        }

    

        .rating {
        background: #ffb703;
        color: white;
        display: inline-block;
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 12px;
        }

        .card-body {
        display: flex;
        align-items: center;
        margin-top: 10px;
        }

   .profile-img {
  width: 90px;
  height: 60px;
  border-radius: 12px;
  object-fit: cover;
}


        .info {
        flex: 1;
        padding-left: 12px;
        }

        .info h3 {
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 6px;
        }

        .verified {
        width: 16px;
        }

        .sub {
        font-size: 12px;
        color: #666;
        }

        .price {
        font-weight: 700;
        margin-top: 4px;
        }

        /* Arrow */
        .next {
        position: absolute;
        right: 14px;
        bottom: 14px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #ff4791;
        color: white;
        border: none;
        font-size: 20px;
        }

     

        .deal-card {
     background: white;
    border-radius: 26px;
    padding: 16px;
        margin-top: 28px;

    margin-bottom: 18px;
    position: relative;
    box-shadow: 0px 0px 20px 5px #0000001f;
}

/* Rating badge */
.deal-rating {
  position: absolute;
  top: 14px;
  left: 14px;
  background: #e0ebfc;
  color: #000000;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

/* Image */
.deal-img {
  width: 100%;
  height: auto  ;
  object-fit: cover;
  border-radius: 18px;
  margin-top: 43px;
}

/* Content */
.deal-content {
  margin-top: 12px;
}

.deal-content h3 {
  font-size: 16px;
  color: #000000;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
}

.sub {
  font-size: 13px;
  color: #666;
  margin-top: 2px;
}

.price {
  font-size: 14px;
  font-weight: 700;
  margin-top: 4px;
}

/* Arrow Button */
.deal-next {
  position: absolute;
  right: 14px;
  bottom: 14px;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #ff4791;
  color: white;
  border: none;
  font-size: 22px;
  cursor: pointer;
}
.search-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
  border-radius: 999px;
  padding: 7px 4px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  width: 100%;
  max-width: 360px;
  margin: 0 auto;
}

/* Location */
.location {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.location-icon {
  font-size: 16px;
}


        
       /* Overlay */
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 9;
}

/* Side Menu */
.side-menu {
  position: fixed;
  top: 0;
  left: -280px;
  width: 280px;
  height: 100vh;
  background: #ffffff;
  z-index: 10;
  transition: 0.3s ease;
  box-shadow: 6px 0 20px rgba(0, 0, 0, 0.15);
  border-top-right-radius: 24px;
  border-bottom-right-radius: 24px;
}

/* Open state */
.side-menu.open {
  left: 0;
}

/* Menu Header */
.menu-header {
  padding: 24px 16px;
  background: linear-gradient(135deg, #ff5fa2, #ff8ccf);
  color: white;
  text-align: center;
  border-top-right-radius: 24px;
}

.menu-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  margin-bottom: 10px;
  object-fit: cover;
}

/* Menu List */
.menu-list {
  list-style: none;
  padding: 16px;
}

.menu-list li {
  padding: 14px 12px;
  font-size: 15px;
  border-radius: 12px;
  cursor: pointer;
  transition: 0.2s;
}

.menu-list li:hover {
  background: #f5f5f5;
}

.menu-list .logout {
  color: #ff3b3b;
  margin-top: 12px;
}
 
/* Top badges wrapper */
.deal-top {
  position: absolute;
  top: 14px;
  left: 14px;
  right: 14px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  z-index: 2;
}

/* Favourite button */
.fav-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.9);
  font-size: 16px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.15s ease, background 0.2s ease;
}

.fav-btn:active {
  transform: scale(0.85);
}

/* Active (❤️) state */
.fav-btn.active {
  background: #fdfdfd;
  animation: pop 0.3s ease;
}

/* Pop animation */
@keyframes pop {
  0% {
    transform: scale(0.6);
  }
  60% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}


.fav-btn:hover {
  transform: scale(1.1);
}

.deal-img {
  margin-top: 50px;
}
.search-input {
  width: 100%;
  padding:9px 14px;
  margin: 10px 0 16px;
  border-radius: 30px;
  border: 1px solid #ddd;
  font-size: 14px;
}

/* Bottom Instagram Style Footer */
.bottom-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 60px;
  background: #ffffff;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: space-around;
  align-items: center;
  z-index: 20;
}

.footer-item {
  font-size: 22px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.footer-item:active {
  transform: scale(0.85);
}

/* Profile avatar */
.footer-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}
.content {
  padding: 20px;
  margin-top: -101px;
  padding-bottom: 80px; /* important */
}
.footer-icon {
  width: 24px;
  height: 24px;
  object-fit: contain;
}
.footer-icon {
  width: 24px;
  height: 24px;
  user-select: none;
  -webkit-user-drag: none;
}
.deal-img {
  width: 100%;
  height: auto;
  object-fit: cover;
  border-radius: 15px;
}

.search-filter-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
}



/* Popup */
.filter-popup {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(10px);
  padding: 20px;
  z-index: 999;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  border-bottom-left-radius: 20px;
  border-bottom-right-radius: 20px;
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.filter-body {
  margin-top: 15px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
}

.filter-group label {
  font-size: 12px;
  font-weight: bold;
  color: #555;
}

.filter-group input,
.filter-group select {
  width: 100%;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #ddd;
}

.filter-actions {
  grid-column: 1 / -1;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.apply-btn {
  background: #ff4d6d;
  color: white;
  padding: 8px 18px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: 0.3s;
}

.apply-btn:hover {
  background: #e63c5d;
}

.clear-btn {
  background: #ddd;
  padding: 8px 18px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
}

/* Animation */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.4s ease;
}

.slide-down-enter-from {
  transform: translateY(-100%);
  opacity: 0;
}

.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
.filter-icon {
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.filter-img {
  width: 22px;
  height: 22px;
  object-fit: contain;
}
.loading-more {
  text-align: center;
  padding: 20px;
  font-weight: 600;
}
.video-wrapper {
  position: relative;
}


/* Hide the fullscreen button in WebKit browsers (Chrome, Safari) */
video.no-fullscreen::-webkit-media-controls-fullscreen-button {
  display: none;
}

/* Hide the fullscreen button in Firefox */
video.no-fullscreen::-moz-fullscreen-button {
  display: none;
}

/* Hide the fullscreen button in Edge / IE (experimental) */
video.no-fullscreen::-ms-fullscreen-button {
  display: none;
}
</style>

