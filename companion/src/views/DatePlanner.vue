<template>
    <!-- <AppToast /> -->
  <div class="date-planner-page" v-if="person">

    <!-- HEADER -->
   <!-- HEADER -->
<div class="planner-header">
  <div class="planner-user">
    <img :src="person.profile_photo" class="planner-avatar" />
    <div>
      <h2>Plan a Perfect Date</h2>
      <p>with {{ person.first_name }} 💖</p>
    </div>
  </div>
</div>


    <!-- CATEGORY FILTER -->
    <div class="planner-filters">
      <button 
        v-for="type in categories"
        :key="type"
        :class="{ active: selectedCategory === type }"
        @click="selectedCategory = type"
      >
        {{ type }}
      </button>
    </div>

    <!-- DATE SUGGESTIONS -->
    <div class="planner-grid">
      <div 
        v-for="(place, index) in filteredSuggestions"
        :key="index"
        class="planner-card"
      >
        <img :src="place.image" />
        <div class="planner-info">
          <h4>{{ place.title }}</h4>
          <p>{{ place.description }}</p>
     <button class="book-btn" @click="openScheduleModal(place)">
  Schedule Date
</button>


        </div>
      </div>
    </div>

  
 <!-- SCHEDULE DATE MODAL -->
<div v-if="showScheduleModal" class="modal-overlay" @click.self="closeScheduleModal">
  <div class="schedule-modal">

    <h3>Schedule Date 💖</h3>

    <p class="selected-place">{{ selectedPlace?.title }}</p>

    <!-- Date -->
    <label>Select Date</label>
    <input type="date" v-model="schedule.date" />

    <!-- Time -->
    <label>Select Time</label>
    <input type="time" v-model="schedule.time" />

    <!-- Optional Note -->
    <label>Special Message (Optional)</label>
    <textarea
      v-model="schedule.note"
      placeholder="Add something sweet..."
      rows="3"
    ></textarea>

    <button class="confirm-btn" @click="confirmSchedule">
      Confirm Date 💘
    </button>

  </div>
</div>
 </div>
</template>

<script>
import axios from "axios"
import { useToastStore } from "@/stores/toast"

export default {
  name: "DatePlanner",
  data() {
    return {
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
    description: "Candlelight dinner with skyline views.",
    type: "Cafe",
    image: "https://images.unsplash.com/photo-1498654896293-37aacf113fd9",
    location: "Skyline Rooftop Cafe, Mumbai"
  },
  {
    title: "Luxury Boutique Hotel",
    description: "Private suite with spa and pool access.",
    type: "Hotel",
    image: "https://images.unsplash.com/photo-1566073771259-6a8506099945",
    location: "The Taj Mahal Palace, Mumbai"
  },
  {
    title: "Sunset Yacht Experience",
    description: "Private sunset cruise with dinner.",
    type: "Experience",
    image: "https://images.unsplash.com/photo-1507525428034-b723cf961d3e",
    location: "Gateway of India, Mumbai"
  }
]

    }
  },

  computed: {
    filteredSuggestions() {
      if (this.selectedCategory === "All") return this.suggestions
      return this.suggestions.filter(
        item => item.type === this.selectedCategory
      )
    }
  },

  async mounted() {
    await this.fetchPerson()
    await this.checkMatchStatus()

    if (this.matchStatus !== "matched") {
      this.$router.push("/")
    }
  },

  methods: {
    openScheduleModal(place) {
  this.selectedPlace = place
  this.showScheduleModal = true
},

closeScheduleModal() {
  this.showScheduleModal = false
  this.schedule = { date: "", time: "", note: "" }
},

async confirmSchedule() {
  const toast = useToastStore()

  if (!this.schedule.date || !this.schedule.time) {
    toast.show("Please select date & time 💖", "error")
    return
  }

  toast.show(
    `Date scheduled with ${this.person.first_name} 💕`,
    "success"
  )

  this.closeScheduleModal()
},


    openMap(place) {
  const query = encodeURIComponent(place.location)
  const url = `https://www.google.com/maps/search/?api=1&query=${query}`
  window.open(url, "_blank")
},

    async fetchPerson() {
      const res = await axios.get(
        `https://companion.ajaywatpade.in/api/users/${this.$route.params.id}`,
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`
          }
        }
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
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("token")}`
          }
        }
      )

      this.matchStatus = res.data.status
    }
  }
}
</script>

<style scoped>

.date-planner-page {
  padding: 20px;
  background: #fafafa;
}

.planner-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 25px;
}

.planner-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.planner-filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.planner-filters button {
  padding: 8px 16px;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  background: #eee;
}

.planner-filters .active {
  background: #ff4e88;
  color: white;
}

.planner-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.planner-card {
  background: white;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  transition: 0.3s;
}

.planner-card:hover {
  transform: translateY(-5px);
}

.planner-card img {
  width: 100%;
  height: 170px;
  object-fit: cover;
}

.planner-info {
  padding: 15px;
}

.book-btn {
  margin-top: 10px;
  background: linear-gradient(135deg, #ff4e88, #ff8c68);
  border: none;
  color: white;
  padding: 8px 14px;
  border-radius: 20px;
  cursor: pointer;
}
.date-plan-title{
    font-size: 15px!important;
}
p{
    font-size: 10px;
}
.date-planner-page {
  padding: 20px;
  background: #f4f6f9;
  min-height: 100vh;
}

/* ================= HEADER ================= */

.planner-header {
  background: linear-gradient(135deg, #ff4e88, #ff8c68);
  padding: 20px;
  border-radius: 18px;
  color: white;
  margin-bottom: 25px;
  box-shadow: 0 10px 30px rgba(255, 78, 136, 0.25);
}

.planner-user {
  display: flex;
  align-items: center;
  gap: 15px;
}

.planner-avatar {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid white;
}

.planner-header h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.planner-header p {
  margin: 0;
  font-size: 13px;
  opacity: 0.9;
}

/* ================= FILTERS ================= */

.planner-filters {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  overflow-x: auto;
  padding-bottom: 5px;
}

.planner-filters::-webkit-scrollbar {
  display: none;
}

.planner-filters button {
  padding: 8px 18px;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  background: white;
  font-size: 13px;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  transition: 0.2s ease;
  white-space: nowrap;
}

.planner-filters button:hover {
  transform: translateY(-2px);
}

.planner-filters .active {
  background: linear-gradient(135deg, #ff4e88, #ff8c68);
  color: white;
  box-shadow: 0 6px 18px rgba(255, 78, 136, 0.35);
}

/* ================= GRID ================= */

.planner-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
}

/* ================= CARD ================= */

.planner-card {
  background: white;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  transition: all 0.25s ease;
  display: flex;
  flex-direction: column;
}

.planner-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 15px 35px rgba(0,0,0,0.12);
}

.planner-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.planner-info {
  padding: 16px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.planner-info h4 {
  margin: 0 0 6px 0;
  font-size: 15px;
  font-weight: 600;
  color: #222;
}

.planner-info p {
  font-size: 12px;
  color: #666;
  margin-bottom: 15px;
  flex-grow: 1;
}

/* ================= BUTTON ================= */

.book-btn {
  background: linear-gradient(135deg, #ff4e88, #ff8c68);
  border: none;
  color: white;
  padding: 9px 14px;
  border-radius: 25px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: 0.2s ease;
}

.book-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 18px rgba(255, 78, 136, 0.3);
}

/* ================= MOBILE POLISH ================= */

@media (max-width: 480px) {
  .planner-grid {
    grid-template-columns: 1fr;
  }

  .planner-header h2 {
    font-size: 16px;
  }

  .planner-avatar {
    width: 48px;
    height: 48px;
  }
}
/* ================= MODAL OVERLAY ================= */

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: flex-end;
  z-index: 999;
  animation: fadeIn 0.2s ease;
}

/* ================= MODAL ================= */

.schedule-modal {
  width: 100%;
  max-width: 500px;
  background: white;
  border-top-left-radius: 25px;
  border-top-right-radius: 25px;
  padding: 25px;
  animation: slideUp 0.3s ease;
  box-shadow: 0 -10px 30px rgba(0,0,0,0.15);
}

.schedule-modal h3 {
  margin-top: 0;
  font-size: 18px;
  font-weight: 600;
}

.selected-place {
  font-size: 13px;
  color: #888;
  margin-bottom: 20px;
}

/* ================= INPUTS ================= */

.schedule-modal label {
  font-size: 12px;
  font-weight: 500;
  margin-top: 12px;
  display: block;
}

.schedule-modal input,
.schedule-modal textarea {
  width: 100%;
  padding: 10px;
  margin-top: 6px;
  border-radius: 12px;
  border: 1px solid #ddd;
  font-size: 13px;
  outline: none;
  transition: 0.2s;
}

.schedule-modal input:focus,
.schedule-modal textarea:focus {
  border-color: #ff4e88;
  box-shadow: 0 0 0 2px rgba(255, 78, 136, 0.2);
}

/* ================= BUTTON ================= */

.confirm-btn {
  width: 100%;
  margin-top: 20px;
  padding: 12px;
  border-radius: 30px;
  border: none;
  background: linear-gradient(135deg, #ff4e88, #ff8c68);
  color: white;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: 0.2s;
}

.confirm-btn:hover {
  transform: scale(1.03);
  box-shadow: 0 8px 25px rgba(255, 78, 136, 0.3);
}

/* ================= ANIMATIONS ================= */

@keyframes slideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

</style>
