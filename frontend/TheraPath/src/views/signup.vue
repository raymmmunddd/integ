<template>
  <div class="login-container">
    <div class="login-separation">
      <!-- LEFT SIDE (Form) -->
      <div class="login-form">
        <h2 class="login-title">Create your account</h2>
        <p class="login-subheading">Start your journey to a healthier mind today.</p>

        <!-- Transition wrapper -->
        <div class="step-wrapper">
          <transition :name="transitionName" mode="out-in">
            <div :key="currentStep" class="step-content">
              <!-- Step 1 -->
              <div v-if="currentStep === 1">
                <div class="input-separation">
                  <div class="testing">
                    <p class="login-username">First Name:</p>
                    <input 
                      v-model="formData.first_name" 
                      type="text" 
                      class="username-input"
                      :class="{ 'input-error': fieldErrors.first_name }"
                    />
                    <p v-if="fieldErrors.first_name" class="field-error">{{ fieldErrors.first_name }}</p>
                  </div>

                  <div class="testing">
                    <p class="login-username">Middle Initial:</p>
                    <input v-model="formData.middle_initial" type="text" class="username-input" maxlength="1" />
                  </div>

                  <div class="testing">
                    <p class="login-username">Last Name:</p>
                    <input 
                      v-model="formData.last_name" 
                      type="text" 
                      class="username-input"
                      :class="{ 'input-error': fieldErrors.last_name }"
                    />
                    <p v-if="fieldErrors.last_name" class="field-error">{{ fieldErrors.last_name }}</p>
                  </div>

                  <div class="testing">
                    <p class="login-username">Program:</p>
                    <select v-model="formData.program" class="username-input">
                      <option value="BSIT">BSIT</option>
                      <option value="BSEMC">BSEMC</option>
                      <option value="BSCS">BSCS</option>
                    </select>
                  </div>
                </div>

                <div class="email">
                  <p class="login-username">Email:</p>
                  <input 
                    v-model="formData.email" 
                    type="email" 
                    class="email-input"
                    placeholder="@gordoncollege.edu.ph"
                    :class="{ 'input-error': fieldErrors.email }"
                  />
                  <p v-if="fieldErrors.email" class="field-error">{{ fieldErrors.email }}</p>
                </div>
              </div>

              <!-- Step 2 -->
              <div v-else-if="currentStep === 2">
                <div class="input-separation">
                  <div class="testing">
                    <p class="login-username">Date of birth:</p>
                    <input 
                      v-model="formData.date_of_birth" 
                      type="date" 
                      class="username-input"
                      :class="{ 'input-error': fieldErrors.date_of_birth }"
                    />
                    <p v-if="fieldErrors.date_of_birth" class="field-error">{{ fieldErrors.date_of_birth }}</p>
                  </div>

                  <div class="testing">
                    <p class="login-username">Gender:</p>
                    <select 
                      v-model="formData.gender" 
                      class="username-input"
                      :class="{ 'input-error': fieldErrors.gender }"
                    >
                      <option value="">-- Select Gender --</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                      <option value="Other">Other</option>
                    </select>
                    <p v-if="fieldErrors.gender" class="field-error">{{ fieldErrors.gender }}</p>
                  </div>

                  <div class="testing">
                    <p class="login-username">Barangay:</p>
                    <select 
                      v-model="formData.barangay" 
                      class="username-input"
                      :class="{ 'input-error': fieldErrors.barangay }"
                    >
                      <option value="">-- Select Barangay --</option>
                      <option value="Asinan">Asinan</option>
                      <option value="Banicain">Banicain</option>
                      <option value="Barretto">Barretto</option>
                      <option value="East Bajac-Bajac">East Bajac-Bajac</option>
                      <option value="East Tapinac">East Tapinac</option>
                      <option value="Gordon Heights">Gordon Heights</option>
                      <option value="Kalaklan">Kalaklan</option>
                      <option value="Mabayuan">Mabayuan</option>
                      <option value="New Cabalan">New Cabalan</option>
                      <option value="New Ilalim">New Ilalim</option>
                      <option value="New Kababae">New Kababae</option>
                      <option value="New Kalalake">New Kalalake</option>
                      <option value="Old Cabalan">Old Cabalan</option>
                      <option value="Pag-asa">Pag-asa</option>
                      <option value="Santa Rita">Santa Rita</option>
                      <option value="West Bajac-Bajac">West Bajac-Bajac</option>
                      <option value="West Tapinac">West Tapinac</option>
                    </select>
                    <p v-if="fieldErrors.barangay" class="field-error">{{ fieldErrors.barangay }}</p>
                  </div>

                  <div class="testing">
                    <p class="login-username">Phone Number:</p>
                    <input 
                      v-model="formData.phone_number" 
                      type="text" 
                      class="username-input"
                      :class="{ 'input-error': fieldErrors.phone_number }"
                    />
                    <p v-if="fieldErrors.phone_number" class="field-error">{{ fieldErrors.phone_number }}</p>
                  </div>

                  <div class="testing">
                    <p class="login-username">Create Password:</p>
                    <div class="password-wrapper">
                      <input 
                        v-model="formData.password" 
                        :type="showPassword ? 'text' : 'password'" 
                        class="password-input"
                        :class="{ 'input-error': fieldErrors.password }"
                        @input="updatePasswordStrength"
                      />
                      <button 
                        type="button"
                        class="password-toggle"
                        @click="showPassword = !showPassword"
                      >
                        <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                          <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </button>
                    </div>
                    <!-- Password Strength Indicator -->
                    <div v-if="formData.password" class="password-strength-container">
                      <div class="password-strength-bar" :class="passwordStrength.class"></div>
                      <p class="password-strength-text">{{ passwordStrength.text }}</p>
                      <div class="password-requirements">
                        <p :class="{ 'requirement-met': passwordChecks.minLength }">
                          <span class="requirement-icon">{{ passwordChecks.minLength ? '✓' : '✗' }}</span>
                          At least 8 characters
                        </p>
                        <p :class="{ 'requirement-met': passwordChecks.hasNumber }">
                          <span class="requirement-icon">{{ passwordChecks.hasNumber ? '✓' : '✗' }}</span>
                          At least 1 number
                        </p>
                        <p :class="{ 'requirement-met': passwordChecks.hasLowercase }">
                          <span class="requirement-icon">{{ passwordChecks.hasLowercase ? '✓' : '✗' }}</span>
                          At least 1 lowercase letter
                        </p>
                        <p :class="{ 'requirement-met': passwordChecks.hasUppercase }">
                          <span class="requirement-icon">{{ passwordChecks.hasUppercase ? '✓' : '✗' }}</span>
                          At least 1 uppercase letter
                        </p>
                        <p :class="{ 'requirement-met': passwordChecks.hasSpecialChar }">
                          <span class="requirement-icon">{{ passwordChecks.hasSpecialChar ? '✓' : '✗' }}</span>
                          At least 1 special character
                        </p>
                      </div>
                    </div>
                    <p v-if="fieldErrors.password" class="field-error">{{ fieldErrors.password }}</p>
                  </div>

                  <div class="testing">
                    <p class="login-username">Confirm Password:</p>
                    <div class="password-wrapper">
                      <input 
                        v-model="formData.password_confirmation" 
                        :type="showConfirmPassword ? 'text' : 'password'" 
                        class="password-input"
                        :class="{ 'input-error': fieldErrors.password_confirmation }"
                      />
                      <button 
                        type="button"
                        class="password-toggle"
                        @click="showConfirmPassword = !showConfirmPassword"
                      >
                        <svg v-if="showConfirmPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                          <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </button>
                    </div>
                    <p v-if="fieldErrors.password_confirmation" class="field-error">{{ fieldErrors.password_confirmation }}</p>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Buttons -->
        <div class="button-group">
          <a @click.prevent="handleBack" class="login-button">Back</a>
          
          <a v-if="currentStep < 2" @click.prevent="nextStep" class="login-button">Next</a>
          <a v-if="currentStep === 2" @click.prevent="handleRegister" class="login-button" :disabled="loading">
            {{ loading ? 'Processing...' : 'Finish' }}
          </a>
        </div>

        <div class="dots">
          <span :class="['dot', currentStep === 1 ? 'active' : '']"></span>
          <span :class="['dot', currentStep === 2 ? 'active' : '']"></span>
        </div>
      </div>

      <!-- RIGHT SIDE (Image) -->
      <div class="login-picture">
        <img src="/public/logo.jpg" alt="willie" class="logo" />
      </div>
    </div>

    <!-- OTP Modal -->
    <div v-if="showOtpModal" class="modal-overlay" @click.self="closeOtpModal">
      <div class="otp-modal">
        <h2 class="otp-title">OTP Verification</h2>
        <p class="otp-subheading">We've sent a verification code to {{ formData.email }}. Enter it below to proceed.</p>

        <div v-if="otpError" class="error-message">
          {{ otpError }}
        </div>

        <p class="otp-label">Enter OTP:</p>
        <div class="otp-inputs">
          <input
            v-for="(digit, index) in otpDigits"
            :key="index"
            :ref="el => otpInputs[index] = el"
            v-model="otpDigits[index]"
            type="text"
            maxlength="1"
            class="otp-input"
            @input="handleOtpInput(index, $event)"
            @keydown="handleOtpKeydown(index, $event)"
          />
        </div>

        <button @click="verifyOtp" class="verify-button" :disabled="verifying">
          {{ verifying ? 'Verifying...' : 'Verify OTP' }}
        </button>

        <p class="resend-text">
          Didn't receive the code?
          <a @click="resendOtp" class="resend-link" :class="{ disabled: resendCooldown > 0 }">
            {{ resendCooldown > 0 ? `Resend in ${resendCooldown}s` : 'Resend OTP' }}
          </a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../axios";
import Swal from 'sweetalert2';

const router = useRouter();

const currentStep = ref(1);
const transitionName = ref("slide-right");
const loading = ref(false);
const fieldErrors = reactive({});
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const showOtpModal = ref(false);
const otpDigits = ref(["", "", "", "", "", ""]);
const otpInputs = ref([]);
const otpError = ref("");
const verifying = ref(false);
const resendCooldown = ref(0);

// Password strength tracking
const passwordChecks = reactive({
  minLength: false,
  hasNumber: false,
  hasLowercase: false,
  hasUppercase: false,
  hasSpecialChar: false
});

const formData = reactive({
  first_name: "",
  middle_initial: "",
  last_name: "",
  program: "BSIT",
  email: "",
  date_of_birth: "",
  gender: "",
  barangay: "",
  city_municipality: "Olongapo",
  phone_number: "",
  password: "",
  password_confirmation: ""
});

function updatePasswordStrength() {
  const password = formData.password;
  
  passwordChecks.minLength = password.length >= 8;
  passwordChecks.hasNumber = /[0-9]/.test(password);
  passwordChecks.hasLowercase = /[a-z]/.test(password);
  passwordChecks.hasUppercase = /[A-Z]/.test(password);
  passwordChecks.hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
}

const passwordStrength = computed(() => {
  if (!formData.password) {
    return { text: '', class: '' };
  }
  
  const metCount = Object.values(passwordChecks).filter(Boolean).length;
  
  if (metCount === 5) {
    return { text: 'Strong password', class: 'strength-strong' };
  } else if (metCount >= 3) {
    return { text: 'Medium password. Must contain:', class: 'strength-medium' };
  } else {
    return { text: 'Weak password. Must contain:', class: 'strength-weak' };
  }
});

function clearFieldErrors() {
  Object.keys(fieldErrors).forEach(key => delete fieldErrors[key]);
}

function validateEmail(email) {
  if (!email) {
    return "Email is required";
  }
  if (!email.endsWith("@gordoncollege.edu.ph")) {
    return "Only school domain (@gordoncollege.edu.ph) can be used";
  }
  return null;
}

function validatePassword(password) {
  if (!password) {
    return "Password is required";
  }
  if (password.length < 8) {
    return "Password must be at least 8 characters";
  }
  
  const hasUpperCase = /[A-Z]/.test(password);
  const hasLowerCase = /[a-z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
  
  if (!hasUpperCase) {
    return "Password must contain at least one uppercase letter";
  }
  if (!hasLowerCase) {
    return "Password must contain at least one lowercase letter";
  }
  if (!hasNumber) {
    return "Password must contain at least one number";
  }
  if (!hasSpecialChar) {
    return "Password must contain at least one special character";
  }
  
  return null;
}

function nextStep() {
  if (currentStep.value < 2) {
    clearFieldErrors();
    
    let hasError = false;
    
    if (!formData.first_name) {
      fieldErrors.first_name = "First name is required";
      hasError = true;
    }
    
    if (!formData.last_name) {
      fieldErrors.last_name = "Last name is required";
      hasError = true;
    }
    
    const emailError = validateEmail(formData.email);
    if (emailError) {
      fieldErrors.email = emailError;
      hasError = true;
    }
    
    if (hasError) return;
    
    transitionName.value = "slide-right";
    currentStep.value++;
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    clearFieldErrors();
    transitionName.value = "slide-left";
    currentStep.value--;
  }
}

function handleBack() {
  if (currentStep.value === 1) {
    window.location.href = "/login";
  } else {
    prevStep();
  }
}

async function handleRegister() {
  clearFieldErrors();
  
  let hasError = false;
  
  if (!formData.date_of_birth) {
    fieldErrors.date_of_birth = "Date of birth is required";
    hasError = true;
  }
  
  if (!formData.gender) {
    fieldErrors.gender = "Gender is required";
    hasError = true;
  }
  
  if (!formData.barangay) {
    fieldErrors.barangay = "Barangay is required";
    hasError = true;
  }
  
  if (!formData.phone_number) {
    fieldErrors.phone_number = "Phone number is required";
    hasError = true;
  }
  
  const passwordError = validatePassword(formData.password);
  if (passwordError) {
    fieldErrors.password = passwordError;
    hasError = true;
  }
  
  if (!formData.password_confirmation) {
    fieldErrors.password_confirmation = "Please confirm your password";
    hasError = true;
  } else if (formData.password !== formData.password_confirmation) {
    fieldErrors.password_confirmation = "Passwords do not match";
    hasError = true;
  }
  
  if (hasError) return;

  loading.value = true;

  try {
    // Parallel CSRF and registration - much faster
    const csrfPromise = api.get('/sanctum/csrf-cookie');
    
    // Wait for CSRF, then immediately send registration
    await csrfPromise;
    await api.post('/api/register', formData);
    
    showOtpModal.value = true;
    startResendCooldown();
  } catch (error) {
    console.error('Registration failed:', error);
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(key => {
        fieldErrors[key] = errors[key][0];
      });
    } else if (error.response?.data?.message) {
      fieldErrors.general = error.response.data.message;
    } else {
      fieldErrors.general = "Registration failed. Please try again.";
    }
  } finally {
    loading.value = false;
  }
}

function handleOtpInput(index, event) {
  const value = event.target.value;
  
  if (!/^\d*$/.test(value)) {
    otpDigits.value[index] = "";
    return;
  }

  otpDigits.value[index] = value;

  if (value && index < 5) {
    otpInputs.value[index + 1]?.focus();
  }
}

function handleOtpKeydown(index, event) {
  if (event.key === "Backspace" && !otpDigits.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus();
  }
}

async function verifyOtp() {
  const otp = otpDigits.value.join("");
  
  if (otp.length !== 6) {
    otpError.value = "Please enter all 6 digits";
    return;
  }

  verifying.value = true;
  otpError.value = "";

  try {
    await api.post('/api/verify-otp', {
      email: formData.email,
      otp: otp
    });

    showOtpModal.value = false;
    
    // Show success message with SweetAlert2
    await Swal.fire({
      icon: 'success',
      title: 'Account Created!',
      text: 'Your registration was successful. You can now login.',
      confirmButtonText: 'Go to Login',
      confirmButtonColor: '#75BDE5',
      timer: 3000,
      timerProgressBar: true
    });
    
    router.push('/login');
  } catch (error) {
    console.error('OTP verification failed:', error);
    if (error.response?.data?.message) {
      otpError.value = error.response.data.message;
    } else {
      otpError.value = "Invalid or expired OTP. Please try again.";
    }
    otpDigits.value = ["", "", "", "", "", ""];
    otpInputs.value[0]?.focus();
  } finally {
    verifying.value = false;
  }
}

async function resendOtp() {
  if (resendCooldown.value > 0) return;

  try {
    await api.post('/api/resend-otp', {
      email: formData.email
    });
    
    otpError.value = "";
    otpDigits.value = ["", "", "", "", "", ""];
    startResendCooldown();
  } catch (error) {
    console.error('Resend OTP failed:', error);
    otpError.value = "Failed to resend OTP. Please try again.";
  }
}

function startResendCooldown() {
  resendCooldown.value = 60;
  const interval = setInterval(() => {
    resendCooldown.value--;
    if (resendCooldown.value <= 0) {
      clearInterval(interval);
    }
  }, 1000);
}

function closeOtpModal() {
}
</script>

<style scoped src="../assets/CSS Files/signup.css"></style>