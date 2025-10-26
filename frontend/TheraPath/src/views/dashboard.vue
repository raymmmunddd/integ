<!-- user dashboard -->

<template>
  <Sidebar />
  <div class="main-container">
    <div class="separation-title-time">
      <h5 class="hello-title">Hi, {{ userName }}!</h5>
    </div>

    <h2 class="welcome-title">Welcome Back!</h2>

    <div v-if="loading" class="loading-state">Loading your dashboard...</div>

    <div v-else class="whole-container">
      <div class="container-separation">
        <!-- Reminders -->
        <div class="container-reminder">
          <h3 class="reminder-title">Reminders</h3>
          <h2 class="reminder-text">
            Have you had your routine check-up this month?
          </h2>
          <RouterLink to="/TherapistList" class="check-btn">Check Now!</RouterLink>
        </div>

        <!-- Sessions -->
        <div class="sessions-separation">
          <div class="session-container">
            <i class="bxr bx-whiteboard" id="session-icon"></i>
            <div class="session-texts">
              <h3 class="session-title">Sessions this month</h3>
              <p class="session-details">{{ stats.sessions_this_month }}</p>
            </div>
          </div>

          <div class="session-container">
            <i class="bxr bx-whiteboard" id="session-icon"></i>
            <div class="session-texts">
              <h3 class="session-title">Weekly Sessions</h3>
              <p class="session-details">{{ stats.weekly_sessions }}</p>
            </div>
          </div>
        </div>

        <!-- Calendar -->
        <div class="calendar">
          <!-- Calendar Header -->
          <div class="calendar-header">
            <button class="calendar-button" @click="prevMonth">‹</button>
            <h2>{{ monthNames[currentMonth] }} {{ currentYear }}</h2>
            <button class="calendar-button" @click="nextMonth">›</button>
          </div>

          <!-- Days of the Week -->
          <div class="weekdays">
            <div v-for="day in days" :key="day">{{ day }}</div>
          </div>

          <!-- Dates -->
          <div class="dates">
            <div
              v-for="(date, index) in calendarDays"
              :key="index"
              class="date"
              :class="{ today: isToday(date), hasEvent: hasAppointment(date), past: isPastDate(date) }"
              @click="selectDate(date)"
            >
              <span v-if="!isNaN(date)">{{ date.getDate() }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Self Journal -->
      <div class="right-div">
        <div class="self-journal">
          <div class="journal-header">
            <h2 class="journal-title">Self Journal</h2>
          </div>
          <div class="journal-textarea">
            <textarea 
              v-model="journalNotes" 
              placeholder="How are you feeling today?"
            ></textarea>
          </div>
        </div>
        <button class="save-journal" @click="saveJournal" :disabled="journalSaving">
          {{ journalSaving ? 'Saving...' : 'Save' }}
        </button>
      </div>
    </div>
  </div>

  <!-- Appointment Modal -->
  <div v-if="selectedDate" class="modal-overlay" @click.self="closeModal">
    <div class="modal">
      <h3>Appointments on {{ selectedDate.toDateString() }}</h3>
      <ul v-if="getAppointments(selectedDate).length">
        <li v-for="(appt, i) in getAppointments(selectedDate)" :key="i">
          <div class="appointment-item">
            <span class="time">{{ appt.time }}</span>
            <span class="title">{{ appt.title }}</span>
            <span class="therapist">with {{ appt.therapist }}</span>
            <span class="status" :class="'status-' + appt.status">{{ appt.status }}</span>
          </div>
        </li>
      </ul>
      <div v-else>No appointments for this date.</div>
      <button class="close-btn" @click="closeModal">Close</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import Sidebar from "../components/Sidebar.vue";
import { useAuth } from "../composables/useAuth";
import Swal from 'sweetalert2';

const router = useRouter();
const auth = useAuth();

/* ---------------- State ---------------- */
const loading = ref(true);
const userName = ref("Guest");
const stats = ref({
  sessions_this_month: 0,
  weekly_sessions: 0
});
const appointments = ref([]);
const journalNotes = ref("");
const journalSaving = ref(false);

/* ---------------- Calendar ---------------- */
const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
const monthNames = [
  "January","February","March","April","May","June",
  "July","August","September","October","November","December"
];

const today = new Date();
const currentMonth = ref(today.getMonth());
const currentYear = ref(today.getFullYear());
const selectedDate = ref(null);

const calendarDays = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1);
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);
  const daysArr = [];

  for (let i = 0; i < firstDay.getDay(); i++) {
    daysArr.push(new Date(NaN));
  }
  for (let i = 1; i <= lastDay.getDate(); i++) {
    daysArr.push(new Date(currentYear.value, currentMonth.value, i));
  }

  return daysArr;
});

const isToday = (date) => {
  if (isNaN(date)) return false;
  const d = new Date();
  return (
    date.getDate() === d.getDate() &&
    date.getMonth() === d.getMonth() &&
    date.getFullYear() === d.getFullYear()
  );
};

const isPastDate = (date) => {
  if (isNaN(date)) return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const compareDate = new Date(date);
  compareDate.setHours(0, 0, 0, 0);
  return compareDate < today;
};

const hasAppointment = (date) => {
  if (isNaN(date)) return false;
  return appointments.value.some((appt) => appt.date === formatDate(date));
};

const getAppointments = (date) => {
  return appointments.value.filter((appt) => appt.date === formatDate(date));
};

const selectDate = (date) => {
  if (!isNaN(date)) {
    selectedDate.value = date;
  }
};

const closeModal = () => {
  selectedDate.value = null;
};

const prevMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
};

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
};

const formatDate = (date) => {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, "0");
  const d = String(date.getDate()).padStart(2, "0");
  return `${y}-${m}-${d}`;
};

/* ---------------- Journal ---------------- */
const saveJournal = async () => {
  // If empty, ask user if they want to clear/delete the journal
  if (!journalNotes.value.trim()) {
    const result = await Swal.fire({
      icon: 'question',
      title: 'Clear Journal?',
      text: 'Do you want to save an empty journal (this will clear your entry)?',
      showCancelButton: true,
      confirmButtonText: 'Yes, clear it',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#6c757d'
    });
    
    if (!result.isConfirmed) return;
  }
  
  journalSaving.value = true;
  try {
    await auth.saveJournal(journalNotes.value || '');
    Swal.fire({
      icon: 'success',
      title: 'Saved!',
      text: journalNotes.value.trim() ? 'Journal saved successfully!' : 'Journal cleared successfully!',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Save Failed',
      text: 'Failed to save journal. Please try again.',
      confirmButtonColor: '#d33'
    });
  } finally {
    journalSaving.value = false;
  }
};

/* ---------------- Load Dashboard Data ---------------- */
const loadDashboardData = async () => {
  try {
    loading.value = true;
    
    const dashboardData = await auth.fetchDashboard();
    
    userName.value = dashboardData.user.first_name;
    stats.value = dashboardData.stats;
    appointments.value = dashboardData.appointments;
    
    const journalData = await auth.getJournal();
    journalNotes.value = journalData.content;
    
  } catch (error) {
    console.error('Failed to load dashboard:', error);
    
    if (error.response?.status === 401) {
      router.push('/login');
    }
  } finally {
    loading.value = false;
  }
};

/* ---------------- Lifecycle ---------------- */
onMounted(async () => {
  if (!auth.isAuthenticated.value) {
    try {
      await auth.fetchUser();
    } catch (error) {
      router.push('/login');
      return;
    }
  }
  
  await loadDashboardData();
});
</script>

<style scoped src="../assets/CSS Students/dashboard.css"></style>