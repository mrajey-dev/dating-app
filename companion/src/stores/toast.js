import { defineStore } from "pinia"

export const useToastStore = defineStore("toast", {
  state: () => ({
    toasts: []
  }),

  actions: {
    show(message, type = "info", duration = 3000) {
      const id = Date.now()

      this.toasts.push({ id, message, type })

      setTimeout(() => {
        this.toasts = this.toasts.filter(t => t.id !== id)
      }, duration)
    }
  }
})
