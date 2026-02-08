<template>
  <div>
    <!-- Loader -->
    <div v-if="loading" class="loader">
      <img src="https://img1.picmix.com/output/stamp/normal/6/9/9/5/2515996_a4e4e.gif" alt="Loading..." />
    </div>

    <!-- Actual content -->
    <router-view v-else />
  </div>
</template>

<script>
export default {
  data() {
    return {
      loading: true
    };
  },
  mounted() {
    // Show loader for 2 seconds
    setTimeout(() => {
      this.loading = false;
    }, 1000);
  }
};
</script>

<style scoped>
.loader {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh; /* Full screen */
  background-color: #fff; /* Optional background */
}
.loader img {
  width: 200px; /* Adjust size */
  height: 200px;
}
</style>
