<template>
  <div class="login-container">
    <div class="login-separation">
      <div class="login-picture">
        <img src="/public/logo.jpg" alt="willie" class="logo" />
      </div>

      <div class="login-form">
        <h2 class="login-title">Therapist Login</h2>
        <p class="login-subheading">
          Access your professional dashboard.
        </p>

        <div class="form-group">
          <p class="login-username">Email</p>
          <input
            v-model="email"
            type="email"
            class="username-input"
            placeholder="Email"
            @keyup.enter="handleLogin"
            :disabled="loading"
            :class="{ 'input-error': emailError }"
          />
          <p v-if="emailError" class="error-text">{{ emailError }}</p>
        </div>

        <div class="form-group">
          <p class="login-password">Password</p>
          <div class="password-wrapper">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              class="password-input"
              placeholder="Password"
              @keyup.enter="handleLogin"
              :disabled="loading"
              :class="{ 'input-error': passwordError }"
            />
            <button
              type="button"
              class="action-icon"
              @click="showPassword = !showPassword"
              :disabled="loading"
            >
              <i :class="showPassword ? 'bxr bx-eye' : 'bxr bx-eye-slash'"></i>
            </button>
          </div>
          <p v-if="passwordError" class="error-text">{{ passwordError }}</p>
        </div>

        <a href="/ForgotPass" class="forgot-password">Forgot Password?</a>

        <button
          @click="handleLogin"
          class="login-button"
          :disabled="loading"
        >
          {{ loading ? "Logging in..." : "Login" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from "vue";
import { useRouter } from "vue-router";
import Swal from 'sweetalert2';

const router = useRouter();
const auth = inject("auth");

const email = ref("");
const password = ref("");
const showPassword = ref(false);
const loading = ref(false);

const emailError = ref("");
const passwordError = ref("");

const handleLogin = async () => {
  // reset errors
  emailError.value = "";
  passwordError.value = "";

  // validation
  if (!email.value) emailError.value = "Email is required";
  if (!password.value) passwordError.value = "Password is required";

  if (emailError.value || passwordError.value) return;

  loading.value = true;

  try {
    await auth.login(email.value, password.value);
    console.log("User logged in:", auth.user.value);

    // Block students from therapist login
    if (auth.user.value.role !== "therapist") {
      await Swal.fire({
        icon: 'error',
        title: 'Access Denied',
        text: 'This is the therapist login. Please use the student login page.',
        confirmButtonColor: '#d33'
      });
      await auth.logout();
      return;
    }

    // Success message
    await Swal.fire({
      icon: 'success',
      title: 'Login Successful',
      text: 'Welcome back!',
      timer: 1500,
      showConfirmButton: false
    });

    // Only allow therapists
    router.push("/DashboardTherapist");
  } catch (err) {
    console.error("Login failed:", err);

    let errorMsg = "Login failed. Please try again.";

    if (err.response?.status === 422) {
      errorMsg = err.response.data.message || "Invalid email or password.";
    } else if (err.response?.status === 419) {
      errorMsg = "Session expired. Please refresh and try again.";
    }

    await Swal.fire({
      icon: 'error',
      title: 'Login Failed',
      text: errorMsg,
      confirmButtonColor: '#d33'
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped src="../assets/CSS Files/login.css"></style>