<template>
  <div class="signup-container">
    <!-- Progress -->
    <div class="progress">
      <span>{{ currentStep + 1 }} / {{ stepTitles.length }}</span>
      <div class="bar">
        <div
          class="fill"
          :style="{ width: ((currentStep + 1) / stepTitles.length) * 100 + '%' }"
        ></div>
      </div>
    </div>

    <div class="card">
      <h2>{{ stepTitles[currentStep] }}</h2>

      <!-- STEP 1 : CONTACT DETAILS -->
      <div v-if="currentStep === 0">
        <label>Mobile Number</label>
        <div class="phone">
          <select v-model="form.countryCode">
            <option value="+91">🇮🇳 +91</option>
            <option value="+1">🇺🇸 +1</option>
            <option value="+44">🇬🇧 +44</option>
          </select>
          <input type="tel" v-model="form.mobile" placeholder="0000000000" />
        </div>

        <label>Email</label>
        <input type="email" v-model="form.email" placeholder="you@example.com" />

        <div class="name-row">
          <div>
            <label>First Name</label>
            <input v-model="form.firstName" placeholder="Ajay" />
          </div>
          <div>
            <label>Last Name</label>
            <input v-model="form.lastName" placeholder="Sharma" />
          </div>
        </div>
      </div>

      <!-- STEP 2 : LOCATION -->
      <div v-if="currentStep === 1" class="center">
        <p class="hint">
          {{ locationStatus || 'We use your location to show nearby matches' }}
        </p>
      <button class="primary full" @click="requestLocation" :disabled="locationLoading">
  <span v-if="locationLoading">Requesting...</span>
  <span v-else>{{ locationGranted ? 'Location Enabled' : 'Allow Location' }}</span>
</button>

      </div>

      <!-- STEP 3 : PROFILE PHOTO -->
      <div v-if="currentStep === 2" class="center">
        <div class="avatar">
          <img v-if="selfiePreview" :src="selfiePreview" />
          <span v-else>+</span>
        </div>

        <input
          type="file"
          accept="image/*"
          capture="user"
          hidden
          ref="cameraInput"
          @change="handleImage"
        />
        <input
          type="file"
          accept="image/*"
          hidden
          ref="galleryInput"
          @change="handleImage"
        />

        <button class="outline full" @click="$refs.cameraInput.click()">Take Selfie</button>
        <button class="outline full" @click="$refs.galleryInput.click()">Upload Photo</button>
      </div>

      <!-- STEP 4 : GENDER -->
      <div v-if="currentStep === 3" class="grid">
        <button class="chip" :class="{ active: form.gender==='Male' }" @click="form.gender='Male'">Male</button>
        <button class="chip" :class="{ active: form.gender==='Female' }" @click="form.gender='Female'">Female</button>
      </div>

      <!-- STEP 4.1 : DATE OF BIRTH -->
 <div v-if="currentStep === 4">
  <label>Date of Birth</label>
  <input 
    type="date" 
    v-model="form.dob" 
    class="dob-input"
    :max="maxDOB" 
  />
</div>



      <!-- STEP 4.2 : GENDER PREFERENCE -->
      <div v-if="currentStep === 5" class="grid">
        
        <button
          class="chip"
          :class="{ active: form.genderPreference.includes('Male') }"
          @click="togglePreference('Male')"
        >Male</button>
        <button
          class="chip"
          :class="{ active: form.genderPreference.includes('Female') }"
          @click="togglePreference('Female')"
        >Female</button>
     
      </div>

      <!-- STEP 5 : STATUS -->
      <div v-if="currentStep === 6">
        <label>Status</label>
        <select v-model="form.status">
          <option disabled value="">Select status</option>
          <option>Single</option>
          <option>Married</option>
          <option>Divorced</option>
          <option>Separated</option>
          <option>Widowed</option>
        </select>
      </div>

      <!-- STEP 6 : SUBTITLE -->
      <div v-if="currentStep === 7">
        <label>Bio</label>
        <input v-model="form.subtitle" placeholder="Student • Singer • Actor" />
      </div>

<!-- STEP 7 : VERIFICATION BADGE -->
<div v-if="currentStep === 8">
  <!-- Title + Badge Image in same line -->
  <div class="title-with-badge">
    <h3>Verification Badge</h3>
    <img src="/verified1.png" alt="Verification Badge" />
  </div>

  <!-- Buttons -->
  <div class="grid" style="margin-top: 16px;">
    <button class="chip" :class="{ active: form.verified===1 }" @click="form.verified=1">
      Yes
    </button>
    <button class="chip" :class="{ active: form.verified===0 }" @click="form.verified=0">
      No 
    </button>
  </div>
</div>




      <!-- STEP 8 : RATE -->
      <div v-if="currentStep === 9">
        <label>Charges (₹)</label>
        <input type="number" v-model="form.rate" placeholder="500 / hr" />
      </div>

      <!-- STEP 9 : HABITS -->
      <div v-if="currentStep === 10">
        <label>Habits</label>
        <input v-model="form.habits" placeholder="Gym, Yoga, Travel" />
      </div>

      <!-- STEP 10 : ABOUT -->
      <div v-if="currentStep === 11">
        <label>About Me</label>
        <textarea v-model="form.about"></textarea>
      </div>

      <!-- STEP 11 : ADD PHOTOS -->
      <div v-if="currentStep === 12" class="photos">
        <div v-for="(img,i) in photos" :key="i" class="photo">
          <img :src="img" />
        </div>
        <div class="photo add" @click="$refs.multiUpload.click()">+</div>
        <input
          type="file"
          multiple
          accept="image/*"
          hidden
          ref="multiUpload"
          @change="handleMultiplePhotos"
        />
      </div>

      <!-- STEP 12 : ADDRESS -->
      <div v-if="currentStep === 13">
        <label>City</label><input v-model="form.city" />
        <label>Address</label><input v-model="form.address" />
        <label>Pincode</label><input v-model="form.pincode" />
        <label>State</label><input v-model="form.state" />
        <label>Country</label><input disabled value="India" />
      </div>

      <!-- NAV -->
      <div class="nav">
        <button v-if="currentStep>0" class="ghost" @click="currentStep--">↩ Back</button>
     <button 
  class="primary" 
  @click="nextStep" 
  :disabled="!isStepValid || loading"
>
  <span v-if="loading">Processing...</span>
  <span v-else>{{ currentStep === stepTitles.length-1 ? 'Create Account' : 'Continue' }}</span>
</button>


      </div>
    </div>
    <!-- CROP MODAL -->
<div v-if="showCropper" class="cropper-overlay">
  <div class="cropper-header">
    <button @click="cancelCrop">Cancel</button>
    <span>Move & Scale</span>
    <button @click="confirmCrop">Done</button>
  </div>

  <div class="cropper-body">
    <img ref="cropperImage" :src="cropImageSrc" />
  </div>
</div>
<!-- TOAST -->
<div v-if="toast.show" class="toast">
  {{ toast.message }}
  <span class="countdown">({{ toast.seconds }})</span>
</div>


  </div>


</template>

<script>
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.min.css'


export default {
  data() {
    return {
   toast: {
  show: false,
  message: '',
  seconds: 5,
  timer: null
},


      cropper: null,
    showCropper: false,
    cropImageSrc: null,
    selectedImageFile: null,
      locationLoading: false, 
      loading: false,
      currentStep: 0,
      stepTitles: [
        'Contact Details',
        'Location Access',
        'Profile Photo',
        'Gender',
        'Date of Birth',
        'Interested in',
        'Status',
        'Profession',
        '',
        'Charges',
        'Habits',
        'About Me',
        'Add Photos',
        'Address'
      ],
      locationGranted: false,
      locationStatus: '',
      selfiePreview: null,
      photos: [],
      form: {
        email: '',
        countryCode: '+91',
        mobile: '',
        firstName: '',
        lastName: '',
        gender: '',
        dob: '',
        genderPreference: [],
        status: '',
        subtitle: '',
        verified: 0,
        rate: '',
        habits: '',
        about: '',
        city: '',
        address: '',
        pincode: '',
        state: '',
        country: 'India',
        latitude: '',
        longitude: '',
        profilePhoto: null,
        gallery: []
      }
    }
  },
  computed: {
    maxDOB() {
    const today = new Date();
    // Subtract 18 years
    today.setFullYear(today.getFullYear() - 18);
    // Format as YYYY-MM-DD
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  },
  isStepValid() {
    switch (this.currentStep) {
      case 0: // Contact Details
        return this.form.email && this.form.mobile && this.form.firstName && this.form.lastName;
      case 1: // Location
        return this.locationGranted;
      case 2: // Profile Photo
        return !!this.form.profilePhoto;
      case 3: // Gender
        return !!this.form.gender;
      case 4: // Date of Birth
        if (!this.form.dob) return false;
        // Check if age >= 18
        const dob = new Date(this.form.dob);
        const ageDifMs = Date.now() - dob.getTime();
        const ageDate = new Date(ageDifMs);
        const age = Math.abs(ageDate.getUTCFullYear() - 1970);
        return age >= 18;
      case 5: // Gender Preference
        return this.form.genderPreference.length > 0;
      case 6: // Status
        return !!this.form.status;
      case 7: // Profession
        return !!this.form.subtitle;
      case 8: // Verification Badge
        return this.form.verified !== null;
      case 9: // Rate
        return !!this.form.rate;
      case 10: // Habits
        return !!this.form.habits;
      case 11: // About Me
        return !!this.form.about;
      case 12: // Add Photos
        return this.form.gallery.length > 0;
      case 13: // Address
        return this.form.city && this.form.address && this.form.pincode && this.form.state;
      default:
        return true;
    }
  }
},

  methods: {
showToast(message, redirect = false) {
  this.toast.message = message
  this.toast.seconds = 5
  this.toast.show = true

  // Clear old timer if any
  if (this.toast.timer) clearInterval(this.toast.timer)

  this.toast.timer = setInterval(() => {
    this.toast.seconds--

    if (this.toast.seconds <= 0) {
      clearInterval(this.toast.timer)
      this.toast.show = false

      if (redirect) {
        window.location.href = '/'
        // OR: this.$router.push('/')
      }
    }
  }, 1000)
}
,

    requestLocation() {
  this.locationLoading = true; // start loader

  navigator.geolocation.getCurrentPosition(
    pos => {
      this.locationGranted = true
      this.locationStatus = 'Location access granted'
      this.form.latitude = pos.coords.latitude
      this.form.longitude = pos.coords.longitude
      this.locationLoading = false // stop loader
    },
    err => {
      this.locationStatus = 'Location permission denied'
      this.locationLoading = false // stop loader
    }
  )
},

nextStep() {
  if (this.currentStep < this.stepTitles.length - 1) {
    this.currentStep++;
  } else {
    // Submit form
    this.loading = true;  // start loader

    const formData = new FormData();
    for (let key in this.form) {
      if (key === 'gallery') {
        this.form.gallery.forEach(file => {
          formData.append('gallery[]', file);
        });
      } else if (key === 'profilePhoto') {
        if (this.form.profilePhoto) formData.append('profile_photo', this.form.profilePhoto);
      } else if (key === 'genderPreference') {
      let genderPreference = 'both'

if (this.form.genderPreference.length === 1) {
  genderPreference = this.form.genderPreference[0].toLowerCase()
}

formData.append('gender_preference', genderPreference)

      } else {
        formData.append(key, this.form[key]);
      }
    }
    formData.append('password', '123456');

    fetch('https://companion.ajaywatpade.in/api/register', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        console.log('User saved:', data);
    this.showToast(
  'Account created successfully 🎉 Redirecting',
  true
)
 
      
      })
      .catch(err => console.error(err))
      .finally(() => {
        this.loading = false; // stop loader
      });
  }
},


   handleImage(e) {
  const file = e.target.files[0]
  if (!file) return

  this.selectedImageFile = file
  this.cropImageSrc = URL.createObjectURL(file)
  this.showCropper = true

  this.$nextTick(() => {
    if (this.cropper) this.cropper.destroy()

    this.cropper = new Cropper(this.$refs.cropperImage, {
      aspectRatio: 1,
      viewMode: 1,
      dragMode: 'move',
      background: false,
      guides: false,
      center: true,
      autoCropArea: 1,
      cropBoxResizable: false,
      cropBoxMovable: false,
    })
  })
},
confirmCrop() {
  this.cropper.getCroppedCanvas({
    width: 600,
    height: 600,
    imageSmoothingQuality: 'high'
  }).toBlob(blob => {
    const croppedFile = new File([blob], 'profile.jpg', {
      type: 'image/jpeg'
    })

    this.form.profilePhoto = croppedFile
    this.selfiePreview = URL.createObjectURL(blob)

    this.showCropper = false
    this.cropper.destroy()
    this.cropper = null
  }, 'image/jpeg', 0.95)
},
cancelCrop() {
  this.showCropper = false
  if (this.cropper) {
    this.cropper.destroy()
    this.cropper = null
  }
},

handleMultiplePhotos(e) {
  const files = Array.from(e.target.files)

  files.forEach(file => {
    // preview
    this.photos.push(URL.createObjectURL(file))

    // actual file for upload
    this.form.gallery.push(file)
  })

  // reset input so same file can be selected again if needed
  e.target.value = ''
},
   togglePreference(gender) {
      const index = this.form.genderPreference.indexOf(gender)
      if (index > -1) {
        this.form.genderPreference.splice(index, 1)
      } else {
        this.form.genderPreference.push(gender)
      }
    }
  }
}
</script>


<style scoped>
.signup-container{max-width:420px;margin:auto}
.progress{position:fixed;top:0;left:50%;transform:translateX(-50%);width:100%;max-width:420px;padding:14px;background:#fff;z-index:9}
.bar{height:3px;background:#e5e7eb;border-radius:99px;margin-top:6px}
.fill{height:100%;background:linear-gradient(90deg,#fd5068,#ff7854);transition:.3s}
.card{padding:1px}
h2{font-size:19px;font-weight:800;margin-bottom:20px}
label[data-v-219bda78] {
    margin-top: 16px;
    display: block;
    font-size: 11px;
    justify-self: left;
    font-weight: 500;
    color: #475569;
}
input,select,textarea{width:100%;padding:16px;margin-top:6px;border-radius:16px;border:1px solid #e5e7eb}
textarea{min-height:120px}
.phone{display:flex;gap:10px}
.phone select{width:90px}
.name-row{display:flex;gap:12px}
.center{text-align:center}
.avatar{width:108px;height:108px;border-radius:50%;margin:20px auto;border:2px dashed #e5e7eb;display:flex;align-items:center;justify-content:center}
.avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover}
.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.chip{padding:18px;border-radius:18px;border:1px solid #e5e7eb;font-weight:600}
.chip.active{background:linear-gradient(135deg,#fd5068,#ff7854);color:#fff;border:none}
.photos{display:flex;gap:12px;flex-wrap:wrap}
.photo{width:100px;height:100px;border-radius:18px;border:2px dashed #e5e7eb;display:flex;align-items:center;justify-content:center}
.photo img{width:100%;height:100%;object-fit:cover;border-radius:18px}
.nav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:420px;padding:14px;background:#fff;display:flex;gap:12px}
.primary{    margin-top: 8px;height: 34px;flex:1;background:linear-gradient(135deg,#fd5068,#ff7854);color:#fff;border:none;border-radius:4px}
.ghost{background:none;border:none;color:#64748b}
.signup-container {
  max-width: 420px;
  margin: auto;
  font-family: 'Inter', sans-serif;
  color: #334155;
}

/* Progress Bar */
.progress {
  position: fixed;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 420px;
  padding: 14px;
  background: #fff;
  z-index: 9;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
}
.bar {
  height: 4px;
  background: #e5e7eb;
  border-radius: 99px;
  margin-top: 6px;
}
.fill {
  height: 100%;
  background: linear-gradient(90deg, #fd5068, #ff7854);
  transition: 0.3s;
}

/* Card */
.card {
  padding: 24px 20px 80px;
  margin-top: 43px;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}
h2 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 20px;
  color: #111827;
}

/* Labels and Inputs */
label {
  margin-top: 16px;
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #475569;
}

input, select, textarea {
  width: 100%;
  padding: 14px 16px;
  margin-top: 6px;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  font-size: 14px;
  transition: all 0.3s;
  outline: none;
}
input::placeholder,
textarea::placeholder {
  color: #94a3b8;
}

input:hover, select:hover, textarea:hover {
  border-color: #fd5068;
  background: #fff;
}
input:focus, select:focus, textarea:focus {
  border-color: #fd5068;
  box-shadow: 0 0 8px rgba(253,80,104,0.25);
  background: #fff;
}

/* Phone input */
.phone {
  display: flex;
  gap: 10px;
}
.phone select {
  width: 90px;
  cursor: pointer;
}

/* Name Row */
.name-row {
  display: flex;
  gap: 12px;
}

/* Centered elements */
.center {
  text-align: center;
}

/* Avatar */
.avatar {
  width: 140px;
  height: 140px;
  border-radius: 50%;
  margin: 20px auto;
  border: 2px dashed #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.3s;
}
.avatar:hover {
  border-color: #fd5068;
  transform: scale(1.05);
}
.avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

/* Grid buttons */
.grid {
  display: grid;
  grid-template-columns: repeat(2,1fr);
  gap: 14px;
}
.chip {
  padding: 14px;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  font-weight: 600;
  text-align: center;
  background: #f9fafb;
  cursor: pointer;
  transition: all 0.3s;
}
.chip.active {
  background: linear-gradient(135deg,#fd5068,#ff7854);
  color: #fff;
  border: none;
}
.chip:hover {
  border-color: #fd5068;
  background: #fd5068;
}

/* Photos */
.photos {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.photo {
  width: 100px;
  height: 100px;
  border-radius: 16px;
  border: 2px dashed #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
}
.photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 16px;
}
.photo:hover {
  border-color: #fd5068;
  transform: scale(1.05);
}

/* Navigation */
.nav {
  position: fixed;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 420px;
  padding: 14px;
  background: #fff;
  display: flex;
  gap: 12px;
  box-shadow: 0 -4px 10px rgba(0,0,0,0.06);
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
}
.primary {
  margin-top: 8px;
  height: 40px;
  flex: 1;
  background: linear-gradient(135deg,#fd5068,#ff7854);
  color: #fff;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.3s;
}
.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(253,80,104,0.35);
}
.ghost {
  background: none;
  border: none;
  color: #64748b;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}
.ghost:hover {
  color: #fd5068;
}
button.outline.full {
  width: 76%;
  padding: 9px 0;
  margin-top: 12px;
  border-radius: 12px;
  border: 2px solid #fd5068;
  background: transparent;
  color: #fd5068;
  font-weight: 600;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.3s;
  display: inline-block;
}

button.outline.full:hover {
  background: linear-gradient(135deg, #fd5068, #ff7854);
  color: #fff;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(253,80,104,0.35);
}

button.outline.full:active {
  transform: translateY(0px);
  box-shadow: 0 3px 6px rgba(253,80,104,0.25);
}
/* Instagram-style Date of Birth Input */
.dob-input {
  width: 100%;
  padding: 14px 16px;
  border-radius: 12px;
  border: 1px solid #d1d5db;
  background: #f9fafb;
  font-size: 14px;
  color: #111827;
  transition: all 0.3s;
  cursor: pointer;
}

/* Hover effect */
.dob-input:hover {
  border-color: #fd5068;
  background: #fff;
}

/* Focus effect */
.dob-input:focus {
  outline: none;
  border-color: #fd5068;
  box-shadow: 0 0 8px rgba(253,80,104,0.25);
  background: #fff;
}

/* Remove the default date picker arrow for better custom styling in some browsers */
.dob-input::-webkit-calendar-picker-indicator {
  filter: invert(50%);
  cursor: pointer;
}

/* Placeholder text (if browser supports it for date) */
.dob-input::placeholder {
  color: #94a3b8;
}
/* Title + Badge alignment */
.title-with-badge {
  display: flex;
  align-items: center;
  place-self: anchor-center;
  margin-bottom: 29px;
  gap: 12px;
}

.title-with-badge h3 {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.title-with-badge img {
  width: 20px;
  height: 20px;
  object-fit: contain;
}

.primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}

.cropper-overlay {
  position: fixed;
  inset: 0;
  background: #000;
  z-index: 9999;
  display: flex;
  flex-direction: column;
}

.cropper-header {
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  color: #fff;
  font-weight: 600;
}

.cropper-header button {
  background: none;
  border: none;
  color: #22c55e;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
}

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
.toast {
  position: fixed;
  top: 18px;
  left: 50%;
  transform: translateX(-50%);
  background: linear-gradient(135deg,#22c55e,#16a34a);
  color: #fff;
  padding: 14px 20px;
  border-radius: 14px;
  font-weight: 600;
  font-size: 14px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.25);
  z-index: 99999;
  animation: toastIn 0.35s ease;
}

@keyframes toastIn {
  from {
    opacity: 0;
    transform: translate(-50%, -10px);
  }
  to {
    opacity: 1;
    transform: translate(-50%, 0);
  }
}

.countdown {
  margin-left: 6px;
  opacity: 0.85;
  font-weight: 700;
}

</style>
