<template>
  <Sidebar />

  <main>
    <div class="title-search-separation">
      <h2 class="doctors-title">Our Doctors</h2>

      <div class="search-wrapper">
        <input 
          type="search" 
          class="search" 
          placeholder="Search by name..." 
          v-model="searchQuery"
          @input="debouncedSearch"
        />
        <i class="bx bx-search search-icon"></i>
      </div>
    </div>

    <select 
      id="specialization" 
      class="specialization" 
      v-model="selectedSpecialization"
      @change="fetchTherapists"
    >
      <option value="">-- All Doctors --</option>
      <option 
        v-for="spec in specializations" 
        :key="spec.id" 
        :value="spec.name"
      >
        {{ spec.name }}
      </option>
    </select>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <p>Loading therapists...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-state">
      <p>{{ error }}</p>
      <button @click="fetchTherapists">Retry</button>
    </div>

    <!-- LISTS OF DOCTORS -->
    <div v-if="!loading && !error" class="lists-doctors-container">
      <div v-if="doctors.length === 0" class="no-results">
        <p>No therapists found matching your criteria.</p>
      </div>

      <div class="lists-doctors-container">
        <div class="doctor-card" v-for="doctor in doctors" :key="doctor.id">
          <img :src="getProfileImageUrl(doctor)" :alt="doctor.name" />
          <h2 class="doctors-name">Dr. {{ doctor.name }}</h2>
          <p class="specialization-subheading">{{ doctor.specialization }}</p>
          <p v-if="doctor.years_of_experience" class="specialization-subheading">
            {{ doctor.years_of_experience }}
          </p>
          <div class="button-arrange">
            <button 
              class="appointment-button" 
              @click="openModal(doctor)"
              :class="{ 'has-appointment': doctor.hasAppointment }"
            >
              {{ doctor.hasAppointment ? "Edit Appointment" : "Make Appointment" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Appointment Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="content-header">
          <h2>Schedule Your Consultation</h2>
          <p class="x-button" @click="closeModal">X</p>
        </div>

        <p class="p-assigned">Your Assigned Doctor</p>

        <div class="modal-doctor-card">
          <img :src="getProfileImageUrl(activeDoctor)" class="avatar" />
          <div class="doctor-info">
            <h3>Dr. {{ activeDoctor?.name }}</h3>
            <p class="role">{{ activeDoctor?.specialization }}</p>

            <div class="availability">
              <div class="days">
                <p>Available Days:</p>
                <span 
                  class="tag" 
                  v-for="day in activeDoctor?.availableDays" 
                  :key="day"
                >
                  {{ day }}
                </span>
              </div>

              <div class="times">
                <p>Available Times:</p>
                <div v-for="time in getUniqueTimes(activeDoctor?.availableTimes)" :key="time">
                  <span>{{ time }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Appointment Form -->
        <form @submit.prevent="confirmAppointment" class="appointment-form">
          <div>
            <label for="date">Select Date:</label>
            <input 
              type="date" 
              id="date" 
              v-model="selectedDate" 
              :min="minDate"
              @change="checkAvailability"
              required
              class="date-input"
            />
            <p v-if="dateMessage" class="date-message" :class="{ 'error': !dateAvailable }">
              {{ dateMessage }}
            </p>
          </div>

          <div>
            <label for="time">Select Time:</label>
            <select 
              id="time" 
              v-model="selectedTime"
              :disabled="!dateAvailable || loadingTimeSlots"
              required
            >
              <option value="">{{ loadingTimeSlots ? 'Loading...' : 'Choose a time' }}</option>
              <option 
                v-for="slot in availableTimeSlots" 
                :key="slot.value.start"
                :value="slot.value"
              >
                {{ slot.display }}
              </option>
            </select>
            <p v-if="availableTimeSlots.length === 0 && selectedDate && !loadingTimeSlots" class="no-slots">
              No available time slots for this date.
            </p>
          </div>

          <div>
            <label for="treatment">Treatment & Session Type:</label>
            <select id="treatment" v-model="selectedTreatment" required>
              <option value="">Choose Treatment Type</option>
              <option value="Individual Therapy">Individual Therapy</option>
              <option value="Group Therapy">Group Therapy</option>
              <option value="Family Therapy">Family Therapy</option>
              <option value="Assessment">Assessment</option>
              <option value="Follow-up">Follow-up</option>
            </select>
          </div>

          <div>
            <label for="appointment">Appointment Type:</label>
            <select id="appointment" v-model="selectedAppointmentType" required>
              <option value="">Choose Appointment Type</option>
              <option value="online">Online</option>
              <option value="physical">Physical</option>
            </select>
          </div>
        </form>

        <!-- Loading state for form submission -->
        <div v-if="submitting" class="submitting-state">
          <p>Processing your request...</p>
        </div>

        <!-- Confirm / Edit button -->
        <button 
          class="confirm-btn" 
          @click="confirmAppointment"
          :disabled="submitting || !dateAvailable"
        >
          {{ activeDoctor?.hasAppointment ? "Update Appointment" : "Schedule Appointment" }}
        </button>

        <!-- Cancel Appointment button -->
        <button 
          v-if="activeDoctor?.hasAppointment" 
          class="cancel-btn" 
          @click="cancelAppointment"
          :disabled="submitting"
        >
          Cancel Appointment
        </button>
      </div>
    </div>
  </main>
</template>

<script setup>
import Sidebar from "./Sidebar.vue";
import { ref, computed, onMounted } from "vue";
import api from '../axios';
import Swal from 'sweetalert2';

const BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000";

const doctors = ref([]);
const specializations = ref([]);
const loading = ref(false);
const error = ref(null);
const submitting = ref(false);

// Modal and form states
const showModal = ref(false);
const activeDoctor = ref(null);
const selectedDate = ref("");
const selectedTime = ref("");
const selectedTreatment = ref("");
const selectedAppointmentType = ref("");

// Availability checking
const dateAvailable = ref(true);
const dateMessage = ref("");
const availableTimeSlots = ref([]);
const loadingTimeSlots = ref(false);

// Filters
const searchQuery = ref("");
const selectedSpecialization = ref("");

// Computed minimum date
const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

let searchTimeout = null;

// Fetch therapists from API
const fetchTherapists = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await api.get('/api/appointments/therapists', {
      params: {
        search: searchQuery.value,
        specialization: selectedSpecialization.value
      }
    });
    doctors.value = response.data;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load therapists. Please try again.';
    console.error('Error fetching therapists:', err);
  } finally {
    loading.value = false;
  }
};

// Fetch specializations from API
const fetchSpecializations = async () => {
  try {
    const response = await api.get('/api/appointments/specializations');
    specializations.value = response.data;
  } catch (err) {
    console.error('Error fetching specializations:', err);
  }
};

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchTherapists();
  }, 500);
};

// Check availability for selected date
const checkAvailability = async () => {
  if (!selectedDate.value || !activeDoctor.value) return;

  loadingTimeSlots.value = true;
  dateMessage.value = "";
  selectedTime.value = "";
  availableTimeSlots.value = [];

  try {
    const response = await api.get('/api/appointments/available-slots', {
      params: {
        therapist_id: activeDoctor.value.id,
        date: selectedDate.value
      }
    });

    if (response.data.available) {
      dateAvailable.value = true;
      availableTimeSlots.value = response.data.timeSlots;
      dateMessage.value = `Available on ${response.data.dayOfWeek}`;
    } else {
      dateAvailable.value = false;
      dateMessage.value = response.data.message || 'This therapist is not available on this date.';
      availableTimeSlots.value = [];
    }
  } catch (err) {
    console.error('Error checking availability:', err);
    dateAvailable.value = false;
    dateMessage.value = 'Error checking availability. Please try again.';
  } finally {
    loadingTimeSlots.value = false;
  }
};

// Format time for display 
const formatTime = (time) => {
  const [hours, minutes] = time.split(':');
  const hour = parseInt(hours);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
  return `${displayHour}:${minutes} ${ampm}`;
};

// Get unique available times for display in modal
const getUniqueTimes = (times) => {
  if (!times) return [];
  const unique = new Set();
  times.forEach(t => {
    unique.add(`${formatTime(t.start)} - ${formatTime(t.end)}`);
  });
  return Array.from(unique);
};

// Function to get the correct profile image URL
const getProfileImageUrl = (doctor) => {
  if (doctor?.image) {
    return `${BASE_URL}/storage/${doctor.image}`;
  }
  return "/public/DoctorPlaceholder.jpg";
};

// Open modal
const openModal = (doctor) => {
  activeDoctor.value = doctor;
  showModal.value = true;

  if (doctor.hasAppointment && doctor.appointmentData) {
    selectedDate.value = doctor.appointmentData.date;
    selectedTreatment.value = doctor.appointmentData.treatment;
    selectedAppointmentType.value = doctor.appointmentData.type;
    
    // Check availability for the existing date, then set time
    checkAvailability().then(() => {
      selectedTime.value = {
        start: doctor.appointmentData.start_time,
        end: doctor.appointmentData.end_time
      };
    });
  }
};

// Close modal
const closeModal = () => {
  showModal.value = false;
  activeDoctor.value = null;
  selectedDate.value = "";
  selectedTime.value = "";
  selectedTreatment.value = "";
  selectedAppointmentType.value = "";
  dateAvailable.value = true;
  dateMessage.value = "";
  availableTimeSlots.value = [];
};

// Confirm appointment 
const confirmAppointment = async () => {
  if (!selectedDate.value || !selectedTime.value || !selectedTreatment.value || !selectedAppointmentType.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Information',
      text: 'Please fill in all fields.',
      confirmButtonColor: '#3085d6'
    });
    return;
  }

  if (!dateAvailable.value) {
    Swal.fire({
      icon: 'error',
      title: 'Date Unavailable',
      text: 'The selected date is not available. Please choose another date.',
      confirmButtonColor: '#3085d6'
    });
    return;
  }

  submitting.value = true;

  try {
    const payload = {
      therapist_id: activeDoctor.value.id,
      appointment_date: selectedDate.value,
      start_time: selectedTime.value.start,
      end_time: selectedTime.value.end,
      treatment_session_type: selectedTreatment.value,
      appointment_type: selectedAppointmentType.value,
    };

    let response;
    if (activeDoctor.value.hasAppointment) {
      response = await api.put(`/api/appointments/${activeDoctor.value.appointmentData.id}`, payload);
    } else {
      response = await api.post('/api/appointments', payload);
    }

    await Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: response.data.message,
      confirmButtonColor: '#3085d6'
    });

    closeModal();
    await fetchTherapists();
  } catch (err) {
    const errorMessage = err.response?.data?.message || 'An error occurred. Please try again.';
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: errorMessage,
      confirmButtonColor: '#3085d6'
    });
    console.error('Error saving appointment:', err);
  } finally {
    submitting.value = false;
  }
};

// Cancel appointment
const cancelAppointment = async () => {
  const result = await Swal.fire({
    icon: 'warning',
    title: 'Cancel Appointment?',
    text: 'Are you sure you want to cancel this appointment?',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, cancel it',
    cancelButtonText: 'No, keep it'
  });

  if (!result.isConfirmed) return;

  submitting.value = true;

  try {
    const response = await api.delete(`/api/appointments/${activeDoctor.value.appointmentData.id}`);
    
    await Swal.fire({
      icon: 'success',
      title: 'Cancelled!',
      text: response.data.message,
      confirmButtonColor: '#3085d6'
    });

    closeModal();
    await fetchTherapists();
  } catch (err) {
    const errorMessage = err.response?.data?.message || 'Failed to cancel appointment. Please try again.';
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: errorMessage,
      confirmButtonColor: '#3085d6'
    });
    console.error('Error canceling appointment:', err);
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchTherapists();
  fetchSpecializations();
});
</script>

<style src="../assets/CSS Students/Therapist-list.css"></style>