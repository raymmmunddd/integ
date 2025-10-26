<template>
<SidebarTherapist />

<div class="main-container">
  <!-- HEADER -->
  <div class="separation-title-time">
    <h2 class="welcome-title">Welcome Back!</h2>
    <RouterLink to="/PatientsTherapist" class="check-btn">New Session</RouterLink>
  </div>

  <div v-if="loading" class="loading-container">
    <p>Loading dashboard...</p>
  </div>

  <div v-else class="whole-container">
    <!-- LEFT SIDE -->
    <div class="container-separation">
      <!-- Reminders -->
      <div class="container-reminder">
        <h2 class="reminder-text">Good day, Dr. {{ userData?.last_name }}!</h2>
        <p class="reminder-subtext">Have a good day!</p>
      </div>

      <!-- Sessions Stats -->
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

        <div class="session-container">
          <i class="bxr bx-whiteboard" id="session-icon"></i>
          <div class="session-texts">
            <h3 class="session-title">Total Patients</h3>
            <p class="session-details">{{ stats.total_patients }}</p>
          </div>
        </div>

        <div class="session-container">
          <i class="bxr bx-whiteboard" id="session-icon"></i>
          <div class="session-texts">
            <h3 class="session-title">Appointments</h3>
            <p class="session-details">{{ stats.total_appointments }}</p>
          </div>
        </div>
      </div>

      <!-- Calendar -->
      <div class="calendar">
        <div class="calendar-header">
          <button class="calendar-button" @click="prevMonth">‹</button>
          <h2>{{ monthNames[currentMonth] }} {{ currentYear }}</h2>
          <button class="calendar-button" @click="nextMonth">›</button>
        </div>

        <div class="weekdays">
          <div v-for="day in days" :key="day">{{ day }}</div>
        </div>

        <div class="dates">
          <div
            v-for="(date, index) in calendarDays"
            :key="index"
            class="date"
            :class="{ 
              today: isToday(date), 
              hasEvent: hasAppointment(date),
              past: isPastDate(date)
            }"
            @click="selectDate(date)"
          >
            <span v-if="!isNaN(date)">{{ date.getDate() }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right-side">
      <!-- STATUS LEGEND -->
      <div class="status-legend">
        <div class="legend-item notyet">
          <span class="dot"></span> Pending
        </div>
        <div class="legend-item ongoing">
          <span class="dot"></span> Approved
        </div>
        <div class="legend-item concluded">
          <span class="dot"></span> Completed
        </div>
        <div class="legend-item cancelled">
          <span class="dot"></span> Cancelled
        </div>
      </div>

      <!-- RECENT APPOINTMENTS -->
      <div class="appointments-card">
        <h3 class="title">
          <span class="icon"></span> Recent Appointments
        </h3>

        <div class="appointments-list">
          <div v-if="recentAppointments.length === 0" class="no-appointments">
            <p>No recent appointments</p>
          </div>
          <div
            v-else
            v-for="(appt, index) in recentAppointments"
            :key="index"
            class="appointment-item"
          >
            <div class="avatar">
              <img v-if="appt.patient_image" :src="getPatientImage(appt.patient_image)" :alt="appt.patient" />
              <span v-else class="initials">{{ getInitials(appt.patient) }}</span>
            </div>
            <div class="details">
              <p class="name">{{ appt.patient }}</p>
              <p class="time">{{ appt.displayTime }}</p>
            </div>
            <div class="status-dot" :class="getStatusClass(appt.status)"></div>
          </div>
        </div>
      </div>

      <!-- TODAY'S SCHEDULE -->
      <div class="todays-schedule-card">
        <h3 class="title">
          <span class="icon"></span> Today's Schedule
        </h3>

        <div class="schedule-list">
          <div v-if="todaysSchedule.length === 0" class="no-appointments">
            <p>No appointments scheduled for today</p>
          </div>
          <div
            v-else
            v-for="(appt, index) in todaysSchedule"
            :key="index"
            class="appointment-item"
          >
            <div class="avatar">
              <img v-if="appt.patient_image" :src="getPatientImage(appt.patient_image)" :alt="appt.patient" />
              <span v-else class="initials">{{ getInitials(appt.patient) }}</span>
            </div>
            <div class="details">
              <p class="name">{{ appt.patient }}</p>
              <p class="time">{{ appt.time }} - {{ appt.type }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Appointment Modal -->
<div v-if="selectedDate" class="modal-overlay" @click.self="closeModal">
  <div class="modal">
    <h3>Appointments on {{ formatModalDate(selectedDate) }}</h3>
    <ul v-if="getAppointments(selectedDate).length">
      <li v-for="(appt, i) in getAppointments(selectedDate)" :key="i">
        <div class="modal-appointment-item">
          <span class="time">{{ appt.time }}</span>
          <span class="title">{{ appt.title }}</span>
          <span class="therapist">with {{ appt.patient }}</span>
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
import { ref, computed, onMounted, onUnmounted } from "vue";
import SidebarTherapist from "../components/SidebarTherapist.vue";
import { useAuth } from "../composables/useAuth";

const { fetchTherapistDashboard } = useAuth();
const BASE_URL = import.meta.env.VITE_APP_URL || "http://localhost:8000";

/* ---------------- State ---------------- */
const loading = ref(true);
const userData = ref(null);
const stats = ref({
  sessions_this_month: 0,
  weekly_sessions: 0,
  total_patients: 0,
  total_appointments: 0
});
const appointments = ref([]);

/* ---------------- Real-time clock ---------------- */
const time = ref("");

const updateTime = () => {
  const now = new Date();
  const day = now.toLocaleDateString(undefined, { weekday: "long" });
  let hours = now.getHours();
  const minutes = now.getMinutes();
  const seconds = now.getSeconds();
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12 || 12;

  time.value = `${day}, ${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")} ${ampm}`;
};

let interval = null;
onMounted(async () => {
  updateTime();
  interval = setInterval(updateTime, 1000);
  
  try {
    const data = await fetchTherapistDashboard();
    userData.value = data.user;
    stats.value = data.stats;
    appointments.value = data.appointments || [];
  } catch (error) {
    console.error('Error loading dashboard:', error);
  } finally {
    loading.value = false;
  }
});

onUnmounted(() => {
  clearInterval(interval);
});

/* ---------------- Calendar ---------------- */
const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];

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
  return date.getDate() === d.getDate() && date.getMonth() === d.getMonth() && date.getFullYear() === d.getFullYear();
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
  if (!isNaN(date)) selectedDate.value = date;
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

const formatModalDate = (date) => {
  const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
  return `${months[date.getMonth()]} ${date.getDate()} ${date.getFullYear()}`;
};

/* ---------------- Helper Functions ---------------- */
const getStatusClass = (status) => {
  const statusMap = {
    'pending': 'notyet',
    'approved': 'ongoing',
    'completed': 'concluded',
    'cancelled': 'cancelled'
  };
  return statusMap[status?.toLowerCase()] || 'notyet';
};

const getRelativeTime = (dateStr) => {
  const apptDate = new Date(dateStr);
  const now = new Date();
  const diffDays = Math.floor((apptDate - now) / (1000 * 60 * 60 * 24));
  
  if (diffDays === 0) return "Today";
  if (diffDays === 1) return "Tomorrow";
  if (diffDays === -1) return "Yesterday";
  
  const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
  return `${months[apptDate.getMonth()]} ${apptDate.getDate()} ${apptDate.getFullYear()}`;
};

const getInitials = (name) => {
  if (!name) return "??";
  const parts = name.trim().split(" ");
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const getPatientImage = (imagePath) => {
  if (!imagePath) return null;
  return `${BASE_URL}/storage/${imagePath}`;
};

/* ---------------- Recent Appointments (last 5) ---------------- */
const recentAppointments = computed(() => {
  return appointments.value
    .slice() 
    .sort((a, b) => new Date(b.date) - new Date(a.date)) 
    .slice(0, 5)
    .map(appt => ({
      ...appt,
      displayTime: `${getRelativeTime(appt.date)} at ${appt.start_time}`
    }));
});

/* ---------------- Today's Schedule ---------------- */
const todaysSchedule = computed(() => {
  const todayStr = formatDate(new Date());
  return appointments.value.filter(appt => appt.date === todayStr);
});
</script>

<style scoped src="../assets/CCS Therapist/dashboard.css"></style>