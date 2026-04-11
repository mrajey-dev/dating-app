<template>
  <div class="date-planner-page" v-if="person">
    <!-- PREMIUM HEADER WITH GLASS EFFECT -->
    <div class="planner-header">
      <div class="header-glow"></div>
      <div class="planner-user">
        <div class="avatar-wrapper">
          <img :src="person.profile_photo" class="planner-avatar" />
          <div class="avatar-ring"></div>
        </div>
        <div class="user-info">
          <h2>Plan a Perfect Date</h2>
          <p>with <span class="highlight">{{ person.first_name }}</span> <span class="heart-beat">💖</span></p>
        </div>
      </div>
      <div class="header-stats">
        <div class="stat-badge">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span>Dream Date</span>
        </div>
      </div>
    </div>

    <!-- CATEGORY FILTER - MODERN DESIGN -->
    <div class="planner-filters">
      <button 
        v-for="type in categories"
        :key="type"
        :class="{ active: selectedCategory === type }"
        @click="selectedCategory = type"
      >
        <span class="filter-icon">{{ getFilterIcon(type) }}</span>
        <span>{{ type }}</span>
      </button>
    </div>

    <!-- DATE SUGGESTIONS GRID -->
    <div class="planner-grid">
      <div 
        v-for="(place, index) in filteredSuggestions"
        :key="index"
        class="planner-card"
        :style="{ animationDelay: `${index * 0.1}s` }"
      >
        <div class="card-image">
          <img :src="place.image" :alt="place.title" />
          <div class="card-type-badge">{{ place.type }}</div>
          <div class="card-overlay"></div>
        </div>
        <div class="planner-info">
          <h4>{{ place.title }}</h4>
          <p>{{ place.description }}</p>
          <div class="card-location">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span>{{ place.location }}</span>
          </div>
          <button class="book-btn" @click="openScheduleModal(place)">
            <span>Schedule Date</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- PENDING INVITATIONS SECTION -->
    <div v-if="pendingInvitations.length" class="pending-invitations">
      <div class="section-header">
        <div class="section-icon">💌</div>
        <h3>Pending Date Invitations</h3>
        <span class="pending-count">{{ pendingInvitations.length }}</span>
      </div>
      <div class="invitations-grid">
        <div v-for="invite in pendingInvitations" :key="invite.id" class="invite-card">
          <div class="invite-image">
            <img :src="imageUrl(invite.place_image)" />
            <div class="invite-type">{{ invite.place_type }}</div>
          </div>
          <div class="invite-content">
            <h4>{{ invite.place_title }}</h4>
            <div class="invite-details">
              <div class="detail-item">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>{{ invite.place_location }}</span>
              </div>
              <div class="detail-item">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>{{ formatDate(invite.schedule_date) }} at {{ invite.schedule_time }}</span>
              </div>
              <div class="detail-item" v-if="invite.note">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>{{ invite.note }}</span>
              </div>
            </div>
            <div class="invite-actions">
              <button class="accept-btn" @click="acceptInvite(invite.id)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Accept
              </button>
              <button class="decline-btn" @click="declineInvite(invite.id)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Decline
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SCHEDULE DATE MODAL -->
    <transition name="modal-fade">
      <div v-if="showScheduleModal" class="modal-overlay" @click.self="closeScheduleModal">
        <div class="schedule-modal">
          <div class="modal-header">
            <div class="modal-icon">💖</div>
            <h3>Schedule Your Dream Date</h3>
            <button class="modal-close" @click="closeScheduleModal">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <p class="selected-place">with <strong>{{ person.first_name }}</strong> at <strong>{{ selectedPlace?.title }}</strong></p>
          
          <div class="form-group">
            <label>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              Select Date
            </label>
            <input type="date" v-model="schedule.date" :min="minDate" />
          </div>
          
          <div class="form-group">
            <label>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Select Time
            </label>
            <input type="time" v-model="schedule.time" />
          </div>
          
          <div class="form-group">
            <label>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Special Message (Optional)
            </label>
            <textarea
              v-model="schedule.note"
              placeholder="Write something sweet..."
              rows="3"
            ></textarea>
          </div>
          
          <button class="confirm-btn" @click="inviteForDate">
            <span>Send Invitation 💌</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios"
import { useToastStore } from "@/stores/toast"

export default {
  name: "DatePlanner",
  data() {
    return {
      pendingInvitations: [],
      toast: useToastStore(),
      showScheduleModal: false,
      selectedPlace: null,
      schedule: {
        date: "",
        time: "",
        note: ""
      },
      person: null,
      matchStatus: null,
      selectedCategory: "All",
      categories: ["All", "Cafe", "Hotel", "Experience"],
      suggestions: [
        {
          title: "Romantic Rooftop Café",
          description: "Candlelight dinner with breathtaking skyline views. Perfect for intimate conversations.",
          type: "Cafe",
          image: "https://images.unsplash.com/photo-1498654896293-37aacf113fd9?w=500",
          location: "Skyline Rooftop Cafe, Mumbai"
        },
        {
          title: "Luxury Boutique Hotel",
          description: "Private suite with spa access, infinity pool, and world-class dining.",
          type: "Hotel",
          image: "https://images.unsplash.com/photo-1566073771259-6a8506099945?w=500",
          location: "The Taj Mahal Palace, Mumbai"
        },
        {
          title: "Sunset Yacht Experience",
          description: "Private sunset cruise with champagne dinner and live music.",
          type: "Experience",
          image: "https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=500",
          location: "Gateway of India, Mumbai"
        },
        {
          title: "Cozy Coffee House",
          description: "Warm ambiance, artisanal coffee, and delicious pastries.",
          type: "Cafe",
          image: "https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=500",
          location: "Blue Tokai, Mumbai"
        },
        {
          title: "Beachside Resort",
          description: "Luxury beachfront resort with private cabanas and sunset views.",
          type: "Hotel",
          image: "https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=500",
          location: "Taj Exotica, Goa"
        },
        {
          title: "Art & Wine Night",
          description: "Paint and sip experience with live music and fine wine.",
          type: "Experience",
          image: "https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=500",
          location: "Art Guild House, Mumbai"
        }
      ]
    }
  },

  computed: {
    filteredSuggestions() {
      if (this.selectedCategory === "All") return this.suggestions
      return this.suggestions.filter(item => item.type === this.selectedCategory)
    },
    minDate() {
      const today = new Date()
      return today.toISOString().split('T')[0]
    }
  },

  async mounted() {
    await this.fetchPerson()
    await this.checkMatchStatus()
    this.fetchPendingInvitations()
    if (this.matchStatus !== "matched") {
      this.$router.push("/")
    }
  },

  methods: {
    getFilterIcon(type) {
      const icons = {
        All: "✨",
        Cafe: "☕",
        Hotel: "🏨",
        Experience: "🎨"
      }
      return icons[type] || "📍"
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    },

    imageUrl(path) {
      if (!path) return "/default-avatar.png"
      if (path.startsWith("http")) return path
      return 'https://companion.ajaywatpade.in/dating-backend/public/storage/' + path
    },

    async fetchPendingInvitations() {
      const token = localStorage.getItem("token")
      const res = await axios.get(
        "https://companion.ajaywatpade.in/api/my-date-invitations",
        { headers: { Authorization: `Bearer ${token}` } }
      )
      this.pendingInvitations = res.data.invitations || []
    },

    async acceptInvite(id) {
      const token = localStorage.getItem("token")
      await axios.post(
        `https://companion.ajaywatpade.in/api/accept-invitation/${id}`,
        {},
        { headers: { Authorization: `Bearer ${token}` } }
      )
      this.toast.show("Date invitation accepted! 🎉", "success")
      this.fetchPendingInvitations()
    },

    async declineInvite(id) {
      const token = localStorage.getItem("token")
      await axios.post(
        `https://companion.ajaywatpade.in/api/decline-invitation/${id}`,
        {},
        { headers: { Authorization: `Bearer ${token}` } }
      )
      this.toast.show("Invitation declined", "info")
      this.fetchPendingInvitations()
    },

    async inviteForDate() {
      if (!this.schedule.date || !this.schedule.time) {
        this.toast.show("Please select date & time 💖", "error")
        return
      }

      try {
        await axios.post(
          "https://companion.ajaywatpade.in/api/invite-date",
          {
            invitee_id: this.person.id,
            place_title: this.selectedPlace.title,
            place_type: this.selectedPlace.type,
            place_image: this.selectedPlace.image,
            place_location: this.selectedPlace.location,
            schedule_date: this.schedule.date,
            schedule_time: this.schedule.time,
            note: this.schedule.note
          },
          {
            headers: { Authorization: `Bearer ${localStorage.getItem("token")}` }
          }
        )
        this.toast.show(`Invitation sent to ${this.person.first_name} 💌`, "success")
        this.closeScheduleModal()
      } catch (error) {
        console.error(error)
        this.toast.show("Failed to send invitation 😢", "error")
      }
    },

    openScheduleModal(place) {
      this.selectedPlace = place
      this.showScheduleModal = true
    },

    closeScheduleModal() {
      this.showScheduleModal = false
      this.schedule = { date: "", time: "", note: "" }
    },

    async fetchPerson() {
      const res = await axios.get(
        `https://companion.ajaywatpade.in/api/users/${this.$route.params.id}`,
        { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
      )
      if (res.data.success) {
        const user = res.data.user
        this.person = {
          ...user,
          profile_photo: `https://companion.ajaywatpade.in/${user.profile_photo}`
        }
      }
    },

    async checkMatchStatus() {
      const res = await axios.post(
        "https://companion.ajaywatpade.in/api/match-status",
        { user_id: this.$route.params.id },
        { headers: { Authorization: `Bearer ${localStorage.getItem("token")}` } }
      )
      this.matchStatus = res.data.status
    }
  }
}
</script>

<style scoped>
.date-planner-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #fdf8f9 0%, #ffffff 100%);
  padding: 20px;
}

/* ================= PREMIUM HEADER ================= */
.planner-header {
  background: linear-gradient(135deg, #ff4d6d, #ff2e63, #ff6b8a);
  border-radius: 32px;
  padding: 24px;
  margin-bottom: 28px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 20px 35px -10px rgba(255, 46, 99, 0.3);
}

.header-glow {
  position: absolute;
  top: -50%;
  right: -20%;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(255,255,255,0.3), transparent);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0) scale(1); }
  50% { transform: translateY(-15px) scale(1.05); }
}

.planner-user {
  display: flex;
  align-items: center;
  gap: 18px;
  position: relative;
  z-index: 2;
}

.avatar-wrapper {
  position: relative;
}

.planner-avatar {
  width: 65px;
  height: 65px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid white;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.avatar-ring {
  position: absolute;
  top: -3px;
  left: -3px;
  right: -3px;
  bottom: -3px;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.5);
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); opacity: 0.6; }
  50% { transform: scale(1.1); opacity: 0.2; }
}

.user-info h2 {
  color: white;
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 4px 0;
}

.user-info p {
  color: rgba(255,255,255,0.9);
  font-size: 14px;
  margin: 0;
}

.highlight {
  font-weight: 700;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.heart-beat {
  display: inline-block;
  animation: heartbeat 1.2s ease-in-out infinite;
}

@keyframes heartbeat {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}

.header-stats {
  position: absolute;
  bottom: 16px;
  right: 20px;
  z-index: 2;
}

.stat-badge {
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(10px);
  padding: 6px 14px;
  border-radius: 30px;
  display: flex;
  align-items: center;
  gap: 6px;
  color: white;
  font-size: 11px;
  font-weight: 500;
}

/* ================= FILTERS ================= */
.planner-filters {
  display: flex;
  gap: 12px;
  margin-bottom: 28px;
  overflow-x: auto;
  padding-bottom: 8px;
  scrollbar-width: none;
}

.planner-filters::-webkit-scrollbar {
  display: none;
}

.planner-filters button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: white;
  border: none;
  border-radius: 50px;
  font-size: 13px;
  font-weight: 500;
  color: #666;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  white-space: nowrap;
}

.planner-filters button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.1);
}

.planner-filters .active {
  background: linear-gradient(135deg, #ff4d6d, #ff2e63);
  color: white;
  box-shadow: 0 6px 16px rgba(255, 46, 99, 0.3);
}

.filter-icon {
  font-size: 16px;
}

/* ================= GRID ================= */
.planner-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

/* ================= CARD ================= */
.planner-card {
  background: white;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
  animation: cardFadeIn 0.5s ease-out backwards;
}

@keyframes cardFadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.planner-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 35px -12px rgba(255, 46, 99, 0.2);
}

.card-image {
  position: relative;
  overflow: hidden;
  height: 180px;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.planner-card:hover .card-image img {
  transform: scale(1.08);
}

.card-type-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(8px);
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 600;
  color: white;
  z-index: 2;
}

.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.3) 100%);
  opacity: 0;
  transition: opacity 0.3s;
}

.planner-card:hover .card-overlay {
  opacity: 1;
}

.planner-info {
  padding: 16px;
}

.planner-info h4 {
  font-size: 16px;
  font-weight: 700;
  margin: 0 0 6px 0;
  color: #1a1a2e;
}

.planner-info p {
  font-size: 12px;
  color: #777;
  line-height: 1.4;
  margin: 0 0 10px 0;
}

.card-location {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  color: #ff4d6d;
  margin-bottom: 14px;
}

.book-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: linear-gradient(135deg, #ff4d6d, #ff2e63);
  border: none;
  padding: 10px 16px;
  border-radius: 40px;
  color: white;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.book-btn:hover {
  transform: scale(1.02);
  box-shadow: 0 6px 18px rgba(255, 46, 99, 0.4);
}

/* ================= PENDING INVITATIONS ================= */
.pending-invitations {
  margin-top: 40px;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid #ffe2ea;
}

.section-icon {
  font-size: 28px;
}

.section-header h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
}

.pending-count {
  background: #ff4d6d;
  color: white;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.invitations-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.invite-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  display: flex;
  gap: 16px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
  transition: all 0.25s ease;
  border: 1px solid #f0f0f0;
}

.invite-card:hover {
  transform: translateX(4px);
  box-shadow: 0 10px 25px rgba(255, 46, 99, 0.12);
}

.invite-image {
  position: relative;
  width: 110px;
  flex-shrink: 0;
}

.invite-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.invite-type {
  position: absolute;
  bottom: 8px;
  left: 8px;
  background: rgba(0,0,0,0.7);
  backdrop-filter: blur(8px);
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 9px;
  font-weight: 600;
  color: white;
}

.invite-content {
  flex: 1;
  padding: 14px 14px 14px 0;
}

.invite-content h4 {
  font-size: 15px;
  font-weight: 700;
  margin: 0 0 8px 0;
  color: #1a1a2e;
}

.invite-details {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 12px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: #666;
}

.detail-item svg {
  flex-shrink: 0;
  color: #ff4d6d;
}

.invite-actions {
  display: flex;
  gap: 10px;
}

.accept-btn, .decline-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.accept-btn {
  background: linear-gradient(135deg, #28a745, #34ce57);
  color: white;
}

.decline-btn {
  background: linear-gradient(135deg, #dc3545, #ff6b6b);
  color: white;
}

.accept-btn:hover, .decline-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* ================= MODAL ================= */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: flex-end;
  z-index: 1000;
}

.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.25s;
}
.modal-fade-enter, .modal-fade-leave-to {
  opacity: 0;
}

.schedule-modal {
  width: 100%;
  max-width: 480px;
  background: white;
  border-radius: 32px 32px 0 0;
  padding: 24px;
  animation: slideUp 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

@keyframes slideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.modal-icon {
  font-size: 32px;
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
  color: #1a1a2e;
}

.modal-close {
  background: #f0f0f0;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.modal-close:hover {
  background: #e0e0e0;
  transform: rotate(90deg);
}

.selected-place {
  text-align: center;
  padding: 12px;
  background: #fff0f3;
  border-radius: 20px;
  font-size: 13px;
  color: #666;
  margin-bottom: 20px;
}

.selected-place strong {
  color: #ff4d6d;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #555;
  margin-bottom: 6px;
}

.form-group input, .form-group textarea {
  width: 100%;
  padding: 12px;
  border: 1.5px solid #eee;
  border-radius: 16px;
  font-size: 13px;
  transition: all 0.2s;
  outline: none;
}

.form-group input:focus, .form-group textarea:focus {
  border-color: #ff4d6d;
  box-shadow: 0 0 0 3px rgba(255, 77, 109, 0.1);
}

.confirm-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  background: linear-gradient(135deg, #ff4d6d, #ff2e63);
  border: none;
  padding: 14px;
  border-radius: 40px;
  color: white;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 8px;
}

.confirm-btn:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 25px rgba(255, 46, 99, 0.4);
}

/* ================= RESPONSIVE ================= */
@media (max-width: 600px) {
  .date-planner-page {
    padding: 16px;
  }
  
  .invite-card {
    flex-direction: column;
  }
  
  .invite-image {
    width: 100%;
    height: 140px;
  }
  
  .invite-content {
    padding: 14px;
  }
  
  .planner-grid {
    grid-template-columns: 1fr;
  }
}
</style>