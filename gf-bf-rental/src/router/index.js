import { createRouter, createWebHistory } from "vue-router";
import Home from "@/views/Home.vue";
import Details from "@/views/Details.vue";
import MyBooking from "@/views/MyBooking.vue";

const routes = [
  { path: "/", name: "AuthPage", component: () => import('@/views/AuthPage.vue') },
  { path: "/home", component: Home },
  { path: "/details/:id", component: Details },
  { path: "/my-bookings", name: "MyBooking", component: MyBooking }
];

export default createRouter({
  history: createWebHistory(), // ← Use history mode for clean URLs
  routes
});
