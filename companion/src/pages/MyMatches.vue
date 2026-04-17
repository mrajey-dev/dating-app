<template>
  <div class="matches-page">
    <!-- Animated Background -->
    <div class="bg-decoration">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>

    <!-- Header -->
    <div class="page-header">
      <button class="back-btn" @click="$router.push('/home')">
        <span>←</span> Back
      </button>
      <div class="header-title">
        <div class="title-icon">💖</div>
        <h1>My Matches</h1>
      </div>
      <div class="header-spacer"></div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-container">
      <div class="tabs">
        <button 
          :class="['tab', { active: tab === 'matches' }]" 
          @click="tab = 'matches'"
        >
          <span class="tab-icon">💕</span>
          <span>Matches</span>
          <span v-if="matches.length" class="tab-count">{{ matches.length }}</span>
        </button>
        <button 
          :class="['tab', { active: tab === 'incoming' }]" 
          @click="tab = 'incoming'"
        >
          <span class="tab-icon">💌</span>
          <span>Invitations</span>
          <span v-if="incomingInviteCount > 0" class="tab-count incoming-count">{{ incomingInviteCount }}</span>
        </button>
        <button 
          :class="['tab', { active: tab === 'sent' }]" 
          @click="tab = 'sent'"
        >
          <span class="tab-icon">📨</span>
          <span>Sent</span>
        </button>
        <button 
          :class="['tab', { active: tab === 'planned' }]" 
          @click="tab = 'planned'"
        >
          <span class="tab-icon">💖</span>
          <span>Planned</span>
          <span v-if="plannedDates.length" class="tab-count">{{ plannedDates.length }}</span>
        </button>
      </div>
    </div>

    <!-- Content Area -->
    <div class="content-area">
      <!-- Matches Tab -->
      <div v-if="tab === 'matches'" class="tab-content">
        <div v-if="matches.length" class="matches-grid">
          <div v-for="match in matches" :key="match.id" class="match-card">
            <div class="card-avatar" @click="goToProfile(match)">
              <img :src="imageUrl(match.profile_photo)" alt="Avatar" />
              <div class="online-indicator" v-if="match.is_online"></div>
            </div>
            <div class="card-info">
              <div class="info-header">
                <h3 @click="goToProfile(match)">{{ match.first_name }} {{ match.last_name }}</h3>
                <div class="match-badge">✨ Match</div>
              </div>
              <p class="match-time">Matched {{ formatRelativeDate(match.created_at) }}</p>
              <div class="card-actions">
                <div class="message-action">
                  <button class="action-btn msg-btn" @click="openChat(match)">
                    <span>💬</span> Message
                  </button>
                  <span v-if="unreadCounts[match.id]" class="msg-badge">{{ unreadCounts[match.id] }}</span>
                </div>
                <button class="action-btn date-action-btn" @click="planDate(match)">
                  <span>📅</span> Plan Date
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <div class="empty-icon">💔</div>
          <h3>No Matches Yet</h3>
          <p>Keep swiping to find your perfect match!</p>
          <button class="explore-btn" @click="$router.push('/home')">Discover People →</button>
        </div>
      </div>

      <!-- Incoming Invitations Tab -->
      <div v-if="tab === 'incoming'" class="tab-content">
        <div v-if="incomingInvitations.length" class="invitations-grid">
          <div v-for="invite in incomingInvitations" :key="invite.id" class="invite-card">
            <div class="invite-image">
              <img :src="imageUrl(invite.place_image)" alt="Venue" />
              <div class="invite-type">{{ invite.place_type }}</div>
            </div>
            <div class="invite-details">
              <h4>{{ invite.place_title }}</h4>
              <p class="invite-location">📍 {{ invite.place_location }}</p>
              <p class="invite-from">👤 From: <strong>{{ invite.planner_name }}</strong></p>
              <p class="invite-datetime">📅 {{ formatDate(invite.schedule_date) }} • ⏰ {{ invite.schedule_time }}</p>
              <p v-if="invite.note" class="invite-note">💬 "{{ invite.note }}"</p>
              <div class="invite-buttons">
                <button class="accept-btn" @click="acceptInvite(invite.id)">✓ Accept</button>
                <button class="decline-btn" @click="declineInvite(invite.id)">✗ Decline</button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <div class="empty-icon">📭</div>
          <h3>No Invitations</h3>
          <p>You haven't received any date invitations yet.</p>
        </div>
      </div>

      <!-- Sent Invitations Tab -->
      <div v-if="tab === 'sent'" class="tab-content">
        <div v-if="sentInvitations.length" class="invitations-grid">
          <div v-for="invite in sentInvitations" :key="invite.id" class="invite-card sent-card">
            <div class="invite-image">
              <img :src="imageUrl(invite.place_image)" alt="Venue" />
              <div class="invite-type">{{ invite.place_type }}</div>
            </div>
            <div class="invite-details">
              <h4>{{ invite.place_title }}</h4>
              <p class="invite-location">📍 {{ invite.place_location }}</p>
              <p class="invite-to">👤 To: <strong>{{ invite.invitee_name }}</strong></p>
              <p class="invite-datetime">📅 {{ formatDate(invite.schedule_date) }} • ⏰ {{ invite.schedule_time }}</p>
              <p v-if="invite.note" class="invite-note">💬 "{{ invite.note }}"</p>
              <div class="status-chip" :class="invite.status.toLowerCase()">
                {{ invite.status }}
              </div>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <div class="empty-icon">✉️</div>
          <h3>No Sent Invitations</h3>
          <p>Your sent invitations will appear here.</p>
        </div>
      </div>

      <!-- Planned Dates Tab -->
      <div v-if="tab === 'planned'" class="tab-content">
        <div v-if="plannedDates.length" class="planned-grid">
          <div v-for="date in plannedDates" :key="date.id" class="planned-card">
            <div class="planned-image">
              <img :src="imageUrl(date.place_image)" alt="Venue" />
              <div class="planned-badge">💖 Confirmed</div>
            </div>
            <div class="planned-details">
              <h4>{{ date.place_title }}</h4>
              <p class="planned-location">📍 {{ date.place_location }}</p>
              <p class="planned-with">👥 With: <strong>{{ date.invitee_name }}</strong></p>
              <p class="planned-datetime">📅 {{ formatDate(date.schedule_date) }} at {{ date.schedule_time }}</p>
              <p v-if="date.note" class="planned-note">💌 "{{ date.note }}"</p>
              <button class="map-btn" @click="openMap(date)">
                🗺️ View on Map
              </button>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <div class="empty-icon">🗓️</div>
          <h3>No Planned Dates</h3>
          <p>Your confirmed dates will show up here.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios"

export default {
  name: "MyMatches",
  data() {
    return {
      incomingInviteCount: 0,
      unreadCounts: {},
      matches: [],
      incomingInvitations: [],
      sentInvitations: [],
      plannedDates: [],
      tab: 'matches',
      unreadInterval: null,
      incomingInviteInterval: null
    }
  },

  async mounted() {
    // Scroll to top immediately when component mounts
    this.scrollToTop()
    
    await this.getMatches()
    await this.fetchIncomingInvites()
    await this.fetchSentInvites()
    await this.fetchUnreadCounts()
    await this.fetchIncomingInviteCount()
    await this.fetchPlannedDates()
    
    this.unreadInterval = setInterval(() => {
      this.fetchUnreadCounts()
      this.fetchIncomingInviteCount()
    }, 5000)

    this.incomingInviteInterval = setInterval(() => {
      if (this.tab === 'incoming') {
        this.fetchIncomingInvites()
      }
    }, 10000)
  },

  beforeRouteEnter(to, from, next) {
    // For Vue Router - scroll to top before entering the route
    next(vm => {
      vm.scrollToTop()
    })
  },

  beforeRouteUpdate(to, from, next) {
    // For Vue Router - scroll to top when navigating within the same component
    this.scrollToTop()
    next()
  },

  beforeUnmount() {
    if (this.unreadInterval) clearInterval(this.unreadInterval)
    if (this.incomingInviteInterval) clearInterval(this.incomingInviteInterval)
  },

  watch: {
    tab(newTab) {
      if (newTab === 'incoming') {
        this.incomingInviteCount = 0
      }
    }
  },

  methods: {
    // Scroll to top with smooth behavior
    scrollToTop() {
      // Use multiple methods to ensure scrolling works across different browsers and scenarios
      window.scrollTo({
        top: 0,
        left: 0,
        behavior: 'smooth'
      })
      
      // Fallback for older browsers
      document.documentElement.scrollTop = 0
      document.body.scrollTop = 0
      
      // Also scroll any scrollable containers
      const scrollableElements = document.querySelectorAll('.matches-page, .content-area')
      scrollableElements.forEach(element => {
        if (element.scrollTop) {
          element.scrollTop = 0
        }
      })
    },

    async fetchPlannedDates() {
      try {
        const token = localStorage.getItem("token")
        const res = await axios.get(
          "https://companion.ajaywatpade.in/api/invitations/accepted",
          { headers: { Authorization: `Bearer ${token}` } }
        )
        this.plannedDates = res.data.map(inv => ({
          ...inv,
          invitee_name: inv.invitee
            ? inv.invitee.first_name + ' ' + inv.invitee.last_name
            : inv.planner.first_name + ' ' + inv.planner.last_name
        }))
      } catch (err) {
        console.error(err)
        this.plannedDates = []
      }
    },

    async fetchIncomingInviteCount() {
      try {
        const token = localStorage.getItem("token")
        const res = await axios.get(
          "https://companion.ajaywatpade.in/api/invitations/incoming-count",
          { headers: { Authorization: `Bearer ${token}` } }
        )
        this.incomingInviteCount = res.data.count || 0
      } catch (err) {
        console.error(err)
        this.incomingInviteCount = 0
      }
    },

    async fetchUnreadCounts() {
      try {
        const token = localStorage.getItem("token")
        const res = await axios.get(
          "https://companion.ajaywatpade.in/api/messages/unread-counts",
          { headers: { Authorization: `Bearer ${token}` } }
        )
        this.unreadCounts = res.data || {}
      } catch (err) {
        console.error(err)
        this.unreadCounts = {}
      }
    },

    async getMatches() {
      try {
        const token = localStorage.getItem("token")
        const userRes = await axios.get("https://companion.ajaywatpade.in/api/me", {
          headers: { Authorization: `Bearer ${token}` }
        })
        const userGender = userRes.data.gender

        const res = await axios.get("https://companion.ajaywatpade.in/api/my-matches", {
          headers: { Authorization: `Bearer ${token}` }
        })

        let matches = res.data || []
        if (userGender === "male") {
          matches = matches.filter(match => match.gender === "female")
        } else if (userGender === "female") {
          matches = matches.filter(match => match.gender === "male")
        }
        this.matches = matches
      } catch (err) {
        console.error(err)
        this.matches = []
      }
    },

    async fetchIncomingInvites() {
      try {
        const token = localStorage.getItem("token")
        const res = await axios.get("https://companion.ajaywatpade.in/api/invitations/incoming", 
          { headers: { Authorization: `Bearer ${token}` } }
        )
        this.incomingInvitations = res.data.map(inv => ({ 
          ...inv, 
          planner_name: inv.planner.first_name + ' ' + inv.planner.last_name 
        }))
      } catch (err) { 
        console.error(err)
        this.incomingInvitations = [] 
      }
    },

    async fetchSentInvites() {
      try {
        const token = localStorage.getItem("token")
        const res = await axios.get("https://companion.ajaywatpade.in/api/invitations/sent", 
          { headers: { Authorization: `Bearer ${token}` } }
        )
        this.sentInvitations = res.data.map(inv => ({ 
          ...inv, 
          invitee_name: inv.invitee.first_name + ' ' + inv.invitee.last_name 
        }))
      } catch (err) { 
        console.error(err)
        this.sentInvitations = [] 
      }
    },

    async acceptInvite(inviteId) {
      try {
        const token = localStorage.getItem("token")
        await axios.post(
          `https://companion.ajaywatpade.in/api/invitations/${inviteId}/accept`,
          {},
          { headers: { Authorization: `Bearer ${token}` } }
        )
        await this.fetchIncomingInvites()
        await this.fetchPlannedDates()
      } catch (err) {
        console.error(err)
        alert("Failed to accept invitation.")
      }
    },

    async declineInvite(inviteId) {
      try {
        const token = localStorage.getItem("token")
        await axios.post(
          `https://companion.ajaywatpade.in/api/invitations/${inviteId}/decline`,
          {},
          { headers: { Authorization: `Bearer ${token}` } }
        )
        await this.fetchIncomingInvites()
      } catch (err) {
        console.error(err)
        alert("Failed to decline invitation.")
      }
    },

    goToProfile(user) { 
      this.$router.push('/details/' + user.id) 
    },

    openChat(user) {
      this.$router.push({ path: '/details/' + user.id, query: { chat: 1 } })
      if (this.unreadCounts[user.id]) {
        this.unreadCounts[user.id] = 0
      }
    },

    planDate(user) { 
      this.$router.push({ path: '/date-planner/' + user.id }) 
    },

    openMap(item) {
      if (!item.place_location) return
      window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(item.place_location)}`, "_blank")
    },

    imageUrl(path) { 
      if (!path) return "https://via.placeholder.com/200"
      return path.startsWith("http") ? path : `https://companion.ajaywatpade.in/dating-backend/public/storage/${path}`
    },

    formatDate(date) { 
      return date ? new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : "" 
    },

    formatRelativeDate(date) {
      if (!date) return ""
      const now = new Date()
      const then = new Date(date)
      const diffDays = Math.floor((now - then) / (1000 * 60 * 60 * 24))
      if (diffDays === 0) return "today"
      if (diffDays === 1) return "yesterday"
      if (diffDays < 7) return `${diffDays} days ago`
      return then.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
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

.matches-page {
  min-height: 100vh;
  background: linear-gradient(145deg, #fef5f7 0%, #fff0f3 100%);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  position: relative;
}

/* Background Decoration */
.bg-decoration {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 0;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.3;
}

.orb-1 {
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, #ffb3c6, #ff6b8b);
  top: -150px;
  right: -100px;
}

.orb-2 {
  width: 350px;
  height: 350px;
  background: radial-gradient(circle, #ffd6e0, #ff8c5a);
  bottom: -100px;
  left: -80px;
}

.orb-3 {
  width: 250px;
  height: 250px;
  background: radial-gradient(circle, #c94b9b, #ff6b8b);
  top: 40%;
  left: 20%;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 32px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  position: sticky;
  top: 0;
  z-index: 100;
  border-bottom: 1px solid rgba(255, 107, 139, 0.15);
}

.back-btn {
  background: #f0f0f0;
  border: none;
  padding: 8px 20px;
  border-radius: 40px;
  font-size: 14px;
  font-weight: 500;
  color: #666;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.back-btn:hover {
  background: #ff6b8b;
  color: white;
  transform: translateX(-2px);
}

.header-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.title-icon {
  font-size: 32px;
}

.header-title h1 {
  font-size: 26px;
  font-weight: 700;
  color: #1a1a2e;
  letter-spacing: -0.5px;
}

.header-spacer {
  width: 100px;
}

/* Tab Container */
.tab-container {
  display: flex;
  justify-content: center;
  padding: 24px 20px 0;
  position: relative;
  z-index: 2;
}

.tabs {
  display: flex;
  overflow: auto;
  gap: 12px;
  background: white;
  padding: 6px;
  border-radius: 60px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.tab {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 24px;
  background: transparent;
  border: none;
  border-radius: 50px;
  font-size: 14px;
  font-weight: 600;
  color: #888;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.tab-icon {
  font-size: 16px;
}

.tab.active {
  background: linear-gradient(135deg, #ff6b8b, #ff8c5a);
  color: white;
  box-shadow: 0 4px 12px rgba(255, 107, 139, 0.3);
}

.tab-count {
  background: rgba(0, 0, 0, 0.1);
  padding: 2px 8px;
  border-radius: 30px;
  font-size: 11px;
  font-weight: 700;
}

.tab.active .tab-count {
  background: rgba(255, 255, 255, 0.25);
  color: white;
}

.incoming-count {
  background: #ff6b8b;
  color: white;
}

/* Content Area */
.content-area {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 24px 60px;
  position: relative;
  z-index: 2;
}

/* Matches Grid */
.matches-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 24px;
}

.match-card {
  background: white;
  border-radius: 28px;
  padding: 20px;
  display: flex;
  gap: 18px;
  transition: all 0.3s ease;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(255, 107, 139, 0.1);
}

.match-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.12);
}

.card-avatar {
  position: relative;
  cursor: pointer;
}

.card-avatar img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #ff6b8b;
}

.online-indicator {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 14px;
  height: 14px;
  background: #4caf50;
  border-radius: 50%;
  border: 2px solid white;
}

.card-info {
  flex: 1;
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 6px;
  flex-wrap: wrap;
  gap: 8px;
}

.info-header h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a2e;
  cursor: pointer;
}

.info-header h3:hover {
  color: #ff6b8b;
}

.match-badge {
  font-size: 11px;
  background: #ffe4e6;
  color: #ff6b8b;
  padding: 3px 10px;
  border-radius: 20px;
  font-weight: 600;
}

.match-time {
  font-size: 12px;
  color: #aaa;
  margin-bottom: 14px;
}

.card-actions {
  display: flex;
  gap: 12px;
}

.message-action {
  position: relative;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 18px;
  border: none;
  border-radius: 40px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.msg-btn {
  background: linear-gradient(135deg, #ff6b8b, #ff8c5a);
  color: white;
}

.date-action-btn {
  background: #f0f2f5;
  color: #444;
}

.action-btn:hover {
  transform: scale(1.02);
}

.msg-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #ff3b3b;
  color: white;
  font-size: 10px;
  font-weight: bold;
  padding: 2px 6px;
  border-radius: 50%;
  min-width: 18px;
  text-align: center;
}

/* Invitations Grid */
.invitations-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 24px;
}

.invite-card {
  background: white;
  border-radius: 24px;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
}

.invite-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.12);
}

.invite-image {
  position: relative;
  height: 170px;
}

.invite-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.invite-type {
  position: absolute;
  top: 12px;
  right: 12px;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  color: white;
  padding: 4px 14px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
}

.invite-details {
  padding: 20px;
}

.invite-details h4 {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 8px;
}

.invite-location, .invite-from, .invite-to {
  font-size: 13px;
  color: #666;
  margin-bottom: 6px;
}

.invite-datetime {
  font-size: 13px;
  color: #ff6b8b;
  font-weight: 500;
  margin: 8px 0;
}

.invite-note {
  font-size: 13px;
  color: #888;
  font-style: italic;
  padding: 10px;
  background: #fef5f7;
  border-radius: 14px;
  margin: 10px 0;
}

.invite-buttons {
  display: flex;
  gap: 12px;
  margin-top: 12px;
}

.accept-btn {
  flex: 1;
  background: linear-gradient(135deg, #28a745, #5cd65c);
  border: none;
  padding: 10px;
  border-radius: 40px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.decline-btn {
  flex: 1;
  background: #f0f2f5;
  border: none;
  padding: 10px;
  border-radius: 40px;
  color: #666;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.accept-btn:hover, .decline-btn:hover {
  transform: scale(1.02);
}

.status-chip {
  display: inline-block;
  padding: 6px 16px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
  margin-top: 12px;
}

.status-chip.pending {
  background: #fff3e0;
  color: #ff9800;
}

.status-chip.accepted {
  background: #e8f5e9;
  color: #4caf50;
}

.status-chip.declined {
  background: #ffebee;
  color: #f44336;
}

/* Planned Grid */
.planned-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 24px;
}

.planned-card {
  background: white;
  border-radius: 24px;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(255, 107, 139, 0.15);
}

.planned-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.12);
}

.planned-image {
  position: relative;
  height: 170px;
}

.planned-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.planned-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: linear-gradient(135deg, #ff6b8b, #ff8c5a);
  color: white;
  padding: 5px 14px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 700;
}

.planned-details {
  padding: 20px;
}

.planned-details h4 {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 8px;
}

.planned-location, .planned-with {
  font-size: 13px;
  color: #666;
  margin-bottom: 6px;
}

.planned-datetime {
  font-size: 13px;
  color: #ff6b8b;
  font-weight: 600;
  margin: 8px 0;
}

.planned-note {
  font-size: 13px;
  color: #888;
  font-style: italic;
  padding: 10px;
  background: #fef5f7;
  border-radius: 14px;
  margin: 10px 0;
}

.map-btn {
  width: 100%;
  background: linear-gradient(135deg, #ff6b8b, #ff8c5a);
  border: none;
  padding: 12px;
  border-radius: 40px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 8px;
}

.map-btn:hover {
  transform: scale(1.02);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
}

.empty-icon {
  font-size: 72px;
  margin-bottom: 20px;
}

.empty-state h3 {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 10px;
}

.empty-state p {
  color: #888;
  margin-bottom: 28px;
}

.explore-btn {
  background: linear-gradient(135deg, #ff6b8b, #ff8c5a);
  border: none;
  padding: 12px 32px;
  border-radius: 40px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.explore-btn:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 20px rgba(255, 107, 139, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .page-header {
    padding: 16px 20px;
  }
  
  .header-title h1 {
    font-size: 20px;
  }
  
  .title-icon {
    font-size: 24px;
  }
  
  .header-spacer {
    width: 60px;
  }
  
  .tabs {
    gap: 6px;
  }
  
  .tab {
    padding: 8px 16px;
    font-size: 12px;
  }
  
  .tab-icon {
    font-size: 14px;
  }
  
  .content-area {
    padding: 20px 16px 40px;
  }
  
  .matches-grid, .invitations-grid, .planned-grid {
    grid-template-columns: 1fr;
  }
  
  .match-card {
    padding: 16px;
    gap: 14px;
  }
  
  .card-avatar img {
    width: 65px;
    height: 65px;
  }
  
  .info-header h3 {
    font-size: 16px;
  }
  
  .action-btn {
    padding: 6px 14px;
    font-size: 12px;
  }
}

@media (max-width: 480px) {
  .back-btn span:first-child {
    display: none;
  }
  
  .back-btn {
    padding: 8px 14px;
  }
  
  .match-card {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  
  .card-actions {
    justify-content: center;
  }
  
  .info-header {
    justify-content: center;
  }
}
</style>