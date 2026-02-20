import { createRouter, createWebHistory } from "vue-router";
import Home from "@/views/Home.vue";
import Details from "@/views/Details.vue";
import MyBooking from "@/views/MyBooking.vue";
import Profile from "@/views/Profile.vue";
import Notifications from "@/views/Notifications.vue";
import DatePlanner from '@/views/DatePlanner.vue'

const routes = [
  { path: "/", name: "AuthPage", component: () => import('@/views/AuthPage.vue') },
  { path: "/home", component: Home },
  {
  path: "/details/:id",
  name: "Details",
  component: Details
},
  { path: "/my-bookings", name: "MyBooking", component: MyBooking },
  { path: "/profile", name: "Profile", component: () => import('@/views/Profile.vue') },
  { path: "/notifications", name: "Notifications", component: Notifications },
  {
  path: '/date-planner/:id',
  name: 'DatePlanner',
  component: DatePlanner
}
];

export default createRouter({
  history: createWebHistory(), // ← Use history mode for clean URLs
  routes
});
