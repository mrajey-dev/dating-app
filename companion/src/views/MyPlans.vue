@ -1,636 +0,0 @@
<template>
  <div class="subscription-dashboard">
    <!-- Background Pattern -->
    <div class="bg-pattern"></div>
    
    <!-- Header -->
    <div class="dashboard-header">
      <button class="back-btn" @click="$router.push('/home')">
        ← Back
      </button>
      <div class="header-badge">
        <span class="badge-icon">⭐</span>
        Premium Member
      </div>
    </div>

    <!-- Hero Section -->
    <div class="hero-area">
      <div class="hero-content">
        <div class="crown-icon">👑</div>
        <h1 class="hero-title">My <span class="highlight">Subscriptions</span></h1>
        <p class="hero-desc">Manage your active plans and unlock exclusive privileges</p>
      </div>
    </div>

    <!-- Plans Grid -->
    <div class="plans-wrapper" v-if="plans.length">
      <div class="plans-grid">
        <div v-for="(plan, idx) in plans" :key="idx" class="subscription-card" :class="plan.planName.toLowerCase()">
          <!-- Card Header -->
          <div class="card-header">
            <div class="plan-type">
              <span class="type-icon">{{ getPlanEmoji(plan.planName) }}</span>
              <h3>{{ plan.planName }}</h3>
            </div>
            <div class="status-badge active">
              <span class="status-dot"></span>
              Active
            </div>
          </div>

          <!-- Partner Section -->
          <div class="partner-section">
            <div class="partner-avatar">
              <span>👥</span>
            </div>
            <div class="partner-info">
              <label>Partner</label>
              <p>{{ plan.partnerName || 'Individual Plan' }}</p>
            </div>
          </div>

          <!-- Expiry -->
          <div class="expiry-card">
            <span class="expiry-icon">📅</span>
            <div class="expiry-text">
              <label>Expires on</label>
              <strong>{{ formatDate(plan.expiry) }}</strong>
            </div>
          </div>

          <!-- Benefits -->
          <div class="benefits-area">
            <div class="benefits-title">
              <span>✨</span> What's Included
            </div>
            <ul class="benefits-list">
              <li v-for="(b, i) in plan.benefits" :key="i">
                <span class="check">✓</span> {{ b }}
              </li>
            </ul>
          </div>

          <!-- Action -->
          <button class="renew-button" @click="$router.push(`/upgrade`)">
            <span>⟳</span> Renew Subscription
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div class="empty-dashboard" v-else>
      <div class="empty-illustration">
        <span>📦</span>
      </div>
      <h3>No Active Plans</h3>
      <p>You haven't subscribed to any plan yet.</p>
      <button class="explore-plans-btn" @click="$router.push('/upgrade')">
        Explore Plans →
      </button>
    </div>

    <!-- Support Footer -->
    <div class="support-footer" v-if="plans.length">
      <p>💬 Need assistance? Contact our support team</p>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { update } from "lodash";

export default {
  name: "MyPlans",
  data() {
    return {
      plans: []
    };
  },

  async mounted() {
    await this.fetchPlans();
  },

  methods: {
    async fetchPlans() {
      try {
        const token = localStorage.getItem("token");
        if (!token) return;

        const response = await axios.get(
          "https://companion.ajaywatpade.in/api/my-plan",
          {
            headers: { Authorization: `Bearer ${token}` }
          }
        );

        if (response.data?.plans?.length) {
          this.plans = response.data.plans.map(p => ({
            planName: p.plan,
            expiry: p.expiry_date,
            benefits: p.benefits,
            partnerName: p.partner_name
          }));
        }
      } catch (error) {
        console.error(error.response?.data || error.message);
      }
    },

    formatDate(dateStr) {
      if (!dateStr) return "N/A";
      const date = new Date(dateStr);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    getPlanEmoji(planName) {
      const emojis = {
        free: '🆓',
        basic: '🌟',
        plus: '💎',
        pro: '👑',
        premium: '⭐',
        over: '🛡️'
      };
      return emojis[planName.toLowerCase()] || '📋';
    },

    renewPlan(plan) {
      this.$router.push(`/pricing?plan=${plan.planName}`);
    }
  }
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.subscription-dashboard {
  min-height: 100vh;
  background: linear-gradient(165deg, #fef5f7 0%, #fff0f3 100%);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Poppins', sans-serif;
  position: relative;
}

/* Background Pattern */
.bg-pattern {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: radial-gradient(#ffd6e0 1.5px, transparent 1.5px);
  background-size: 35px 35px;
  opacity: 0.4;
  pointer-events: none;
  z-index: 0;
}

/* Header */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 32px;
  position: relative;
  z-index: 2;
}

.back-btn {
  background: white;
  border: none;
  padding: 10px 24px;
  border-radius: 40px;
  font-size: 14px;
  font-weight: 500;
  color: #ff6b8b;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.back-btn:hover {
  transform: translateX(-3px);
  box-shadow: 0 8px 20px rgba(255, 107, 139, 0.2);
}

.header-badge {
  background: linear-gradient(135deg, #ffd89b, #ff6b8b);
  padding: 8px 20px;
  border-radius: 40px;
  color: white;
  font-size: 13px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 15px rgba(255, 107, 139, 0.3);
}

.badge-icon {
  font-size: 14px;
}

/* Hero Section */
.hero-area {
  text-align: center;
  padding: 40px 20px 50px;
  position: relative;
  z-index: 2;
}

.crown-icon {
  font-size: 48px;
  margin-bottom: 16px;
  display: inline-block;
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

.hero-title {
  font-size: 44px;
  font-weight: 800;
  color: #2d2d2d;
  margin-bottom: 12px;
  letter-spacing: -0.5px;
}

.hero-title .highlight {
  background: linear-gradient(135deg, #ff6b8b, #ff8c5a);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.hero-desc {
  font-size: 16px;
  color: #7a7a8a;
  font-weight: 400;
}

/* Plans Grid */
.plans-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px 80px;
  position: relative;
  z-index: 2;
}

.plans-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
  gap: 28px;
}

/* Subscription Card */
.subscription-card {
  background: white;
  border-radius: 32px;
  padding: 28px;
  transition: all 0.3s ease;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(255, 107, 139, 0.1);
}

.subscription-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 25px 45px rgba(255, 107, 139, 0.15);
}

/* Premium Card Styling */
.subscription-card.pro {
  background: linear-gradient(135deg, #fff8f5, #ffffff);
  border-left: 4px solid #ff6b8b;
  box-shadow: 0 20px 40px rgba(255, 107, 139, 0.12);
}

.subscription-card.plus {
  border-left: 4px solid #ffb347;
}

.subscription-card.basic {
  border-left: 4px solid #6c5ce7;
}

/* Card Header */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 12px;
}

.plan-type {
  display: flex;
  align-items: center;
  gap: 10px;
}

.type-icon {
  font-size: 28px;
}

.plan-type h3 {
  font-size: 24px;
  font-weight: 700;
  color: #1a1a2e;
  letter-spacing: -0.3px;
}

.status-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #e8f5e9;
  padding: 6px 14px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
  color: #2e7d32;
}

.status-dot {
  width: 8px;
  height: 8px;
  background: #4caf50;
  border-radius: 50%;
  display: inline-block;
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.2); }
}

/* Partner Section */
.partner-section {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #fef5f7;
  padding: 16px;
  border-radius: 20px;
  margin-bottom: 20px;
}

.partner-avatar {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #ffd6e0, #ffe0e8);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
}

.partner-info label {
  font-size: 12px;
  color: #ff6b8b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
}

.partner-info p {
  font-size: 16px;
  font-weight: 600;
  color: #2d2d2d;
  margin-top: 4px;
}

/* Expiry Card */
.expiry-card {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #f0f2f8;
  padding: 14px 18px;
  border-radius: 18px;
  margin-bottom: 24px;
}

.expiry-icon {
  font-size: 24px;
}

.expiry-text label {
  font-size: 11px;
  color: #888;
  display: block;
  text-transform: uppercase;
}

.expiry-text strong {
  font-size: 14px;
  color: #2d2d2d;
  font-weight: 700;
}

/* Benefits Area */
.benefits-area {
  margin-bottom: 28px;
}

.benefits-title {
  font-size: 15px;
  font-weight: 700;
  color: #2d2d2d;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.benefits-list {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.benefits-list li {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  color: #555;
  padding: 6px 0;
  border-bottom: 1px dashed #ffe0e5;
}

.benefits-list li .check {
  width: 22px;
  height: 22px;
  background: #ff6b8b20;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: #ff6b8b;
  font-weight: bold;
}

/* Renew Button */
.renew-button {
  width: 100%;
  background: linear-gradient(135deg, #ff6b8b, #ff8c5a);
  border: none;
  padding: 14px;
  border-radius: 60px;
  color: white;
  font-weight: 700;
  font-size: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 8px;
}

.renew-button:hover {
  transform: scale(1.02);
  box-shadow: 0 10px 20px rgba(255, 107, 139, 0.3);
}

/* Empty State */
.empty-dashboard {
  text-align: center;
  padding: 80px 24px;
  position: relative;
  z-index: 2;
  max-width: 500px;
  margin: 0 auto;
}

.empty-illustration {
  font-size: 80px;
  background: #fff0f3;
  width: 140px;
  height: 140px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.empty-dashboard h3 {
  font-size: 28px;
  color: #2d2d2d;
  margin-bottom: 12px;
}

.empty-dashboard p {
  color: #7a7a8a;
  margin-bottom: 32px;
}

.explore-plans-btn {
  background: #ff6b8b;
  border: none;
  padding: 14px 32px;
  border-radius: 50px;
  color: white;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.2s;
}

.explore-plans-btn:hover {
  background: #ff5277;
  transform: translateY(-2px);
}

/* Support Footer */
.support-footer {
  text-align: center;
  padding: 24px;
  border-top: 1px solid rgba(255, 107, 139, 0.15);
  margin-top: 20px;
  position: relative;
  z-index: 2;
}

.support-footer p {
  font-size: 13px;
  color: #888;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-header {
    padding: 16px 20px;
  }
  
  .hero-title {
    font-size: 32px;
  }
  
  .plans-wrapper {
    padding: 0 16px 60px;
  }
  
  .plans-grid {
    grid-template-columns: 1fr;
  }
  
  .subscription-card {
    padding: 20px;
  }
  
  .plan-type h3 {
    font-size: 20px;
  }
}

@media (max-width: 480px) {
  .hero-area {
    padding: 20px 16px 30px;
  }
  
  .crown-icon {
    font-size: 36px;
  }
  
  .hero-title {
    font-size: 28px;
  }
  
  .back-btn {
    padding: 8px 16px;
    font-size: 12px;
  }
  
  .header-badge {
    padding: 6px 14px;
    font-size: 11px;
  }
}
</style>