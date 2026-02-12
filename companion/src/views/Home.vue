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
       <li @click="showFavourites">❤️ Favorites</li>
<li @click="showAllUsers">🌐 All Users</li>

        <li>⚙️ Settings</li>
        <li class="logout" @click="logout">🚪 Logout</li>
      </ul>
    </div>

    <!-- Header -->
    <div class="header">
     

   <h1 class="page-title">Choose a Companion</h1>

<input
  type="text"
  v-model="search"
  placeholder="Search"
  class="search-input"
/>

    </div>

    <!-- Results -->
    <div class="content">
      <div v-if="people.length">
       <div
  class="deal-card"
  v-for="person in filteredPeople"
  :key="person.id"
  :data-id="person.id"
  ref="cards"
>


          <!-- Top -->
          <div class="deal-top">
            <div class="deal-rating">⭐ {{ person.rating || 0 }}</div>
           <button
  class="fav-btn"
  :class="{ active: person.liked }"
  @click.stop="toggleFavourite(person)"
>
  {{ person.liked ? "❤️" : "🤍" }}
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
            <!-- <p class="price">₹{{ person.rate || 0 }} / hour</p> -->
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
 
    <!-- Instagram Style Footer -->
<div class="bottom-footer">
 <div class="footer-item" @click="$router.push('/home')">
  <img src="@/assets/home.png" class="footer-icon" />
</div>
<div class="footer-item">
  <img src="@/assets/search.png" class="footer-icon" />
</div>

<div class="footer-item">
  <img src="@/assets/golden.png" class="footer-icon" />
</div>

<div class="footer-item" @click="$router.push('/notifications')">
  <img src="@/assets/heart.png" class="footer-icon" />
</div>


  <div class="footer-item" @click="$router.push('/profile')">
    <img :src="user.profile_photo" class="footer-avatar" />
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
      showOnlyFavourites: false,
      isMenuOpen: false,
        refreshInterval: null,
     
      people: [],
        search: "",
         visibleUsers: new Set(), 
          observer: null,
      user: { first_name: "", last_name: "", city: "", profile_photo: "" },
    }
  },
async mounted() {
  await this.loadUser()
  // this.favourites = JSON.parse(localStorage.getItem("favourites") || "[]")
  await this.loadUsers()

  this.setupObserver()

  this.refreshInterval = setInterval(() => {
    this.refreshVisibleUsers()
  }, 2000)
},

beforeUnmount() {
  if (this.refreshInterval) clearInterval(this.refreshInterval)
  if (this.observer) this.observer.disconnect()
},


  computed: {
 filteredPeople() {
    let list = this.people;

    // If "Favorites" clicked, filter only liked users
    if (this.showOnlyFavourites) {
      list = list.filter(user => user.liked);
    }

    if (!this.search) return list;

    const q = this.search.toLowerCase();

    return list.filter(person => {
      return (
        (person.first_name && person.first_name.toLowerCase().includes(q)) ||
        (person.last_name && person.last_name.toLowerCase().includes(q)) ||
        (person.status && person.status.toLowerCase().includes(q)) ||
        (person.subtitle && person.subtitle.toLowerCase().includes(q)) ||
        (person.city && person.city.toLowerCase().includes(q)) ||
        (person.state && person.state.toLowerCase().includes(q)) ||
        (person.country && person.country.toLowerCase().includes(q)) ||
        (person.address && person.address.toLowerCase().includes(q)) ||
        (person.rate && person.rate.toString().includes(q))
      )
    })
  }
},

  methods: {
    showFavourites() {
    this.isMenuOpen = false; // close sidebar
    this.showOnlyFavourites = true;
  },

  showAllUsers() {
    this.showOnlyFavourites = false;
  },
    setupObserver() {
    this.observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          const id = Number(entry.target.dataset.id)

          if (entry.isIntersecting) {
            this.visibleUsers.add(id)
          } else {
            this.visibleUsers.delete(id)
          }
        })
      },
      { threshold: 0.3 } // 30% visible
    )

    this.$nextTick(() => {
      this.$refs.cards?.forEach((el) => {
        this.observer.observe(el)
      })
    })
  },
  async refreshVisibleUsers() {
  if (!this.visibleUsers.size) return

  const token = localStorage.getItem("token")
  if (!token) return

  try {
    const res = await axios.get(
      "https://companion.ajaywatpade.in/api/users",
      { headers: { Authorization: `Bearer ${token}` } }
    )

    res.data.forEach((updated) => {
  if (this.visibleUsers.has(updated.id)) {
    const index = this.people.findIndex(p => p.id === updated.id)
    if (index !== -1) {
      this.people[index] = {
        ...this.people[index],
        rating: updated.rating,
        like_count: updated.like_count,
        status: updated.status,
        rate: updated.rate,
        liked: updated.liked, // <-- add this
      }
    }
  }
})

  } catch (e) {
    console.error("Visible refresh failed", e)
  }
},

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
        liked: person.liked || false, // <-- keep this
      }))
      .sort((a, b) => b.id - a.id)

    // ❌ REMOVE the old favourites line entirely
    // this.favourites = this.people
    //   .filter(p => p.liked || storedFavourites.includes(p.id))
    //   .map(p => p.id)

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
async toggleFavourite(person) {
  const token = localStorage.getItem("token")
  if (!token) return

  try {
    const res = await axios.post(
      `https://companion.ajaywatpade.in/api/users/${person.id}/toggle-like`,
      {},
      { headers: { Authorization: `Bearer ${token}` } }
    )

    // Update like status from backend
    person.liked = res.data.liked
    person.like_count = res.data.like_count
  } catch (err) {
    console.error(err)
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
        padding: 7px 48px 63px;
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
.search-input {
  width: 100%;
  padding:9px 14px;
  margin: 10px 0 16px;
  border-radius: 30px;
  border: 1px solid #ddd;
  font-size: 14px;
}

/* Bottom Instagram Style Footer */
.bottom-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 60px;
  background: #ffffff;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: space-around;
  align-items: center;
  z-index: 20;
}

.footer-item {
  font-size: 22px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.footer-item:active {
  transform: scale(0.85);
}

/* Profile avatar */
.footer-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
}
.content {
  padding: 20px;
  margin-top: -101px;
  padding-bottom: 80px; /* important */
}
.footer-icon {
  width: 24px;
  height: 24px;
  object-fit: contain;
}
.footer-icon {
  width: 24px;
  height: 24px;
  user-select: none;
  -webkit-user-drag: none;
}

</style>

