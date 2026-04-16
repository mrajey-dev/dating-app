<template>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <div class="signup-wrap">
    <!-- Progress Steps -->
    <div class="progress-header">
      <div class="step-indicators">
        <div 
          v-for="(step, idx) in stepNames" 
          :key="idx"
          class="step-indicator"
          :class="{ active: currentStep >= idx, completed: currentStep > idx }"
        >
          <div class="step-number">{{ idx + 1 }}</div>
          <span class="step-label">{{ step }}</span>
        </div>
      </div>
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: ((currentStep + 1) / stepNames.length) * 100 + '%' }"></div>
      </div>
    </div>

    <!-- Step Card -->
    <div class="step-card">
      <transition name="step-fade" mode="out-in">
        <div :key="currentStep" class="step-content">
          
          <!-- Step 0: Basic Info -->
          <div v-if="currentStep === 0">
            <h2 class="step-title">Welcome! Let's start with basics</h2>
            <p class="step-subtitle">Tell us who you are</p>
            
            <div class="two-col">
              <div class="floating-group">
                <i class="fa fa-user-o"></i>
                <div class="input-floating-wrapper" :class="{ focused: focusedField === 'firstName', 'has-value': form.firstName.length > 0 }">
                  <input
                    id="firstName"
                    type="text"
                    v-model="form.firstName"
                    @focus="focusedField = 'firstName'"
                    @blur="focusedField = ''"
                    class="floating-input"
                  />
                  <label for="firstName" class="floating-placeholder">First name</label>
                  <div class="input-accent-line"></div>
                </div>
              </div>
              <div class="floating-group">
                <div class="input-floating-wrapper" :class="{ focused: focusedField === 'lastName', 'has-value': form.lastName.length > 0 }">
                  <input
                    id="lastName"
                    type="text"
                    v-model="form.lastName"
                    @focus="focusedField = 'lastName'"
                    @blur="focusedField = ''"
                    class="floating-input"
                  />
                  <label for="lastName" class="floating-placeholder">Last name</label>
                  <div class="input-accent-line"></div>
                </div>
              </div>
            </div>
            
            <div class="floating-group">
              <i class="fa fa-envelope"></i>
              <div class="input-floating-wrapper" :class="{ focused: focusedField === 'email', 'has-value': form.email.length > 0 }">
                <input
                  id="email"
                  type="email"
                  v-model="form.email"
                  @focus="focusedField = 'email'"
                  @blur="focusedField = ''"
                  class="floating-input"
                />
                <label for="email" class="floating-placeholder">Email address</label>
                <div class="input-accent-line"></div>
              </div>
            </div>
            
            <div class="floating-group">
              <i class="fa fa-lock"></i>
              <div class="input-floating-wrapper" :class="{ focused: focusedField === 'password', 'has-value': form.password.length > 0 }">
                <input
                  id="password"
                  :type="showPassword ? 'text' : 'password'"
                  v-model="form.password"
                  @focus="focusedField = 'password'"
                  @blur="focusedField = ''"
                  class="floating-input"
                />
                <label for="password" class="floating-placeholder">Password</label>
                <div class="input-accent-line"></div>
              </div>
            </div>
            
            <!-- Phone Field: Country Code + Mobile Number Combined in Single Field -->
            <div class="floating-group">
              <i class="fa fa-phone"></i>
              <div class="phone-combined-container">
                <!-- Country Code Selector - Fixed Width -->
                <div class="country-code-selector" :class="{ open: countryDropdownOpen }">
                  <div class="selected-country" @click="toggleCountryDropdown">
                    <img 
                      v-if="selectedCountry.flag" 
                      :src="selectedCountry.flag" 
                      class="country-flag"
                      :alt="selectedCountry.code"
                    />
                    <span class="country-code-text">{{ selectedCountry.dialCode }}</span>
                    <i class="fa fa-chevron-down"></i>
                  </div>
                  <div class="country-dropdown" v-show="countryDropdownOpen">
                    <div class="search-box">
                      <i class="fa fa-search"></i>
                      <input 
                        type="text" 
                        v-model="countrySearch" 
                        placeholder="Search country..."
                        @input="filterCountries"
                      />
                    </div>
                    <div class="country-list">
                      <div 
                        v-for="country in filteredCountries" 
                        :key="country.code"
                        class="country-option"
                        @click="selectCountry(country)"
                      >
                        <img :src="country.flag" class="country-flag" :alt="country.code" />
                        <span class="country-name">{{ country.name }}</span>
                        <span class="country-dial">{{ country.dialCode }}</span>
                      </div>
                      <div v-if="filteredCountries.length === 0" class="no-results">
                        No countries found
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Combined Mobile Number Input with Country Code Prefix Display -->
                <div class="mobile-input-combined">
                  <div class="input-floating-wrapper" :class="{ focused: focusedField === 'mobile', 'has-value': form.mobile.length > 0 }">
                    <input
                      id="mobile"
                      type="tel"
                      v-model="form.mobile"
                      @focus="focusedField = 'mobile'"
                      @blur="focusedField = ''"
                      class="floating-input combined-input"
                      placeholder=" "
                    />
                    <label for="mobile" class="floating-placeholder">Mobile number</label>
                    <div class="input-accent-line"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 1: Personal Details -->
          <div v-if="currentStep === 1">
            <h2 class="step-title">Tell us more about you</h2>
            <p class="step-subtitle">This helps us find your perfect match</p>
            
            <div class="floating-group">
              <div class="input-floating-wrapper" :class="{ focused: focusedField === 'dob', 'has-value': form.dob.length > 0 }">
                <input
                  id="dob"
                  type="date"
                  v-model="form.dob"
                  :max="maxDOB"
                  @focus="focusedField = 'dob'"
                  @blur="focusedField = ''"
                  class="floating-input"
                />
                <label for="dob" class="floating-placeholder">Date of birth</label>
                <div class="input-accent-line"></div>
              </div>
              <p class="hint-text">You must be 18+ to use HeartLink</p>
            </div>
            
            <div class="field-group">
              <label class="field-label-static">Gender</label>
              <div class="gender-grid">
                <div class="gender-card" :class="{ active: form.gender === 'Male' }" @click="form.gender = 'Male'">
                  <div class="gender-icon male">♂</div>
                  <span>Male</span>
                </div>
                <div class="gender-card" :class="{ active: form.gender === 'Female' }" @click="form.gender = 'Female'">
                  <div class="gender-icon female">♀</div>
                  <span>Female</span>
                </div>
              </div>
            </div>
            
            <div class="field-group">
              <label class="field-label-static">Relationship status</label>
              <div class="status-grid">
                <span v-for="s in statuses" :key="s" class="status-chip" :class="{ active: form.status === s }" @click="form.status = s">{{ s }}</span>
              </div>
            </div>
          </div>

          <!-- Step 2: Location -->
          <div v-if="currentStep === 2" class="center-step">
            <h2 class="step-title">Where are you located?</h2>
            <p class="step-subtitle">We'll show you matches nearby</p>
            
            <div class="location-visual">
              <div class="pulse-ring"></div>
              <div class="location-pin">
                📍
              </div>
            </div>
            
            <button class="location-btn" @click="requestLocation" :disabled="locationLoading">
              <span v-if="locationLoading" class="spinner"></span>
              <span v-else>📍</span>
              <span>{{ locationGranted ? 'Location Enabled ✓' : 'Enable Location Access' }}</span>
            </button>
            
            <button v-if="!locationGranted" class="skip-btn" @click="skipLocation">
              Skip for now →
            </button>
            
            <p v-if="locationStatus" class="location-status" :class="{ error: locationStatus.includes('denied') }">
              {{ locationStatus }}
            </p>
          </div>

          <!-- Step 3: Profile Photo -->
          <div v-if="currentStep === 3" class="center-step">
            <h2 class="step-title">Add your profile photo</h2>
            <p class="step-subtitle">Profiles with photos get 5× more matches</p>
            
            <div class="avatar-container" @click="triggerPhotoUpload">
              <img v-if="profilePreview" :src="profilePreview" class="avatar-image" />
              <div v-else class="avatar-placeholder">
                📷
                <span>Add photo</span>
              </div>
              <div v-if="profilePreview" class="avatar-edit">
                ✎
              </div>
            </div>
            
            <div class="upload-buttons">
              <button class="upload-btn" @click.stop="triggerCamera">
                📷 Take selfie
              </button>
              <button class="upload-btn" @click.stop="triggerGallery">
                🖼️ From gallery
              </button>
            </div>
            
            <input type="file" ref="photoInput" accept="image/*" hidden @change="handlePhotoSelect" />
          </div>

          <!-- Step 4: Bio & Interests -->
          <div v-if="currentStep === 4">
            <h2 class="step-title">Tell the world about you</h2>
            <p class="step-subtitle">Share what makes you unique</p>
            
            <div class="floating-group">
              <div class="input-floating-wrapper" :class="{ focused: focusedField === 'bio', 'has-value': form.bio.length > 0 }">
                <input
                  id="bio"
                  type="text"
                  v-model="form.bio"
                  maxlength="60"
                  @focus="focusedField = 'bio'"
                  @blur="focusedField = ''"
                  class="floating-input"
                />
                <label for="bio" class="floating-placeholder">Bio tagline</label>
                <div class="input-accent-line"></div>
              </div>
              <div class="char-counter" :class="{ warn: form.bio.length > 50 }">{{ form.bio.length }}/60</div>
            </div>
            
            <div class="floating-group">
              <div class="input-floating-wrapper" :class="{ focused: focusedField === 'habits', 'has-value': form.habits.length > 0 }">
                <input
                  id="habits"
                  type="text"
                  v-model="form.habits"
                  @focus="focusedField = 'habits'"
                  @blur="focusedField = ''"
                  class="floating-input"
                />
                <label for="habits" class="floating-placeholder">Habits & interests</label>
                <div class="input-accent-line"></div>
              </div>
              <p class="hint-text">Separate with commas (e.g., Traveling, Music, Photography)</p>
            </div>
            
            <div class="habit-tags" v-if="habitList.length">
              <span v-for="h in habitList.slice(0, 6)" :key="h" class="habit-tag">{{ h }}</span>
            </div>
          </div>

          <!-- Step 5: Photos Gallery -->
          <div v-if="currentStep === 5">
            <h2 class="step-title">Show more of your life</h2>
            <p class="step-subtitle">Add up to 9 photos to your gallery</p>
            
            <div class="photo-gallery">
              <div v-for="(photo, idx) in galleryPhotos" :key="idx" class="gallery-photo">
                <img :src="photo" />
                <button class="remove-photo" @click="removeGalleryPhoto(idx)">×</button>
              </div>
              <div v-if="galleryPhotos.length < 9" class="add-photo" @click="triggerGalleryUpload">
                <span>+</span>
                <span>Add photo</span>
              </div>
            </div>
            
            <input type="file" ref="galleryInput" accept="image/*" multiple hidden @change="handleGallerySelect" />
          </div>

          <!-- Step 6: Address -->
          <div v-if="currentStep === 6">
            <h2 class="step-title">Your address</h2>
            <p class="step-subtitle">Used to find matches in your area</p>
            
            <div class="two-col">
              <div class="floating-group">
                <div class="input-floating-wrapper" :class="{ focused: focusedField === 'city', 'has-value': form.city.length > 0 }">
                  <input
                    id="city"
                    type="text"
                    v-model="form.city"
                    @focus="focusedField = 'city'"
                    @blur="focusedField = ''"
                    class="floating-input"
                  />
                  <label for="city" class="floating-placeholder">City</label>
                  <div class="input-accent-line"></div>
                </div>
              </div>
              <div class="floating-group">
                <div class="input-floating-wrapper" :class="{ focused: focusedField === 'pincode', 'has-value': form.pincode.length > 0 }">
                  <input
                    id="pincode"
                    type="text"
                    v-model="form.pincode"
                    @focus="focusedField = 'pincode'"
                    @blur="focusedField = ''"
                    class="floating-input"
                  />
                  <label for="pincode" class="floating-placeholder">Pincode</label>
                  <div class="input-accent-line"></div>
                </div>
              </div>
            </div>
            
            <div class="floating-group">
              <div class="input-floating-wrapper" :class="{ focused: focusedField === 'address', 'has-value': form.address.length > 0 }">
                <input
                  id="address"
                  type="text"
                  v-model="form.address"
                  @focus="focusedField = 'address'"
                  @blur="focusedField = ''"
                  class="floating-input"
                />
                <label for="address" class="floating-placeholder">Address</label>
                <div class="input-accent-line"></div>
              </div>
            </div>
            
            <div class="two-col">
              <div class="floating-group">
                <div class="input-floating-wrapper" :class="{ focused: focusedField === 'state', 'has-value': form.state.length > 0 }">
                  <input
                    id="state"
                    type="text"
                    v-model="form.state"
                    @focus="focusedField = 'state'"
                    @blur="focusedField = ''"
                    class="floating-input"
                  />
                  <label for="state" class="floating-placeholder">State</label>
                  <div class="input-accent-line"></div>
                </div>
              </div>
              <div class="floating-group">
                <div class="input-floating-wrapper disabled-wrapper" :class="{ 'has-value': form.country.length > 0 }">
                  <input
                    id="country"
                    type="text"
                    v-model="form.country"
                    disabled
                    class="floating-input disabled-input"
                  />
                  <label for="country" class="floating-placeholder">Country</label>
                  <div class="input-accent-line"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <!-- Navigation Buttons -->
    <div class="bottom-nav">
      <button v-if="currentStep > 0" class="nav-btn back" @click="currentStep--">
        ← Back
      </button>
      <div v-else class="nav-placeholder"></div>
      
      <button class="nav-btn next" @click="nextStep" :disabled="!isStepValid">
        <span>{{ currentStep === stepNames.length - 1 ? 'Create Account 🎉' : 'Continue' }}</span>
        <span v-if="currentStep < stepNames.length - 1">→</span>
      </button>
    </div>

    <!-- Cropper Modal -->
    <div v-if="showCropper" class="cropper-modal">
      <div class="cropper-header">
        <button class="cropper-cancel" @click="cancelCrop">Cancel</button>
        <span class="cropper-title">Adjust photo</span>
        <button class="cropper-done" @click="confirmCrop">Done</button>
      </div>
      <div class="cropper-body">
        <img ref="cropperImage" :src="cropImageSrc" />
      </div>
    </div>

    <!-- Toast Notification -->
    <transition name="toast-fade">
      <div v-if="toast.show" class="toast" :class="{ error: toast.isError }">
        <span>{{ toast.isError ? '⚠️' : '✓' }}</span>
        <span>{{ toast.message }}</span>
      </div>
    </transition>
  </div>
</template>

<script>
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.min.css'

export default {
  name: 'CreateAccount',
  
  data() {
    return {
      currentStep: 0,
      focusedField: '',
      showPassword: false,
      stepNames: ['Basics', 'Details', 'Location', 'Photo', 'Bio', 'Gallery', 'Address'],
      statuses: ['Single', 'Married', 'Divorced', 'Separated', 'Widowed'],
      
      form: {
        firstName: '', 
        lastName: '', 
        email: '', 
        password: '',
        mobile: '', 
        countryCode: '+91',
        dob: '', 
        gender: '', 
        gender_preference: 'both',
        status: '', 
        bio: '', 
        habits: '',
        city: '', 
        address: '', 
        pincode: '', 
        state: '', 
        country: 'India',
        latitude: '', 
        longitude: ''
      },
      
      // Country selector data
      countries: [],
      filteredCountries: [],
      selectedCountry: {
        name: 'India',
        code: 'IN',
        dialCode: '+91',
        flag: 'https://flagcdn.com/w320/in.png'
      },
      countryDropdownOpen: false,
      countrySearch: '',
      isLoadingCountries: false,
      
      profilePhoto: null,
      profilePreview: null,
      galleryPhotos: [],
      galleryFiles: [],
      
      locationGranted: false,
      locationSkipped: false,
      locationLoading: false,
      locationStatus: '',
      
      showCropper: false,
      cropImageSrc: null,
      cropper: null,
      pendingImageFile: null,
      
      toast: { show: false, message: '', isError: false, timer: null },
      
      loading: false
    }
  },
  
  computed: {
    maxDOB() {
      const d = new Date()
      d.setFullYear(d.getFullYear() - 18)
      return d.toISOString().split('T')[0]
    },
    
    habitList() {
      return this.form.habits.split(',').map(h => h.trim()).filter(h => h)
    },
    
    isStepValid() {
      switch(this.currentStep) {
        case 0: return this.form.firstName && this.form.lastName && this.form.email && this.form.password && this.form.mobile && this.form.password.length >= 6
        case 1: return this.form.dob && this.form.gender && this.form.status
        case 2: return this.locationGranted || this.locationSkipped
        case 3: return !!this.profilePhoto
        case 4: return this.form.bio && this.form.habits
        case 5: return true
        case 6: return this.form.city && this.form.address && this.form.pincode && this.form.state
        default: return true
      }
    }
  },
  
  mounted() {
    this.fetchCountries()
    // Close dropdown when clicking outside
    document.addEventListener('click', this.handleClickOutside)
  },
  
  beforeDestroy() {
    document.removeEventListener('click', this.handleClickOutside)
    if (this.profilePreview) URL.revokeObjectURL(this.profilePreview)
    this.galleryPhotos.forEach(p => URL.revokeObjectURL(p))
    if (this.cropImageSrc) URL.revokeObjectURL(this.cropImageSrc)
  },
  
  methods: {
    // Fetch all countries from REST API
    async fetchCountries() {
      this.isLoadingCountries = true
      try {
        const response = await fetch('https://restcountries.com/v3.1/all?fields=name,cca2,idd,flags')
        const data = await response.json()
        
        const countryList = data
          .filter(country => {
            return country.idd && (country.idd.root || country.idd.suffixes)
          })
          .map(country => {
            let dialCode = ''
            if (country.idd.root) {
              if (country.idd.suffixes && country.idd.suffixes.length > 0) {
                dialCode = country.idd.root + country.idd.suffixes[0]
              } else {
                dialCode = country.idd.root
              }
            }
            return {
              name: country.name.common,
              code: country.cca2,
              dialCode: dialCode,
              flag: country.flags.svg || country.flags.png
            }
          })
          .sort((a, b) => a.name.localeCompare(b.name))
        
        this.countries = countryList
        this.filteredCountries = countryList
        
        const india = this.countries.find(c => c.code === 'IN')
        if (india) {
          this.selectedCountry = india
          this.form.countryCode = india.dialCode
        } else if (this.countries.length > 0) {
          this.selectedCountry = this.countries[0]
          this.form.countryCode = this.countries[0].dialCode
        }
        
      } catch (error) {
        console.error('Error fetching countries:', error)
        this.showToast('Failed to load country list', true)
        this.loadFallbackCountries()
      } finally {
        this.isLoadingCountries = false
      }
    },
    
    loadFallbackCountries() {
      const fallback = [
        { name: 'United States', code: 'US', dialCode: '+1', flag: 'https://flagcdn.com/w320/us.png' },
        { name: 'United Kingdom', code: 'GB', dialCode: '+44', flag: 'https://flagcdn.com/w320/gb.png' },
        { name: 'India', code: 'IN', dialCode: '+91', flag: 'https://flagcdn.com/w320/in.png' },
        { name: 'Canada', code: 'CA', dialCode: '+1', flag: 'https://flagcdn.com/w320/ca.png' },
        { name: 'Australia', code: 'AU', dialCode: '+61', flag: 'https://flagcdn.com/w320/au.png' },
        { name: 'Germany', code: 'DE', dialCode: '+49', flag: 'https://flagcdn.com/w320/de.png' },
        { name: 'France', code: 'FR', dialCode: '+33', flag: 'https://flagcdn.com/w320/fr.png' },
        { name: 'Japan', code: 'JP', dialCode: '+81', flag: 'https://flagcdn.com/w320/jp.png' },
        { name: 'China', code: 'CN', dialCode: '+86', flag: 'https://flagcdn.com/w320/cn.png' },
        { name: 'Brazil', code: 'BR', dialCode: '+55', flag: 'https://flagcdn.com/w320/br.png' }
      ]
      this.countries = fallback
      this.filteredCountries = fallback
      this.selectedCountry = fallback.find(c => c.code === 'IN') || fallback[0]
      this.form.countryCode = this.selectedCountry.dialCode
    },
    
    filterCountries() {
      const search = this.countrySearch.toLowerCase()
      this.filteredCountries = this.countries.filter(country => 
        country.name.toLowerCase().includes(search) || 
        country.dialCode.includes(search)
      )
    },
    
    toggleCountryDropdown() {
      this.countryDropdownOpen = !this.countryDropdownOpen
      if (this.countryDropdownOpen) {
        this.countrySearch = ''
        this.filteredCountries = this.countries
      }
    },
    
    selectCountry(country) {
      this.selectedCountry = country
      this.form.countryCode = country.dialCode
      this.countryDropdownOpen = false
      this.countrySearch = ''
    },
    
    handleClickOutside(event) {
      const selector = document.querySelector('.phone-combined-container')
      if (selector && !selector.contains(event.target)) {
        this.countryDropdownOpen = false
      }
    },
    
    nextStep() {
      if (this.currentStep < this.stepNames.length - 1) {
        this.currentStep++
        this.focusedField = ''
        window.scrollTo(0, 0)
      } else {
        this.submitSignup()
      }
    },
    
    async requestLocation() {
      this.locationLoading = true
      this.locationStatus = 'Requesting location...'
      
      if (!navigator.geolocation) {
        this.locationStatus = 'Geolocation not supported'
        this.locationLoading = false
        return
      }
      
      navigator.geolocation.getCurrentPosition(
        (position) => {
          this.locationGranted = true
          this.locationSkipped = false
          this.locationStatus = '✓ Location access granted'
          this.form.latitude = position.coords.latitude
          this.form.longitude = position.coords.longitude
          this.locationLoading = false
          setTimeout(() => { 
            if (this.currentStep === 2) this.currentStep++ 
          }, 500)
        },
        (error) => {
          let msg = ''
          switch(error.code) {
            case error.PERMISSION_DENIED: msg = 'Permission denied. You can skip or enable later.'; break
            case error.POSITION_UNAVAILABLE: msg = 'Location unavailable. Try again or skip.'; break
            default: msg = 'Location error. You can skip.'
          }
          this.locationStatus = msg
          this.locationLoading = false
          this.locationGranted = false
        },
        { enableHighAccuracy: false, timeout: 10000 }
      )
    },
    
    skipLocation() {
      this.locationSkipped = true
      this.locationGranted = false
      this.locationStatus = 'Location skipped - can enable later'
      this.currentStep++
    },
    
    triggerPhotoUpload() { this.$refs.photoInput.click() },
    triggerCamera() {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = 'image/*'
      input.capture = 'environment'
      input.onchange = (e) => this.handlePhotoSelect(e)
      input.click()
    },
    triggerGallery() { this.$refs.photoInput.click() },
    triggerGalleryUpload() { this.$refs.galleryInput.click() },
    
    handlePhotoSelect(e) {
      const file = e.target.files[0]
      if (!file) return
      if (file.size > 10 * 1024 * 1024) {
        this.showToast('Image too large (max 10MB)', true)
        return
      }
      this.pendingImageFile = file
      this.cropImageSrc = URL.createObjectURL(file)
      this.showCropper = true
      this.$nextTick(() => {
        if (this.cropper) this.cropper.destroy()
        this.cropper = new Cropper(this.$refs.cropperImage, {
          aspectRatio: 1,
          viewMode: 1,
          background: false,
          autoCropArea: 0.9
        })
      })
      e.target.value = ''
    },
    
    confirmCrop() {
      if (!this.cropper) return
      const canvas = this.cropper.getCroppedCanvas({ width: 600, height: 600 })
      canvas.toBlob(blob => {
        this.profilePhoto = new File([blob], 'profile.jpg', { type: 'image/jpeg' })
        this.profilePreview = URL.createObjectURL(blob)
        this.showCropper = false
        this.cropper.destroy()
        this.cropper = null
        this.showToast('Photo added!', false)
      }, 'image/jpeg', 0.9)
    },
    
    cancelCrop() {
      this.showCropper = false
      if (this.cropper) this.cropper.destroy()
      if (this.cropImageSrc) URL.revokeObjectURL(this.cropImageSrc)
    },
    
    handleGallerySelect(e) {
      const files = Array.from(e.target.files)
      for (let file of files) {
        if (file.size > 10 * 1024 * 1024) continue
        if (this.galleryPhotos.length >= 9) break
        this.galleryPhotos.push(URL.createObjectURL(file))
        this.galleryFiles.push(file)
      }
      this.showToast(`${files.length} photo(s) added`, false)
      e.target.value = ''
    },
    
    removeGalleryPhoto(index) {
      URL.revokeObjectURL(this.galleryPhotos[index])
      this.galleryPhotos.splice(index, 1)
      this.galleryFiles.splice(index, 1)
    },
    
    async submitSignup() {
      this.loading = true
      this.showToast('Creating your account...', false)
      
      try {
        const formData = new FormData()
        
        formData.append('firstName', this.form.firstName)
        formData.append('lastName', this.form.lastName)
        formData.append('email', this.form.email)
        formData.append('password', this.form.password)
        formData.append('mobile', this.form.mobile)
        formData.append('countryCode', this.form.countryCode)
        formData.append('gender', this.form.gender)
        formData.append('dob', this.form.dob)
        formData.append('gender_preference', this.form.gender_preference)
        formData.append('status', this.form.status)
        formData.append('subtitle', this.form.bio)
        formData.append('habits', this.form.habits)
        formData.append('city', this.form.city)
        formData.append('address', this.form.address)
        formData.append('pincode', this.form.pincode)
        formData.append('state', this.form.state)
        formData.append('country', this.form.country)
        
        if (this.form.latitude) formData.append('latitude', this.form.latitude)
        if (this.form.longitude) formData.append('longitude', this.form.longitude)
        
        formData.append('location_skipped', this.locationSkipped ? '1' : '0')
        
        if (this.profilePhoto) {
          formData.append('profile_photo', this.profilePhoto)
        }
        
        this.galleryFiles.forEach(file => {
          formData.append('gallery[]', file)
        })
        
        const response = await fetch('https://companion.ajaywatpade.in/api/register', {
          method: 'POST',
          body: formData,
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        
        const data = await response.json()
        
        if (!response.ok) {
          if (response.status === 422 && data.errors) {
            const errorMessages = Object.values(data.errors).flat().join('\n')
            throw new Error(errorMessages)
          }
          throw new Error(data.message || data.error || 'Registration failed')
        }
        
        if (data.access_token) {
          localStorage.setItem('access_token', data.access_token)
          localStorage.setItem('token_type', data.token_type || 'Bearer')
          localStorage.setItem('user', JSON.stringify(data.user))
          
          this.showToast(data.message || 'Account created successfully! 🎉', false)
          
          setTimeout(() => { 
            if (this.$router) {
              this.$router.push('/login')
            } else {
              window.location.href = '/home'
            }
          }, 1500)
        } else if (data.success && data.user) {
          localStorage.setItem('user', JSON.stringify(data.user))
          this.showToast('Account created successfully! 🎉', false)
          setTimeout(() => { 
            if (this.$router) {
              this.$router.push('/login')
            } else {
              window.location.href = '/login'
            }
          }, 1500)
        } else {
          throw new Error(data.message || 'Registration failed - no token received')
        }
        
      } catch (err) {
        console.error('Signup error:', err)
        this.showToast(err.message || 'Registration failed. Please try again.', true)
      } finally {
        this.loading = false
      }
    },
    
    showToast(msg, isError = false) {
      if (this.toast.timer) clearTimeout(this.toast.timer)
      this.toast = { show: true, message: msg, isError, timer: null }
      this.toast.timer = setTimeout(() => {
        this.toast.show = false
      }, 3000)
    }
  }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

/* Signup Page - Clean White Theme */
.signup-wrap {
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  padding: 2rem;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  background: #ffffff;
}

/* Progress Header */
.progress-header {
  max-width: 800px;
  margin: 0 auto 2rem auto;
  width: 100%;
}

.step-indicators {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.step-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f5f5f5;
  border: 2px solid #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #999;
  transition: all 0.3s ease;
}

.step-indicator.active .step-number {
  background: linear-gradient(135deg, #ff9a9e, #fad0c4);
  border-color: #ff9a9e;
  color: #c44569;
  box-shadow: 0 4px 12px rgba(255, 154, 158, 0.3);
}

.step-indicator.completed .step-number {
  background: #10b981;
  border-color: #10b981;
  color: white;
}

.step-label {
  font-size: 0.75rem;
  color: #666;
  font-weight: 500;
}

.step-indicator.active .step-label {
  color: #c44569;
  font-weight: 600;
}

.progress-bar {
  height: 4px;
  background: #e0e0e0;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #ff9a9e, #fad0c4);
  border-radius: 4px;
  transition: width 0.3s ease;
}

/* Step Card */
.step-card {
  max-width: 600px;
  margin: 0 auto;
  width: 100%;
  background: #ffffff;
  border-radius: 0;
  padding: 2rem 0;
}

.step-content {
  width: 100%;
}

.step-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 0.5rem;
  letter-spacing: -0.02em;
}

.step-subtitle {
  color: #666;
  font-size: 0.95rem;
  margin-bottom: 2rem;
}

/* ===== FLOATING LABEL STYLES ===== */
.floating-group {
  margin-bottom: 1.5rem;
  width: 100%;
  display: flex;
  gap: 9px;
  align-items: center;
}

.input-floating-wrapper {
  position: relative;
  width: 100%;
}

.floating-input, .floating-select {
  width: 100%;
  padding: 1rem 1rem 0.5rem 1rem;
  font-size: 1rem;
  font-family: inherit;
  border: 1.5px solid #e2e8f0;
  border-radius: 18px;
  background: white;
  outline: none;
  transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
  box-sizing: border-box;
}

.floating-select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238e8ea8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1rem;
}

.floating-input:focus, .floating-select:focus {
  border-color: #ff9a9e;
  box-shadow: 0 0 0 3px rgba(255, 154, 158, 0.15);
}

.floating-input.disabled-input {
  background: #f5f5f5;
  color: #999;
  cursor: not-allowed;
}

.floating-placeholder {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1rem;
  color: #8e8ea8;
  pointer-events: none;
  transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
  background: transparent;
  padding: 0 0.25rem;
  line-height: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: calc(100% - 2rem);
}

/* FLOATING EFFECT */
.input-floating-wrapper.focused .floating-placeholder,
.input-floating-wrapper.has-value .floating-placeholder {
  top: 0;
  transform: translateY(-50%);
  font-size: 0.75rem;
  color: #c44569;
  background: white;
  left: 0.75rem;
  padding: 0 0.35rem;
  white-space: nowrap;
}

/* Accent line animation */
.input-accent-line {
  position: absolute;
  bottom: 0;
  left: 50%;
  display: none;
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, #ff9a9e, #fad0c4);
  transition: all 0.3s ease;
  border-radius: 2px;
}

.input-floating-wrapper.focused .input-accent-line {
  left: 0;
  width: 100%;
}

/* ===== COMBINED PHONE FIELD ===== */
.phone-combined-container {
  display: flex;
  width: 100%;
  gap: 0;
  align-items: stretch;
  background: white;
  border: 1.5px solid #e2e8f0;
  border-radius: 18px;
  transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
}

.phone-combined-container:focus-within {
  border-color: #ff9a9e;
  box-shadow: 0 0 0 3px rgba(255, 154, 158, 0.15);
}

/* Country Code Selector */
.country-code-selector {
  position: relative;
  flex-shrink: 0;
  border-right: 1px solid #e2e8f0;
}

.selected-country {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 0.6rem;
  background: white;
  cursor: pointer;
  transition: all 0.2s ease;
  height: 52px;
  border-radius: 18px 0 0 18px;
}

.selected-country:hover {
  background: #fafafa;
}

.country-flag {
  width: 24px;
  height: 18px;
  object-fit: cover;
  border-radius: 2px;
}

.country-code-text {
  font-size: 0.9rem;
  font-weight: 500;
  color: #333;
}

.selected-country .fa-chevron-down {
  font-size: 0.7rem;
  color: #999;
  transition: transform 0.2s ease;
}

.country-code-selector.open .selected-country .fa-chevron-down {
  transform: rotate(180deg);
}

/* Dropdown for country selector */
.country-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  margin-top: 0.5rem;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
  z-index: 100;
  max-height: 320px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  width: 280px;
  min-width: 260px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-bottom: 1px solid #e2e8f0;
}

.search-box .fa-search {
  color: #999;
  font-size: 0.875rem;
}

.search-box input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.875rem;
  padding: 0.25rem 0;
}

.search-box input::placeholder {
  color: #bbb;
}

.country-list {
  overflow-y: auto;
  max-height: 260px;
}

.country-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 0.75rem;
  cursor: pointer;
  transition: background 0.15s ease;
}

.country-option:hover {
  background: #f5f5f5;
}

.country-option .country-flag {
  width: 24px;
  height: 18px;
}

.country-option .country-name {
  flex: 1;
  font-size: 0.875rem;
  color: #333;
}

.country-option .country-dial {
  font-size: 0.75rem;
  color: #999;
}

.no-results {
  padding: 1rem;
  text-align: center;
  color: #999;
  font-size: 0.875rem;
}

/* Mobile Input - Takes remaining space */
.mobile-input-combined {
  flex: 1;
}

.mobile-input-combined .input-floating-wrapper {
  width: 100%;
  height: 100%;
}

.combined-input {
  border: none !important;
  border-radius: 0 18px 18px 0 !important;
  padding: 1rem 1rem 0.5rem 0.5rem !important;
  box-shadow: none !important;
}

.combined-input:focus {
  box-shadow: none !important;
}

.mobile-input-combined .input-floating-wrapper .floating-placeholder {
  left: 0.5rem;
}

.mobile-input-combined .input-floating-wrapper.focused .floating-placeholder,
.mobile-input-combined .input-floating-wrapper.has-value .floating-placeholder {
  left: 0.25rem;
}

/* Field label static */
.field-label-static {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.75rem;
}

.hint-text {
  font-size: 0.7rem;
  color: #999;
  margin-top: 0.25rem;
}

.char-counter {
  text-align: right;
  font-size: 0.7rem;
  color: #999;
  margin-top: 0.25rem;
}

.char-counter.warn {
  color: #dc2626;
}

/* Gender Grid */
.gender-grid {
  display: flex;
  gap: 1rem;
}

.gender-card {
  flex: 1;
  padding: 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 14px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #ffffff;
}

.gender-card.active {
  border-color: #ff9a9e;
  background: linear-gradient(135deg, rgba(255, 154, 158, 0.05) 0%, rgba(250, 208, 196, 0.05) 100%);
}

.gender-icon {
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.gender-icon.male { color: #3b82f6; }
.gender-icon.female { color: #ec489a; }

/* Status Chips */
.status-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.status-chip {
  padding: 0.5rem 1.25rem;
  border-radius: 30px;
  background: #f5f5f5;
  color: #666;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.status-chip.active {
  background: linear-gradient(135deg, #ff9a9e, #fad0c4);
  color: #c44569;
  box-shadow: 0 4px 12px rgba(255, 154, 158, 0.3);
}

.status-chip:hover {
  transform: translateY(-2px);
}

/* Center Step */
.center-step {
  text-align: center;
}

.location-visual {
  position: relative;
  width: 120px;
  height: 120px;
  margin: 2rem auto;
}

.pulse-ring {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: rgba(255, 154, 158, 0.15);
  animation: pulse 1.5s infinite;
}

.location-pin {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #ff9a9e, #fad0c4);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 8px 20px rgba(255, 154, 158, 0.3);
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 0.6; }
  100% { transform: scale(1.5); opacity: 0; }
}

.location-btn {
  background: linear-gradient(135deg, #ff9a9e, #fad0c4);
  color: #c44569;
  border: none;
  padding: 1rem 2rem;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 40px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  transition: all 0.3s ease;
  box-shadow: 0 8px 20px rgba(255, 154, 158, 0.3);
}

.location-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(255, 154, 158, 0.4);
}

.skip-btn {
  background: none;
  border: none;
  color: #c44569;
  font-size: 0.9rem;
  cursor: pointer;
  font-weight: 500;
}

.location-status {
  margin-top: 1rem;
  font-size: 0.8rem;
  color: #10b981;
}

.location-status.error {
  color: #dc2626;
}

/* Avatar Container */
.avatar-container {
  width: 160px;
  height: 160px;
  margin: 1.5rem auto;
  border-radius: 50%;
  background: #f5f5f5;
  border: 3px dashed #d0d0d0;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.2s ease;
}

.avatar-container:hover {
  border-color: #ff9a9e;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #999;
  font-size: 0.85rem;
}

.avatar-placeholder span:first-child {
  font-size: 2rem;
}

.avatar-edit {
  position: absolute;
  bottom: 0;
  right: 0;
  background: linear-gradient(135deg, #ff9a9e, #fad0c4);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #c44569;
  font-size: 0.8rem;
}

.upload-buttons {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 1rem;
}

.upload-btn {
  padding: 0.75rem 1.5rem;
  background: #f5f5f5;
  border: none;
  border-radius: 40px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #333;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.upload-btn:hover {
  background: #e0e0e0;
}

/* Habit Tags */
.habit-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 1rem;
}

.habit-tag {
  padding: 0.4rem 1rem;
  background: linear-gradient(135deg, #ff9a9e, #fad0c4);
  color: #c44569;
  border-radius: 30px;
  font-size: 0.75rem;
  font-weight: 500;
}

/* Photo Gallery */
.photo-gallery {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-top: 1rem;
}

.gallery-photo {
  aspect-ratio: 1;
  border-radius: 12px;
  overflow: hidden;
  position: relative;
}

.gallery-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-photo {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.6);
  color: white;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.add-photo {
  aspect-ratio: 1;
  border: 2px dashed #d0d0d0;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #999;
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-photo:hover {
  border-color: #ff9a9e;
  color: #c44569;
}

.add-photo span:first-child {
  font-size: 1.5rem;
}

/* Navigation Buttons */
.bottom-nav {
  max-width: 600px;
  margin: 2rem auto 0 auto;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-placeholder {
  width: 100px;
}

.nav-btn {
  padding: 0.875rem 2rem;
  border-radius: 40px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
}

.nav-btn.back {
  background: #f5f5f5;
  color: #333;
}

.nav-btn.back:hover {
  background: #e0e0e0;
}

.nav-btn.next {
  background: linear-gradient(135deg, #ff9a9e, #fad0c4);
  color: #c44569;
  box-shadow: 0 4px 12px rgba(255, 154, 158, 0.3);
}

.nav-btn.next:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(255, 154, 158, 0.4);
}

.nav-btn.next:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Two Column Layout */
.two-col {
  display: flex;
  gap: 0.5rem;
}

/* Spinner */
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(196, 69, 105, 0.3);
  border-top: 2px solid #c44569;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  display: inline-block;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Cropper Modal */
.cropper-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  z-index: 1000;
  display: flex;
  flex-direction: column;
}

.cropper-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #1a1a2e;
  color: white;
}

.cropper-cancel, .cropper-done {
  background: none;
  border: none;
  color: white;
  font-size: 1rem;
  cursor: pointer;
  padding: 0.5rem;
}

.cropper-done {
  color: #ff9a9e;
  font-weight: 600;
}

.cropper-title {
  font-weight: 500;
}

.cropper-body {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.cropper-body img {
  max-width: 100%;
  max-height: 100%;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  background: #1a1a2e;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 40px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.85rem;
  z-index: 1001;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.toast.error {
  background: #dc2626;
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: opacity 0.3s ease;
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
}

/* Step Transitions */
.step-fade-enter-active,
.step-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.step-fade-enter-from {
  opacity: 0;
  transform: translateX(20px);
}

.step-fade-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

/* Responsive */
@media (max-width: 640px) {
  .signup-wrap {
    padding: 1rem;
  }
  
  .step-card {
    padding: 1rem 0;
  }
  
  .step-title {
    font-size: 1.5rem;
  }
  
  .phone-combined-container {
    flex-wrap: wrap;
  }
  
  .country-code-selector {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid #e2e8f0;
  }
  
  .selected-country {
    /* justify-content: space-between; */
    border-radius: 18px 18px 0 0;
  }
  
  .country-dropdown {
    width: 100%;
    left: 0;
    right: 0;
  }
  
  .combined-input {
    border-radius: 0 0 18px 18px !important;
  }
  
  .gender-grid {
    flex-direction: column;
  }
  
  .photo-gallery {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .step-indicator .step-label {
    display: none;
  }
  
  .step-number {
    width: 32px;
    height: 32px;
    font-size: 0.8rem;
  }
  
  .bottom-nav {
    margin-top: 1rem;
  }
  
  .nav-btn {
    padding: 0.75rem 1.5rem;
  }
}
</style>