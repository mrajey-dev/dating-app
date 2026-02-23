<template>
  <div class="matches-page">

    <h2 class="title">❤️ My Matches</h2>

    <!-- Tabs: Matches / Invitations / Planned Dates -->
    <div class="tabs">
      <button :class="{active: tab==='matches'}" @click="tab='matches'">Matches</button>
      <button :class="{active: tab==='incoming'}" @click="tab='incoming'">Incoming Invitations 💌</button>
      <button :class="{active: tab==='sent'}" @click="tab='sent'">Sent Invitations 📨</button>
      <button :class="{active: tab==='planned'}" @click="tab='planned'">Planned Dates 💖</button>
    </div>

    <!-- Matches List -->
    <div v-if="tab==='matches' && matches.length" class="match-list">
      <div v-for="match in matches" :key="match.id" class="match-card">
        <img :src="imageUrl(match.profile_photo)" class="avatar clickable" @click="goToProfile(match)" />
        <div class="info">
          <div class="name clickable" @click="goToProfile(match)">
            {{ match.first_name ? match.first_name + ' ' + match.last_name : 'User' }}
          </div>
          <div class="time">Matched {{ formatDate(match.created_at) }}</div>
          <div class="actions">
            <button class="chat-btn" @click="openChat(match)">Message</button>
            <button class="date-btn" @click="planDate(match)">Date Plan</button>
          </div>
        </div>
      </div>
      <div v-if="matches.length===0" class="empty">No Matches Yet</div>
    </div>

    <!-- Incoming Invitations -->
    <div v-if="tab==='incoming'">
      <div v-if="incomingInvitations.length===0" class="empty">No incoming invitations 💔</div>
      <div class="invitation-grid">
        <div class="invite-card" v-for="invite in incomingInvitations" :key="invite.id">
          <img :src="imageUrl(invite.place_image)" class="invite-img" />
          <div class="invite-info">
            <h4>{{ invite.place_title }} 💖</h4>
            <p>{{ invite.place_type }} • {{ invite.place_location }}</p>
            <p>From: <strong>{{ invite.planner_name }}</strong></p>
            <p>📅 {{ formatDate(invite.schedule_date) }} ⏰ {{ invite.schedule_time }}</p>
            <p v-if="invite.note">💌 Note: {{ invite.note }}</p>
            <div class="invite-actions">
              <button @click="acceptInvite(invite.id)">Accept ✅</button>
              <button @click="declineInvite(invite.id)">Decline ❌</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sent Invitations -->
    <div v-if="tab==='sent'">
      <div v-if="sentInvitations.length===0" class="empty">No sent invitations 📭</div>
      <div class="invitation-grid">
        <div class="invite-card" v-for="invite in sentInvitations" :key="invite.id">
          <img :src="imageUrl(invite.place_image)" class="invite-img" />
          <div class="invite-info">
            <h4>{{ invite.place_title }} 💌</h4>
            <p>{{ invite.place_type }} • {{ invite.place_location }}</p>
            <p>To: <strong>{{ invite.invitee_name }}</strong></p>
            <p>📅 {{ formatDate(invite.schedule_date) }} ⏰ {{ invite.schedule_time }}</p>
            <p v-if="invite.note">💌 Note: {{ invite.note }}</p>
            <p class="status">Status: <strong>{{ invite.status }}</strong></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Planned Dates Section -->
    <div v-if="tab==='planned'" class="planned-dates-section">
      <div v-if="plannedDates.length===0" class="empty">You have no planned dates yet 💔</div>
      <div class="planned-dates-grid">
        <div class="planned-date-card" v-for="date in plannedDates" :key="date.id">
          <div class="planned-card-image">
            <img :src="imageUrl(date.place_image)" />
          </div>
          <div class="planned-card-content">
            <h4>{{ date.place_title }} 💖</h4>
            <p>{{ date.place_type }} • {{ date.place_location }}</p>
            <p>With: <strong>{{ date.partner_name }}</strong></p>
            <p>📅 {{ formatDate(date.schedule_date) }} ⏰ {{ date.schedule_time }}</p>
            <p v-if="date.note">💌 Note: {{ date.note }}</p>
            <button class="view-map-btn" @click="openMap(date)">View on Map 🗺️</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from "axios"

export default {
  data() {
    return {
      matches: [],
      incomingInvitations: [],
      sentInvitations: [],
      plannedDates: [],
      tab: 'matches'
    }
  },

  async mounted() {
    await this.getMatches()
    await this.fetchIncomingInvites()
    await this.fetchSentInvites()
     await this.fetchPlannedDates()
  },

  methods: {
async fetchPlannedDates() {
  try {
    const token = localStorage.getItem("token")
    const userId = localStorage.getItem("user_id") // current logged user id

    const res = await axios.get(
      "https://companion.ajaywatpade.in/api/invitations/accepted",
      { headers: { Authorization: `Bearer ${token}` } }
    )

    this.plannedDates = res.data.map(inv => {

      let partner = ""

      if (inv.planner_id == userId) {
        // I sent the invitation
        partner = inv.invitee
          ? inv.invitee.first_name + " " + inv.invitee.last_name
          : ""
      } else {
        // Someone invited me
        partner = inv.planner
          ? inv.planner.first_name + " " + inv.planner.last_name
          : ""
      }

      return {
        ...inv,
        partner_name: partner
      }
    })

  } catch (err) {
    console.error(err)
    this.plannedDates = []
  }
},
    async getMatches() {
      try {
        const token = localStorage.getItem("token")
        const res = await axios.get("https://companion.ajaywatpade.in/api/my-matches", { headers: { Authorization: `Bearer ${token}` } })
        this.matches = res.data || []
      } catch (err) { console.error(err); this.matches = [] }
    },

    async fetchIncomingInvites() {
      try {
        const token = localStorage.getItem("token")
        const res = await axios.get("https://companion.ajaywatpade.in/api/invitations/incoming", { headers: { Authorization: `Bearer ${token}` } })
        this.incomingInvitations = res.data.map(inv => ({ ...inv, planner_name: inv.planner.first_name + ' ' + inv.planner.last_name }))
      } catch (err) { console.error(err); this.incomingInvitations = [] }
    },

    async fetchSentInvites() {
      try {
        const token = localStorage.getItem("token")
        const res = await axios.get("https://companion.ajaywatpade.in/api/invitations/sent", { headers: { Authorization: `Bearer ${token}` } })
        this.sentInvitations = res.data.map(inv => ({ ...inv, invitee_name: inv.invitee.first_name + ' ' + inv.invitee.last_name }))
      } catch (err) { console.error(err); this.sentInvitations = [] }
    },

async acceptInvite(inviteId) {
  try {
    const token = localStorage.getItem("token")

    await axios.post(
      `https://companion.ajaywatpade.in/api/invitations/${inviteId}/accept`,
      {},
      { headers: { Authorization: `Bearer ${token}` } }
    )

    this.fetchIncomingInvites()
    this.fetchPlannedDates() // 👈 refresh planned dates

  } catch (err) {
    console.error(err)
    alert("Failed to accept invitation.")
  }
},

async declineInvite(inviteId) {
  try {
    const token = localStorage.getItem("token");
    await axios.post(
      `https://companion.ajaywatpade.in/api/invitations/${inviteId}/decline`,
      {},
      { headers: { Authorization: `Bearer ${token}` } }
    );
    // Refresh incoming invitations after declining
    this.fetchIncomingInvites();
  } catch (err) {
    console.error(err);
    alert("Failed to decline invitation.");
  }
},

    goToProfile(user) { this.$router.push('/details/' + user.id) },
    openChat(user) { this.$router.push({ path:'/details/'+user.id, query:{chat:1} }) },
    planDate(user) { this.$router.push({ path:'/date-planner/'+user.id }) },

    openMap(item) {
      if(!item.place_location) return
      window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(item.place_location)}`, "_blank")
    },

    imageUrl(path) { 
      if(!path) return "/default-avatar.png"
      return path.startsWith("http") ? path : `https://companion.ajaywatpade.in/dating-backend/public/storage/${path}`
    },
    formatDate(date) { return date ? new Date(date).toLocaleDateString() : "" }
  }
}
</script>

<style scoped>
.matches-page{ padding:20px; background:#fafafa; min-height:100vh; font-family:Arial, Helvetica, sans-serif; }
.title{ text-align:center; margin-bottom:25px; font-size:19px; font-weight:700; color:#333; }

/* Tabs */
.tabs{ display:flex; gap:10px; margin-bottom:20px; justify-content:center; flex-wrap:wrap; }
.tabs button{ padding:6px 14px; border:none; border-radius:12px; cursor:pointer; background:#ddd; transition:.2s; font-size:14px; }
.tabs button.active{ background:#ff758f; color:#fff; box-shadow:0 4px 12px rgba(255,77,109,.4); }

/* Match Cards */
.match-list{ display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
.match-card{ background:#fff; border-radius:16px; padding:14px; display:flex; align-items:center; box-shadow:0 6px 20px rgba(0,0,0,0.06); border:1px solid #f1f1f1; transition:.25s; }
.match-card:hover{ transform:translateY(-3px); box-shadow:0 10px 25px rgba(0,0,0,0.08); }
.avatar{ width:60px; height:60px; border-radius:50%; object-fit:cover; margin-right:12px; border:3px solid #fff; box-shadow:0 3px 8px rgba(0,0,0,0.15); }
.info{ flex:1; }
.name{ font-size:16px; font-weight:600; color:#222; margin-bottom:6px; }
.time{ font-size:12px; color:#aaa; margin-bottom:6px; }
.actions{ display:flex; gap:8px; margin-top:6px; flex-wrap:wrap; }
.chat-btn{ background:linear-gradient(135deg,#ff4d6d,#ff758f); color:#fff; }
.date-btn{ background:linear-gradient(135deg,#2565fc,#1db0ff); color:#fff; }
.planned-dates-btn{ background:linear-gradient(135deg,#e79e00,#f5a623); color:#fff; }
.chat-btn,.date-btn,.planned-dates-btn{ padding:6px 12px; border-radius:10px; border:none; cursor:pointer; transition:.2s; font-size:12px; }
.chat-btn:hover,.date-btn:hover,.planned-dates-btn:hover{ transform:scale(1.05); box-shadow:0 4px 12px rgba(0,0,0,.3); }

/* Invitation Cards */
.invitation-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
.invite-card{ background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.06); border:1px solid #f1f1f1; display:flex; flex-direction:column; transition:.25s; }
.invite-card:hover{ transform:translateY(-3px); box-shadow:0 10px 25px rgba(0,0,0,0.08); }
.invite-img{ width:100%; height:160px; object-fit:cover; }
.invite-info{ padding:12px; display:flex; flex-direction:column; gap:4px; }
.invite-actions{ display:flex; gap:8px; margin-top:6px; }
.invite-actions button{ flex:1; padding:6px 12px; border:none; border-radius:10px; cursor:pointer; font-size:12px; transition:.2s; }
.invite-actions button:hover{ transform:scale(1.05); }
.invite-actions button:first-child{ background:linear-gradient(135deg,#28a745,#5cd65c); color:#fff; }
.invite-actions button:last-child{ background:linear-gradient(135deg,#dc3545,#ff4d4d); color:#fff; }

/* Planned Dates Cards */
.planned-dates-section{ margin-top:20px; }
.planned-dates-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
.planned-date-card{ background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.06); border:1px solid #f1f1f1; display:flex; flex-direction:column; transition:all .25s ease; }
.planned-card-image img{ width:100%; height:160px; object-fit:cover; }
.planned-card-content{ padding:12px; display:flex; flex-direction:column; gap:4px; }
.view-map-btn{ margin-top:6px; padding:6px 12px; border:none; border-radius:10px; cursor:pointer; background:linear-gradient(135deg,#ff4d6d,#ff758f); color:#fff; font-size:12px; }
.view-map-btn:hover{ transform:scale(1.05); box-shadow:0 4px 12px rgba(255,77,109,.4); }

.empty{ text-align:center; margin-top:40px; color:#888; font-size:18px; font-weight:500; }
.planned-date-card{
  background:#fff;
  border-radius:18px;
  overflow:hidden;
  box-shadow:0 10px 30px rgba(0,0,0,0.08);
  border:1px solid #f3f3f3;
  transition:all .3s ease;
  position:relative;
}

.planned-date-card:hover{
  transform:translateY(-6px);
  box-shadow:0 20px 40px rgba(0,0,0,0.15);
}

.planned-card-image{
  position:relative;
}

.planned-card-image img{
  width:100%;
  height:170px;
  object-fit:cover;
}

.planned-card-content{
  padding:14px;
}

.planned-card-content h4{
  font-size:18px;
  margin-bottom:4px;
}

.planned-card-content p{
  font-size:13px;
  color:#666;
}

.view-map-btn{
  margin-top:10px;
  padding:8px 14px;
  border-radius:12px;
  border:none;
  background:linear-gradient(135deg,#ff4d6d,#ff758f);
  color:#fff;
  cursor:pointer;
  transition:.2s;
}

.view-map-btn:hover{
  transform:scale(1.05);
}
</style>