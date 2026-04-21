@ -1,610 +0,0 @@
<template>
  <div class="upgrade-container">
    <!-- Background -->
    <div class="bg-gradient"></div>
    
    <!-- Header -->
    <div class="header-section">
      <button class="back-link" @click="$router.push('/home')">
        ← Back
      </button>
      <div class="header-content">
        <span class="badge">PREMIUM ACCESS</span>
        <h1>Upgrade to <span class="highlight">HeartLink</span></h1>
        <p>Get unlimited matches, priority boosts & exclusive features</p>
      </div>
    </div>

    <!-- Pricing Cards -->
    <div class="pricing-section">
      <div class="pricing-grid">
        <!-- Basic -->
        <div class="pricing-card" :class="{ 'card-active': selectedPlan === 'basic' }" @click="selectedPlan = 'basic'">
          <div class="card-top">
            <div class="plan-name">Basic</div>
            <div class="plan-price">
              <span class="currency">₹</span>
              <span class="amount">299</span>
              <span class="period">/ month</span>
            </div>
            <div class="plan-desc">Perfect for starters</div>
          </div>
          <div class="card-features">
            <div class="feature"><span>✓</span> Chat with 1 matched profile</div>
            <div class="feature"><span>✓</span> 1 Profile Boost / month</div>
            <div class="feature"><span>✓</span> Unlimited Likes</div>
            <div class="feature"><span>✓</span> 5 daily matches</div>
            <div class="feature"><span>✓</span> Priority notifications</div>
          </div>
          <button class="plan-button basic-button" @click.stop="selectPlan('Basic')">
            Get Basic
          </button>
        </div>

        <!-- Plus - Popular -->
        <div class="pricing-card popular" :class="{ 'card-active': selectedPlan === 'plus' }" @click="selectedPlan = 'plus'">
          <div class="popular-tag">🔥 Most Popular</div>
          <div class="card-top">
            <div class="plan-name">Plus</div>
            <div class="plan-price">
              <span class="currency">₹</span>
              <span class="amount">2,999</span>
              <span class="period">/ year</span>
            </div>
            <div class="plan-desc">Best value for serious daters</div>
            <div class="savings">Save ₹600 vs monthly</div>
          </div>
          <div class="card-features">
            <div class="feature"><span>✓</span> Chat with 3 matched profiles</div>
            <div class="feature"><span>✓</span> 5 Profile Boosts / month</div>
            <div class="feature"><span>✓</span> Unlimited Likes</div>
            <div class="feature"><span>✓</span> 20 daily matches</div>
            <div class="feature"><span>✓</span> Priority notifications</div>
            <div class="feature"><span>✓</span> Seasonal gifts 🎁</div>
          </div>
          <button class="plan-button plus-button" @click.stop="selectPlan('Plus')">
            Choose Plus
          </button>
        </div>

        <!-- Pro -->
        <div class="pricing-card" :class="{ 'card-active': selectedPlan === 'pro' }" @click="selectedPlan = 'pro'">
          <div class="card-top">
            <div class="plan-name">Pro</div>
            <div class="plan-price">
              <span class="currency">₹</span>
              <span class="amount">4,999</span>
              <span class="period">/ year</span>
            </div>
            <div class="plan-desc">Ultimate experience</div>
            <div class="savings">Best value + VIP perks</div>
          </div>
          <div class="card-features">
            <div class="feature"><span>✓</span> Chat with 5 matched profiles</div>
            <div class="feature"><span>✓</span> 10 Profile Boosts / month</div>
            <div class="feature"><span>✓</span> Unlimited Likes</div>
            <div class="feature"><span>✓</span> Unlimited daily matches</div>
            <div class="feature"><span>✓</span> Priority notifications</div>
            <div class="feature"><span>✓</span> Seasonal gifts 🎁</div>
            <div class="feature"><span>✓</span> Verified Badge 🏆</div>
          </div>
          <button class="plan-button pro-button" @click.stop="selectPlan('Pro')">
            Go Pro
          </button>
        </div>
      </div>
    </div>

    <!-- Trust Indicators -->
    <div class="trust-bar">
      <div class="trust-item">🔒 256-bit SSL Secure</div>
      <div class="trust-item">💳 All Cards Accepted</div>
      <div class="trust-item">🔄 Cancel Anytime</div>
      <div class="trust-item">⭐ 4.9/5 Rating</div>
    </div>

    <!-- Success Modal -->
    <transition name="modal">
      <div v-if="showPlanPopup" class="modal-overlay" @click.self="closePopup">
        <div class="modal-content">
          <div class="modal-icon">✨</div>
          <h2>Plan Activated!</h2>
          <p>Your <strong>{{ currentPlanDetails }}</strong> plan is now active.</p>
          <p class="modal-note">You now have access to all premium features.</p>
          <button class="modal-button" @click="goToHome">Continue to Dashboard →</button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Upgrade",
  data() {
    return {
      showPlanPopup: false,
      currentPlanDetails: null,
      selectedPlan: 'plus'
    };
  },
  methods: {
    async selectPlan(plan) {
      try {
        const token = localStorage.getItem("token");
        const chatUserId = this.$route.query.targetUserId;

        // ✅ Validate targetUserId exists
        if (!chatUserId) {
          this.showErrorToast("Missing user information. Please try again from the chat.");
          return;
        }

        if (!token) {
          this.showErrorToast("Please login again to continue.");
          return;
        }

        // ✅ Send ONLY target_user_id (backend gets current user from token)
        const response = await axios.post(
          'https://companion.ajaywatpade.in/api/upgrade-plan',
          {
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
        console.error('Upgrade error:', error.response?.data || error.message);
        
        // ✅ Better error handling
        const errorMessage = error.response?.data?.message || 
                            error.response?.data?.error || 
                            "Failed to select plan. Please try again.";
        
        alert(errorMessage);
      }
    },
    
    goToHome() {
      this.showPlanPopup = false;
      this.$router.push({ path: '/home' });
    },
    
    closePopup() {
      this.showPlanPopup = false;
    },
    
    showErrorToast(message) {
      // You can replace this with your toast component
      alert(message);
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

.upgrade-container {
  min-height: 100vh;
  background: #f8fafc;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', sans-serif;
  position: relative;
}

/* Background */
.bg-gradient {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(145deg, #fef2f2 0%, #fef8e8 100%);
  z-index: -1;
}

/* Header */
.header-section {
  text-align: center;
  padding: 48px 24px 40px;
}

.back-link {
  position: absolute;
  top: 24px;
  left: 24px;
  background: white;
  border: none;
  padding: 8px 20px;
  border-radius: 40px;
  font-size: 14px;
  color: #e11d48;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
}

.back-link:hover {
  transform: translateX(-2px);
  box-shadow: 0 4px 12px rgba(225, 29, 72, 0.15);
}

.badge {
  display: inline-block;
  background: #ffe4e6;
  color: #e11d48;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 30px;
  letter-spacing: 1px;
  margin-bottom: 20px;
}

.header-content h1 {
  font-size: 48px;
  font-weight: 800;
  color: #1e1e2a;
  margin-bottom: 12px;
}

.header-content .highlight {
  background: linear-gradient(135deg, #e11d48, #f97316);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.header-content p {
  font-size: 16px;
  color: #64748b;
}

/* Pricing Section */
.pricing-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px 24px 48px;
}

.pricing-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 28px;
}

/* Card */
.pricing-card {
  flex: 1;
  min-width: 280px;
  max-width: 340px;
  background: white;
  border-radius: 28px;
  padding: 28px 24px;
  position: relative;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
  border: 1px solid #f1f5f9;
}

.pricing-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.15);
}

.card-active {
  border: 2px solid #e11d48;
  box-shadow: 0 20px 35px -12px rgba(225, 29, 72, 0.2);
}

/* Popular Card */
.popular {
  border: 2px solid #e11d48;
  background: linear-gradient(145deg, #ffffff, #fffafb);
  transform: scale(1.02);
}

.popular-tag {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(135deg, #e11d48, #f97316);
  color: white;
  font-size: 12px;
  font-weight: 700;
  padding: 5px 16px;
  border-radius: 30px;
  white-space: nowrap;
  box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);
}

/* Card Top */
.card-top {
  text-align: center;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f0f2f5;
}

.plan-name {
  font-size: 22px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 16px;
}

.plan-price {
  margin-bottom: 8px;
}

.currency {
  font-size: 20px;
  font-weight: 600;
  color: #475569;
  vertical-align: top;
}

.amount {
  font-size: 48px;
  font-weight: 800;
  color: #e11d48;
  letter-spacing: -1px;
}

.period {
  font-size: 14px;
  color: #94a3b8;
  font-weight: 400;
}

.plan-desc {
  font-size: 13px;
  color: #64748b;
  margin-top: 8px;
}

.savings {
  font-size: 11px;
  font-weight: 600;
  color: #10b981;
  background: #d1fae5;
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  margin-top: 10px;
}

/* Features */
.card-features {
  margin: 24px 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.feature {
  font-size: 14px;
  color: #334155;
  display: flex;
  align-items: center;
  gap: 10px;
}

.feature span {
  width: 22px;
  height: 22px;
  background: #e11d4810;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
  color: #e11d48;
}

/* Buttons */
.plan-button {
  width: 100%;
  padding: 14px;
  border-radius: 50px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.25s ease;
  border: none;
}

.basic-button {
  background: #f1f5f9;
  color: #1e293b;
}

.basic-button:hover {
  background: #e2e8f0;
  transform: scale(1.02);
}

.plus-button {
  background: linear-gradient(135deg, #e11d48, #f97316);
  color: white;
  box-shadow: 0 6px 14px rgba(225, 29, 72, 0.3);
}

.plus-button:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 20px rgba(225, 29, 72, 0.4);
}

.pro-button {
  background: #1e293b;
  color: white;
}

.pro-button:hover {
  background: #0f172a;
  transform: scale(1.02);
}

/* Trust Bar */
.trust-bar {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 32px;
  padding: 32px 24px 60px;
  border-top: 1px solid #e2e8f0;
  max-width: 900px;
  margin: 0 auto;
}

.trust-item {
  font-size: 14px;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 32px;
  padding: 40px 32px;
  text-align: center;
  max-width: 380px;
  width: 90%;
  animation: modalIn 0.3s ease;
}

@keyframes modalIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-icon {
  font-size: 56px;
  margin-bottom: 16px;
}

.modal-content h2 {
  font-size: 28px;
  font-weight: 800;
  color: #e11d48;
  margin-bottom: 12px;
}

.modal-content p {
  color: #475569;
  margin-bottom: 8px;
}

.modal-content strong {
  color: #1e293b;
}

.modal-note {
  font-size: 13px;
  color: #94a3b8;
  margin-top: 8px;
}

.modal-button {
  background: linear-gradient(135deg, #e11d48, #f97316);
  border: none;
  padding: 14px 28px;
  border-radius: 50px;
  color: white;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
  margin-top: 24px;
  transition: all 0.2s;
}

.modal-button:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 20px rgba(225, 29, 72, 0.3);
}

.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter, .modal-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .header-content h1 {
    font-size: 34px;
  }
  
  .pricing-grid {
    gap: 20px;
  }
  
  .pricing-card {
    min-width: 260px;
  }
  
  .popular {
    transform: scale(1);
  }
  
  .trust-bar {
    gap: 20px;
  }
  
  .back-link {
    top: 16px;
    left: 16px;
    padding: 6px 16px;
    font-size: 12px;
  }
}

@media (max-width: 480px) {
  .header-section {
    padding: 40px 20px 24px;
  }
  
  .header-content h1 {
    font-size: 28px;
  }
  
  .pricing-section {
    padding: 16px;
  }
  
  .pricing-card {
    padding: 20px 18px;
  }
  
  .amount {
    font-size: 38px;
  }
  
  .trust-bar {
    flex-direction: column;
    align-items: center;
    gap: 12px;
  }
}
</style>