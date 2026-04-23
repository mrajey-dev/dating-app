<template>
  <div class="bottom-nav-bar">
    <button
      v-for="item in items"
      :key="item.id"
      class="nav-item"
      :class="{ active: item.active, disabled: item.disabled }"
      :disabled="item.disabled"
      @click="item.action"
    >
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        class="nav-icon"
        :class="{ 'glow': item.showGlow && item.active }"
        v-html="item.icon"
      />
      <span class="nav-label">{{ item.label }}</span>
    </button>
  </div>
</template>

<script>
export default {
  name: 'BottomNavBar',
  props: {
    items: {
      type: Array,
      required: true,
      validator: (items) =>
        items.every(
          (item) =>
            item.id &&
            item.label &&
            item.icon &&
            typeof item.action === 'function'
        )
    }
  }
}
</script>

<style scoped>
.bottom-nav-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 100px;
  background: rgba(255, 240, 245, 0.95);
  backdrop-filter: blur(20px);
  display: flex;
  justify-content: space-around;
  align-items: center;
  border-top: 1px solid rgba(255, 77, 109, 0.2);
  z-index: 100;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
  display: none;
}

.nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 12px 16px;
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
  transition: all 0.3s ease;
  position: relative;
  font-size: 12px;
  font-weight: 500;
  border-radius: 12px;
}

.nav-item:hover:not(.disabled) {
  color: #ff4d6d;
  transform: translateY(-2px);
}

.nav-item.active {
  color: #ff4d6d;
  background: rgba(255, 77, 109, 0.1);
}

.nav-item.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.nav-icon {
  transition: all 0.3s ease;
  filter: drop-shadow(0 0 0 transparent);
}

.nav-item.active .nav-icon.glow {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  filter: drop-shadow(0 0 8px rgba(255, 77, 109, 0.6));
}

.nav-label {
  font-size: 11px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 60px;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
    filter: drop-shadow(0 0 8px rgba(255, 77, 109, 0.6));
  }
  50% {
    opacity: 0.8;
    filter: drop-shadow(0 0 12px rgba(255, 77, 109, 0.8));
  }
}

@media (max-width: 480px) {
  .bottom-nav-bar {
    height: 90px;
    display: none;
  }

  .nav-item {
    padding: 10px 12px;
    gap: 4px;
  }

  .nav-label {
    font-size: 10px;
    max-width: 50px;
  }
}

@media (max-width: 360px) {
  .bottom-nav-bar {
    height: 80px;
    display: none;
  }

  .nav-item {
    padding: 8px 8px;
    gap: 2px;
  }

  .nav-label {
    font-size: 9px;
    max-width: 40px;
  }

  .nav-icon {
    width: 20px;
    height: 20px;
  }
}
</style>
