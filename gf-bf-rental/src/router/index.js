import { createRouter, createWebHistory } from "vue-router";
import Home from "@/views/Home.vue";
import Details from "@/views/Details.vue";
import MyBooking from "@/views/MyBooking.vue";
import Profile from "@/views/Profile.vue";

const routes = [
  { path: "/", name: "AuthPage", component: () => import('@/views/AuthPage.vue') },
  { path: "/home", component: Home },
  { path: "/details/:id", component: Details },
  { path: "/my-bookings", name: "MyBooking", component: MyBooking },
  { path: "/profile", name: "Profile", component: () => import('@/views/Profile.vue') }
];

export default createRouter({
  history: createWebHistory(), // ← Use history mode for clean URLs
  routes
});
