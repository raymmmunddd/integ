<!-- PatientsTherapist.vue -->
<template>
  <SidebarTherapist />

  <!-- MAIN -->
  <div class="whole-container">
    <!-- Header: Patients + Search -->
    <div class="name-search-separation">
      <h1 class="patients-title">Patients</h1>
      <input
        type="text"
        class="search-bar"
        placeholder="Search Patient..."
        v-model="searchQuery"
      />
    </div>

    <!-- Status Filter -->
    <div>
      <select id="status" name="status" class="status-filter" v-model="statusFilter">
        <option value="">---Status---</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <!-- Patients Today Section -->
    <div class="patient-legend-separation">
      <h2 class="patients-today">Patients for Today</h2>

      <!-- STATUS LEGEND -->
      <div class="status-legend">
        <!-- NOTE: class is on the .dot only so the text remains neutral -->
        <div class="legend-item"><span class="dot notyet"></span> Pending</div>
        <div class="legend-item"><span class="dot ongoing"></span> Approved</div>
        <div class="legend-item"><span class="dot concluded"></span> Completed</div>
        <div class="legend-item"><span class="dot cancelled"></span> Cancelled</div>
      </div>
    </div>

    <!-- Appointment Cards -->
    <div
      v-for="(appt, index) in filteredAppointments"
      :key="index"
      class="appointment-card"
    >
      <!-- left colored border: now takes a status class -->
      <div :class="['patient-border', getStatusClass(appt.status)]"></div>

      <div class="appointment-info">
        <!-- Appointment Title & Status -->
        <div class="title-status">
          <h3 class="patient-title">{{ appt.title }}</h3>
          <div class="testing">
            <h6 class="status">Dr. {{ therapistName }}</h6>
          </div>
        </div>

        <!-- Date & Time -->
        <div class="date-time">
          <h3 class="patient-title">{{ formatDisplayDate(appt.date) }}</h3>
          <h6 class="status">{{ appt.time }}</h6>
        </div>

        <!-- User Info -->
        <div class="user-card">
          <img
            v-if="getPatientImage(appt)"
            :src="getPatientImage(appt)"
            alt="Patient Image"
            class="avatar"
          />
          <div v-else class="avatar initials-avatar">
            {{ getPatientInitials(appt) }}
          </div>
          <div class="user-info">
            <!-- patient from backend is a string, show fallback -->
            <p class="name">{{ appt.patient || 'Unknown Patient' }}</p>
            <!-- phone: fallback-safe helper -->
            <p class="number">{{ getContact(appt) }}</p>
          </div>
        </div>
      </div>

      <!-- Arrow to Details Page -->
      <router-link :to="`/PatientList/${appt.id}`" class="arrow-link">➔</router-link>
    </div>

    <div v-if="loading" class="loading-container">Loading appointments...</div>
    <div v-else-if="filteredAppointments.length === 0" class="no-results">No appointments found.</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import SidebarTherapist from "../components/SidebarTherapist.vue";
import { useAuth } from "../composables/useAuth";

const { fetchTherapistDashboard } = useAuth();

const searchQuery = ref("");
const statusFilter = ref("");
const appointments = ref([]);
const loading = ref(true);

const BASE_URL = import.meta.env.VITE_APP_URL || "http://localhost:8000";

const getPatientImage = (appt) => {
  if (!appt || !appt.patient_image) return null;
  return `${BASE_URL}/storage/${appt.patient_image}`;
};

const getPatientInitials = (appt) => {
  const name = appt?.patient || "";
  if (!name.trim()) return "U";
  const parts = name.trim().split(" ");
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
};

/* ---------------- Fetch therapist appointments ---------------- */
onMounted(async () => {
  try {
    const data = await fetchTherapistDashboard();
    // console.log('dashboard data', data) // optionally debug
    appointments.value = data.appointments || [];
  } catch (error) {
    console.error("Error fetching therapist appointments:", error);
  } finally {
    loading.value = false;
  }
});

/* ---------------- Normalizers & helpers ---------------- */
const normalizeStatus = (status) => (status || "").toString().toLowerCase().trim();

// returns class names: notyet / ongoing / concluded / cancelled
const getStatusClass = (status) => {
  const s = normalizeStatus(status);
  const map = {
    pending: "notyet",
    approved: "ongoing",
    completed: "concluded",
    cancelled: "cancelled",
  };
  return map[s] || "notyet";
};

// human-readable/capitalized
const prettyStatus = (status) => {
  const s = normalizeStatus(status);
  if (!s) return "";
  return s.charAt(0).toUpperCase() + s.slice(1);
};

// format date from 'YYYY-MM-DD' -> 'October 6, 2025'
const formatDisplayDate = (dateStr) => {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  if (isNaN(d)) return dateStr; // if string already pretty, fallback
  return d.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

// safe contact getter — check many possible properties and fallback to 'No Contact'
const getContact = (appt) => {
  // check common names that backend might return
  return (
    appt.patient_contact ||
    appt.patient_phone ||
    appt.phone ||
    appt.contact ||
    appt.patient_number ||
    // some APIs might attach a nested student object
    (appt.student && (appt.student.phone || appt.student.phone_number)) ||
    "No Contact"
  );
};

const therapistName = ref("");

onMounted(async () => {
  try {
    const data = await fetchTherapistDashboard();
    appointments.value = data.appointments || [];
    therapistName.value = data.user.full_name || "Unknown Therapist";
  } catch (error) {
    console.error("Error fetching therapist appointments:", error);
  } finally {
    loading.value = false;
  }
});

/* ---------------- Filtering logic ---------------- */
const filteredAppointments = computed(() => {
  return appointments.value.filter((appt) => {
    const q = searchQuery.value.toLowerCase();

    const matchesSearch =
      (appt.title && appt.title.toLowerCase().includes(q)) ||
      (typeof appt.patient === "string" && appt.patient.toLowerCase().includes(q)) ||
      (appt.date && appt.date.toLowerCase().includes(q)) ||
      (appt.time && appt.time.toLowerCase().includes(q)) ||
      (appt.status && appt.status.toLowerCase().includes(q)) ||
      q === "";

    const matchesStatus =
      !statusFilter.value || normalizeStatus(appt.status) === normalizeStatus(statusFilter.value);

    return matchesSearch && matchesStatus;
  });
});
</script>

<style scoped src="../assets/CCS Therapist/patients.css"></style>

<style scoped>
/* lightweight overrides / fixes to ensure legend + dots + borders behave correctly */

/* Legend area layout */
.status-legend {
  display: flex;
  gap: 12px;
  align-items: center;
  /* keep it visually light so it doesn't override your main styling */
  background: transparent;
  padding: 4px;
}

/* Legend item: keep text neutral, dot colored */
.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #222;
  padding: 4px 8px;
  border-radius: 6px;
}

/* tiny round dot used in legend */
.legend-item .dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  display: inline-block;
}

/* small dot used in cards */
.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
  margin-right: 8px;
}

/* left colored border element in card (only color overrides here) */
.patient-border {
  width: 6px;
  border-radius: 6px 0 0 6px;
  margin-right: 16px;
  background-color: #2b9cff; /* default if status unknown (blue) */
}

/* status color mappings (apply to both legend .dot and status-dot/patient-border) */
.legend-item .dot.notyet,
.status-dot.notyet,
.patient-border.notyet {
  background-color: #ffb74d; /* Pending - orange */
}

.legend-item .dot.ongoing,
.status-dot.ongoing,
.patient-border.ongoing {
  background-color: #4fc3f7; /* Approved - blue */
}

.legend-item .dot.concluded,
.status-dot.concluded,
.patient-border.concluded {
  background-color: #81c784; /* Completed - green */
}

.legend-item .dot.cancelled,
.status-dot.cancelled,
.patient-border.cancelled {
  background-color: #e57373; /* Cancelled - red */
}

/* keep status text neutral (so only the dot is colored) */
.title-status .status {
  color: #333;
  font-weight: 600;
  text-transform: capitalize;
}

/* name / phone styling */
.user-info .name {
  font-weight: 700;
}
.user-info .number {
  color: #666;
  margin-top: 4px;
  font-size: 0.95rem;
}

/* small UX niceties */
.appointment-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px;
  border-radius: 10px;
  background: #fff;
  box-shadow: 0 3px 10px rgba(15, 15, 15, 0.03);
  margin-bottom: 16px;
}
</style>
