import { defineStore } from 'pinia'
import axios from 'axios'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    count: 0
  }),

  actions: {
    async fetchCount() {
      try {
        const res = await axios.get(
          'https://companion.ajaywatpade.in/api/notifications-count',
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token")}`
            }
          }
        )

        this.count = res.data.count || 0
      } catch (error) {
        console.error("Failed to fetch notifications", error)
      }
    },

    increment() {
      this.count++
    },

    decrement() {
      if (this.count > 0) this.count--
    },

    async reset() {
      try {
        // Call backend to mark notifications as read
        await axios.post(
          'https://companion.ajaywatpade.in/api/notifications-read',
          {},
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem("token")}`
            }
          }
        )

        // Reset frontend count
        this.count = 0
      } catch (error) {
        console.error("Failed to reset notifications", error)
      }
    }
  }
})
