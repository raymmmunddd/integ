<template>
  <div class="login-container">
    <div class="login-separation">

      <!-- LEFT SIDE (Form) -->
      <div class="login-form">
        <div class="login-center">

          <!-- Transition wrapper -->
          <div class="step-wrapper">
            <transition :name="transitionName" mode="out-in">
              <div :key="currentStep" class="step-content">

                <!-- Step 1: Email Input -->
                <div v-if="currentStep === 1">
                  <h2 class="login-title">Forgot Your Password?</h2>
                  <p class="login-subheading">
                    No worries, let's help you get back on track.
                  </p>
                  <div class="email">
                    <p class="login-username">Email:</p>
                    <input 
                      v-model="email" 
                      type="email" 
                      class="email-input" 
                      placeholder="Enter your email"
                      @keyup.enter="sendOtp"
                      :disabled="loading"
                    />
                    <p v-if="errors.email" class="error-message">{{ errors.email }}</p>
                  </div>
                </div>

                <!-- Step 2: OTP Verification -->
                <div v-else-if="currentStep === 2">
                  <h2 class="login-title">OTP Verification</h2>
                  <p class="login-subheading">
                    We've sent a verification code to <strong>{{ email }}</strong>. Enter it below to proceed.
                  </p>
                  <div class="otp-container">
                    <p class="login-otp">Enter OTP:</p>
                    <div class="otp-input">
                      <input 
                        v-for="(digit, index) in otpDigits" 
                        :key="index"
                        v-model="otpDigits[index]"
                        type="text" 
                        maxlength="1" 
                        inputmode="numeric" 
                        pattern="[0-9]*"
                        @input="handleOtpInput($event, index)"
                        @keydown="handleOtpKeydown($event, index)"
                        @paste="handleOtpPaste"
                        :ref="el => otpInputs[index] = el"
                        :disabled="loading"
                      />
                    </div>
                    <p v-if="errors.otp" class="error-message">{{ errors.otp }}</p>
                    <p class="resend-text">
                      Didn't receive the code? 
                      <a @click="resendOtp" :class="{ 'disabled-link': loading || resendCooldown > 0 }">
                        {{ resendCooldown > 0 ? `Resend OTP (${resendCooldown}s)` : 'Resend OTP' }}
                      </a>
                    </p>
                  </div>
                </div>

                <!-- Step 3: New Password -->
                <div v-else-if="currentStep === 3">
                  <h2 class="login-title">Create Your Password</h2>
                  <p class="login-subheading">
                    Set a strong new password to secure your account.
                  </p>
                  <div class="input-separation">
                    <div class="testing">
                      <p class="login-username">New Password:</p>
                      <div class="password-wrapper">
                        <input 
                          v-model="password" 
                          :type="showPassword ? 'text' : 'password'" 
                          class="username-input"
                          placeholder="Enter new password"
                          :disabled="loading"
                        />
                        <button
                          type="button"
                          class="action-icon"
                          @click="showPassword = !showPassword"
                          :disabled="loading"
                        >
                          <i :class="showPassword ? 'bx bx-eye' : 'bx bx-eye-slash'"></i>
                        </button>
                      </div>
                      <p v-if="errors.password" class="error-message">{{ errors.password }}</p>
                    </div>
                    <div class="testing">
                      <p class="login-username">Confirm Password:</p>
                      <div class="password-wrapper">
                        <input 
                          v-model="passwordConfirmation" 
                          :type="showConfirmPassword ? 'text' : 'password'" 
                          class="username-input"
                          placeholder="Confirm new password"
                          @keyup.enter="resetPassword"
                          :disabled="loading"
                        />
                        <button
                          type="button"
                          class="action-icon"
                          @click="showConfirmPassword = !showConfirmPassword"
                          :disabled="loading"
                        >
                          <i :class="showConfirmPassword ? 'bx bx-eye' : 'bx bx-eye-slash'"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </transition>
          </div>

          <!-- Buttons -->
          <div class="button-group">
            <a
              @click.prevent="handleNext"
              class="login-button next-button"
              :class="{ 'disabled': loading }"
            >
              {{ loading ? 'Processing...' : buttonText }}
            </a>

            <a
              v-if="currentStep === 1"
              href="/login"
              class="login-button back-button"
              :class="{ 'disabled': loading }"
            >
              Back
            </a>
            <a
              v-else
              @click.prevent="handleBack"
              class="login-button back-button"
              :class="{ 'disabled': loading }"
            >
              Back
            </a>
          </div>
        </div>

        <!-- Dots -->
        <div class="dots">
          <span
            v-for="n in totalSteps"
            :key="n"
            :class="['dot', currentStep === n ? 'active' : '']"
          ></span>
        </div>
      </div>

      <!-- RIGHT SIDE (Image) -->
      <div class="login-picture">
        <img src="/public/logo.jpg" alt="logo" class="logo" />
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import api from "../axios";
import Swal from 'sweetalert2';

const router = useRouter();

const currentStep = ref(1);
const totalSteps = 3;
const transitionName = ref("slide-right");
const loading = ref(false);
const resendCooldown = ref(0);
let resendTimer = null;

// Form data
const email = ref("");
const otpDigits = ref(["", "", "", "", "", ""]);
const password = ref("");
const passwordConfirmation = ref("");
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Messages
const errors = ref({});

// OTP input refs
const otpInputs = ref([]);

const otp = computed(() => otpDigits.value.join(""));

const buttonText = computed(() => {
  if (currentStep.value === 1) return "Send OTP";
  if (currentStep.value === 2) return "Verify OTP";
  return "Reset Password";
});

// Start resend cooldown timer
const startResendCooldown = () => {
  resendCooldown.value = 60; // 60 seconds cooldown
  resendTimer = setInterval(() => {
    resendCooldown.value--;
    if (resendCooldown.value <= 0) {
      clearInterval(resendTimer);
    }
  }, 1000);
};

// Send OTP to email
const sendOtp = async () => {
  if (loading.value) return;
  
  errors.value = {};

  if (!email.value) {
    errors.value.email = "Email is required";
    return;
  }

  loading.value = true;

  try {
    const response = await api.post("/api/forgot-password", {
      email: email.value,
    });

    await Swal.fire({
      icon: 'success',
      title: 'OTP Sent!',
      text: response.data.message,
      confirmButtonColor: '#75BDE5',
      timer: 3000,
      timerProgressBar: true
    });

    startResendCooldown();
    nextStep();
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || 'Failed to send OTP. Please try again.',
        confirmButtonColor: '#75BDE5'
      });
    }
  } finally {
    loading.value = false;
  }
};

// Verify OTP
const verifyOtp = async () => {
  if (loading.value) return;
  
  errors.value = {};

  if (otp.value.length !== 6) {
    errors.value.otp = "Please enter the complete 6-digit OTP";
    return;
  }

  loading.value = true;

  try {
    const response = await api.post("/api/verify-password-otp", {
      email: email.value,
      otp: otp.value,
    });

    await Swal.fire({
      icon: 'success',
      title: 'Verified!',
      text: response.data.message,
      confirmButtonColor: '#75BDE5',
      timer: 2000,
      timerProgressBar: true,
      showConfirmButton: false
    });

    nextStep();
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      await Swal.fire({
        icon: 'error',
        title: 'Invalid OTP',
        text: error.response?.data?.message || 'The OTP code is invalid or expired.',
        confirmButtonColor: '#75BDE5'
      });
    }
  } finally {
    loading.value = false;
  }
};

// Reset password
const resetPassword = async () => {
  if (loading.value) return;
  
  errors.value = {};

  if (!password.value) {
    errors.value.password = "Password is required";
    return;
  }

  if (password.value !== passwordConfirmation.value) {
    errors.value.password = "Passwords do not match";
    return;
  }

  if (password.value.length < 8) {
    errors.value.password = "Password must be at least 8 characters";
    return;
  }

  loading.value = true;

  try {
    const response = await api.post("/api/reset-password", {
      email: email.value,
      otp: otp.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });

    await Swal.fire({
      icon: 'success',
      title: 'Password Reset!',
      text: response.data.message,
      confirmButtonColor: '#75BDE5',
      timer: 2000,
      timerProgressBar: true,
      showConfirmButton: false
    });
    
    setTimeout(() => {
      router.push("/login");
    }, 2000);
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || 'Failed to reset password. Please try again.',
        confirmButtonColor: '#75BDE5'
      });
    }
  } finally {
    loading.value = false;
  }
};

// Resend OTP
const resendOtp = async () => {
  if (loading.value || resendCooldown.value > 0) return;
  
  errors.value = {};
  loading.value = true;

  try {
    await api.post("/api/resend-password-otp", {
      email: email.value,
    });

    await Swal.fire({
      icon: 'success',
      title: 'OTP Resent!',
      text: 'A new OTP has been sent to your email',
      confirmButtonColor: '#75BDE5',
      timer: 2000,
      timerProgressBar: true,
      showConfirmButton: false
    });

    otpDigits.value = ["", "", "", "", "", ""];
    startResendCooldown();
    
    // Focus first OTP input
    setTimeout(() => {
      otpInputs.value[0]?.focus();
    }, 100);
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to resend OTP. Please try again.',
      confirmButtonColor: '#75BDE5'
    });
  } finally {
    loading.value = false;
  }
};

// Handle next button click
const handleNext = () => {
  if (currentStep.value === 1) {
    sendOtp();
  } else if (currentStep.value === 2) {
    verifyOtp();
  } else if (currentStep.value === 3) {
    resetPassword();
  }
};

// Navigation
const nextStep = () => {
  if (currentStep.value < totalSteps) {
    transitionName.value = "slide-right";
    currentStep.value++;
  }
};

const handleBack = () => {
  if (loading.value) return;
  
  if (currentStep.value > 1) {
    transitionName.value = "slide-left";
    currentStep.value--;
    errors.value = {};
  }
};

// OTP input handlers
const handleOtpInput = (event, index) => {
  const value = event.target.value;
  
  if (!/^\d*$/.test(value)) {
    otpDigits.value[index] = "";
    return;
  }

  otpDigits.value[index] = value;

  if (value && index < 5) {
    otpInputs.value[index + 1]?.focus();
  }
};

const handleOtpKeydown = (event, index) => {
  if (event.key === "Backspace" && !otpDigits.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus();
  }
};

const handleOtpPaste = (event) => {
  event.preventDefault();
  const pastedData = event.clipboardData.getData("text").slice(0, 6);
  
  if (!/^\d+$/.test(pastedData)) return;

  const digits = pastedData.split("");
  digits.forEach((digit, index) => {
    if (index < 6) {
      otpDigits.value[index] = digit;
    }
  });

  const focusIndex = Math.min(digits.length, 5);
  otpInputs.value[focusIndex]?.focus();
};

// Cleanup on component unmount
onUnmounted(() => {
  if (resendTimer) {
    clearInterval(resendTimer);
  }
});
</script>

<style scoped src="../assets/CSS Files/ForgotPass.css"></style>