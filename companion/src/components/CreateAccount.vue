<template>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <div class="signup-wrap">

    <!-- Progress Header - Glass style -->
    <div class="progress-header glass-progress">
      <div class="prog-top-row">
        <div class="step-badge glass-step-badge">
          <div class="badge-dot"></div>
          <span>{{ stepTitles[currentStep] }}</span>
        </div>
        <span class="step-counter">{{ currentStep + 1 }} <span class="of">of</span> {{ stepTitles.length }}</span>
      </div>
      <div class="prog-bar-wrap">
        <div class="prog-bar glass-prog-bar">
          <div class="prog-fill" :style="{ width: ((currentStep + 1) / stepTitles.length) * 100 + '%' }"></div>
        </div>
      </div>
    </div>

    <!-- Step Card - Glass Card -->
    <div class="step-card glass-card">

      <!-- Step Title -->
      <transition name="step-fade" mode="out-in">
        <div :key="currentStep" class="step-head">
          <h2 class="step-title glass-title">{{ stepHeadings[currentStep].title }}</h2>
          <!-- <p class="step-sub glass-sub">{{ stepHeadings[currentStep].sub }}</p> -->
        </div>
      </transition>

      <!-- Step Content -->
      <transition name="step-slide" mode="out-in">
        <div :key="currentStep" class="step-body">

          <!-- STEP 0: Contact Details -->
          <div v-if="currentStep === 0">
            <div class="field-group" :class="{ focused: f === 'mobile' }">
              <label class="fl glass-fl">Mobile number</label>
              <div class="phone-row">
                <div class="country-select">
                  <select v-model="form.countryCode" @focus="f='mobile'" @blur="f=''" class="glass-select">
                    <option value="+91">🇮🇳 +91</option>
                    <option value="+1">🇺🇸 +1</option>
                    <option value="+44">🇬🇧 +44</option>
                  </select>
                </div>
                <input type="tel" v-model="form.mobile" placeholder="00000 00000" @focus="f='mobile'" @blur="f=''" class="glass-input" />
              </div>
            </div>

            <div class="field-group" :class="{ focused: f === 'email' }">
              <label class="fl glass-fl">Email address</label>
              <input type="email" v-model="form.email" placeholder="you@example.com" @focus="f='email'" @blur="f=''" class="glass-input" />
            </div>

            <div class="two-col">
              <div class="field-group" :class="{ focused: f === 'first' }">
                <label class="fl glass-fl">First name</label>
                <input v-model="form.firstName" placeholder="Ajay" @focus="f='first'" @blur="f=''" class="glass-input" />
              </div>
              <div class="field-group" :class="{ focused: f === 'last' }">
                <label class="fl glass-fl">Last name</label>
                <input v-model="form.lastName" placeholder="Sharma" @focus="f='last'" @blur="f=''" class="glass-input" />
              </div>
            </div>
          </div>

          <!-- STEP 1: Location (Optional) -->
          <div v-if="currentStep === 1" class="center-step">
            <div class="loc-visual">
              <div class="loc-rings">
                <div class="loc-ring r1"></div>
                <div class="loc-ring r2"></div>
                <div class="loc-ring r3"></div>
              </div>
              <div class="loc-pin-wrap">
                <div class="loc-pin glass-pin">
                  <i class="fa fa-map-marker" style="font-size:22px;color:#fff"></i>
                </div>
              </div>
            </div>
            <p class="loc-note glass-loc-note">HeartLink shows you people nearby. Your location is <strong>never shared</strong> publicly.</p>
            <button
              class="loc-btn glass-loc-btn"
              @click="requestLocation"
              :disabled="locationLoading"
              :class="{ granted: locationGranted }"
            >
              <span v-if="locationLoading" class="btn-spinner white"></span>
              <i v-else-if="locationGranted" class="fa fa-check" style="font-size:16px"></i>
              <i v-else class="fa fa-map-marker" style="font-size:16px"></i>
              <span>{{ locationGranted ? 'Location enabled' : locationLoading ? 'Requesting...' : 'Allow location access' }}</span>
            </button>
            <button 
              v-if="!locationGranted" 
              class="skip-location-btn glass-skip-link" 
              @click="skipLocation"
            >
              Skip for now →
            </button>
            <p v-if="locationStatus" class="loc-status" :class="{ error: locationStatus.includes('denied') || locationStatus.includes('error') }">
              {{ locationStatus }}
            </p>
          </div>

          <!-- STEP 2: Profile Photo -->
          <div v-if="currentStep === 2" class="center-step">
            <div class="avatar-upload" @click="triggerCamera">
              <img v-if="selfiePreview" :src="selfiePreview" class="avatar-img" />
              <div v-else class="avatar-placeholder glass-avatar-placeholder">
                <i class="fa fa-camera" style="font-size:28px;color:#fd5068"></i>
                <span>Add photo</span>
              </div>
              <div v-if="selfiePreview" class="avatar-edit-badge glass-edit-badge">
                <i class="fa fa-pencil" style="font-size:10px;color:#fff"></i>
              </div>
            </div>

            <!-- Use simple file inputs that work better on Android -->
            <input type="file" accept="image/jpeg,image/png,image/jpg" hidden ref="cameraInput" @change="handleImage" />
            <input type="file" accept="image/jpeg,image/png,image/jpg" hidden ref="galleryInput" @change="handleImage" />

            <div class="upload-options">
              <button class="upload-opt-btn glass-upload-btn" @click="triggerCameraClick">
                <i class="fa fa-camera" style="font-size:14px"></i> Take selfie
              </button>
              <button class="upload-opt-btn glass-upload-btn" @click="triggerGalleryClick">
                <i class="fa fa-image" style="font-size:14px"></i> From gallery
              </button>
            </div>
          </div>

          <!-- STEP 3: Intro Video -->
          <div v-if="currentStep === 3" class="center-step">
            <div class="video-box glass-video-box">
              <video v-if="videoPreview" :src="videoPreview" controls class="video-player"></video>
              <div v-else class="video-placeholder">
                <i class="fa fa-video-camera" style="font-size:30px;color:#fd5068"></i>
                <p>Record a quick hello</p>
              </div>
            </div>

            <div class="tips-card glass-tips-card">
              <div class="tips-title">
                <i class="fa fa-lightbulb-o" style="font-size:13px;color:#fd5068"></i>
                Quick tips
              </div>
              <ul class="tips-list glass-tips-list">
                <li>Smile and say hello 👋</li>
                <li>Talk about your hobbies</li>
                <li>Keep it under 15 seconds</li>
              </ul>
            </div>

            <input type="file" accept="video/*" hidden ref="videoInput" @change="handleVideo" />

            <button class="btn-outline-pink full glass-outline-btn" @click="triggerVideoRecord">
              <i class="fa fa-video-camera" style="font-size:14px"></i>
              Record intro video
            </button>
            <button class="skip-link glass-skip-link" @click="skipVideo">Skip for now →</button>
          </div>

          <!-- STEP 4: Gender -->
          <div v-if="currentStep === 4">
            <div class="gender-grid">
              <div
                class="gender-card glass-gender-card"
                :class="{ active: form.gender === 'Male' }"
                @click="form.gender = 'Male'"
              >
                <div class="gender-icon male-icon">♂</div>
                <span class="gender-label">Male</span>
              </div>
              <div
                class="gender-card glass-gender-card"
                :class="{ active: form.gender === 'Female' }"
                @click="form.gender = 'Female'"
              >
                <div class="gender-icon female-icon">♀</div>
                <span class="gender-label">Female</span>
              </div>
            </div>
          </div>

          <!-- STEP 5: Date of Birth -->
          <div v-if="currentStep === 5">
            <div class="field-group" :class="{ focused: f === 'dob' }">
              <label class="fl glass-fl">Your date of birth</label>
              <input
                type="date"
                v-model="form.dob"
                :max="maxDOB"
                class="dob-input glass-input"
                @focus="f='dob'"
                @blur="f=''"
              />
            </div>
            <p class="field-hint-text glass-hint">You must be 18+ to use HeartLink</p>
          </div>

          <!-- STEP 6: Relationship Status -->
          <div v-if="currentStep === 6">
            <div class="status-grid">
              <div
                v-for="s in ['Single','Married','Divorced','Separated','Widowed']"
                :key="s"
                class="status-chip glass-status-chip"
                :class="{ active: form.status === s }"
                @click="form.status = s"
              >{{ s }}</div>
            </div>
          </div>

          <!-- STEP 7: Bio -->
          <div v-if="currentStep === 7">
            <div class="field-group" :class="{ focused: f === 'bio' }">
              <label class="fl glass-fl">Your bio tagline</label>
              <input v-model="form.subtitle" placeholder="e.g. Traveller • Coffee lover • Dog mom" @focus="f='bio'" @blur="f=''" class="glass-input" />
            </div>
            <div class="char-count glass-char" :class="{ warn: form.subtitle.length > 50 }">
              {{ form.subtitle.length }} / 60
            </div>
          </div>

          <!-- STEP 8: Habits -->
          <div v-if="currentStep === 8">
            <div class="field-group" :class="{ focused: f === 'habits' }">
              <label class="fl glass-fl">Your habits & interests</label>
              <input v-model="form.habits" placeholder="Gym, Reading, Traveling, Cooking..." @focus="f='habits'" @blur="f=''" class="glass-input" />
            </div>
            <p class="field-hint-text glass-hint">Separate each habit with a comma</p>

            <div class="habit-tags" v-if="habitList.length">
              <span v-for="h in habitList" :key="h" class="habit-tag glass-habit-tag">{{ h }}</span>
            </div>
          </div>

          <!-- STEP 9: Add Photos -->
          <div v-if="currentStep === 9">
            <div class="photo-grid">
              <div v-for="(img, i) in photos" :key="i" class="photo-thumb glass-photo-thumb">
                <img :src="img" />
                <button class="remove-photo glass-remove-photo" @click="removePhoto(i)">×</button>
              </div>
              <div
                class="photo-thumb add-thumb glass-add-thumb"
                @click="triggerMultiUpload"
                v-if="photos.length < 9"
              >
                <i class="fa fa-plus" style="font-size:20px;color:#fd5068"></i>
              </div>
            </div>
            <input type="file" multiple accept="image/jpeg,image/png,image/jpg" hidden ref="multiUpload" @change="handleMultiplePhotos" />
            <p class="field-hint-text glass-hint">Add up to 9 photos. First photo is your main profile picture.</p>
          </div>

          <!-- STEP 10: Address -->
          <div v-if="currentStep === 10">
            <div class="two-col">
              <div class="field-group" :class="{ focused: f === 'city' }">
                <label class="fl glass-fl">City</label>
                <input v-model="form.city" placeholder="Mumbai" @focus="f='city'" @blur="f=''" class="glass-input" />
              </div>
              <div class="field-group" :class="{ focused: f === 'pincode' }">
                <label class="fl glass-fl">Pincode</label>
                <input v-model="form.pincode" placeholder="400001" @focus="f='pincode'" @blur="f=''" class="glass-input" />
              </div>
            </div>
            <div class="field-group" :class="{ focused: f === 'address' }">
              <label class="fl glass-fl">Address</label>
              <input v-model="form.address" placeholder="Street, area..." @focus="f='address'" @blur="f=''" class="glass-input" />
            </div>
            <div class="field-group" :class="{ focused: f === 'state' }">
              <label class="fl glass-fl">State</label>
              <input v-model="form.state" placeholder="Maharashtra" @focus="f='state'" @blur="f=''" class="glass-input" />
            </div>
            <div class="field-group">
              <label class="fl glass-fl">Country</label>
              <input disabled value="India" class="input-disabled glass-input-disabled" />
            </div>
          </div>

        </div>
      </transition>
    </div>

    <!-- CROP MODAL -->
    <div v-if="showCropper" class="cropper-overlay">
      <div class="cropper-header glass-cropper-header">
        <button class="crop-cancel" @click="cancelCrop">Cancel</button>
        <span class="crop-title">Adjust Photo</span>
        <button class="crop-done" @click="confirmCrop">Done</button>
      </div>
      <div class="cropper-body">
        <img ref="cropperImage" :src="cropImageSrc" />
      </div>
    </div>

    <!-- TOAST -->
    <transition name="toast-pop">
      <div v-if="toast.show" class="toast-msg glass-toast" :class="{ success: !toast.isError, error: toast.isError }">
        <i :class="toast.isError ? 'fa fa-times-circle' : 'fa fa-check-circle'" style="font-size:16px"></i>
        <span>{{ toast.message }}</span>
        <span v-if="toast.seconds > 0" class="toast-countdown">{{ toast.seconds }}s</span>
      </div>
    </transition>

    <!-- Bottom Navigation - Glass style -->
    <div class="bottom-nav glass-bottom-nav">
      <button v-if="currentStep > 0" class="nav-back glass-nav-back" @click="currentStep--">
        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
          <path d="M11 4L6 9L11 14" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Back
      </button>
      <div v-else class="nav-back-spacer"></div>
      <button
        class="nav-next glass-nav-next"
        @click="nextStep"
        :disabled="!isStepValid || loading"
      >
        <span v-if="loading" class="btn-spinner white"></span>
        <span v-else>{{ currentStep === stepTitles.length - 1 ? 'Create Account 🎉' : 'Continue' }}</span>
        <svg v-if="!loading && currentStep < stepTitles.length - 1" width="16" height="16" viewBox="0 0 16 16" fill="none">
          <path d="M3 8H13M9 4L13 8L9 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.min.css'

export default {
  name: 'CreateAccount',

  data() {
    return {
      f: '',
      videoPreview: null,
      toast: { show: false, message: '', seconds: 0, timer: null, isError: false },
      cropper: null,
      showCropper: false,
      cropImageSrc: null,
      selectedImageFile: null,
      locationLoading: false,
      locationGranted: false,
      locationSkipped: false,
      locationStatus: '',
      loading: false,
      currentStep: 0,

      stepTitles: [
        'Contact Details', 'Location Access', 'Profile Photo',
        'Intro Video', 'Gender', 'Date of Birth',
        'Relationship Status', 'Bio', 'Habits & Interests',
        'More Photos', 'Address'
      ],

      stepHeadings: [
        { title: "Let's get started", sub: 'We need a few details to create your profile' },
        { title: 'Enable location', sub: 'We show you matches close to where you are' },
        { title: 'Your profile photo', sub: 'A great photo gets 5× more matches' },
        { title: 'Introduce yourself', sub: 'Profiles with videos get 3× more connections' },
        { title: 'I identify as', sub: 'This helps us find the right matches for you' },
        { title: 'When were you born?', sub: 'You must be 18 or older to use HeartLink' },
        { title: 'Relationship status', sub: 'Let others know where you stand' },
        { title: 'Tell us about you', sub: 'A short tagline to show on your profile' },
        { title: 'Your lifestyle', sub: 'Shared interests lead to better matches' },
        { title: 'Add more photos', sub: 'Profiles with more photos get noticed more' },
        { title: 'Your address', sub: 'Used to personalize your match radius' }
      ],

      selfiePreview: null,
      photos: [],

      form: {
        email: '', countryCode: '+91', mobile: '',
        firstName: '', lastName: '',
        gender: '', dob: '', status: '',
        subtitle: '', habits: '', about: '',
        city: '', address: '', pincode: '', state: '', country: 'India',
        latitude: '', longitude: '',
        profilePhoto: null, introVideo: null, gallery: []
      }
    }
  },

  computed: {
    maxDOB() {
      const d = new Date()
      d.setFullYear(d.getFullYear() - 18)
      return d.toISOString().split('T')[0]
    },

    habitList() {
      return this.form.habits
        .split(',')
        .map(h => h.trim())
        .filter(h => h.length > 0)
    },

    isStepValid() {
      switch (this.currentStep) {
        case 0: return this.form.email && this.form.mobile && this.form.firstName && this.form.lastName
        case 1: return this.locationGranted || this.locationSkipped
        case 2: return !!this.form.profilePhoto
        case 3: return true
        case 4: return !!this.form.gender
        case 5: {
          if (!this.form.dob) return false
          const age = Math.floor((Date.now() - new Date(this.form.dob)) / 31557600000)
          return age >= 18
        }
        case 6: return !!this.form.status
        case 7: return !!this.form.subtitle
        case 8: return !!this.form.habits
        case 9: return this.form.gallery.length > 0
        case 10: return this.form.city && this.form.address && this.form.pincode && this.form.state
        default: return true
      }
    }
  },

  mounted() {
    this.checkAndRequestPermissions()
  },

  methods: {
    checkAndRequestPermissions() {
      // Check if running in Android WebView
      const userAgent = navigator.userAgent || navigator.vendor || window.opera;
      if (/android/i.test(userAgent)) {
        console.log('Running on Android device')
      }
      
      // Try to request permissions if possible
      if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' }).then(result => {
          if (result.state === 'granted') {
            this.locationGranted = true
          }
        }).catch(err => {
          console.log('Permission query error:', err)
        })
      }
    },

    // Photo upload methods with better Android support
    triggerCamera() {
      this.triggerCameraClick()
    },

    triggerCameraClick() {
      // Create a temporary input for camera
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = 'image/jpeg,image/png,image/jpg'
      input.capture = 'environment'
      input.onchange = (e) => this.handleImage(e)
      input.click()
    },

    triggerGalleryClick() {
      this.$refs.galleryInput.click()
    },

    triggerVideoRecord() {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = 'video/*'
      input.capture = 'environment'
      input.onchange = (e) => this.handleVideo(e)
      input.click()
    },

    triggerMultiUpload() {
      this.$refs.multiUpload.click()
    },

    skipLocation() {
      this.locationSkipped = true
      this.locationGranted = false
      this.locationStatus = 'Location skipped - you can enable it later in settings'
      this.currentStep++
    },

    skipVideo() {
      this.form.introVideo = null
      this.videoPreview = null
      this.currentStep++
    },

    handleVideo(e) {
      const file = e.target.files[0]
      if (!file) {
        this.showToast('No video selected', false, true)
        return
      }
      
      // Check file size (max 50MB)
      if (file.size > 50 * 1024 * 1024) {
        this.showToast('Video too large. Please keep it under 50MB', false, true)
        return
      }
      
      this.form.introVideo = file
      this.videoPreview = URL.createObjectURL(file)
      e.target.value = ''
      this.showToast('Video added successfully!', false, false)
    },

    showToast(message, redirect = false, isError = false) {
      if (this.toast.timer) clearInterval(this.toast.timer)
      this.toast = { show: true, message, seconds: 5, timer: null, isError }
      this.toast.timer = setInterval(() => {
        this.toast.seconds--
        if (this.toast.seconds <= 0) {
          clearInterval(this.toast.timer)
          this.toast.show = false
          if (redirect) this.$router.push('/')
        }
      }, 1000)
    },

    requestLocation() {
      this.locationLoading = true
      this.locationStatus = 'Requesting location...'
      
      if (!navigator.geolocation) {
        this.locationStatus = 'Geolocation is not supported by your browser'
        this.locationLoading = false
        return
      }

      // For Android, use a more compatible approach
      const options = {
        enableHighAccuracy: false, // Set to false for better compatibility
        timeout: 15000,
        maximumAge: 60000 // Accept cached position for up to 1 minute
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          this.locationGranted = true
          this.locationSkipped = false
          this.locationStatus = '✓ Location access granted'
          this.form.latitude = position.coords.latitude
          this.form.longitude = position.coords.longitude
          this.locationLoading = false
          
          // Auto proceed after successful location
          setTimeout(() => {
            if (this.currentStep === 1) {
              this.currentStep++
            }
          }, 500)
        },
        (error) => {
          console.error('Geolocation error:', error)
          let errorMessage = ''
          
          switch(error.code) {
            case error.PERMISSION_DENIED:
              errorMessage = 'Location permission denied. You can skip or enable in settings.'
              break
            case error.POSITION_UNAVAILABLE:
              errorMessage = 'Location information unavailable. Please try again or skip.'
              break
            case error.TIMEOUT:
              errorMessage = 'Location request timed out. Please try again or skip.'
              break
            default:
              errorMessage = 'An unknown error occurred. You can skip location.'
          }
          
          this.locationStatus = errorMessage
          this.locationLoading = false
          this.locationGranted = false
        },
        options
      )
    },

    async nextStep() {
      if (this.currentStep < this.stepTitles.length - 1) {
        this.currentStep++
        return
      }

      this.loading = true
      const formData = new FormData()

      for (let key in this.form) {
        if (key === 'gallery') {
          this.form.gallery.forEach(f => formData.append('gallery[]', f))
        } else if (key === 'profilePhoto') {
          if (this.form.profilePhoto) formData.append('profile_photo', this.form.profilePhoto)
        } else if (key === 'introVideo') {
          if (this.form.introVideo) formData.append('introduction_video', this.form.introVideo)
        } else {
          formData.append(key, this.form[key] || '')
        }
      }
      formData.append('password', '123456')
      formData.append('location_skipped', this.locationSkipped)

      try {
        const response = await fetch('https://companion.ajaywatpade.in/api/register', {
          method: 'POST',
          body: formData
        })
        
        if (response.ok) {
          this.showToast('Account created successfully 🎉 Redirecting…', true)
        } else {
          const error = await response.json()
          this.showToast(error.message || 'Registration failed', false, true)
        }
      } catch (error) {
        console.error('Registration error:', error)
        this.showToast('Network error. Please check your connection.', false, true)
      } finally {
        this.loading = false
      }
    },

    handleImage(e) {
      const file = e.target.files[0]
      if (!file) {
        this.showToast('No file selected', false, true)
        return
      }
      
      // Check file size (max 10MB)
      if (file.size > 10 * 1024 * 1024) {
        this.showToast('Image too large. Please choose an image under 10MB', false, true)
        return
      }
      
      // Check if it's actually an image
      if (!file.type.startsWith('image/')) {
        this.showToast('Please select a valid image file', false, true)
        return
      }
      
      this.selectedImageFile = file
      this.cropImageSrc = URL.createObjectURL(file)
      this.showCropper = true
      
      this.$nextTick(() => {
        if (this.cropper) {
          this.cropper.destroy()
        }
        
        if (this.$refs.cropperImage) {
          this.cropper = new Cropper(this.$refs.cropperImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            background: false,
            guides: true,
            center: true,
            autoCropArea: 1,
            cropBoxResizable: true,
            cropBoxMovable: true,
            responsive: true,
          })
        }
      })
      
      e.target.value = ''
    },

    confirmCrop() {
      if (!this.cropper) return
      
      try {
        const canvas = this.cropper.getCroppedCanvas({ 
          width: 600, 
          height: 600, 
          imageSmoothingQuality: 'high' 
        })
        
        canvas.toBlob(blob => {
          if (blob) {
            this.form.profilePhoto = new File([blob], 'profile.jpg', { type: 'image/jpeg' })
            this.selfiePreview = URL.createObjectURL(blob)
            this.showCropper = false
            if (this.cropper) {
              this.cropper.destroy()
              this.cropper = null
            }
            this.showToast('Photo added successfully!', false, false)
          } else {
            this.showToast('Failed to process image', false, true)
          }
        }, 'image/jpeg', 0.9)
      } catch (error) {
        console.error('Crop error:', error)
        this.showToast('Error processing image', false, true)
        this.cancelCrop()
      }
    },

    cancelCrop() {
      this.showCropper = false
      if (this.cropper) {
        this.cropper.destroy()
        this.cropper = null
      }
      if (this.cropImageSrc) {
        URL.revokeObjectURL(this.cropImageSrc)
        this.cropImageSrc = null
      }
      this.selectedImageFile = null
    },

    handleMultiplePhotos(e) {
      const files = Array.from(e.target.files)
      if (files.length === 0) return
      
      const validFiles = files.filter(file => {
        if (!file.type.startsWith('image/')) {
          this.showToast(`${file.name} is not a valid image`, false, true)
          return false
        }
        if (file.size > 10 * 1024 * 1024) {
          this.showToast(`${file.name} is too large (max 10MB)`, false, true)
          return false
        }
        return true
      })
      
      validFiles.forEach(file => {
        this.photos.push(URL.createObjectURL(file))
        this.form.gallery.push(file)
      })
      
      e.target.value = ''
      
      if (validFiles.length > 0) {
        this.showToast(`${validFiles.length} photo(s) added!`, false, false)
      }
    },

    removePhoto(i) {
      if (this.photos[i]) {
        URL.revokeObjectURL(this.photos[i])
      }
      this.photos.splice(i, 1)
      this.form.gallery.splice(i, 1)
    }
  },

  beforeDestroy() {
    // Clean up object URLs to prevent memory leaks
    if (this.selfiePreview) {
      URL.revokeObjectURL(this.selfiePreview)
    }
    if (this.videoPreview) {
      URL.revokeObjectURL(this.videoPreview)
    }
    this.photos.forEach(photo => {
      URL.revokeObjectURL(photo)
    })
    if (this.cropImageSrc) {
      URL.revokeObjectURL(this.cropImageSrc)
    }
  }
}
</script>

<style scoped>
/* Keep all your existing styles, just update the glass-bottom-nav background */
.glass-bottom-nav {
  background: rgba(255, 255, 255, 0.95); /* Changed from transparent to white with opacity */
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(253, 80, 104, 0.15);
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.08);
}

/* Make sure inputs are visible */
.glass-select {
  background: rgba(255, 255, 255, 0.9); /* Increased opacity for better visibility */
}

/* Ensure buttons are clickable */
.upload-opt-btn, .loc-btn, .nav-next, .nav-back {
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}

/* Improve touch targets for mobile */
button, .gender-card, .status-chip, .photo-thumb {
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}



* { box-sizing: border-box; }

.signup-wrap {
  max-width: 420px;
  margin: 0 auto;
  font-family: 'Nunito', sans-serif;
  padding: 0 0 36px;
  /* background: #00000026; */
  /* min-height: 100vh; */
}

/* ========= GLASS PROGRESS HEADER ========= */
.progress-header {
  position: sticky;
  top: 0;
  z-index: 10;
  padding: 16px 14px 12px;
  border-bottom-left-radius: 28px;
  border-bottom-right-radius: 28px;
}

.glass-progress {
  display: none;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(253, 80, 104, 0.2);
}

.prog-top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.step-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  border-radius: 40px;
  padding: 5px 14px;
}

.glass-step-badge {
  background: rgba(253, 80, 104, 0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(253, 80, 104, 0.3);
}

.badge-dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: #fd5068;
}

.step-badge span {
  font-size: 12px;
  font-weight: 700;
  color: #fd5068;
}

.step-counter {
  font-size: 12px;
  font-weight: 700;
  color: rgba(0,0,0,0.5);
}

.step-counter .of { font-weight: 400; }

.prog-bar-wrap { margin-top: 4px; }

.prog-bar {
  height: 5px;
  border-radius: 10px;
  overflow: hidden;
}

.glass-prog-bar {
  background: rgba(0, 0, 0, 0.08);
}

.prog-fill {
  height: 100%;
  background: linear-gradient(90deg, #fd5068, #ff7854);
  border-radius: 10px;
  transition: width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* ========= GLASS STEP CARD ========= */
.step-card {
  /* margin: 16px 14px 0; */
  border-radius: 32px;
  padding: 24px 22px 28px;
}

.glass-card {
  /* background: rgba(255, 255, 255, 0.9); */
  /* backdrop-filter: blur(16px); */
  /* border: 1px solid rgba(255, 255, 255, 0.5); */
  /* box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08); */
}

.step-head { margin-bottom: 22px; }

.step-title {
  font-size: 22px;
  font-weight: 800;
  margin: 0 0 4px;
}

.glass-title {
  color: #1a1a2e;
  background: linear-gradient(135deg, #1a1a2e, #2d2d44);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.step-sub {
  font-size: 13px;
  margin: 0;
  font-weight: 500;
}

.glass-sub {
  color: rgba(255, 255, 255, 0.888);
}

/* ========= GLASS FIELD STYLES ========= */
.fl {
  display: block;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
  transition: color 0.2s;
}

.glass-fl {
  color: rgb(144, 140, 140);
}

.field-group { margin-bottom: 16px;  }
.field-group.focused .glass-fl { color: #fdfdfd; }

.glass-input {
  width: 100%;
  height: 48px;
  border-radius: 24px;
  border: 1.5px solid rgba(0, 0, 0, 0.1);
  background: rgba(255,255,255,0.12);
  padding: 0 16px;
  font-size: 14px;
  font-family: 'Nunito', sans-serif;
  color: #1a1a2e;
  outline: none;
  transition: all 0.25s;
  backdrop-filter: blur(4px);
}

.glass-input::placeholder { color: rgba(0, 0, 0, 0.35); }

.glass-input:focus {
  border-color: #fd5068;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 0 0 3px rgba(253, 80, 104, 0.15);
}

.glass-select {
  width: 100%;
  height: 48px;
  border-radius: 24px;
  border: 1.5px solid rgba(0, 0, 0, 0.1);
  background: rgb(124 124 127 / 21%);
  padding: 0 12px;
  font-size: 14px;
  font-family: 'Nunito', sans-serif;
  color: #1a1a2e;
  outline: none;
  cursor: pointer;
  backdrop-filter: blur(4px);
}

.glass-select:focus {
  border-color: #fd5068;
}

.input-disabled {
  background: rgba(0, 0, 0, 0.05) !important;
  color: rgba(0, 0, 0, 0.4) !important;
  cursor: not-allowed;
}

.phone-row {
  display: grid;
  grid-template-columns: 100px 1fr;
  gap: 10px;
}

.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.dob-input::-webkit-calendar-picker-indicator {
  filter: invert(0.4);
  cursor: pointer;
}

.field-hint-text {
  font-size: 11px;
  margin-top: -6px;
  margin-bottom: 12px;
}

.glass-hint {
  color: rgba(0, 0, 0, 0.45);
}

.char-count {
  font-size: 11px;
  text-align: right;
  margin-top: -8px;
}

.glass-char {
  color: rgba(0, 0, 0, 0.45);
}

.char-count.warn { color: #fd5068; }

/* ========= CENTER STEP ========= */
.center-step { text-align: center; }

/* Location */
.loc-visual {
  position: relative;
  width: 100px; height: 100px;
  margin: 0 auto 20px;
  display: flex; align-items: center; justify-content: center;
}

.loc-rings { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }

.loc-ring {
  position: absolute;
  border-radius: 50%;
  border: 1.5px solid rgba(253, 80, 104, 0.3);
  animation: ripple 2.5s ease-out infinite;
}

.r1 { width: 40px; height: 40px; animation-delay: 0s; }
.r2 { width: 65px; height: 65px; animation-delay: 0.5s; }
.r3 { width: 90px; height: 90px; animation-delay: 1s; }

@keyframes ripple {
  0% { transform: scale(0.8); opacity: 0.6; }
  100% { transform: scale(1.2); opacity: 0; }
}

.loc-pin-wrap { position: relative; z-index: 1; }

.glass-pin {
  width: 52px; height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, #fd5068, #ff7854);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 20px rgba(253, 80, 104, 0.4);
}

.loc-note {
  font-size: 13px;
  line-height: 1.5;
  margin-bottom: 20px;
}

.glass-loc-note {
  color: rgba(0, 0, 0, 0.65);
}

.loc-btn {
  width: 100%;
  height: 52px;
  border-radius: 30px;
  border: none;
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  font-family: 'Nunito', sans-serif;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  box-shadow: 0 6px 20px rgba(253, 80, 104, 0.35);
  transition: all 0.25s;
}

.loc-btn.granted {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.35);
}

.loc-btn:disabled { opacity: 0.7; cursor: not-allowed; }

.skip-location-btn {
  background: none;
  border: none;
  font-size: 14px;
  cursor: pointer;
  font-family: 'Nunito', sans-serif;
  margin-top: 16px;
  display: inline-block;
  width: auto;
  text-align: center;
  transition: all 0.2s;
  padding: 10px 20px;
  border-radius: 30px;
}

.glass-skip-link {
  color: rgba(0, 0, 0, 0.45);
}

.glass-skip-link:hover { 
  color: #fd5068; 
  background: rgba(253, 80, 104, 0.1);
}

.loc-status {
  font-size: 12px;
  color: #22c55e;
  margin-top: 12px;
  font-weight: 600;
}

.loc-status.error { color: #ef4444; }

/* Avatar Upload */
.avatar-upload {
  position: relative;
  width: 140px; height: 140px;
  border-radius: 50%;
  margin: 0 auto 20px;
  cursor: pointer;
  transition: transform 0.2s;
}

.avatar-upload:hover { transform: scale(1.02); }

.avatar-img {
  width: 100%; height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #fd5068;
  box-shadow: 0 4px 20px rgba(253, 80, 104, 0.3);
}

.avatar-placeholder {
  width: 100%; height: 100%;
  border-radius: 50%;
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
  font-size: 12px;
  font-weight: 700;
}

.glass-avatar-placeholder {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(8px);
  border: 2px dashed rgba(253, 80, 104, 0.5);
  color: #fd5068;
}

.avatar-edit-badge {
  position: absolute;
  bottom: 6px; right: 6px;
  width: 32px; height: 32px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}

.glass-edit-badge {
  background: #fd5068;
  border: 2px solid #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.upload-options { display: flex; flex-direction: column; gap: 12px; }

.glass-upload-btn {
  height: 48px;
  border-radius: 30px;
  border: 1.5px solid rgba(253, 80, 104, 0.3);
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(8px);
  color: #fd5068;
  font-size: 13px;
  font-weight: 700;
  font-family: 'Nunito', sans-serif;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: all 0.2s;
}

.glass-upload-btn:hover {
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: #fff;
  border-color: transparent;
  transform: translateY(-1px);
}

/* Video */
.video-box {
  width: 100%;
  height: 200px;
  border-radius: 24px;
  overflow: hidden;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 16px;
}

.glass-video-box {
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.3);
}

.video-player { width: 100%; height: 100%; object-fit: cover; }

.video-placeholder {
  text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 10px;
  font-size: 13px;
  color: rgba(0,0,0,0.5);
}

.tips-card {
  border-radius: 20px;
  padding: 14px 18px;
  margin-bottom: 16px;
  text-align: left;
}

.glass-tips-card {
  background: rgba(255, 245, 247, 0.8);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(253, 80, 104, 0.2);
}

.tips-title {
  font-size: 12px;
  font-weight: 700;
  color: #fd5068;
  margin-bottom: 8px;
  display: flex; align-items: center; gap: 6px;
}

.glass-tips-list {
  margin: 0; padding-left: 20px;
  font-size: 12px;
  color: rgba(0, 0, 0, 0.6);
  line-height: 1.7;
}

.btn-outline-pink {
  height: 48px;
  border-radius: 30px;
  font-size: 14px;
  font-weight: 700;
  font-family: 'Nunito', sans-serif;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: all 0.25s;
}

.glass-outline-btn {
  border: 2px solid #fd5068;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(8px);
  color: #fd5068;
}

.glass-outline-btn:hover {
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: #fff;
  border-color: transparent;
}

.btn-outline-pink.full { width: 100%; }

.skip-link {
  background: none;
  border: none;
  font-size: 13px;
  cursor: pointer;
  font-family: 'Nunito', sans-serif;
  margin-top: 14px;
  display: block;
  width: 100%;
  text-align: center;
  transition: all 0.2s;
}

.glass-skip-link {
  color: rgba(0, 0, 0, 0.45);
}

.glass-skip-link:hover { color: #fd5068; }

/* Gender */
.gender-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.glass-gender-card {
  padding: 24px 12px;
  border-radius: 28px;
  border: 2px solid rgba(0, 0, 0, 0.08);
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(8px);
  display: flex; flex-direction: column; align-items: center; gap: 12px;
  cursor: pointer;
  transition: all 0.25s;
}

.glass-gender-card:hover { border-color: #fd5068; background: rgba(255, 245, 247, 0.8); }

.glass-gender-card.active {
  border-color: #fd5068;
  background: linear-gradient(145deg, rgba(255, 240, 242, 0.9), rgba(255, 224, 229, 0.9));
  box-shadow: 0 4px 20px rgba(253, 80, 104, 0.2);
}

.gender-icon {
  width: 52px; height: 52px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 24px;
  font-weight: 700;
}

.male-icon { background: #e0eeff; color: #3b82f6; }
.female-icon { background: #ffe0e8; color: #fd5068; }

.gender-label { font-size: 15px; font-weight: 700; color: rgba(0,0,0,0.7); }
.glass-gender-card.active .gender-label { color: #fd5068; }

/* Status */
.status-grid { display: flex; flex-wrap: wrap; gap: 12px; }

.glass-status-chip {
  padding: 10px 20px;
  border-radius: 40px;
  border: 1.5px solid rgba(0, 0, 0, 0.1);
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(4px);
  font-size: 13px;
  font-weight: 700;
  color: rgba(0, 0, 0, 0.6);
  cursor: pointer;
  transition: all 0.2s;
  font-family: 'Nunito', sans-serif;
}

.glass-status-chip:hover { border-color: #fd5068; color: #fd5068; background: rgba(255, 245, 247, 0.8); }

.glass-status-chip.active {
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: #fff;
  border-color: transparent;
  box-shadow: 0 4px 14px rgba(253, 80, 104, 0.3);
}

/* Habit Tags */
.habit-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
}

.glass-habit-tag {
  background: rgba(255, 240, 242, 0.8);
  backdrop-filter: blur(4px);
  color: #fd5068;
  border: 1px solid rgba(253, 80, 104, 0.3);
  border-radius: 30px;
  padding: 5px 14px;
  font-size: 12px;
  font-weight: 700;
}

/* Photo Grid */
.photo-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 14px;
}

.photo-thumb {
  width: 100px; height: 100px;
  border-radius: 20px;
  overflow: hidden;
  position: relative;
}

.glass-photo-thumb {
  border: 2px solid rgba(0, 0, 0, 0.1);
  background: rgba(255, 255, 255, 0.5);
}

.photo-thumb img { width: 100%; height: 100%; object-fit: cover; }

.glass-add-thumb {
  background: rgba(255, 245, 247, 0.7);
  backdrop-filter: blur(8px);
  border: 2px dashed rgba(253, 80, 104, 0.4);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.glass-add-thumb:hover { border-color: #fd5068; background: rgba(255, 224, 229, 0.8); }

.glass-remove-photo {
  position: absolute;
  top: 6px; right: 6px;
  width: 26px; height: 26px;
  border-radius: 50%;
  background: rgba(253, 80, 104, 0.9);
  color: #fff;
  border: none;
  font-size: 16px;
  line-height: 1;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Nunito', sans-serif;
  backdrop-filter: blur(4px);
}

/* Bottom Nav - Glass */
.bottom-nav {
  position: fixed;
  bottom: 0; left: 50%;
  transform: translateX(-50%);
  width: 100%; max-width: 420px;
  padding: 12px 20px;
  /* border-top-left-radius: 28px;
  border-top-right-radius: 28px; */
  display: flex;
  gap: 14px;
  align-items: center;
  z-index: 20;
}

.glass-bottom-nav {
  background: rgba(255, 255, 255, 0);
  backdrop-filter: blur(20px);
  border-top: 1px solid rgba(253, 80, 104, 0.15);
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.08);
}

.nav-back {
  border: none;
  height: 39px;
  border-radius: 30px;
  padding: 0 20px;
  display: flex; align-items: center; gap: 6px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  font-family: 'Nunito', sans-serif;
  transition: all 0.2s;
  white-space: nowrap;
}

.glass-nav-back {
  background: rgba(0, 0, 0, 0.05);
  backdrop-filter: blur(4px);
  color: rgba(0, 0, 0, 0.6);
}

.glass-nav-back:hover { background: rgba(253, 80, 104, 0.15); color: #fd5068; }

.nav-back-spacer { width: 60px; }

.nav-next {
  flex: 1;
  height: 39px;
  border-radius: 30px;
  border: none;
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: #fff;
  font-size: 15px;
  font-weight: 700;
  font-family: 'Nunito', sans-serif;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  box-shadow: 0 6px 20px rgba(253, 80, 104, 0.35);
  transition: all 0.25s;
}

.nav-next:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(253, 80, 104, 0.45);
}

.nav-next:disabled { opacity: 0.5; cursor: not-allowed; box-shadow: none; transform: none; }

/* Spinner */
.btn-spinner {
  width: 18px; height: 18px;
  border: 2.5px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Cropper */
.cropper-overlay {
  position: fixed;
  inset: 0;
  background: #000;
  z-index: 9999;
  display: flex;
  flex-direction: column;
}

.glass-cropper-header {
  height: 60px;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 20px;
  border-bottom: 1px solid #222;
}

.crop-title { color: #fff; font-size: 15px; font-weight: 700; }

.crop-cancel, .crop-done {
  background: none; border: none;
  font-size: 14px; font-weight: 700;
  font-family: 'Nunito', sans-serif;
  cursor: pointer;
  padding: 8px 16px;
  border-radius: 8px;
}

.crop-cancel { color: #aaa; }
.crop-done { color: #22c55e; background: rgba(34, 197, 94, 0.1); }

.cropper-body {
  flex: 1;
  display: flex; align-items: center; justify-content: center;
}

.cropper-body img { max-width: 100%; max-height: 100%; }

/* Toast */
.toast-msg {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 14px 22px;
  border-radius: 40px;
  font-size: 13px;
  font-weight: 700;
  display: flex; align-items: center; gap: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  z-index: 99999;
  white-space: nowrap;
  font-family: 'Nunito', sans-serif;
}

.glass-toast {
  background: rgba(34, 197, 94, 0.95);
  backdrop-filter: blur(12px);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.2);
}

.glass-toast.error { background: rgba(239, 68, 68, 0.95); }

.toast-countdown { opacity: 0.8; font-weight: 400; }

.toast-pop-enter-active { animation: toastIn 0.35s ease; }
.toast-pop-leave-active { animation: toastIn 0.3s ease reverse; }
@keyframes toastIn {
  from { opacity: 0; transform: translate(-50%, -12px) scale(0.95); }
  to { opacity: 1; transform: translate(-50%, 0) scale(1); }
}

/* Step Transitions */
.step-fade-enter-active, .step-fade-leave-active { transition: all 0.25s ease; }
.step-fade-enter-from { opacity: 0; transform: translateY(6px); }
.step-fade-leave-to { opacity: 0; transform: translateY(-6px); }

.step-slide-enter-active, .step-slide-leave-active { transition: all 0.3s ease; }
.step-slide-enter-from { opacity: 0; transform: translateX(24px); }
.step-slide-leave-to { opacity: 0; transform: translateX(-24px); position: absolute; }
</style>