<template>
  <div class="my-plans-page">
    <!-- Header -->
    <div class="plans-header">
      <h1>My <span>Plans</span></h1>
      <p>View your active subscription and plan details</p>
    </div>

    <!-- Current Plan Card -->
    <div v-if="currentPlan" :class="['plan-card', currentPlan.planName.toLowerCase()]">
      <div class="plan-header">
        <h2>{{ currentPlan.planName.toUpperCase() }}</h2>
        <span class="expiry">Expires on: {{ formatDate(currentPlan.expiry) }}</span>
      </div>

      <ul class="plan-benefits">
        <li v-for="(benefit, index) in currentPlan.benefits" :key="index">
           {{ benefit }}
        </li>
      </ul>

      <!-- <button @click="">Upgrade / Change Plan</button> -->
    </div>

    <!-- No Plan -->
    <div v-else class="no-plan">
      <p>You currently have no active plan.</p>
      <button @click="goToUpgrade">Subscribe Now</button>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "MyPlans",
  data() {
    return {
      currentPlan: null
    };
  },
  async mounted() {
    try {
      const token = localStorage.getItem("token");
      if (!token) return;

      const response = await axios.get(
        "https://companion.ajaywatpade.in/api/my-plan",
        { headers: { Authorization: `Bearer ${token}` } }
      );

      if (response.data?.plan) {
        this.currentPlan = {
          planName: response.data.plan.plan,
          expiry: response.data.plan.expiry_date,
          benefits: response.data.plan.benefits
        };
      }
    } catch (error) {
      console.error(error.response?.data || error.message);
    }
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return "N/A";
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(dateStr).toLocaleDateString(undefined, options);
    },
    goToUpgrade() {
      this.$router.push("/upgrade");
    }
  }
};
</script>

<style scoped>
.my-plans-page {
  font-family: 'Helvetica Neue', sans-serif;
  text-align: center;
  padding: 40px 20px;
  background: #f0f2f5;
  min-height: 100vh;
}

/* Header */
.plans-header h1 {
  font-size: 26px;
  font-weight: bold;
  color: #fd5068;
  margin-bottom: 5px;
text-transform: uppercase;
}
.plans-header h1 span {
  color: #ff7854;
  font-weight: bold;
}
.plans-header p {
  color: #666;
  font-size: 16px;
  margin-bottom: 40px;
}

/* Current Plan Card */
.plan-card {
  max-width: 450px;
  margin: 0 auto 40px;
  padding: 30px 25px;
  border-radius: 25px;
  background: linear-gradient(145deg, #ffffff, #ffe6e9);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
  transition: transform 0.3s, box-shadow 0.3s;
}

.plan-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(253, 80, 104, 0.35);
}

.plan-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}
.plan-header h2 {
  font-size: 25px;
  color: #fd5068;
  font-weight: bold;
  letter-spacing: 1px;
}
.expiry {
  font-size: 14px;
  color: #999;
}

/* Plan Colors */
.plan-card.free { border-left: 6px solid #aaa; }
.plan-card.over { border-left: 6px solid #6c63ff; }
.plan-card.basic { border-left: 6px solid #ff7854; }
.plan-card.plus { border-left: 6px solid #fd5068; }
.plan-card.pro { border-left: 6px solid #ff3366; }

/* Benefits */
.plan-benefits {
  list-style: none;
  padding: 0;
  margin-bottom: 25px;
  text-align: left;
}
.plan-benefits li {
  font-size: 16px;
  margin-bottom: 10px;
  color: #333;
  position: relative;
  padding-left: 24px;
}
.plan-benefits li::before {
  /* content: "✔️"; */
  position: absolute;
  left: 0;
  color: #fd5068;
}

/* Buttons */
.plan-card button,
.no-plan button {
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: white;
  border: none;
  border-radius: 25px;
  padding: 12px 25px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}
.plan-card button:hover,
.no-plan button:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(253, 80, 104, 0.4);
}

/* No Plan */
.no-plan {
  margin-top: 50px;
  color: #666;
  font-size: 18px;
}
</style>