<template>
  <SidebarTherapist />
  <div class="profile-page">
    <h1>Profile</h1>

    <div class="container">
      <div class="edit-profile-container">
        <h2>Edit Therapist Profile</h2>

        <form class="edit-form" @submit.prevent="saveProfile">
          <!-- Row 1 -->
          <div class="form-row">
            <div class="form-group">
              <label>Full Name</label>
              <input v-model="profile.name" type="text" placeholder="Enter full name" disabled />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input v-model="profile.email" type="email" placeholder="Enter email" disabled />
            </div>
          </div>

          <!-- Row 2 -->
          <div class="form-row">
            <div class="form-group">
              <label>Phone No.</label>
              <input v-model="profile.phone" type="text" placeholder="Enter phone number" required />
            </div>

            <div class="form-group">
              <label>Specialization</label>
              <select v-model="profile.specialization" class="specialization" required>
                <option value="">-- Select Specialization --</option>
                <option
                  v-for="spec in availableSpecializations"
                  :key="spec.id"
                  :value="spec.id"
                >
                  {{ spec.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Row 3 -->
          <div class="form-row">
            <div class="form-group">
              <label>Years of Experience</label>
              <select v-model="profile.experience" required>
                <option value="">-- Select Experience --</option>
                <option>1-5 years</option>
                <option>6-10 years</option>
                <option>11-15 years</option>
                <option>16-20 years</option>
                <option>21+ years</option>
              </select>
            </div>

            <div class="form-group">
              <label>License Number</label>
              <input 
                v-model="profile.license" 
                type="text" 
                placeholder="Enter license number" 
              />
            </div>
          </div>

          <!-- Bio -->
          <div class="form-row single">
            <div class="form-group full">
              <label>Bio</label>
              <textarea
                v-model="profile.bio"
                rows="3"
                placeholder="Write something about yourself"
              ></textarea>
            </div>
          </div>

          <!-- Availability -->
          <div class="form-row single">
            <div class="form-group full">
              <label>Availability / Schedule</label>
              <div class="availability-box">
                <div class="days">
                  <label><input type="checkbox" value="Monday" v-model="profile.days" /> Mon</label>
                  <label><input type="checkbox" value="Tuesday" v-model="profile.days" /> Tue</label>
                  <label><input type="checkbox" value="Wednesday" v-model="profile.days" /> Wed</label>
                  <label><input type="checkbox" value="Thursday" v-model="profile.days" /> Thu</label>
                  <label><input type="checkbox" value="Friday" v-model="profile.days" /> Fri</label>
                </div>

                <div class="time-container">
                  <div class="time">
                    <label>
                      Start: 
                      <input 
                        type="time" 
                        v-model="profile.startTime" 
                        required 
                      />
                    </label>
                    <label>
                      End: 
                      <input 
                        type="time" 
                        v-model="profile.endTime" 
                        required 
                      />
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Profile Photo Upload -->
          <div class="form-row single">
            <div class="form-group full">
              <label>Profile Photo</label>
              <div class="upload-box" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
                <input 
                  type="file" 
                  ref="fileInput" 
                  @change="handlePhotoUpload" 
                  accept="image/*" 
                  style="display: none;"
                />
                <div v-if="!profile.photo" class="upload-placeholder">
                  <div class="upload-icon bxr bx-image"></div>
                  <p>Click to upload or browse a file to upload</p>
                </div>
                <div v-else class="photo-preview">
                  <img :src="profile.photo" alt="Profile Preview" />
                </div>
              </div>
              <div v-if="photoFile" class="file-info">
                <span>📎 {{ photoFile.name }}</span>
                <button type="button" class="cancel-file-btn" @click="cancelPhoto">Cancel</button>
              </div>
            </div>
          </div>

          <button type="submit" class="save-btn" :disabled="isLoading">
            {{ isLoading ? "Saving..." : "Save Changes" }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";
import api from "../axios";
import SidebarTherapist from "../components/SidebarTherapist.vue";

const profile = ref({
  name: "",
  email: "",
  phone: "",
  specialization: "",
  experience: "",
  license: "",
  bio: "",
  days: [],
  startTime: "",
  endTime: "",
  photo: ""
});

const availableSpecializations = ref([]);
const isLoading = ref(false);
const photoFile = ref(null);
const fileInput = ref(null);

// Parse time value from various formats
const parseTimeValue = (timeValue) => {
  if (!timeValue) return "";
  
  const timeStr = String(timeValue);
  
  // If it's a datetime string (contains date), extract time part
  if (timeStr.includes('T')) {
    // Format: 2025-01-01T09:00:00
    return timeStr.split('T')[1].substring(0, 5);
  }
  
  // If it contains a space (datetime with space)
  if (timeStr.includes(' ')) {
    // Format: 2025-01-01 09:00:00
    return timeStr.split(' ')[1].substring(0, 5);
  }
  
  // If it's just time format HH:MM:SS or HH:MM
  return timeStr.substring(0, 5);
};

// Fetch specializations
const fetchSpecializations = async () => {
  try {
    const response = await api.get("/api/appointments/specializations");
    availableSpecializations.value = response.data;
  } catch (error) {
    console.error("Failed to fetch specializations:", error);
  }
};

// Fetch therapist profile
const fetchProfile = async () => {
  try {
    const res = await api.get("/api/therapist/profile");
    const data = res.data;
    
    console.log("Raw API response:", data);

    profile.value.name = data.full_name;
    profile.value.email = data.email;
    profile.value.phone = data.phone_number || "";
    profile.value.bio = data.bio || "";
    profile.value.experience = data.years_of_experience || "";

    if (data.specializations?.length) {
      profile.value.specialization = data.specializations[0].id;
    }

    if (data.licenses?.length) {
      profile.value.license = data.licenses[0];
    }

    if (data.availabilities?.length) {
      const avail = data.availabilities[0];
      // Parse time - handle datetime, time, or string formats
      profile.value.startTime = parseTimeValue(avail.start_time);
      profile.value.endTime = parseTimeValue(avail.end_time);
      profile.value.days = avail.days || [];
    }
    
    console.log("Loaded profile data:", {
      startTime: profile.value.startTime,
      endTime: profile.value.endTime,
      days: profile.value.days,
      rawAvailabilities: data.availabilities
    });

    profile.value.photo = data.image || "";
  } catch (error) {
    console.error("Failed to fetch therapist profile:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Failed to load profile data"
    });
  }
};

// Trigger file input click
const triggerFileInput = () => {
  fileInput.value?.click();
};

// Handle photo upload
const handlePhotoUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    Swal.fire({
      icon: "error",
      title: "Invalid File",
      text: "Please upload an image file"
    });
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    Swal.fire({
      icon: "error",
      title: "File Too Large",
      text: "Image size must be less than 5MB"
    });
    return;
  }
  
  photoFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    profile.value.photo = e.target.result;
  };
  reader.readAsDataURL(file);
};

// Handle drag and drop
const handleDrop = (event) => {
  const file = event.dataTransfer.files[0];
  if (file?.type.startsWith('image/')) {
    handlePhotoUpload({ target: { files: [file] } });
  }
};

// Cancel photo upload
const cancelPhoto = () => {
  profile.value.photo = "";
  photoFile.value = null;
  if (fileInput.value) {
    fileInput.value.value = "";
  }
};

// Save profile
const saveProfile = async () => {
  isLoading.value = true;

  if (!profile.value.phone) {
    Swal.fire({ icon: "error", title: "Validation Error", text: "Phone number is required" });
    isLoading.value = false;
    return;
  }
  if (!profile.value.specialization) {
    Swal.fire({ icon: "error", title: "Validation Error", text: "Please select a specialization" });
    isLoading.value = false;
    return;
  }
  if (!profile.value.experience) {
    Swal.fire({ icon: "error", title: "Validation Error", text: "Please select years of experience" });
    isLoading.value = false;
    return;
  }
  if (!profile.value.days.length) {
    Swal.fire({ icon: "error", title: "Validation Error", text: "Select at least one available day" });
    isLoading.value = false;
    return;
  }
  if (!profile.value.startTime || !profile.value.endTime) {
    Swal.fire({ icon: "error", title: "Validation Error", text: "Please set start and end times" });
    isLoading.value = false;
    return;
  }

  const payload = {
    phone_number: profile.value.phone,
    specializations: [profile.value.specialization],
    years_of_experience: profile.value.experience,
    licenses: profile.value.license ? [profile.value.license] : [],
    bio: profile.value.bio,
    availabilities: [{
      days: profile.value.days,
      start_time: profile.value.startTime,
      end_time: profile.value.endTime
    }]
  };

  try {
    const res = await api.put("/api/therapist/profile", payload);
    await Swal.fire({
      icon: "success",
      title: "Success",
      text: res.data.message || "Profile updated successfully!",
      timer: 2000
    });
    await fetchProfile();
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: err.response?.data?.message || "Failed to update profile"
    });
    console.error("Update error:", err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(async () => {
  await fetchSpecializations();
  await fetchProfile();
});
</script>

<style scoped src="../assets/CCS Therapist/profile.css"></style>