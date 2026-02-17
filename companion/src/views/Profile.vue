<template>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <div class="profile-page">
    <div class="profile-card">


  <!-- USER STATS -->
<div class="profile-card insta-profile">
  <!-- PROFILE HEADER -->
  <div class="profile-header">
  <div class="profile-photo" @click="triggerProfilePhotoInput">
  <img :src="previewPhoto.profile_photo || user.profile_photo" class="avatar" />
  
  <!-- Hidden file input -->
  <input
    type="file"
    ref="profilePhotoInput"
    accept="image/*"
    @change="onProfilePhotoSelect"
    hidden
  />
</div>


    <div class="profile-info">
     <h2 class="username">
  {{ user.first_name }} {{ user.last_name }}
  <img
    v-if="user.verified_badge"
    src="/verified1.png"
    alt="Verified Badge"
    class="verified-icon"
  />
</h2>


      <div class="profile-stats">
        <div class="stat">
         <div class="count">
  {{ user.photo_gallery ? user.photo_gallery.length : 0 }}
</div>

          <div class="label">Posts</div>
        </div>
        <div class="stat">
          <div class="count">{{ user.followers || 0 }}</div>
          <div class="label">Followers</div>
        </div>
        <div class="stat">
          <div class="count">{{ user.following || 0 }}</div>
          <div class="label">Following</div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- INTRODUCTION VIDEO -->
<div class="video-section">
  <h3 class="introduction">Introduction Video</h3>

  <div class="video-preview">
    <video
      v-if="previewVideo"
      :src="previewVideo"
      controls
      class="intro-video"
    ></video>

    <label class="add-video">
      <i class="fa fa-folder-open-o" style="font-size:15px"></i> Upload Video
      <input
        type="file"
        accept="video/*"
        @change="onVideoSelect"
        hidden
      />
    </label>
  </div>
</div>


      <!-- PHOTO GALLERY -->
      <div class="photo-gallery">
        <h3>Gallery</h3>
        <div class="gallery-scroll" @click.stop>  
 <div
  v-for="(photo, i) in previewPhoto.photo_gallery || []"
  :key="i"
  class="gallery-item"
  @click="togglePhoto(i)"
>
  <img :src="photo" class="gallery-img" />

  <!-- REMOVE BUTTON (only when selected) -->
  <button
    v-if="selectedPhotoIndex === i"
    class="remove-photo"
    @click.stop="removeGalleryPhoto(i)"
  >
    ✕
  </button>
</div>


          <label class="add-photo">
            +
          <input type="file" multiple @change="e => onFileChange('photo_gallery', e)" hidden />

          </label>
        </div>
      </div>
<div class="edit-bar">
   <button class="btn-logout" @click="logout">
   <i class="fa fa-sign-out" style="font-size:15px"></i> Logout
  </button>
  <button
    type="button"
    class="btn-edit"
    v-if="!showPersonalDetails"
    @click="showPersonalDetails = true"
  >
    <i class="fas fa-edit" style="font-size: 13px;"></i> Edit Personal Details
  </button>

  <button
    type="button"
    class="btn-cancel"
    v-if="showPersonalDetails"
    @click="showPersonalDetails = false"
  >
   <i class="fa fa-eye-slash" aria-hidden="true"></i> Hide
  </button>
</div>

    <form
  v-if="showPersonalDetails"
  class="profile-form"
  @submit.prevent="updateProfile"
>
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
              <!-- <option value="other">Other</option> -->
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
      
          <select v-model="user.status">
          <option disabled value="">Select status</option>
          <option>Single</option>
          <option>Married</option>
          <option>Divorced</option>
          <option>Separated</option>
          <option>Widowed</option>
        </select>
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

          <!-- <div class="form-group"><label>Rate (₹/hour)</label><input type="number" v-model="user.rate" min="0" /></div> -->
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

<!-- DELETE ACCOUNT -->
<!-- <div class="delete-account">
  <button @click="confirmDelete" class="btn-delete">
    Delete My Account
  </button>
</div> -->

      <!-- Sticky Save Bar -->
<!-- Sticky Save & Logout Bar -->
<div class="save-bar">
 
  <button type="submit" class="btn-save"><i class="fa fa-save" style="font-size:15px"></i> Save Changes</button>
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
<!-- CROP MODAL -->
<div v-if="showCropper" class="cropper-modal">
  <div class="cropper-header">
    <button @click="closeCropper">Cancel</button>
    <span>Move & Scale</span>
    <button class="done" @click="cropAndUpload">Done</button>
  </div>

  <div class="cropper-body">
    <img ref="cropImage" :src="cropImageUrl" />
  </div>
</div>

 </div>

</template>

<script>
import axios from "axios"
import Cropper from "cropperjs"
import "cropperjs/dist/cropper.min.css"
import '@fortawesome/fontawesome-free/css/all.min.css'

export default {
  data() {
    return {
      showPersonalDetails: false,
      previewVideo: null,
      selectedPhotoIndex: null,
        showCropper: false,
cropper: null,
cropImageUrl: null,
selectedProfileFile: null,

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
  console.log(storedUser)
  if (!storedUser) return

  // Profile photo
  const profilePhoto = storedUser.profile_photo
    ? storedUser.profile_photo.startsWith("http")
      ? storedUser.profile_photo
      : `https://companion.ajaywatpade.in/dating-backend/public/storage/${storedUser.profile_photo}`
    : ""
if (storedUser.introduction_video) {
  this.previewVideo = storedUser.introduction_video.startsWith("http")
    ? storedUser.introduction_video
    : `https://companion.ajaywatpade.in/dating-backend/public/storage/${storedUser.introduction_video}`
}

  this.user = {
    ...storedUser,
    profile_photo: profilePhoto,
     verified_badge:
    storedUser.verified_badge === 1 ||
    storedUser.verified_badge === "1" ||
    storedUser.verified_badge === true,
   
  followers: Number(storedUser.followers_count || 0),
  following: Number(storedUser.following_count || 0),
  }
  document.addEventListener("click", this.closePhotoSelection)

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
beforeUnmount() {
  document.removeEventListener("click", this.closePhotoSelection)
},



  methods: {
    async onVideoSelect(e) {
  const token = localStorage.getItem("token")

  const file = e.target.files[0]
  if (!file) return

  // Optional: limit size (50MB example)
  if (file.size > 50 * 1024 * 1024) {
    this.showToast("Video must be under 50MB", "error")
    return
  }

  const formData = new FormData()
  formData.append("introduction_video", file)

  try {
    const res = await axios.post(
      "https://companion.ajaywatpade.in/api/profile/upload-video",
      formData,
      { headers: { Authorization: `Bearer ${token}` } }
    )

    this.previewVideo = URL.createObjectURL(file)
    this.user.introduction_video = res.data.introduction_video
    localStorage.setItem("user", JSON.stringify(res.data))

    this.showToast("Video uploaded successfully")
  } catch (err) {
    console.error(err)
    this.showToast("Failed to upload video", "error")
  }
},

    triggerProfilePhotoInput() {
  this.$refs.profilePhotoInput.click()
},

    closePhotoSelection() {
  this.selectedPhotoIndex = null
},
    togglePhoto(index) {
  if (this.selectedPhotoIndex === index) {
    this.selectedPhotoIndex = null
  } else {
    this.selectedPhotoIndex = index
  }
},

    logout() {
  localStorage.removeItem("token")
  localStorage.removeItem("user")
  window.location.href = "/"
},

    async removeGalleryPhoto(index) {
  const confirmed = confirm("Remove this photo from your gallery?")
  if (!confirmed) return
  this.selectedPhotoIndex = null
  const token = localStorage.getItem("token")
  if (!token) return

  // Remove from preview instantly (UX win)
  const removedPhoto = this.user.photo_gallery[index]

  this.previewPhoto.photo_gallery.splice(index, 1)
  this.user.photo_gallery.splice(index, 1)

  try {
    const res = await axios.post(
      "https://companion.ajaywatpade.in/api/profile/remove-gallery-photo",
      {
        photo: removedPhoto,
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    )

    // Update local storage with latest user data
    localStorage.setItem("user", JSON.stringify(res.data))
    this.showToast("Photo removed successfully")
  } catch (err) {
    console.error(err)
    this.showToast("Failed to remove photo", "error")
  }
},

    confirmDelete() {
  const confirmed = confirm(
    "Are you sure?\nThis will permanently delete your account and cannot be undone."
  )

  if (confirmed) {
    this.deleteAccount()
  }
},

async deleteAccount() {
  const token = localStorage.getItem("token")
  if (!token) return

  try {
    await axios.delete(
      "https://companion.ajaywatpade.in/api/profile/delete",
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    )

    // Clear everything
    localStorage.removeItem("token")
    localStorage.removeItem("user")

    // Redirect to home
    window.location.href = "/"
  } catch (err) {
    console.error(err)
    this.showToast("Failed to delete account", "error")
  }
},

    onProfilePhotoSelect(e) {
  const file = e.target.files[0]
  if (!file) return

  this.selectedProfileFile = file
  this.cropImageUrl = URL.createObjectURL(file)
  this.showCropper = true

  this.$nextTick(() => {
    this.cropper = new Cropper(this.$refs.cropImage, {
      aspectRatio: 1,
      viewMode: 1,
      dragMode: "move",
      background: false,
      autoCropArea: 1,
      responsive: true,
      zoomOnWheel: true,
    })
  })
},

closeCropper() {
  if (this.cropper) {
    this.cropper.destroy()
    this.cropper = null
  }
  this.showCropper = false
},

async cropAndUpload() {
  const token = localStorage.getItem("token")
  if (!token) return

  const canvas = this.cropper.getCroppedCanvas({
    width: 512,
    height: 512,
    imageSmoothingQuality: "high",
  })

  canvas.toBlob(async (blob) => {
    const formData = new FormData()
    formData.append("profile_photo", blob, "profile.jpg")

    try {
      const res = await axios.post(
        "https://companion.ajaywatpade.in/api/profile/upload-profile-photo",
        formData,
        { headers: { Authorization: `Bearer ${token}` } }
      )

      this.previewPhoto.profile_photo = canvas.toDataURL()
      this.user.profile_photo = res.data.profile_photo
      localStorage.setItem("user", JSON.stringify(res.data))
      this.showToast("Profile photo updated")
    } catch (err) {
      console.error(err)
      this.showToast("Failed to upload photo", "error")
    } finally {
      this.closeCropper()
    }
  }, "image/jpeg", 0.9)
},

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
  if (!token) return

  // Convert habits text to array
  this.user.habits = this.habitsText
    .split(",")
    .map(h => h.trim())
    .filter(Boolean)

  const formData = new FormData()

  Object.keys(this.user).forEach((key) => {
    if (["profile_photo", "photo_gallery"].includes(key)) return

    if (key === "verified_badge") {
      formData.append(key, this.user.verified_badge ? 1 : 0)
    } else if (Array.isArray(this.user[key])) {
      // Handle array fields like habits
      this.user[key].forEach(value => {
        formData.append(`${key}[]`, value)
      })
    } else {
      formData.append(key, this.user[key] ?? "")
    }
  })

  try {
    const res = await axios.post(
      "https://companion.ajaywatpade.in/api/profile/update",
      formData,
      { headers: { Authorization: `Bearer ${token}` } }
    )

    localStorage.setItem("user", JSON.stringify(res.data))
    this.showToast("Profile updated successfully")

    // Optional: hide form after save
    this.showPersonalDetails = false

  } catch (err) {
    console.error(err)
    this.showToast("Failed to update profile", "error")
  }
},


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
      margin-bottom: 39px;
  padding: 8px;
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
  flex-wrap: wrap;
  justify-content: flex-start;
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
  color: #96040e;
  padding: 4px 10px;
  border-radius: 2px;
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
  height: 28px;
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
  left: 6px;
  top: 7px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.3s ease;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
}

/* Active state */
.toggle-switch input:checked + .toggle-slider {
  background: linear-gradient(135deg, #ff5864, #ff2e44);
  height: 28px;
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
  height: 16px;
  margin-left: 4px;
  vertical-align: middle;
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

/* Cropper Modal */
.cropper-modal {
  position: fixed;
  inset: 0;
  background: #000;
  z-index: 9999;
  display: flex;
  flex-direction: column;
}

/* Header */
.cropper-header {
  height: 54px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 16px;
  color: #fff;
  font-weight: 600;
}

.cropper-header button {
  background: none;
  border: none;
  color: #25d366;
  font-size: 15px;
  cursor: pointer;
}

.cropper-header .done {
  font-weight: 700;
}

/* Body */
.cropper-body {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cropper-body img {
  max-width: 100%;
  max-height: 100%;
}

/* Dark background like WhatsApp */
.cropper-bg {
  background: #000 !important;
}
.cropper-modal {
    background-color: #000;
    opacity: 8!important;
}
.delete-account {
  margin-top: 20px;
  text-align: center;
}

.btn-delete {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #ff3b3b, #c70000);
  color: #fff;
  font-weight: bold;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  transition: 0.3s;
}

.btn-delete:hover {
  opacity: 0.9;
  box-shadow: 0 10px 25px rgba(255, 0, 0, 0.35);
}

.gallery-item {
  position: relative;
}

.remove-photo {
  position: absolute;
  top: 2px;
    right: 0px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #ff3b3b;
  color: #fff;
  border: none;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
}

.remove-photo:hover {
  transform: scale(1.1);
}
/* TOP ACTION BAR */
.top-actions {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 15px;
}

/* LOGOUT */
.btn-logout-top {
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid #ddd;
      height: 35px;
    width: auto;
      background: #ff0000;
    color: #ffffff;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}

.btn-logout-top:hover {
  background: #f3f3f3;
}

/* DELETE */
.btn-delete-top {
  padding: 6px 14px;
  border-radius: 20px;
  border: none;
  background: #ffe5e5;
  color: #ff3b3b;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: 0.3s;
}

.btn-delete-top:hover {
  background: #ff3b3b;
  color: #fff;
}

.profile-stats {
  display: flex;
  justify-content: space-around;
  margin: 15px 0;
  padding: 10px 0;
  border-bottom: 1px solid #eee;
}

.profile-stats .stat {
  text-align: center;
}

.profile-stats .count {
  font-weight: bold;
  font-size: 16px;
  color: #333;
}

.profile-stats .label {
  font-size: 12px;
  color: #777;
}

/* Instagram-style profile header */
.insta-profile .profile-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
}

.insta-profile .profile-photo .avatar {
  width: 81px;
  height: 81px;
  border-radius: 50%;
  border: 2px solid #ddd;
  object-fit: cover;
  transition: all 0.3s ease;
}

.insta-profile .profile-info {
  flex: 1;
}

.insta-profile .username {
font-size: 15px;
    font-weight: 600;
    margin-bottom: -19px;
  color: #111;
}

.insta-profile .profile-stats {
  display: flex;
  gap: 25px;
}

.insta-profile .profile-stats .stat {
  text-align: center;
}

.insta-profile .profile-stats .count {
  font-weight: 600;
  font-size: 16px;
  color: #111;
}

.insta-profile .profile-stats .label {
  font-size: 12px;
  color: #555;
}
.save-bar {
  position: fixed;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  width: calc(100% - 30px);
  max-width: 470px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
  background: #fff;
  padding: 10px;
  border-radius: 9px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  z-index: 10;
}

.save-bar .btn-save {
  flex: 1;
  background:linear-gradient(135deg, #E91E63, #E91E63);
  color: #fff;
  font-weight: bold;
  border: none;
  border-radius: 9px;
  padding: 12px;
  cursor: pointer;
}

.save-bar .btn-logout-bottom {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #e92d60;
  color: #fff;
  font-weight: 600;
  border: none;
  border-radius: 9px;
  padding: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.save-bar .btn-logout-bottom:hover {
  background: #d10000;
}

.save-bar .btn-save:hover {
  opacity: 0.9;
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
  padding-bottom: 40px; /* add extra space equal or slightly more than the save-bar height */
}

.video-section {
  margin-bottom: 20px;
}

.intro-video {
     width: 100%;
    max-height: 300px;
    padding: 15px;
    background-color: var(--vt-c-white-mute);
    border-radius: 10px;
}

.add-video {
    display: inline-block;
    margin-top: 10px;
    cursor: pointer;
    padding: 5px 18px;
    font-size: 13px;
    border-radius: 7px;
    background-color: #007584;
    color: #ffffff;
    font-weight: 600;
}

.edit-bar {
 text-align: center;
    display: flex;
    gap: 3px;
    margin: 15px 0;
    justify-content: space-between;
}

.btn-edit {
  padding: 10px 18px;
  width: 100%;
  background: linear-gradient(135deg, #d8d8d8, #d8d8d8);
  color: #000000;
  border: none;
     border-radius: 7px;
  font-weight: 500;
  cursor: pointer;
}

.btn-cancel {
  padding: 10px 18px;
  background: #ddd;
  color: #333;
  width: 100%;
  border: none;
      border-radius: 7px;
  font-weight: 500;
  cursor: pointer;
}
.btn-logout {
  padding: 10px 18px;
  background: #ce0000;
      width: 145px;
  color: #ffffff;
  border: none;
      border-radius: 7px;
  font-weight: 600;
  cursor: pointer;
}

.introduction{
  color: rgb(30, 1, 75);
  font-family: math;
}
</style>
