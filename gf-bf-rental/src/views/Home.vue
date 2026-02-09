<template>
  <div class="app">
    <!-- Side Menu Overlay -->
    <div class="overlay" v-if="isMenuOpen" @click="closeMenu"></div>

    <!-- Side Menu -->
    <div class="side-menu" :class="{ open: isMenuOpen }">
      <div class="menu-header">
        <img :src="user.profile_photo" class="menu-avatar" />
        <h3>{{ user.first_name }} {{ user.last_name }}</h3>

        <p>{{ user.city }}</p>
      </div>

      <ul class="menu-list">
       <li @click="goToProfile">👤 My Profile</li>
        <li>❤️ Matches</li>
        <li>💬 Chats</li>
        <li @click="goToBookings">📅 Bookings</li>
        <li>⭐ Favorites</li>
        <li>⚙️ Settings</li>
        <li class="logout" @click="logout">🚪 Logout</li>
      </ul>
    </div>

    <!-- Header -->
    <div class="header">
      <div class="top-bar">
        <span class="menu" @click="toggleMenu">☰</span>
        <img class="avatar" :src="user.profile_photo" />
      </div>

      <h1>Choose a Companion</h1>

      <div class="search-box">
        <div class="location">
          <span class="location-icon">📍</span>
          <span class="location-text">Nashik, India</span>
        </div>
        <div class="actions">
          <button class="icon-btn filter">⚙️</button>
        </div>
      </div>
    </div>

    <!-- Results -->
    <div class="content">
      <div v-if="people.length">
        <div class="deal-card" v-for="person in people" :key="person.id">
          <!-- Top -->
          <div class="deal-top">
            <div class="deal-rating">⭐ {{ person.rating || 0 }}</div>
            <button
              class="fav-btn"
              :class="{ active: isFavourite(person.id) }"
              @click.stop="toggleFavourite(person.id)"
            >
              {{ isFavourite(person.id) ? "❤️" : "🤍" }}
            </button>
          </div>

          <!-- Image -->
          <img
            class="deal-img"
            :src="person.profile_photo || 'https://via.placeholder.com/200'"
          />

          <!-- Content -->
          <div class="deal-content">
            <h3>
              {{ person.first_name || person.name }}
              <img
                v-if="person.verified_badge == 1"
                src="@/assets/verified1.png"
                class="verified"
              />
            </h3>
            <p class="sub">{{ person.status || '' }}</p>
            <p class="price">₹{{ person.rate || 0 }} / hour</p>
          </div>

          <!-- Arrow -->
          <button class="deal-next" @click="goToDetails(person.id)">
            ››
          </button>
        </div>
      </div>
      <div v-else>
        No companions available. Make sure you are logged in!
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios"

export default {
  name: "Home",
  data() {
    return {
      isMenuOpen: false,
      favourites: [],
      people: [],
      user: { first_name: "", last_name: "", city: "", profile_photo: "" },
    }
  },
  async mounted() {
    await this.loadUser()
    this.favourites = JSON.parse(localStorage.getItem("favourites") || "[]")
    await this.loadUsers()
  },
  methods: {
    goToProfile() {
    this.isMenuOpen = false
    this.$router.push("/profile")
  },
async loadUsers() {
  const token = localStorage.getItem("token")
  if (!token) return

  try {
    const res = await axios.get(
      "https://companion.ajaywatpade.in/api/users",
      { headers: { Authorization: `Bearer ${token}` } }
    )

    this.people = res.data
      .map((person) => ({
        ...person,
        profile_photo: person.profile_photo
          ? `https://companion.ajaywatpade.in/${person.profile_photo}`
          : "https://via.placeholder.com/200",
      }))
      .sort((a, b) => b.id - a.id)
  } catch (err) {
    console.error("Failed to load users:", err.response || err)
  }
},


    logout() {
      axios
        .post(
          "https://companion.ajaywatpade.in/api/logout",
          {},
          {
            headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
          }
        )
        .catch(() => {})

      localStorage.removeItem("token")
      localStorage.removeItem("favourites")
      localStorage.removeItem("user")
      this.isMenuOpen = false
      this.$router.replace("/")
    },

    /* ================= USERS ================= */
async loadUser() {
  const storedUser = JSON.parse(localStorage.getItem("user"))
  if (!storedUser || !localStorage.getItem("token")) {
    this.$router.replace("/")
    return
  }

  this.user = {
    first_name: storedUser.first_name || "",
    last_name: storedUser.last_name || "",
    city: storedUser.city || "Unknown",
    profile_photo: storedUser.profile_photo
      ? `https://companion.ajaywatpade.in/dating-backend/public/storage/${storedUser.profile_photo}`
      : "https://via.placeholder.com/100",
  }
},




    /* ================= FAVOURITES ================= */
    async toggleFavourite(id) {
      const token = localStorage.getItem("token")
      if (!token) return

      try {
        const res = await axios.post(
          `https://companion.ajaywatpade.in/api/users/${id}/toggle-like`,
          {},
          { headers: { Authorization: `Bearer ${token}` } }
        )

        const person = this.people.find((p) => p.id === id)
        if (res.data.liked) {
          if (!this.favourites.includes(id)) this.favourites.push(id)
          if (person) person.like_count = (person.like_count || 0) + 1
        } else {
          this.favourites = this.favourites.filter((f) => f !== id)
          if (person) person.like_count = Math.max((person.like_count || 1) - 1, 0)
        }

        localStorage.setItem("favourites", JSON.stringify(this.favourites))
      } catch (err) {
        console.error("Toggle failed", err.response || err)
      }
    },

    isFavourite(id) {
      return this.favourites.includes(id)
    },

    /* ================= NAV ================= */
    goToDetails(id) {
      this.$router.push(`/details/${id}`)
    },

    goToBookings() {
      this.isMenuOpen = false
      this.$router.push("/my-bookings")
    },

    toggleMenu() {
      this.isMenuOpen = true
    },

    closeMenu() {
      this.isMenuOpen = false
    },
  },
}
</script>




        <style scoped>
        /* App Wrapper */
        .app {
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
        position: relative;
        overflow-x: hidden;
        }

        /* Header with waves */
        .header {
        /* background-image: url(https://img.freepik.com/free-vector/wave-gradient-blue-background-modern-design_343694-3814.jpg); */
        background-image: url(https://png.pngtree.com/thumb_back/fh260/background/20241126/pngtree-abstract-pink-background-vector-image_16667423.jpg);
        padding: 24px 20px 80px;
        border-bottom-left-radius: 40px;
        border-bottom-right-radius: 40px;
        color: white;
        position: relative;
        }

        /* Top bar */
        .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        }

        .menu {
        font-size: 22px;
        }

        .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        }

        /* Title */
        .header h1 {
        margin-top: 20px;
        font-size: 22px;
        font-weight: 600;
        }

    

        /* Content */
        .content {
        padding: 20px;
        margin-top: -101px;
        }

        .results {
        font-size: 14px;
        color: #444;
        margin-bottom: 14px;
        }

    

        .rating {
        background: #ffb703;
        color: white;
        display: inline-block;
        padding: 4px 10px;
        border-radius: 10px;
        font-size: 12px;
        }

        .card-body {
        display: flex;
        align-items: center;
        margin-top: 10px;
        }

   .profile-img {
  width: 90px;
  height: 60px;
  border-radius: 12px;
  object-fit: cover;
}


        .info {
        flex: 1;
        padding-left: 12px;
        }

        .info h3 {
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 6px;
        }

        .verified {
        width: 16px;
        }

        .sub {
        font-size: 12px;
        color: #666;
        }

        .price {
        font-weight: 700;
        margin-top: 4px;
        }

        /* Arrow */
        .next {
        position: absolute;
        right: 14px;
        bottom: 14px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #ff4791;
        color: white;
        border: none;
        font-size: 20px;
        }

     

        .deal-card {
     background: white;
    border-radius: 26px;
    padding: 16px;
        margin-top: 28px;

    margin-bottom: 18px;
    position: relative;
    box-shadow: 0px 0px 20px 5px #0000001f;
}

/* Rating badge */
.deal-rating {
  position: absolute;
  top: 14px;
  left: 14px;
  background: #e0ebfc;
  color: #000000;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

/* Image */
.deal-img {
  width: 100%;
  height: auto  ;
  object-fit: cover;
  border-radius: 18px;
  margin-top: 43px;
}

/* Content */
.deal-content {
  margin-top: 12px;
}

.deal-content h3 {
  font-size: 16px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
}

.sub {
  font-size: 13px;
  color: #666;
  margin-top: 2px;
}

.price {
  font-size: 14px;
  font-weight: 700;
  margin-top: 4px;
}

/* Arrow Button */
.deal-next {
  position: absolute;
  right: 14px;
  bottom: 14px;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #ff4791;
  color: white;
  border: none;
  font-size: 22px;
  cursor: pointer;
}
.search-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
  border-radius: 999px;
  padding: 7px 4px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
  width: 100%;
  max-width: 360px;
  margin: 0 auto;
}

/* Location */
.location {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.location-icon {
  font-size: 16px;
}

/* Right buttons */
.actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.icon-btn {
  border: none;
  outline: none;
  background: #000000;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  cursor: pointer;
}



        
       /* Overlay */
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 9;
}

/* Side Menu */
.side-menu {
  position: fixed;
  top: 0;
  left: -280px;
  width: 280px;
  height: 100vh;
  background: #ffffff;
  z-index: 10;
  transition: 0.3s ease;
  box-shadow: 6px 0 20px rgba(0, 0, 0, 0.15);
  border-top-right-radius: 24px;
  border-bottom-right-radius: 24px;
}

/* Open state */
.side-menu.open {
  left: 0;
}

/* Menu Header */
.menu-header {
  padding: 24px 16px;
  background: linear-gradient(135deg, #ff5fa2, #ff8ccf);
  color: white;
  text-align: center;
  border-top-right-radius: 24px;
}

.menu-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  margin-bottom: 10px;
  object-fit: cover;
}

/* Menu List */
.menu-list {
  list-style: none;
  padding: 16px;
}

.menu-list li {
  padding: 14px 12px;
  font-size: 15px;
  border-radius: 12px;
  cursor: pointer;
  transition: 0.2s;
}

.menu-list li:hover {
  background: #f5f5f5;
}

.menu-list .logout {
  color: #ff3b3b;
  margin-top: 12px;
}
 
/* Top badges wrapper */
.deal-top {
  position: absolute;
  top: 14px;
  left: 14px;
  right: 14px;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  z-index: 2;
}

/* Favourite button */
.fav-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.9);
  font-size: 16px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.15s ease, background 0.2s ease;
}

.fav-btn:active {
  transform: scale(0.85);
}

/* Active (❤️) state */
.fav-btn.active {
  background: #fdfdfd;
  animation: pop 0.3s ease;
}

/* Pop animation */
@keyframes pop {
  0% {
    transform: scale(0.6);
  }
  60% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}


.fav-btn:hover {
  transform: scale(1.1);
}

.deal-img {
  margin-top: 50px;
}
 </style>

