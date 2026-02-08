<template>
  <div class="welcome-page">
    <img src="@/assets/welcome.png" alt="Welcome" class="welcome-image" />
    <h2>Let's get closer 😘</h2>
    <p>The best place to meet your future partner.</p>
    <button class="get-started" @click="$emit('start')">Get Started</button>
  </div>
</template>

<script>
export default {
  name: "WelcomePage",
};
</script>

<style scoped>
.welcome-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  text-align: center;
  background: #fff;
  padding: 20px;
}

.welcome-image {
  width: 80%;
  max-width: 300px;
  border-radius: 20px;
  margin-bottom: 30px;
}

h2 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 10px;
}

p {
  font-size: 16px;
  color: #555;
  margin-bottom: 30px;
}

.get-started {
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: white;
  border: none;
  padding: 14px 40px;
  border-radius: 25px;
  font-size: 16px;
  cursor: pointer;
  transition: 0.3s;
}

.get-started:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(253, 80, 104, 0.4);
}
</style>
