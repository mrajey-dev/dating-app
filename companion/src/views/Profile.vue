<template>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <div class="profile-page">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="gradient-bg"></div>
      <div class="floating-hearts">
        <span v-for="i in 6" :key="i" class="heart" :style="{ animationDelay: `${i * 0.5}s` }">❤️</span>
      </div>
    </div>

    <div class="profile-container">
      <!-- Profile Card with Glassmorphism -->
      <div class="profile-card glass-effect">
        
        <!-- Logout Button - Top Right Corner -->
        <button class="logout-btn-top" @click="showLogoutConfirm">
          <i class="fa fa-sign-out"></i>
          <span>Logout</span>
        </button>

        <!-- Cover Photo -->
        <div class="cover-photo">
          <div class="cover-overlay"></div>
        </div>

        <!-- Profile Header - Instagram Style -->
        <div class="profile-header">
          <div class="profile-photo-wrapper">
            <div class="profile-photo" @click="triggerProfilePhotoInput">
              <img :src="previewPhoto.profile_photo || user.profile_photo || '/default-avatar.png'" class="avatar" />
              <div class="photo-overlay">
                <i class="fa fa-camera"></i>
              </div>
              <input type="file" ref="profilePhotoInput" accept="image/*" @change="onProfilePhotoSelect" hidden />
            </div>
            <div class="online-indicator" v-if="user.is_online"></div>
          </div>

          <div class="profile-info">
            <div class="name-section">
              <h2 class="username">
                {{ user.first_name }} {{ user.last_name }}
                <img v-if="user.verified_badge" src="/verified1.png" alt="Verified" class="verified-icon" />
              </h2>
              <button class="icon-btn edit-btn" @click="toggleEditForm">
                <i class="fa fa-edit"></i>
              </button>
            </div>

            <div class="profile-stats">
              <div class="stat">
                <div class="count">{{ user.photo_gallery ? user.photo_gallery.length : 0 }}</div>
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

        <!-- Bio Section -->
        <div class="bio-section" v-if="user.subtitle">
          <i class="fa fa-quote-left"></i>
          <p>{{ user.subtitle }}</p>
        </div>

        <!-- Introduction Video -->
        <div class="video-section">
          <h3 class="section-title">
            <i class="fa fa-video-camera"></i> Introduction Video
          </h3>
          <div class="video-container">
            <video v-if="previewVideo" :src="previewVideo" controls class="intro-video"></video>
            <label class="upload-video-btn" v-else>
              <i class="fa fa-cloud-upload"></i> Upload Introduction Video
              <input type="file" accept="video/*" @change="onVideoSelect" hidden />
            </label>
            <label class="upload-video-btn small" v-if="previewVideo">
              <i class="fa fa-exchange"></i> Change Video
              <input type="file" accept="video/*" @change="onVideoSelect" hidden />
            </label>
          </div>
        </div>

        <!-- Photo Gallery -->
        <div class="gallery-section">
          <h3 class="section-title">
            <i class="fa fa-picture-o"></i> Photo Gallery
            <span class="gallery-count">{{ previewPhoto.photo_gallery?.length || 0 }}/12</span>
          </h3>
          <div class="gallery-grid">
            <div v-for="(photo, i) in previewPhoto.photo_gallery" :key="i" class="gallery-item" @click="openLightbox(i)">
              <img :src="photo" class="gallery-img" />
              <div class="gallery-overlay">
                <button class="remove-btn" @click.stop="showRemovePhotoConfirm(i)">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </div>
            <label class="add-photo-btn" v-if="(previewPhoto.photo_gallery?.length || 0) < 12">
              <i class="fa fa-plus"></i>
              <span>Add Photo</span>
              <input type="file" multiple accept="image/*" @change="e => onFileChange('photo_gallery', e)" hidden />
            </label>
          </div>
        </div>

        <!-- Edit Form with Slide Animation -->
        <div ref="editFormContainer" class="edit-form-container" v-show="showPersonalDetails">
          <div class="form-header">
            <h3><i class="fa fa-user-circle"></i> Edit Profile</h3>
            <button class="close-form" @click="closeEditForm">✕</button>
          </div>
          <form class="profile-form" @submit.prevent="updateProfile">
            <div class="form-row">
              <div class="form-group">
                <label><i class="fa fa-user"></i> First Name</label>
                <input v-model="user.first_name" required placeholder="Enter first name" />
              </div>
              <div class="form-group">
                <label><i class="fa fa-user"></i> Last Name</label>
                <input v-model="user.last_name" required placeholder="Enter last name" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fa fa-envelope"></i> Email</label>
                <input type="email" v-model="user.email" required placeholder="your@email.com" />
              </div>
              <div class="form-group">
                <label><i class="fa fa-phone"></i> Phone</label>
                <input v-model="user.phone_number" placeholder="Phone number" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fa fa-lock"></i> Password</label>
                <input type="password" v-model="user.password" placeholder="Leave blank to keep current" />
              </div>
              <div class="form-group">
                <label><i class="fa fa-venus-mars"></i> Gender</label>
                <select v-model="user.gender" disabled>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label><i class="fa fa-calendar"></i> Date of Birth</label>
              <input type="date" v-model="user.dob" :max="maxDate" />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fa fa-globe"></i> Country</label>
                <input v-model="user.country" placeholder="Country" />
              </div>
              <div class="form-group">
                <label><i class="fa fa-map-marker"></i> State</label>
                <input v-model="user.state" placeholder="State" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fa fa-building"></i> City</label>
                <input v-model="user.city" placeholder="City" />
              </div>
              <div class="form-group">
                <label><i class="fa fa-map-pin"></i> Pin Code</label>
                <input v-model="user.pin_code" placeholder="Pin code" />
              </div>
            </div>

            <div class="form-group">
              <label><i class="fa fa-location-arrow"></i> Address</label>
              <textarea v-model="user.address" rows="3" placeholder="Your address"></textarea>
            </div>

            <div class="form-group">
              <label><i class="fa fa-comment"></i> Bio</label>
              <input v-model="user.subtitle" placeholder="Write something about yourself" />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label><i class="fa fa-heart"></i> Relationship Status</label>
                <select v-model="user.status">
                  <option disabled value="">Select status</option>
                  <option>Single</option>
                  <option>Married</option>
                  <option>Divorced</option>
                  <option>Separated</option>
                  <option>Widowed</option>
                </select>
              </div>
              <div class="form-group toggle-group">
                <label class="toggle-label">
                  <i class="fa fa-shield"></i> Verified Badge
                  <img v-if="user.verified_badge" src="/verified1.png" alt="Badge" class="verified-icon" />
                </label>
                <div class="toggle-switch">
                  <input type="checkbox" id="verifiedToggle" v-model="user.verified_badge" />
                  <label for="verifiedToggle" class="toggle-slider"></label>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label><i class="fa fa-heartbeat"></i> Habits / Hobbies</label>
              <input v-model="habitsText" placeholder="Enter habits separated by commas (e.g., Reading, Travel, Music)" />
              <div class="habits-preview">
                <span v-for="(h, i) in habitsText.split(',').map(h => h.trim()).filter(h => h)" :key="i" class="habit-chip">
                  {{ h }}
                </span>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-save">
                <i class="fa fa-save"></i> Save Changes
              </button>
              <button type="button" class="btn-cancel-form" @click="closeEditForm">
                <i class="fa fa-times"></i> Cancel
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>

    <!-- Lightbox Modal for Gallery -->
    <transition name="modal-fade">
      <div v-if="lightboxOpen" class="lightbox-modal" @click="closeLightbox">
        <div class="lightbox-content" @click.stop>
          <img :src="previewPhoto.photo_gallery[lightboxIndex]" class="lightbox-image" />
          <button class="lightbox-close" @click="closeLightbox">✕</button>
          <button class="lightbox-prev" @click="prevImage" v-if="previewPhoto.photo_gallery?.length > 1">‹</button>
          <button class="lightbox-next" @click="nextImage" v-if="previewPhoto.photo_gallery?.length > 1">›</button>
        </div>
      </div>
    </transition>

    <!-- Toast Notification -->
    <transition name="toast-fade">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <i :class="toast.type === 'success' ? 'fa fa-check-circle' : 'fa fa-exclamation-circle'"></i>
        {{ toast.message }}
      </div>
    </transition>

    <!-- Custom Confirmation Modal -->
    <transition name="modal-fade">
      <div v-if="confirmModal.show" class="confirm-modal-overlay" @click.self="closeConfirmModal">
        <div class="confirm-modal">
          <div class="confirm-modal-header" :class="confirmModal.type">
            <i :class="confirmModal.type === 'danger' ? 'fa fa-exclamation-triangle' : 'fa fa-question-circle'"></i>
            <h3>{{ confirmModal.title }}</h3>
          </div>
          <div class="confirm-modal-body">
            <p>{{ confirmModal.message }}</p>
          </div>
          <div class="confirm-modal-footer">
            <button class="confirm-btn cancel" @click="cancelConfirmAction">
              <i class="fa fa-times"></i> {{ confirmModal.cancelText || 'Cancel' }}
            </button>
            <button class="confirm-btn confirm" :class="confirmModal.type" @click="executeConfirmAction">
              <i class="fa fa-check"></i> {{ confirmModal.confirmText || 'Confirm' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Cropper Modal -->
    <div v-if="showCropper" class="cropper-modal-overlay" @click.self="closeCropper">
      <div class="cropper-container">
        <div class="cropper-header">
          <button @click="closeCropper">Cancel</button>
          <span>Edit Profile Photo</span>
          <button class="done" @click="cropAndUpload">Done</button>
        </div>
        <div class="cropper-body">
          <img ref="cropImage" :src="cropImageUrl" />
        </div>
        <div class="cropper-footer">
          <i class="fa fa-hand-pointer-o"></i> Drag & Zoom to adjust
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios"
import Cropper from "cropperjs"
import "cropperjs/dist/cropper.min.css"
import '@fortawesome/fontawesome-free/css/all.min.css'
import BottomNavBar from '@/components/BottomNavBar.vue'

export default {
  components: { BottomNavBar },
  data() {
    return {
      showPersonalDetails: false,
      previewVideo: null,
      lightboxOpen: false,
      lightboxIndex: 0,
      showCropper: false,
      cropper: null,
      cropImageUrl: null,
      selectedProfileFile: null,
      toast: {
        show: false,
        message: "",
        type: "success",
      },
      confirmModal: {
        show: false,
        title: "",
        message: "",
        type: "warning",
        confirmText: "",
        cancelText: "",
        onConfirm: null,
        onCancel: null,
      },
      pendingRemovePhotoIndex: null,
      user: {
        first_name: "",
        last_name: "",
        gender: "",
        dob: "",
        email: "",
        phone_number: "",
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
        habits: [],
        status: "",
        followers: 0,
        following: 0,
        is_online: true,
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
      verified_badge: storedUser.verified_badge === 1 || storedUser.verified_badge === "1" || storedUser.verified_badge === true,
      followers: Number(storedUser.followers_count || 0),
      following: Number(storedUser.following_count || 0),
    }

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
    }

    if (storedUser.photo_gallery) {
      let galleryArr = []
      if (typeof storedUser.photo_gallery === "string") {
        try {
          galleryArr = JSON.parse(storedUser.photo_gallery)
        } catch (e) {
          galleryArr = []
        }
      } else if (Array.isArray(storedUser.photo_gallery)) {
        galleryArr = storedUser.photo_gallery
      }
      this.previewPhoto.photo_gallery = galleryArr.map(p =>
        p.startsWith("http") ? p : `https://companion.ajaywatpade.in/dating-backend/public/storage/${p.replace("gallery\\", "gallery/")}`
      )
      this.user.photo_gallery = galleryArr
    }
  },
  methods: {
    // Custom Confirmation Modal Methods
    showConfirmModal(options) {
      return new Promise((resolve) => {
        this.confirmModal = {
          show: true,
          title: options.title || "Confirm Action",
          message: options.message || "Are you sure?",
          type: options.type || "warning",
          confirmText: options.confirmText || "Confirm",
          cancelText: options.cancelText || "Cancel",
          onConfirm: () => {
            this.confirmModal.show = false
            resolve(true)
          },
          onCancel: () => {
            this.confirmModal.show = false
            resolve(false)
          },
        }
      })
    },

    closeConfirmModal() {
      if (this.confirmModal.onCancel) {
        this.confirmModal.onCancel()
      }
      this.confirmModal.show = false
    },

    cancelConfirmAction() {
      if (this.confirmModal.onCancel) {
        this.confirmModal.onCancel()
      }
      this.confirmModal.show = false
    },

    executeConfirmAction() {
      if (this.confirmModal.onConfirm) {
        this.confirmModal.onConfirm()
      }
    },

    async showLogoutConfirm() {
      const confirmed = await this.showConfirmModal({
        title: "Logout",
        message: "Are you sure you want to logout?",
        type: "warning",
        confirmText: "Logout",
        cancelText: "Stay",
      })
      if (confirmed) {
        this.logout()
      }
    },

    async showRemovePhotoConfirm(index) {
      this.pendingRemovePhotoIndex = index
      const confirmed = await this.showConfirmModal({
        title: "Remove Photo",
        message: "Are you sure you want to remove this photo from your gallery?",
        type: "danger",
        confirmText: "Remove",
        cancelText: "Cancel",
      })
      if (confirmed) {
        await this.removeGalleryPhoto(this.pendingRemovePhotoIndex)
      }
      this.pendingRemovePhotoIndex = null
    },

    // Toggle edit form with auto-scroll
    toggleEditForm() {
      if (this.showPersonalDetails) {
        this.closeEditForm()
      } else {
        this.showPersonalDetails = true
        this.$nextTick(() => {
          this.scrollToEditForm()
        })
      }
    },
    
    // Close edit form
    closeEditForm() {
      this.showPersonalDetails = false
    },
    
    // Auto-scroll to edit form with smooth animation
    scrollToEditForm() {
      const editForm = this.$refs.editFormContainer
      if (editForm) {
        const elementPosition = editForm.getBoundingClientRect().top
        const offsetPosition = elementPosition + window.pageYOffset - 80
        
        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        })
        
        editForm.style.transition = 'all 0.3s ease'
        editForm.style.boxShadow = '0 0 0 3px #fd5068'
        setTimeout(() => {
          editForm.style.boxShadow = ''
        }, 1000)
      }
    },
    
    openLightbox(index) {
      this.lightboxIndex = index
      this.lightboxOpen = true
    },
    closeLightbox() {
      this.lightboxOpen = false
    },
    prevImage() {
      if (this.lightboxIndex > 0) {
        this.lightboxIndex--
      } else {
        this.lightboxIndex = this.previewPhoto.photo_gallery.length - 1
      }
    },
    nextImage() {
      if (this.lightboxIndex < this.previewPhoto.photo_gallery.length - 1) {
        this.lightboxIndex++
      } else {
        this.lightboxIndex = 0
      }
    },
    async onVideoSelect(e) {
      const token = localStorage.getItem("token")
      const file = e.target.files[0]
      if (!file) return

      if (file.size > 50 * 1024 * 1024) {
        this.showToast("Video must be under 50MB", "error")
        return
      }

      const formData = new FormData()
      formData.append("introduction_video", file)

      try {
        const res = await axios.post("https://companion.ajaywatpade.in/api/profile/upload-video", formData, {
          headers: { Authorization: `Bearer ${token}` }
        })
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
    async removeGalleryPhoto(index) {
      const token = localStorage.getItem("token")
      const removedPhoto = this.user.photo_gallery[index]

      this.previewPhoto.photo_gallery.splice(index, 1)
      this.user.photo_gallery.splice(index, 1)

      try {
        const res = await axios.post("https://companion.ajaywatpade.in/api/profile/remove-gallery-photo",
          { photo: removedPhoto },
          { headers: { Authorization: `Bearer ${token}` } }
        )
        localStorage.setItem("user", JSON.stringify(res.data))
        this.showToast("Photo removed successfully")
      } catch (err) {
        console.error(err)
        this.showToast("Failed to remove photo", "error")
        // Revert on error
        this.previewPhoto.photo_gallery.splice(index, 0, removedPhoto)
        this.user.photo_gallery.splice(index, 0, removedPhoto)
      }
    },
    logout() {
      localStorage.removeItem("token")
      localStorage.removeItem("user")
      window.location.href = "/"
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

      const canvas = this.cropper.getCroppedCanvas({ width: 512, height: 512, imageSmoothingQuality: "high" })
      canvas.toBlob(async (blob) => {
        const formData = new FormData()
        formData.append("profile_photo", blob, "profile.jpg")

        try {
          const res = await axios.post("https://companion.ajaywatpade.in/api/profile/upload-profile-photo", formData, {
            headers: { Authorization: `Bearer ${token}` }
          })
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
      setTimeout(() => { this.toast.show = false }, 3000)
    },
    async onFileChange(field, e) {
      const token = localStorage.getItem("token")
      if (field === "photo_gallery") {
        const files = Array.from(e.target.files).slice(0, 5)
        if (!files.length) return

        const previews = files.map(f => URL.createObjectURL(f))
        this.previewPhoto.photo_gallery.push(...previews)

        const formData = new FormData()
        files.forEach(file => formData.append("photo_gallery[]", file))

        try {
          const res = await axios.post("https://companion.ajaywatpade.in/api/profile/upload-gallery", formData, {
            headers: { Authorization: `Bearer ${token}` }
          })
          this.user.photo_gallery = res.data.photo_gallery
          localStorage.setItem("user", JSON.stringify(res.data))
          this.showToast("Gallery photos added")
        } catch (err) {
          console.error(err)
          this.showToast("Failed to upload gallery images", "error")
        }
      }
    },
    async updateProfile() {
      const token = localStorage.getItem("token")
      if (!token) return

      this.user.habits = this.habitsText.split(",").map(h => h.trim()).filter(Boolean)

      const formData = new FormData()
      Object.keys(this.user).forEach(key => {
        if (["profile_photo", "photo_gallery"].includes(key)) return
        if (key === "verified_badge") {
          formData.append(key, this.user.verified_badge ? 1 : 0)
        } else if (Array.isArray(this.user[key])) {
          this.user[key].forEach(value => formData.append(`${key}[]`, value))
        } else {
          formData.append(key, this.user[key] ?? "")
        }
      })

      try {
        const res = await axios.post("https://companion.ajaywatpade.in/api/profile/update", formData, {
          headers: { Authorization: `Bearer ${token}` }
        })
        localStorage.setItem("user", JSON.stringify(res.data))
        this.showToast("Profile updated successfully")
        this.closeEditForm()
      } catch (err) {
        console.error(err)
        this.showToast("Failed to update profile", "error")
      }
    },
  },
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.profile-page {
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  padding: 20px;
}

/* Animated Background */
.animated-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 0;
}

.gradient-bg {
  position: absolute;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at 20% 50%, rgba(255, 107, 139, 0.15) 0%, transparent 50%);
  animation: rotate 20s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.floating-hearts {
  position: absolute;
  width: 100%;
  height: 100%;
}

.heart {
  position: absolute;
  font-size: 20px;
  opacity: 0.25;
  animation: floatHeart 10s infinite ease-in-out;
}

.heart:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
.heart:nth-child(2) { top: 70%; left: 85%; animation-delay: 2s; }
.heart:nth-child(3) { top: 40%; left: 20%; animation-delay: 4s; }
.heart:nth-child(4) { top: 80%; left: 30%; animation-delay: 6s; }
.heart:nth-child(5) { top: 20%; left: 75%; animation-delay: 8s; }
.heart:nth-child(6) { top: 60%; left: 50%; animation-delay: 3s; }

@keyframes floatHeart {
  0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.2; }
  50% { transform: translateY(-40px) rotate(10deg); opacity: 0.4; }
}

.profile-container {
  position: relative;
  z-index: 1;
  max-width: 600px;
  margin: 0 auto;
}

/* Glass Effect Card */
.glass-effect {
  background: rgba(255, 255, 255, 0.96);
  backdrop-filter: blur(10px);
  border-radius: 30px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: slideUp 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1);
  position: relative;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Logout Button - Top Right Corner */
.logout-btn-top {
  position: absolute;
  top: 15px;
  right: 15px;
  z-index: 10;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.95);
  border: none;
  border-radius: 40px;
  font-weight: 600;
  color: #ff4444;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  font-size: 14px;
}

.logout-btn-top i {
  font-size: 14px;
}

.logout-btn-top:hover {
  background: #ff4444;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
}

/* Cover Photo */
.cover-photo {
  height: 140px;
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  position: relative;
}

.cover-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.3));
}

/* Profile Header */
.profile-header {
  padding: 0 20px 20px;
  position: relative;
  margin-top: -50px;
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.profile-photo-wrapper {
  position: relative;
}

.profile-photo {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 4px solid white;
  box-shadow: 0 5px 20px rgba(0,0,0,0.15);
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: transform 0.3s;
}

.profile-photo:hover {
  transform: scale(1.03);
}

.profile-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
  color: white;
  font-size: 24px;
}

.profile-photo:hover .photo-overlay {
  opacity: 1;
}

.online-indicator {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 16px;
  height: 16px;
  background: #4cd964;
  border-radius: 50%;
  border: 2px solid white;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(76, 217, 100, 0.7); }
  50% { box-shadow: 0 0 0 6px rgba(76, 217, 100, 0); }
}

.profile-info {
  flex: 1;
}

.name-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  flex-wrap: wrap;
  gap: 10px;
}

.username {
  font-size: 20px;
  font-weight: 700;
  color: #1a1a2e;
  display: flex;
  align-items: center;
  gap: 5px;
}

.verified-icon {
  width: 18px;
  height: 18px;
}

.edit-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  color: white;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.edit-btn:hover {
  transform: scale(1.08);
  box-shadow: 0 5px 15px rgba(253,80,104,0.4);
}

.profile-stats {
  display: flex;
  gap: 30px;
}

.stat {
  text-align: center;
}

.stat .count {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a2e;
}

.stat .label {
  font-size: 12px;
  color: #666;
}

/* Bio Section */
.bio-section {
  padding: 0 20px 20px;
  display: flex;
  gap: 10px;
  align-items: flex-start;
  color: #666;
  font-size: 14px;
}

.bio-section i {
  color: #fd5068;
  margin-top: 2px;
}

/* Section Titles */
.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #1a1a2e;
  margin-bottom: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-title i {
  color: #fd5068;
}

.gallery-count {
  margin-left: auto;
  font-size: 12px;
  font-weight: normal;
  color: #999;
}

/* Video Section */
.video-section {
  padding: 20px;
  border-top: 1px solid rgba(0,0,0,0.05);
  border-bottom: 1px solid rgba(0,0,0,0.05);
}

.video-container {
  position: relative;
}

.intro-video {
  width: 100%;
  border-radius: 15px;
  max-height: 300px;
}

.upload-video-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 15px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border-radius: 15px;
  cursor: pointer;
  transition: all 0.3s;
  font-weight: 600;
}

.upload-video-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102,126,234,0.3);
}

.upload-video-btn.small {
  margin-top: 10px;
  background: #f0f0f0;
  color: #666;
}

/* Gallery Section */
.gallery-section {
  padding: 20px;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.gallery-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 15px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.3s;
}

.gallery-item:hover {
  transform: scale(1.02);
}

.gallery-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.gallery-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s;
}

.gallery-item:hover .gallery-overlay {
  opacity: 1;
}

.remove-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #ff4444;
  border: none;
  color: white;
  cursor: pointer;
  transition: all 0.3s;
}

.remove-btn:hover {
  transform: scale(1.1);
}

.add-photo-btn {
  aspect-ratio: 1;
  border-radius: 15px;
  background: #f5f5f5;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.3s;
  border: 2px dashed #ddd;
}

.add-photo-btn:hover {
  background: #fd5068;
  color: white;
  border-color: #fd5068;
}

.add-photo-btn i {
  font-size: 24px;
}

.add-photo-btn span {
  font-size: 12px;
}

/* Edit Form */
.edit-form-container {
  padding: 20px;
  background: #f8f9fa;
  border-top: 1px solid rgba(0,0,0,0.05);
  animation: slideUp 0.4s ease;
  scroll-margin-top: 80px;
}

.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.form-header h3 {
  font-size: 18px;
  color: #1a1a2e;
}

.close-form {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: #ddd;
  cursor: pointer;
  transition: all 0.3s;
}

.close-form:hover {
  background: #fd5068;
  color: white;
  transform: rotate(90deg);
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.form-group label {
  font-size: 12px;
  font-weight: 600;
  color: #666;
  display: flex;
  align-items: center;
  gap: 5px;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 12px;
  font-size: 14px;
  transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #fd5068;
  box-shadow: 0 0 0 3px rgba(253,80,104,0.1);
}

/* Toggle Switch */
.toggle-group {
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
}

.toggle-switch {
  position: relative;
  width: 50px;
  height: 26px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  inset: 0;
  background: #ddd;
  border-radius: 30px;
  cursor: pointer;
  transition: 0.3s;
}

.toggle-slider::before {
  content: "";
  position: absolute;
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background: white;
  border-radius: 50%;
  transition: 0.3s;
}

.toggle-switch input:checked + .toggle-slider {
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
}

.toggle-switch input:checked + .toggle-slider::before {
  transform: translateX(24px);
}

/* Habits Preview */
.habits-preview {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.habit-chip {
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 11px;
  animation: chipPop 0.3s ease;
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

/* Form Actions */
.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 10px;
}

.btn-save,
.btn-cancel-form {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-save {
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  color: white;
}

.btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(253,80,104,0.4);
}

.btn-cancel-form {
  background: #f0f0f0;
  color: #666;
}

.btn-cancel-form:hover {
  background: #e0e0e0;
}

/* Lightbox Modal */
.lightbox-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.95);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.lightbox-content {
  position: relative;
  max-width: 90%;
  max-height: 90%;
}

.lightbox-image {
  max-width: 100%;
  max-height: 90vh;
  border-radius: 10px;
}

.lightbox-close,
.lightbox-prev,
.lightbox-next {
  position: absolute;
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  transition: all 0.3s;
}

.lightbox-close {
  top: 20px;
  right: 20px;
}

.lightbox-prev {
  left: 20px;
  top: 50%;
  transform: translateY(-50%);
}

.lightbox-next {
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
}

.lightbox-close:hover,
.lightbox-prev:hover,
.lightbox-next:hover {
  background: rgba(255,255,255,0.4);
  transform: scale(1.1);
}

.lightbox-prev:hover,
.lightbox-next:hover {
  transform: translateY(-50%) scale(1.1);
}

/* Custom Confirmation Modal */
.confirm-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  z-index: 10003;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.confirm-modal {
  background: white;
  border-radius: 24px;
  width: 90%;
  max-width: 380px;
  overflow: hidden;
  animation: modalPop 0.3s cubic-bezier(0.34, 1.2, 0.64, 1);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

@keyframes modalPop {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.confirm-modal-header {
  padding: 20px 20px 12px;
  text-align: center;
}

.confirm-modal-header i {
  font-size: 48px;
  margin-bottom: 12px;
  display: block;
}

.confirm-modal-header.warning i {
  color: #ff9800;
}

.confirm-modal-header.danger i {
  color: #ff4444;
}

.confirm-modal-header.success i {
  color: #4caf50;
}

.confirm-modal-header h3 {
  font-size: 20px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}

.confirm-modal-body {
  padding: 0 20px 20px;
  text-align: center;
}

.confirm-modal-body p {
  color: #666;
  font-size: 14px;
  line-height: 1.5;
  margin: 0;
}

.confirm-modal-footer {
  display: flex;
  gap: 12px;
  padding: 16px 20px 20px;
  border-top: 1px solid #f0f0f0;
}

.confirm-btn {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 40px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.confirm-btn.cancel {
  background: #f5f5f5;
  color: #666;
}

.confirm-btn.cancel:hover {
  background: #e0e0e0;
}

.confirm-btn.confirm {
  background: linear-gradient(135deg, #fd5068, #ff8a5c);
  color: white;
}

.confirm-btn.confirm:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(253, 80, 104, 0.3);
}

.confirm-btn.confirm.danger {
  background: linear-gradient(135deg, #ff4444, #ff6b6b);
}

.confirm-btn.confirm.danger:hover {
  box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
}

/* Cropper Modal */
.cropper-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.95);
  z-index: 10001;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cropper-container {
  width: 100%;
  max-width: 500px;
  background: #000;
  border-radius: 20px;
  overflow: hidden;
}

.cropper-header {
  display: flex;
  justify-content: space-between;
  padding: 15px 20px;
  background: #1a1a1a;
  color: white;
}

.cropper-header button {
  background: none;
  border: none;
  color: #fd5068;
  font-weight: 600;
  cursor: pointer;
}

.cropper-body {
  height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cropper-footer {
  padding: 15px;
  text-align: center;
  color: #888;
  font-size: 12px;
  background: #1a1a1a;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  font-weight: 600;
  z-index: 10002;
  display: flex;
  align-items: center;
  gap: 10px;
  animation: toastSlide 0.3s ease;
  border-radius: 50px;
}

.toast.success {
  background: linear-gradient(135deg, #28a745, #20c997);
  color: white;
}

.toast.error {
  background: linear-gradient(135deg, #dc3545, #fd7e7e);
  color: white;
}

@keyframes toastSlide {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.3s;
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(20px);
}

/* Slide Up Animation */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Modal Fade */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 600px) {
  .profile-page {
    padding: 10px;
  }
  
  .profile-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  
  .profile-stats {
    justify-content: center;
  }
  
  .name-section {
    justify-content: center;
  }
  
  .bio-section {
    text-align: center;
    justify-content: center;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .gallery-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .logout-btn-top {
    padding: 6px 12px;
    font-size: 12px;
  }
  
  .logout-btn-top span {
    display: none;
  }
  
  .logout-btn-top i {
    font-size: 16px;
  }
  
  .confirm-modal {
    width: 85%;
    margin: 20px;
  }
}
</style>