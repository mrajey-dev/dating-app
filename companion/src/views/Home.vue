<template>
  <div class="dating-app">
    <!-- Gradient Header with Glow -->
    <div class="hero-header">
      <div class="header-glow"></div>
      <div class="header-content">
        <div class="greeting-badge">
          <span class="wave-emoji">✨</span>
          <span>Discover</span>
        </div>
        <h1 class="main-title">Find Your<span class="accent"> Vibe</span></h1>
        <p class="sub-headline">Meaningful connections start here</p>
      </div>
      
      <!-- Search & Filter Bar -->
      <div class="action-bar">
        <div class="search-container" :class="{ focused: searchFocused }">
          <i class="fas fa-search search-icon"></i>
          <div class="search-input-wrapper">
            <input 
              type="text" 
              v-model="search" 
              class="search-input-modern"
              @focus="searchFocused = true"
              @blur="searchFocused = false"
            />
            <div class="animated-placeholder" :class="{ hidden: search || searchFocused }">
              <span class="placeholder-text">{{ currentPlaceholder }}</span>
              <span class="cursor"></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Advanced Filter Modal (Slide Down) -->
    <transition name="filter-slide">
      <div v-if="showFilter" class="filter-modal">
        <div class="filter-modal-header">
          <h3><i class="fas fa-magic"></i> Refine Magic</h3>
          <button class="close-filter" @click="showFilter = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="filter-grid">
          <div class="filter-field">
            <label><i class="fas fa-user-check"></i> Status</label>
            <input type="text" v-model="filters.status" placeholder="e.g., Active, Busy">
          </div>
          <div class="filter-field">
            <label><i class="fas fa-tag"></i> Subtitle</label>
            <input type="text" v-model="filters.subtitle" placeholder="Interests, vibe">
          </div>
          <div class="filter-field">
            <label><i class="fas fa-city"></i> City</label>
            <input type="text" v-model="filters.city" placeholder="City">
          </div>
          <div class="filter-field">
            <label><i class="fas fa-map-marker-alt"></i> State</label>
            <input type="text" v-model="filters.state" placeholder="State">
          </div>
          <div class="filter-field">
            <label><i class="fas fa-certificate"></i> Verified</label>
            <select v-model="filters.verified_badge">
              <option value="">Everyone</option>
              <option value="1">Verified Only</option>
            </select>
          </div>
          <div class="filter-field">
            <label><i class="fas fa-heartbeat"></i> Habits</label>
            <input type="text" v-model="filters.habits" placeholder="Gym, Yoga, Coffee">
          </div>
        </div>
        <div class="filter-actions">
          <button class="btn-clear" @click="clearFilters"><i class="fas fa-eraser"></i> Clear</button>
          <button class="btn-apply" @click="applyFilters"><i class="fas fa-check-circle"></i> Apply</button>
        </div>
      </div>
    </transition>

    <!-- Cards Grid / Feed -->
    <div class="feed-container">
      <div v-if="loading && displayedPeople.length === 0" class="loading-skeleton">
        <div class="skeleton-card" v-for="i in 3" :key="i"></div>
      </div>
      
      <div v-else-if="displayedPeople.length === 0" class="empty-state">
        <i class="fas fa-heart-broken"></i>
        <h3>No companions found</h3>
        <p>Try adjusting your filters or check back later</p>
      </div>

      <transition-group name="card-list" tag="div" class="cards-grid">
        <div 
          class="companion-card" 
          v-for="person in displayedPeople" 
          :key="person.id"
          :data-id="person.id"
          :ref="'card-' + person.id"
        >
          <!-- Media Section (Video/Image) -->
          <div class="media-wrapper" @click="goToDetails(person.id)">
            <div v-if="person.introduction_video" class="video-container">
              <video 
                :ref="el => setVideoRef(el, person.id)"
                :data-video-id="person.id"
                class="card-media"
                :src="person.introduction_video"
                preload="metadata"
                muted
                loop
                playsinline
                @click.stop
                @play="onVideoPlay(person.id)"
                @pause="onVideoPause(person.id)"
              ></video>
              <div class="video-overlay" v-if="controlsVisible[person.id]" @click.stop>
                <button class="video-ctrl" @click.stop="toggleVideo(person.id)">
                  <i :class="playingVideo === person.id ? 'fas fa-pause' : 'fas fa-play'"></i>
                </button>
                <button class="video-ctrl" @click.stop="toggleSound(person.id)">
                  <i :class="videoMuted[person.id] ? 'fas fa-volume-mute' : 'fas fa-volume-up'"></i>
                </button>
              </div>
            </div>
            <img v-else class="card-media" :src="person.profile_photo" alt="profile" />
            
            <!-- Top Badges -->
            <div class="card-badges">
              <div class="rating-badge">
                <i class="fas fa-star"></i> {{ person.rating || 'New' }}
              </div>
              <button class="like-btn" :class="{ liked: person.liked }" @click.stop="toggleFavourite(person)">
                <i class="fas fa-heart"></i>
                <span class="like-count" v-if="person.like_count > 0">{{ person.like_count }}</span>
              </button>
            </div>
          </div>

          <!-- Card Content -->
          <div class="card-info">
            <div class="name-row">
              <h3>
                {{ person.first_name || person.name }} {{ person.last_name || '' }} 
                <img 
                  v-if="person.verified_badge == 1" 
                  src="/verified.png" 
                  alt="Verified"
                  class="verified-icon"
                >
              </h3>
              <span class="distance"><i class="fa fa-map-marker" style="color: red;"></i> {{ person.city || 'Nearby' }}</span>
            </div>
            <p class="status-tag">{{ person.status || '✨ Open to connect' }}</p>
            <p class="subtitle" v-if="person.subtitle">{{ person.subtitle }}</p>
            <div class="action-row">
              <button class="chat-preview-btn" @click="goToDetails(person.id)">
                Connect <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      </transition-group>

      <!-- Loading More Indicator -->
      <div v-if="loadingMore" class="loader-more">
        <div class="dot-floating"></div>
        <span>Loading more sparks...</span>
      </div>
      
      <!-- End of content message -->
      <div v-if="!hasMore && displayedPeople.length > 0" class="end-message">
        <i class="fas fa-heart"></i>
        <p>You've seen everyone! ✨</p>
      </div>
    </div>

    <!-- Stylish Bottom Navigation -->
    <div class="bottom-nav">
      <div class="nav-item" :class="{ active: activeNav === 'home' }" @click="goHome">
        <i class="fas fa-compass"></i>
        <span>Discover</span>
      </div>
      <div class="nav-item" @click="openSearchFocus">
        <i class="fas fa-search"></i>
        <span>Search</span>
      </div>
      <div class="nav-item center-btn" v-if="showPlansIcon" @click="$router.push('/myplans')">
        <i class="fas fa-crown"></i>
      </div>
      <div class="nav-item" @click="$router.push('/mymatches')">
        <i class="fas fa-heart" style="color: red;"></i>
        <span>Matches</span>
      </div>
      <div class="nav-item" @click="$router.push('/profile')">
        <img :src="user.profile_photo" class="nav-avatar" />
        <span>Profile</span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import _ from "lodash";

export default {
  name: "DatingHome",
  data() {
    return {
      userGender: null,
      searchFocused: false,
      showFilter: false,
      search: "",
      filters: {
        status: "",
        subtitle: "",
        city: "",
        state: "",
        verified_badge: "",
        habits: ""
      },
      allPeople: [],        // Store all fetched profiles
      displayedPeople: [],  // Currently displayed profiles (paginated)
      currentDisplayIndex: 0, // Track how many profiles are displayed
      currentPage: 1,
      lastPage: 1,
      loading: false,
      loadingMore: false,
      playingVideo: null,
      videoMuted: {},
      controlsVisible: {},
      controlTimeouts: {},
      videoRefs: {},
      user: {
        first_name: "",
        last_name: "",
        city: "",
        profile_photo: ""
      },
      activeNav: "home",
      observer: null,
      visibleUsers: new Set(),
      // Animated placeholder data
      placeholderIndex: 0,
      currentPlaceholder: "",
      currentDisplayText: "",
      placeholders: ["Search by name", "Search by city", "Search by vibes"],
      typingSpeed: 80,
      deletingSpeed: 50,
      pauseDuration: 2000,
      typingTimeout: null,
      isDeleting: false,
      // Pagination settings
      itemsPerPage: 3,     // Load 3 profiles at a time
      hasMore: true        // Check if more profiles available
    };
  },
  computed: {
    showPlansIcon() {
      const gender = (this.userGender || "").trim().toLowerCase();
      return gender !== "female";
    }
  },
  watch: {
    search: _.debounce(function() {
      this.resetAndSearch();
    }, 500),
    filters: {
      deep: true,
      handler: _.debounce(function() {
        if (!this.showFilter) {
          this.resetAndSearch();
        }
      }, 400)
    }
  },
  async mounted() {
    const storedUser = JSON.parse(localStorage.getItem("user"));
    this.userGender = storedUser?.gender || null;
    await this.loadUser();
    await this.loadUsers(1);
    this.startTypingAnimation();
    this.$nextTick(() => {
      this.setupIntersectionObserver();
    });
    window.addEventListener("scroll", this.handleScroll);
  },
  beforeDestroy() {
    window.removeEventListener("scroll", this.handleScroll);
    if (this.observer) this.observer.disconnect();
    Object.values(this.controlTimeouts).forEach(clearTimeout);
    if (this.typingTimeout) clearTimeout(this.typingTimeout);
  },
  methods: {
    resetAndSearch() {
      this.currentPage = 1;
      this.currentDisplayIndex = 0;
      this.displayedPeople = [];
      this.hasMore = true;
      this.loadUsers(1);
    },
    
    setVideoRef(el, id) {
      if (el) this.videoRefs[id] = el;
    },
    
    getVideoElement(id) {
      return this.videoRefs[id];
    },
    
    onVideoPlay(id) {
      this.playingVideo = id;
      this.showControls(id);
    },
    
    onVideoPause(id) {
      if (this.playingVideo === id) this.playingVideo = null;
    },
    
    showControls(id) {
      this.controlsVisible[id] = true;
      if (this.controlTimeouts[id]) clearTimeout(this.controlTimeouts[id]);
      this.controlTimeouts[id] = setTimeout(() => {
        this.controlsVisible[id] = false;
      }, 2000);
    },
    
    toggleVideo(id) {
      const video = this.getVideoElement(id);
      if (!video) return;
      this.showControls(id);
      if (this.playingVideo === id) {
        video.pause();
      } else {
        Object.keys(this.videoRefs).forEach(vid => {
          const other = this.videoRefs[vid];
          if (other && parseInt(vid) !== id && !other.paused) {
            other.pause();
            other.currentTime = 0;
          }
        });
        video.play().catch(e => console.log("play error", e));
      }
    },
    
    toggleSound(id) {
      const video = this.getVideoElement(id);
      if (video) {
        video.muted = !video.muted;
        this.videoMuted[id] = video.muted;
      }
    },
    
    handleScroll() {
      const scrollTop = window.scrollY;
      const windowHeight = window.innerHeight;
      const fullHeight = document.documentElement.scrollHeight;
      
      // Load more when user scrolls near bottom (300px before end)
      if (scrollTop + windowHeight >= fullHeight - 300 && 
          !this.loadingMore && 
          this.hasMore && 
          this.currentDisplayIndex < this.allPeople.length) {
        this.loadNextBatch();
      }
      
      // Also fetch next page from API if needed
      if (scrollTop + windowHeight >= fullHeight - 500 && 
          !this.loadingMore && 
          this.currentPage < this.lastPage) {
        this.loadNextPage();
      }
    },
    
    loadNextBatch() {
      // Load next 3 profiles from already fetched data
      const nextIndex = this.currentDisplayIndex + this.itemsPerPage;
      const nextBatch = this.allPeople.slice(this.currentDisplayIndex, nextIndex);
      
      if (nextBatch.length > 0) {
        this.displayedPeople.push(...nextBatch);
        this.currentDisplayIndex = nextIndex;
        
        // Update hasMore flag
        this.hasMore = this.currentDisplayIndex < this.allPeople.length || this.currentPage < this.lastPage;
        
        // Refresh observer for new cards
        this.$nextTick(() => {
          this.refreshObserver();
        });
      } else {
        this.hasMore = false;
      }
    },
    
    async loadNextPage() {
      if (this.loadingMore) return;
      this.loadingMore = true;
      
      try {
        await this.loadUsers(this.currentPage + 1);
      } finally {
        this.loadingMore = false;
      }
    },
    
    toggleFilter() {
      this.showFilter = !this.showFilter;
    },
    
    applyFilters() {
      this.showFilter = false;
      this.resetAndSearch();
    },
    
    clearFilters() {
      this.filters = {
        status: "",
        subtitle: "",
        city: "",
        state: "",
        verified_badge: "",
        habits: ""
      };
      this.showFilter = false;
      this.resetAndSearch();
    },
    
    shuffleArray(array) {
      const shuffled = [...array];
      for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
      }
      return shuffled;
    },
    
    async loadUsers(page = 1) {
      const token = localStorage.getItem("token");
      if (!token) return;
      
      if (page === 1) {
        this.loading = true;
        this.displayedPeople = [];
        this.currentDisplayIndex = 0;
        this.allPeople = [];
      }
      
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
            habits: this.filters.habits,
            per_page: 9  // Fetch 9 profiles at once from API but display 3 at a time
          }
        });
        
        let newUsers = res.data.data.map(p => ({
          ...p,
          profile_photo: p.profile_photo
            ? `https://companion.ajaywatpade.in/${p.profile_photo}`
            : "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400",
          introduction_video: p.introduction_video
            ? `https://companion.ajaywatpade.in/dating-backend/public/storage/${p.introduction_video}`
            : null,
          liked: p.liked || false,
          like_count: p.like_count || 0
        }));
        
        newUsers = this.shuffleArray(newUsers);
        
        if (page === 1) {
          this.allPeople = newUsers;
          // Load first batch (3 profiles)
          const firstBatch = this.allPeople.slice(0, this.itemsPerPage);
          this.displayedPeople = firstBatch;
          this.currentDisplayIndex = firstBatch.length;
          this.hasMore = this.currentDisplayIndex < this.allPeople.length || page < res.data.last_page;
        } else {
          // Append new users to allPeople
          this.allPeople = [...this.allPeople, ...newUsers];
          // Check if we need to load more into displayedPeople
          if (this.displayedPeople.length < this.allPeople.length && this.hasMore) {
            const remainingSlots = this.itemsPerPage - (this.displayedPeople.length % this.itemsPerPage);
            if (remainingSlots > 0 && remainingSlots < this.itemsPerPage) {
              this.loadNextBatch();
            }
          }
        }
        
        this.currentPage = res.data.current_page;
        this.lastPage = res.data.last_page;
        this.hasMore = this.currentDisplayIndex < this.allPeople.length || this.currentPage < this.lastPage;
        
        this.$nextTick(() => {
          this.refreshObserver();
        });
        
      } catch (err) {
        console.error("Load error", err);
      } finally {
        this.loading = false;
        this.loadingMore = false;
      }
    },
    
    refreshObserver() {
      // Re-setup observer for new cards
      if (this.observer) {
        this.observer.disconnect();
        this.setupIntersectionObserver();
      }
    },
    
    async loadUser() {
      const stored = JSON.parse(localStorage.getItem("user"));
      if (!stored || !localStorage.getItem("token")) {
        this.$router.replace("/");
        return;
      }
      this.user = {
        first_name: stored.first_name || "",
        last_name: stored.last_name || "",
        city: stored.city || "World",
        profile_photo: stored.profile_photo
          ? `https://companion.ajaywatpade.in/dating-backend/public/storage/${stored.profile_photo}`
          : "https://randomuser.me/api/portraits/women/68.jpg"
      };
    },
    
    async toggleFavourite(person) {
      const token = localStorage.getItem("token");
      if (!token) return;
      try {
        const res = await axios.post(
          `https://companion.ajaywatpade.in/api/users/${person.id}/toggle-like`,
          {},
          { headers: { Authorization: `Bearer ${token}` } }
        );
        person.liked = res.data.liked;
        person.like_count = res.data.like_count;
      } catch (e) {
        console.error(e);
      }
    },
    
    goToDetails(id) {
      this.$router.push(`/details/${id}`);
    },
    
    goHome() {
      this.activeNav = "home";
      window.scrollTo({ top: 0, behavior: "smooth" });
      this.resetAndSearch();
    },
    
    openSearchFocus() {
      this.searchFocused = true;
      document.querySelector(".search-input-modern")?.focus();
    },
    
    setupIntersectionObserver() {
      if (this.observer) this.observer.disconnect();
      
      this.observer = new IntersectionObserver(
        (entries) => {
          const visibleEntries = entries.filter(e => e.isIntersecting).sort((a,b) => a.boundingClientRect.top - b.boundingClientRect.top);
          
          entries.forEach(entry => {
            if (!entry.isIntersecting) {
              const id = parseInt(entry.target.dataset.id);
              const vid = this.getVideoElement(id);
              if (vid && !vid.paused) {
                vid.pause();
                vid.currentTime = 0;
              }
            }
          });
          
          if (visibleEntries.length > 0 && this.playingVideo === null) {
            const topId = parseInt(visibleEntries[0].target.dataset.id);
            const topVideo = this.getVideoElement(topId);
            if (topVideo && topVideo.src) {
              topVideo.muted = false;
              this.videoMuted[topId] = false;
              topVideo.play().catch(() => {});
            }
          }
        },
        { threshold: 0.4 }
      );
      
      this.displayedPeople.forEach(p => {
        const card = this.$refs[`card-${p.id}`];
        if (card && card[0]) {
          card[0].setAttribute("data-id", p.id);
          this.observer.observe(card[0]);
        }
      });
    },
    
    async refreshVisibleUsers() {
      const token = localStorage.getItem("token");
      if (!token) return;
      try {
        const res = await axios.get("https://companion.ajaywatpade.in/api/users", {
          headers: { Authorization: `Bearer ${token}` },
          params: { per_page: 50 }
        });
        const updatedMap = new Map(res.data.data.map(u => [u.id, u]));
        this.allPeople = this.allPeople.map(p => {
          const fresh = updatedMap.get(p.id);
          if (fresh) {
            return {
              ...p,
              rating: fresh.rating,
              like_count: fresh.like_count,
              status: fresh.status,
              liked: fresh.liked
            };
          }
          return p;
        });
        
        // Update displayedPeople with fresh data
        this.displayedPeople = this.displayedPeople.map(p => {
          const fresh = updatedMap.get(p.id);
          if (fresh) {
            return {
              ...p,
              rating: fresh.rating,
              like_count: fresh.like_count,
              status: fresh.status,
              liked: fresh.liked
            };
          }
          return p;
        });
        
      } catch(e) {}
    },
    
    // Animated placeholder methods
    startTypingAnimation() {
      this.currentDisplayText = "";
      this.isDeleting = false;
      this.placeholderIndex = 0;
      this.currentPlaceholder = this.placeholders[0];
      this.typeNextCharacter();
    },
    
    typeNextCharacter() {
      const fullText = this.placeholders[this.placeholderIndex];
      
      if (!this.isDeleting) {
        // Typing
        if (this.currentDisplayText.length < fullText.length) {
          this.currentDisplayText = fullText.substring(0, this.currentDisplayText.length + 1);
          this.currentPlaceholder = this.currentDisplayText;
          this.typingTimeout = setTimeout(() => this.typeNextCharacter(), this.typingSpeed);
        } else {
          // Finished typing, pause then start deleting
          this.isDeleting = true;
          this.typingTimeout = setTimeout(() => this.typeNextCharacter(), this.pauseDuration);
        }
      } else {
        // Deleting
        if (this.currentDisplayText.length > 0) {
          this.currentDisplayText = this.currentDisplayText.substring(0, this.currentDisplayText.length - 1);
          this.currentPlaceholder = this.currentDisplayText;
          this.typingTimeout = setTimeout(() => this.typeNextCharacter(), this.deletingSpeed);
        } else {
          // Move to next placeholder
          this.isDeleting = false;
          this.placeholderIndex = (this.placeholderIndex + 1) % this.placeholders.length;
          this.typingTimeout = setTimeout(() => this.typeNextCharacter(), this.typingSpeed);
        }
      }
    }
  }
};
</script>

<style scoped>
/* Keep all existing styles... */

/* Add these new styles */
.end-message {
  text-align: center;
  padding: 40px 20px;
  color: #999;
}

.end-message i {
  font-size: 48px;
  color: #FF6B9E;
  margin-bottom: 12px;
  display: inline-block;
  animation: heartbeat 1.5s ease infinite;
}

.end-message p {
  font-size: 14px;
  font-weight: 500;
}

@keyframes heartbeat {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

/* All existing styles remain the same */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.dating-app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background: #fef9ff;
  min-height: 100vh;
  padding-bottom: 80px;
}

/* Hero Header */
.hero-header {
  background: linear-gradient(135deg, #ff0058 0%, #df004c 100%);
  border-radius: 0 0 48px 48px;
  padding: 32px 24px 56px;
  position: relative;
  overflow: hidden;
}
.header-glow {
  position: absolute;
  top: -40%;
  right: -20%;
  width: 250px;
  height: 250px;
  background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 70%);
  border-radius: 50%;
}
.header-content {
  position: relative;
  z-index: 2;
}
.greeting-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(8px);
  padding: 6px 16px;
  border-radius: 60px;
  font-size: 14px;
  font-weight: 500;
  color: white;
  margin-bottom: 16px;
}
.main-title {
  font-size: 30px;
  font-weight: 800;
  color: white;
  letter-spacing: -0.5px;
  line-height: 1.2;
}
.accent {
  background: linear-gradient(120deg, #FFE259, #FFA751);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  display: inline-block;
}
.sub-headline {
  color: rgba(255,255,255,0.9);
  margin-top: 8px;
  font-weight: 500;
}
.action-bar {
  display: flex;
  gap: 12px;
  margin-top: 28px;
  position: relative;
  z-index: 3;
}
.search-container {
  flex: 1;
  background: white;
  border-radius: 60px;
  display: flex;
  align-items: center;
  padding: 0 18px;
  gap: 12px;
  transition: all 0.2s ease;
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.search-container.focused {
  box-shadow: 0 0 0 3px rgba(255,255,255,0.6), 0 8px 20px rgba(0,0,0,0.15);
  transform: scale(1.01);
}
.search-icon {
  color: #FF6B9E;
  font-size: 18px;
}
.search-input-wrapper {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
}
.search-input-modern {
  width: 100%;
  border: none;
  padding: 14px 0;
  font-size: 16px;
  background: transparent;
  outline: none;
  position: relative;
  z-index: 2;
}
.animated-placeholder {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  color: #aaa;
  font-size: 16px;
  pointer-events: none;
  white-space: nowrap;
  transition: opacity 0.2s ease;
  z-index: 1;
}
.animated-placeholder.hidden {
  opacity: 0;
}
.placeholder-text {
  display: inline-block;
}
.cursor {
  display: inline-block;
  animation: blink 0.7s step-end infinite;
  margin-left: 2px;
  font-weight: 400;
}
@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}
.filter-chip {
  background: rgba(255,255,255,0.95);
  border: none;
  padding: 0 20px;
  border-radius: 60px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #FF5F8F;
  cursor: pointer;
  transition: 0.2s;
  backdrop-filter: blur(4px);
}
.filter-chip.active {
  background: #FF3B6F;
  color: white;
}
/* Filter Modal */
.filter-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background: white;
  z-index: 1000;
  padding: 24px;
  border-radius: 0 0 32px 32px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
  animation: slideDown 0.3s ease;
}
@keyframes slideDown {
  from { transform: translateY(-100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.filter-slide-enter-active, .filter-slide-leave-active { transition: all 0.3s ease; }
.filter-slide-enter, .filter-slide-leave-to { transform: translateY(-100%); opacity: 0; }

.filter-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  font-size: 20px;
}
.filter-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.filter-field label {
  font-size: 12px;
  font-weight: 600;
  color: #666;
  display: block;
  margin-bottom: 6px;
}
.filter-field input, .filter-field select {
  width: 100%;
  padding: 12px;
  border-radius: 16px;
  border: 1px solid #eee;
  background: #fafafa;
  transition: 0.2s;
}
.filter-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}
.btn-clear, .btn-apply {
  padding: 12px 24px;
  border-radius: 40px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}
.btn-clear { background: #f0f0f0; color: #555; }
.btn-apply { background: linear-gradient(135deg, #FF6B9E, #FF8E53); color: white; }

/* Feed Grid */
.feed-container {
  padding: 24px 16px;
}
.cards-grid {
  display: flex;
  flex-direction: column;
  gap: 28px;
}
.companion-card {
  background: white;
  border-radius: 36px;
  overflow: hidden;
  box-shadow: 0 15px 35px rgba(0,0,0,0.05), 0 5px 12px rgba(0,0,0,0.08);
  transition: transform 0.2s ease, box-shadow 0.2s;
}
.companion-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 25px 40px rgba(0,0,0,0.12);
}
.media-wrapper {
  position: relative;
  cursor: pointer;
}
.card-media {
  width: 100%;
  aspect-ratio: 4 / 5;
  object-fit: cover;
  display: block;
}
.video-container {
  position: relative;
}
.video-overlay {
  position: absolute;
  bottom: 16px;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  gap: 20px;
  z-index: 5;
}
.video-ctrl {
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(8px);
  border: none;
  width: 44px;
  height: 44px;
  border-radius: 60px;
  color: white;
  font-size: 18px;
  cursor: pointer;
}
.card-badges {
  position: absolute;
  top: 16px;
  left: 16px;
  right: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.rating-badge {
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(8px);
  padding: 6px 12px;
  border-radius: 40px;
  color: gold;
  font-weight: 600;
  font-size: 13px;
}
.like-btn {
  background: rgba(255,255,255,0.9);
  border: none;
  width: 44px;
  height: 44px;
  border-radius: 60px;
  font-size: 20px;
  color: #aaa;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: 0.2s;
}
.like-btn.liked {
  color: #FF3366;
  background: white;
  transform: scale(1.05);
}
.like-count {
  font-size: 12px;
  font-weight: bold;
}
.card-info {
  padding: 20px;
}
.name-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
}
.name-row h3 {
  font-size: 18px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 6px;
}
.verified-icon {
  width: 22px;
  height: 23px;
  margin-left: 4px;
  vertical-align: middle;
}
.distance {
  font-size: 13px;
  color: #888;
}
.status-tag {
  background: #FFE9F0;
  display: inline-block;
  padding: 5px 14px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
  color: #FF5F8F;
  margin: 12px 0 8px;
}
.subtitle {
  color: #666;
  font-size: 14px;
  margin-bottom: 16px;
}
.action-row {
  margin-top: 8px;
}
.chat-preview-btn {
  background: linear-gradient(135deg, #ff0058 0%, #df004c 100%);
  border: none;
  padding: 12px 20px;
  border-radius: 60px;
  color: white;
  font-weight: 700;
  width: 100%;
  cursor: pointer;
  display: flex;
  justify-content: center;
  gap: 8px;
  transition: 0.2s;
}
/* Bottom Nav */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background: rgba(255,255,255,0.96);
  backdrop-filter: blur(20px);
  display: flex;
  justify-content: space-around;
  align-items: center;
  padding: 12px 20px 28px;
  border-top: 1px solid rgba(0,0,0,0.05);
  z-index: 100;
}
.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #888;
  cursor: pointer;
  transition: 0.2s;
}
.nav-item i, .nav-avatar {
  font-size: 22px;
}
.nav-item.active {
  color: #FF5F8F;
  transform: translateY(-3px);
}
.center-btn {
  background: #FF5F8F;
  width: 52px;
  height: 52px;
  border-radius: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white !important;
  margin-top: -20px;
  box-shadow: 0 8px 20px rgba(255,95,143,0.4);
}
.nav-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}
.loading-skeleton, .empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #aaa;
}
.skeleton-card {
  height: 480px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: loading 1.5s infinite;
  border-radius: 36px;
  margin-bottom: 24px;
}
@keyframes loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
.loader-more {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  padding: 20px;
}
.dot-floating {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #FF6B9E;
  animation: pulse 0.8s infinite alternate;
}
@keyframes pulse {
  from { opacity: 0.3; transform: scale(0.8); }
  to { opacity: 1; transform: scale(1.2); }
}
.card-list-enter-active, .card-list-leave-active { transition: all 0.4s; }
.card-list-enter, .card-list-leave-to { opacity: 0; transform: translateY(30px); }
</style>