<template>
  <div class="upgrade-page">
    <!-- HEADER -->
    <div class="upgrade-header">
      <h1>Upgrade to <span>HeartLink</span> ❤️</h1>
      <p>Unlock more connections and premium features</p>
    </div>

    <!-- PLANS -->
    <div class="plans">
      <div
        v-for="plan in plans"
        :key="plan.name"
        :class="['plan-card', plan.type]"
      >
        <div v-if="plan.popular" class="badge">Most Popular</div>
        <h2>{{ plan.displayName }}</h2>
        <div class="price">₹{{ plan.price }}<span>/{{ plan.duration }}</span></div>
        <ul>
          <li v-for="(benefit, index) in plan.benefits" :key="index">{{ benefit }}</li>
        </ul>
        <button @click="selectPlan(plan.name)">{{ plan.buttonText }}</button>
      </div>
    </div>

    <!-- FOOTER NOTE -->
    <p class="secure-note">🔒 Secure payments • Cancel anytime</p>

    <!-- PLAN SUCCESS POPUP -->
    <div v-if="showPlanPopup" class="plan-popup-overlay">
      <div class="plan-popup">
        <h2>🎉 Plan Upgraded!</h2>
        <p>Your current plan: <b>{{ currentPlanDetails }}</b></p>
        <button @click="goToChat">Go Back to Home</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Upgrade",
  data() {
    return {
      showPlanPopup: false,      // popup visibility
      currentPlanDetails: null,   // store plan info
      plans: [
        {
          name: "Basic",
          displayName: "Monthly Basic",
          type: "",
          price: 299,
          duration: "month",
          buttonText: "Choose Plan",
          benefits: [
            "💬 Chat with 1 matched profile",
            "🚀 1 Profile Boost every month",
            "❤️ Unlimited Likes",
            "📅 Access to 5 daily matches",
            "🔔 Priority notifications"
          ]
        },
        {
          name: "Plus",
          displayName: "Yearly Plus",
          type: "popular",
          popular: true,
          price: 2999,
          duration: "year",
          buttonText: "Choose Plan",
          benefits: [
            "💬 Chat with 1 matched profile",
            "🚀 5 Profile Boosts every month",
            "❤️ Unlimited Likes",
            "📅 Access to 20 daily matches",
            "🔔 Priority notifications",
            "🎁 Special seasonal gifts"
          ]
        },
        {
          name: "Pro",
          displayName: "Yearly Pro",
          type: "premium",
          price: 4999,
          duration: "year",
          buttonText: "Go Premium 👑",
          benefits: [
            "💬 Chat with 5 matched profiles",
            "🚀 10 Profile Boosts every month",
            "❤️ Unlimited Likes",
            "📅 Access to unlimited daily matches",
            "🔔 Priority notifications",
            "🎁 Special seasonal gifts",
            "🏆 Verified badge for profile"
          ]
        }
      ]
    };
  },
  methods: {
    async selectPlan(plan) {
      try {
        const token = localStorage.getItem("token");          
        const loggedInUserId = localStorage.getItem("userId"); 
        const chatUserId = this.$route.query.targetUserId;     

        if (!token || !loggedInUserId || !chatUserId) {
          throw new Error("Missing authentication or user info");
        }

        const response = await axios.post(
          'https://companion.ajaywatpade.in/api/upgrade-plan',
          {
            user_id: loggedInUserId,
            target_user_id: chatUserId,
            plan: plan.toLowerCase()
          },
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        this.currentPlanDetails = response.data.plan || plan;
        this.showPlanPopup = true;

      } catch (error) {
        console.error(error.response?.data || error.message);
        alert('Failed to select plan: ' + (error.response?.data?.message || error.message));
      }
    },

    goToChat() {
      this.showPlanPopup = false;
      this.$router.push({ path: '/home' });
    }
  }
};
</script>

<style scoped>
.upgrade-page { min-height:100vh; background: linear-gradient(135deg,#ff416c,#eba194); color:#fff; padding:40px 20px; text-align:center; }
.upgrade-header h1 { margin-bottom:10px; }
.upgrade-header span { color:#ffeaa7; font-weight:800; }
.upgrade-header p { opacity:0.9; margin-bottom:40px; }
.plans { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:25px; max-width:1000px; margin:auto; }
.plan-card { background:#fff; color:#333; border-radius:16px; padding:25px; position:relative; box-shadow:0 10px 30px rgba(0,0,0,0.15); transition:transform 0.3s; }
.plan-card:hover { transform: translateY(-6px); }
.plan-card h2 { margin-bottom:10px; }
.price { font-size:32px; font-weight:bold; margin:15px 0; color:#ff416c; }
.price span { font-size:14px; }
.plan-card ul { list-style:none; padding:0; margin:20px 0; text-align:left; }
.plan-card ul li { margin-bottom:10px; }
.plan-card button { width:100%; padding:12px; border:none; border-radius:25px; background: linear-gradient(135deg,#ff416c,#ff4b2b); color:#fff; font-size:16px; cursor:pointer; }
.plan-card button:hover { opacity:0.9; }
.popular { border:3px solid #ff416c; }
.badge { position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:#416eff; color:#fff; padding:4px 12px; border-radius:12px; font-size:15px; }
.premium { background: linear-gradient(135deg,#2c3e50,#4b79a1); color:#fff; }
.premium .price { color:#ffeaa7; }
.premium button { background: linear-gradient(135deg,#fbc531,#e1b12c); color:#333; }
.secure-note { margin-top:30px; opacity:0.9; font-size:14px; }
/* Popup styles */
.plan-popup-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.plan-popup {
  background: #fff;
  padding: 30px;
  border-radius: 20px;
  text-align: center;
  width: 90%;
  max-width: 320px;
}

.plan-popup h2 {
  color: #fd5068;
  margin-bottom: 15px;
  font-weight: bold;
}

.plan-popup button {
  margin-top: 20px;
  padding: 12px 25px;
  border: none;
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: white;
  font-weight: bold;
  border-radius: 25px;
  cursor: pointer;
}
</style>