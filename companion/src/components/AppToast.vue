<template>
  <div class="toast-container">
    <transition-group name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="['toast', toast.type]"
      >
        {{ toast.message }}
      </div>
    </transition-group>
  </div>
</template>

<script>
import { useToastStore } from "@/stores/toast"
import { storeToRefs } from "pinia"

export default {
  setup() {
    const toastStore = useToastStore()
    const { toasts } = storeToRefs(toastStore) // ✅ make it reactive

    return {
      toasts
    }
  }
}
</script>


<style scoped>
.toast-container {
  position: fixed;
  top: 80px;
  right: 20px;
  z-index: 99999;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.toast {
  min-width: 250px;
  padding: 12px 16px;
  border-radius: 12px;
  color: white;
  font-size: 13px;
  font-weight: 500;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.toast.success {
  background: linear-gradient(135deg, #00c853, #43a047);
}

.toast.error {
  background: linear-gradient(135deg, #ff1744, #d50000);
}

.toast.info {
  background: linear-gradient(135deg, #2979ff, #1565c0);
}

/* Animation */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
