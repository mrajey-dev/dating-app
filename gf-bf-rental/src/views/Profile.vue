<template>
  <div class="profile-page">
    <div class="profile-card">
      <h1>Edit Profile</h1>

      <!-- PROFILE PHOTO -->
      <div class="profile-photo">
        <img :src="previewPhoto.profile_photo || user.profile_photo" class="avatar" />
        <label class="upload-btn">
          Change Photo
         <input type="file" @change="e => onFileChange('profile_photo', e)" hidden />

        </label>
      </div>

      <!-- PHOTO GALLERY -->
      <div class="photo-gallery">
        <h3>Gallery</h3>
        <div class="gallery-scroll">
          <div v-for="(photo, i) in previewPhoto.photo_gallery || user.photo_gallery || []" :key="i">
            <img :src="photo" class="gallery-img" />
          </div>
          <label class="add-photo">
            +
          <input type="file" multiple @change="e => onFileChange('photo_gallery', e)" hidden />

          </label>
        </div>
      </div>

      <form class="profile-form" @submit.prevent="updateProfile">
        <!-- BASIC INFO -->
        <div class="form-row">
          <div class="form-group">
            <label>First Name</label>
            <input v-model="user.first_name" required />
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input v-model="user.last_name" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Email</label>
            <input type="email" v-model="user.email" required />
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input v-model="user.phone_number" />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Password</label>
            <input type="password" v-model="user.password" placeholder="Leave blank to keep current" />
          </div>
          <div class="form-group">
            <label>Gender</label>
            <select v-model="user.gender">
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Gender Preference</label>
            <select v-model="user.gender_preference">
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="both">Both</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Date of Birth</label>
          <input type="date" v-model="user.dob" :max="maxDate" />
        </div>

        <!-- LOCATION -->
        <div class="form-row">
          <div class="form-group"><label>Country</label><input v-model="user.country" /></div>
          <div class="form-group"><label>State</label><input v-model="user.state" /></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>City</label><input v-model="user.city" /></div>
          <div class="form-group"><label>Pin Code</label><input v-model="user.pin_code" /></div>
        </div>
        <div class="form-group">
          <label>Address</label>
          <textarea v-model="user.address"></textarea>
        </div>

        <!-- PROFILE DETAILS -->
        <div class="form-group">
          <label>Bio</label>
          <input v-model="user.subtitle" />
        </div>
        <div class="form-group">
          <label>Status</label>
          <input v-model="user.status" />
        </div>

        <div class="form-row">
        <div class="form-group toggle-group">
 <label class="toggle-label">
  Verified Badge  
  <img
    v-if="user.verified_badge"
    src="/verified1.png"
    alt="Verification Badge"
    class="verified-icon"
  />
</label>


  <div class="toggle-switch">
    <input
      type="checkbox"
      id="verifiedToggle"
      v-model="user.verified_badge"
    />
    <label for="verifiedToggle" class="toggle-slider"></label>
  </div>
</div>

          <div class="form-group"><label>Rate (₹/hour)</label><input type="number" v-model="user.rate" min="0" /></div>
        </div>

     <!-- HABITS FIELD -->
<div class="form-group">
  <label>Habits / Hobbies</label>
  <input
    v-model="habitsText"
    placeholder="Enter habits separated by commas"
  />
  <div class="habits-preview">
  <span v-for="(h, i) in habitsText.split(',').map(h => h.trim()).filter(h => h)" :key="i" class="habit-chip">
    {{ h }}
  </span>
</div>

</div>


      <!-- Sticky Save Bar -->
<div class="save-bar">
  <button type="submit" class="btn-save">Save Changes</button>
</div>

      </form>
    </div>
  
 <!-- TOAST -->
<div class="toast-container">
  <div
    v-if="toast.show"
    :class="['toast', toast.type]"
  >
    {{ toast.message }}
  </div>
</div>
 </div>
</template>

<script>
import axios from "axios"

export default {
  data() {
    return {
        toast: {
      show: false,
      message: "",
      type: "success", // success | error
    },
      user: {
        first_name: "",
        last_name: "",
        gender: "",
        gender_preference: "",
        dob: "",
        email: "",
        phone_number: "",
        country_code: "",
        password: "",
        profile_photo: "",
        photo_gallery: [],
        subtitle: "",
        city: "",
        state: "",
        address: "",
        country: "",
        pin_code: "",
        verified_badge: false,
        rate: 0,
       habits: [],
        status: "",
      },
      habitsText: "", 

      fileUploads: {
        profile_photo: null,
        photo_gallery: [],
      },
      previewPhoto: {
        profile_photo: null,
        photo_gallery: [],
      },
    }
  },
  computed: {
    maxDate() {
      const today = new Date()
      today.setFullYear(today.getFullYear() - 18)
      return today.toISOString().split("T")[0]
    },
  },
async mounted() {
  const storedUser = JSON.parse(localStorage.getItem("user"))
  if (!storedUser) return

  // Profile photo
  const profilePhoto = storedUser.profile_photo
    ? storedUser.profile_photo.startsWith("http")
      ? storedUser.profile_photo
      : `https://companion.ajaywatpade.in/dating-backend/public/storage/${storedUser.profile_photo}`
    : ""

  this.user = {
    ...storedUser,
    profile_photo: profilePhoto
  }
  this.user.verified_badge =
  storedUser.verified_badge === 1 ||
  storedUser.verified_badge === "1" ||
  storedUser.verified_badge === true


  // Habits text
  if (Array.isArray(storedUser.habits)) {
    this.habitsText = storedUser.habits.join(", ")
  } else if (typeof storedUser.habits === "string") {
    try {
      const parsed = JSON.parse(storedUser.habits)
      if (Array.isArray(parsed)) this.habitsText = parsed.join(", ")
      else this.habitsText = storedUser.habits
    } catch (e) {
      this.habitsText = storedUser.habits
    }
  } else {
    this.habitsText = ""
  }

  // --- PHOTO GALLERY ---
  if (storedUser.photo_gallery) {
    let galleryArr = []

    // Parse if stored as JSON string
    if (typeof storedUser.photo_gallery === "string") {
      try {
        galleryArr = JSON.parse(storedUser.photo_gallery)
      } catch (e) {
        galleryArr = []
      }
    } else if (Array.isArray(storedUser.photo_gallery)) {
      galleryArr = storedUser.photo_gallery
    }

    // Set preview with /storage/ prepended
    this.previewPhoto.photo_gallery = galleryArr.map(
      (p) =>
        p.startsWith("http")
          ? p
          : `https://companion.ajaywatpade.in/dating-backend/public/storage/${p.replace("gallery\\", "gallery/")}`
    )

    // Keep original array for backend
    this.user.photo_gallery = galleryArr
  }
},




  methods: {
    showToast(message, type = "success") {
  this.toast.message = message
  this.toast.type = type
  this.toast.show = true

  setTimeout(() => {
    this.toast.show = false
  }, 3000)
},

async onFileChange(field, e) {
  const token = localStorage.getItem("token")
  if (!token) return alert("Please login!")

  if (field === "profile_photo") {
    const file = e.target.files[0]
    if (!file) return

    // Preview instantly
    this.previewPhoto.profile_photo = URL.createObjectURL(file)

    const formData = new FormData()
    formData.append("profile_photo", file)

    try {
      const res = await axios.post(
        "https://companion.ajaywatpade.in/api/profile/upload-profile-photo",
        formData,
        { headers: { Authorization: `Bearer ${token}` } }
      )

      // Update user data instantly
      this.user.profile_photo = res.data.profile_photo
      localStorage.setItem("user", JSON.stringify(res.data))
    } catch (err) {
      console.error(err)
    this.showToast("Failed to upload profile photo", "error")

    }
  }

  // ---------------- GALLERY ----------------
  if (field === "photo_gallery") {
    const files = Array.from(e.target.files).slice(0, 5)
    if (!files.length) return

    // Preview instantly
    const previews = files.map((f) => URL.createObjectURL(f))
    this.previewPhoto.photo_gallery.push(...previews)

    const formData = new FormData()
    files.forEach((file) => formData.append("photo_gallery[]", file))

    try {
      const res = await axios.post(
        "https://companion.ajaywatpade.in/api/profile/upload-gallery",
        formData,
        { headers: { Authorization: `Bearer ${token}` } }
      )

      // Backend should return updated gallery array
      this.user.photo_gallery = res.data.photo_gallery
      localStorage.setItem("user", JSON.stringify(res.data))
    } catch (err) {
      console.error(err)
      alert("Failed to upload gallery images")
    }
  }
},

 async updateProfile() {
  const token = localStorage.getItem("token")
  if (!token) return alert("Please login!")

  this.user.habits = this.habitsText
    .split(",")
    .map(h => h.trim())
    .filter(Boolean)

  const formData = new FormData()

  Object.keys(this.user).forEach((key) => {
    if (["profile_photo", "photo_gallery"].includes(key)) return
   Object.keys(this.user).forEach((key) => {
  if (["profile_photo", "photo_gallery"].includes(key)) return

  if (key === "verified_badge") {
    formData.append(key, this.user.verified_badge ? 1 : 0)
  } else {
    formData.append(key, this.user[key])
  }
})

  })

  try {
    const res = await axios.post(
      "https://companion.ajaywatpade.in/api/profile/update",
      formData,
      { headers: { Authorization: `Bearer ${token}` } }
    )

    localStorage.setItem("user", JSON.stringify(res.data))
    this.showToast("Profile updated successfully")

  } catch (err) {
    console.error(err)
    this.showToast("Failed to update profile", "error")
  }
}

  },
}
</script>

<style scoped>
/* MOBILE-FRIENDLY STYLING */
.profile-page {
  display: flex;
  justify-content: center;
  padding: 15px;
  background: #f7f7f7;
  min-height: 100vh;
}

.profile-card {
  width: 100%;
  max-width: 500px;
  background: #fff;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

.profile-card h1 {
  text-align: center;
  margin-bottom: 20px;
  font-size: 24px;
    font-family: math;
  color: #ff5864;
}

.profile-photo {
text-align: center;
    display: flex;
    margin-bottom: 20px;
    flex-direction: column;
    align-items: center;
}

.profile-photo .avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #ff5864;
}

.upload-btn {
  display: inline-block;
  margin-top: 10px;
  background: #ff5864;
  color: #fff;
  padding: 3px 11px;
  border-radius: 7px;
  cursor: pointer;
  font-size: 12px;
}

.photo-gallery h3 {
  font-weight: 600;
  margin-bottom: 8px;
  font-size: 10px;
  color: #333;
}

.gallery-scroll {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  padding-bottom: 10px;
}

.gallery-img {
  width: 70px;
  height: 70px;
  border-radius: 12px;
  object-fit: cover;
}

.add-photo {
  width: 70px;
  height: 70px;
  background: #ffe0e6;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  font-weight: bold;
  cursor: pointer;
  color: #ff5864;
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

input, textarea, select {
  padding: 12px;
  border-radius: 12px;
  border: 1px solid #ddd;
  font-size: 14px;
  outline: none;
}

input:focus, textarea:focus, select:focus {
  border-color: #ff5864;
}

textarea {
  resize: vertical;
  min-height: 60px;
}

.btn-save {
  padding: 14px;
  background: linear-gradient(135deg, #ff5864, #ff2e44);
  color: #fff;
  font-weight: bold;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  transition: 0.3s;
}

.btn-save:hover {
  opacity: 0.9;
}

/* RESPONSIVE FOR TABLETS AND DESKTOP */
@media (min-width: 600px) {
  .form-row {
    flex-direction: row;
  }
}
.habits-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 5px;
}

.habit-chip {
  background: #ffe0e6;
  color: #ff5864;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
}

/* Page fade + slide */
.profile-page {
  animation: pageFade 0.6s ease-out;
}

@keyframes pageFade {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Card float-in */
.profile-card {
  animation: cardFloat 0.7s ease-out;
}

@keyframes cardFloat {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
.profile-photo .avatar {
  transition: all 0.4s ease;
}

.profile-photo .avatar:hover {
  transform: scale(1.05);
  box-shadow: 0 0 0 6px rgba(255, 88, 100, 0.15),
              0 15px 30px rgba(255, 88, 100, 0.3);
}
.upload-btn,
.btn-save {
  transition: all 0.25s ease;
}

.upload-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 18px rgba(255, 88, 100, 0.35);
}

.btn-save:active {
  transform: scale(0.96);
}
input,
textarea,
select {
  transition: all 0.25s ease;
  background: #fafafa;
}

input:focus,
textarea:focus,
select:focus {
  background: #fff;
  box-shadow: 0 0 0 4px rgba(255, 88, 100, 0.12);
}
.gallery-img {
  transition: all 0.35s ease;
}

.gallery-img:hover {
  transform: scale(1.12);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.25);
}
.add-photo {
  transition: all 0.3s ease;
}

.add-photo:hover {
  background: #ff5864;
  color: #fff;
  transform: scale(1.1);
}
.habit-chip {
  animation: chipPop 0.35s ease;
}

@keyframes chipPop {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.form-group label {
    font-size: 10px;
  font-weight: 600;
  margin-bottom: 6px;
  color: #444;
}

.profile-form {
  margin-top: 10px;
}
/* Toggle container */
.toggle-group {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-direction: row;
  gap: 12px;
}

/* Label text */
.toggle-label {
  font-weight: 600;
  color: #444;
  font-size: 14px;
}

/* Toggle switch wrapper */
.toggle-switch {
  position: relative;
  width: 50px;
  height: 26px;
}

/* Hide default checkbox */
.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* Slider background */
.toggle-slider {
  position: absolute;
  inset: 0;
  background: #ddd;
  border-radius: 30px;
  cursor: pointer;
  transition: background 0.3s ease;
}

/* Slider knob */
.toggle-slider::before {
  content: "";
  position: absolute;
 height: 15px;
    width: 15px;
  left: 3px;
  top: 3px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.3s ease;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
}

/* Active state */
.toggle-switch input:checked + .toggle-slider {
  background: linear-gradient(135deg, #ff5864, #ff2e44);
}

.toggle-switch input:checked + .toggle-slider::before {
  transform: translateX(24px);
}
img {
  width: 16px;
    height: 11px;
  object-fit: contain;
}
.verified-icon {
  width: 16px;
  height: 11px;
  margin-left: 1px;
  object-fit: contain;
  animation: badgeFade 0.3s ease;
}

@keyframes badgeFade {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.save-bar {
  position: sticky;
  bottom: 0;
  background: #fff;
  padding: 12px 0;
  margin-top: 15px;
  box-shadow: 0 -8px 20px rgba(0, 0, 0, 0.08);
  z-index: 10;
}

/* Make button full-width for mobile UX */
.save-bar .btn-save {
  width: 100%;
}
.save-bar {
  position: fixed;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  width: calc(100% - 30px);
  max-width: 470px;
  background: #ffffff;
  box-shadow: none;
}
/* TOAST */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
}

.toast {
  min-width: 260px;
  padding: 14px 18px;
  border-radius: 14px;
  font-size: 14px;
  font-weight: 600;
  color: #fff;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
  animation: toastIn 0.4s ease, toastOut 0.4s ease 2.6s forwards;
}

/* Success */
.toast.success {
  background: linear-gradient(135deg, #28c76f, #1f9d57);
}

/* Error */
.toast.error {
  background: linear-gradient(135deg, #ff5864, #ff2e44);
}

/* Animations */
@keyframes toastIn {
  from {
    opacity: 0;
    transform: translateX(40px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes toastOut {
  to {
    opacity: 0;
    transform: translateX(40px);
  }
}

</style>
