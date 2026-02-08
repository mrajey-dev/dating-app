<template>
  <div class="my-booking">
    <!-- Header -->
    <div class="header">
      <span class="back" @click="$router.back()">‹</span>
      <h2>My Bookings</h2>
    </div>

    <!-- Booking List -->
    <div v-if="bookings.length">
      <div class="booking-card" v-for="(booking, index) in bookings" :key="index">
        <img :src="booking.image || defaultImg" class="avatar" />

        <div class="info">
          <h3>
            {{ booking.name }}
            <img v-if="booking.verified" src="@/assets/verified1.png" class="verified" />
          </h3>

          <p>{{ booking.role }}</p>

          <div class="meta">
            <span>⏱ {{ booking.hours }} Hours</span>
            <span>💰 ₹{{ booking.total }}</span>
          </div>

          <span class="status confirmed">Confirmed</span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="empty">
      No bookings yet 💤
    </div>
  </div>
</template>

<script>
import defaultImg from "@/assets/user-details.png";

export default {
  name: "MyBooking",
  data() {
    return {
      bookings: [],
      defaultImg
    }
  },
  mounted() {
    // Fetch all bookings from localStorage (stored as array)
    const data = localStorage.getItem("bookings") // changed from "latestBooking"
    if (data) {
      this.bookings = JSON.parse(data)
    }
  }
}
</script>


<style scoped>
.my-booking {
  min-height: 100vh;
  background: #f5f7ff;
  padding: 16px;
  font-family: Inter, sans-serif;
}

/* Header */
.header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.back {
  font-size: 26px;
  cursor: pointer;
}

.header h2 {
  font-size: 18px;
}

/* Card */
.booking-card {
  background: white;
  border-radius: 20px;
  margin-bottom: 7px;
  padding: 14px;
  display: flex;
  gap: 14px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.avatar {
  width: 90px;
  height: 120px;
  border-radius: 16px;
  object-fit: cover;
}

.info h3 {
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.verified {
  width: 16px;
  height: 16px;
}

.info p {
  font-size: 13px;
  color: #666;
}

.meta {
  display: flex;
  gap: 12px;
  margin: 8px 0;
  font-size: 13px;
}

/* Status */
.status {
  display: inline-block;
  padding: 4px 10px;
  font-size: 12px;
  border-radius: 10px;
}

.confirmed {
  background: #e8f5e9;
  color: #2e7d32;
}
.empty {
  text-align: center;
  margin-top: 80px;
  font-size: 14px;
  color: #777;
}

</style>
