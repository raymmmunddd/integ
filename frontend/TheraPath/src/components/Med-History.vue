<template>
  <Sidebar />

  <main>
    <h2 class="title">Medical Records</h2>

    <div v-if="loading" class="loading-state">Loading your medical history...</div>

    <div v-else class="upper-container">
      <!-- Personal Background -->
      <div class="personal-bg">
        <div class="profile-pic-container" @click="$refs.fileInput.click()">
          <img v-if="profileImageUrl" :src="profileImageUrl" alt="Profile" class="profile-pic" />
          <div v-else class="initials-avatar">{{ userInitials }}</div>
          <div class="image-overlay"><i class="bx bx-camera"></i></div>
        </div>
        <input ref="fileInput" type="file" accept="image/*" @change="uploadProfileImage" style="display: none" />
        <h3 class="name">{{ userProfile.full_name }}</h3>
        <p class="email">{{ userProfile.email }}</p>
        <p class="appointments">Appointments</p>
        <p class="appointment-no">{{ userProfile.appointment_count }}</p>
      </div>

      <!-- Editable Info -->
      <div class="other-infos">
        <div class="info-container-main">
          <div class="info-container">
            <h2 class="info-title">Gender:</h2>
            <input v-model="form.gender" :disabled="!isEditing" class="info-input" />
            <h2 class="info-title">Contact No.:</h2>
            <input v-model="form.phone_number" :disabled="!isEditing" class="info-input" />
            <h2 class="info-title">Barangay:</h2>
            <input v-model="form.barangay" :disabled="!isEditing" class="info-input" />
          </div>
          <div class="info-container">
            <h2 class="info-title">Date of Birth:</h2>
            <input v-model="form.date_of_birth" :disabled="!isEditing" class="info-input" />
            <h2 class="info-title">House No.:</h2>
            <input v-model="form.house_number" :disabled="!isEditing" class="info-input" />
            <h2 class="info-title">City/Municipality:</h2>
            <input v-model="form.city_municipality" :disabled="!isEditing" class="info-input" />
          </div>
        </div>
        <div class="button-group">
          <button class="edit-button" @click="isEditing = true">Edit</button>
          <button class="save-button" :disabled="!isEditing" @click="saveChanges">Save</button>
        </div>
      </div>

      <!-- Reports/Notes Container -->
      <div class="reports-container">
        <div class="reports-header">
          <button 
            v-for="tab in ['reports', 'notes']" 
            :key="tab"
            :class="['reports-subheading', { active: activeReportTab === tab }]"
            @click="activeReportTab = tab"
          >
            {{ tab.charAt(0).toUpperCase() + tab.slice(1) }}
          </button>
        </div>

        <table class="reports-table">
          <thead>
            <tr>
              <th>File Name</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="currentFiles.length === 0">
              <td colspan="3" style="text-align: center">No {{ activeReportTab }} available</td>
            </tr>
            <tr v-for="file in currentFiles" :key="file.id">
              <td>{{ file.file_name }}</td>
              <td>{{ file.date }}</td>
              <td>
                <div class="action-icon">
                  <i class="bx bx-arrow-down-circle" @click="downloadFile(file)" title="Download"></i>
                  <i class="bx bxs-trash delete" @click="deleteFile(file.id)" title="Delete"></i>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Appointments Table -->
    <div v-if="!loading" class="lower-container">
      <h2 class="title">Appointments</h2>

      <div class="tabs">
        <button 
          v-for="tab in appointmentTabs" 
          :key="tab.value"
          :class="['tab', { active: activeTab === tab.value }]"
          @click="activeTab = tab.value"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="table-wrapper">
        <table class="appointments-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Date</th>
              <th>Treatment Type</th>
              <th>Booking Time</th>
              <th>Doctor</th>
              <th>Type</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="filteredAppointments.length === 0">
              <td colspan="8" style="text-align: center">No appointments available</td>
            </tr>
            <tr v-for="appt in filteredAppointments" :key="appt.id">
              <td>{{ appt.id }}</td>
              <td>{{ appt.date }}</td>
              <td>{{ appt.treatment }}</td>
              <td>{{ formatTime12Hour(appt.rawStartTime, appt.rawEndTime) }}</td>
              <td>{{ appt.doctor }}</td>
              <td>{{ appt.type }}</td>
              <td><span :class="'status-' + appt.status">{{ appt.status }}</span></td>
              <td>
                <div v-if="appt.status === 'completed'">
                  <button v-if="!appt.evaluated" class="eval-btn" @click="openPostEval(appt)">Post Eval</button>
                  <button v-else class="delete-btn" @click="deleteAppointment(appt.id)">Delete</button>
                </div>
                <div v-else-if="appt.status === 'pending'" class="action-menu">
                  <button 
                    class="action-btn" 
                    :data-appt-id="appt.id"
                    @click.stop="toggleMenu(appt.id)"
                  >
                    ⋮
                  </button>
                </div>
                <div v-else-if="appt.status === 'approved'">
                  <span style="color: #999; font-size: 12px">—</span>
                </div>
                <div v-else>
                  <button class="delete-btn" @click="deleteAppointment(appt.id)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="appointmentMenuOpen" class="dropdown-portal" @click="appointmentMenuOpen = null">
          <div 
            class="dropdown-menu" 
            :style="dropdownPosition"
            @click.stop
          >
            <button @click="openEditAppointment(selectedAppointment)">Edit</button>
            <button @click="cancelAppointment(selectedAppointment.id)" class="delete">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Post Evaluation Modal -->
    <div v-if="showEvalModal" class="modal-overlay" @click.self="showEvalModal = false">
      <div class="modal-content">
        <h3>Post Evaluation</h3>
        <p>Please rate your experience:</p>
        <div v-for="(q, idx) in feedbackQuestions" :key="idx" class="feedback-question">
          <p>{{ q.label }}</p>
          <div class="stars">
            <span v-for="n in 5" :key="n" @click="q.value = n" :class="{ active: q.value >= n }">★</span>
          </div>
        </div>
        <div class="feedback-message">
          <label>Optional message:</label>
          <textarea v-model="feedbackMessage" placeholder="Write your feedback here..."></textarea>
        </div>
        <div class="modal-actions">
          <button class="eval-btn" @click="submitPostEval">Submit</button>
          <button class="delete-btn" @click="showEvalModal = false">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Edit Appointment Modal -->
    <div v-if="showAppointmentModal" class="appointment-modal-overlay" @click.self="closeAppointmentModal">
      <div class="appointment-modal-content">
        <div class="appointment-modal-header">
          <h3>Edit Appointment</h3>
          <span class="appointment-close" @click="closeAppointmentModal">×</span>
        </div>

        <div class="appointment-modal-body">
          <div v-if="activeDoctor" class="available-days-info">
            <p><strong>Available Days:</strong> {{ availableDaysDisplay }}</p>
          </div>

          <div class="appointment-form-row">
            <div class="appointment-form-test">
              <label>Date:</label>
              <input type="date" v-model="selectedDate" :min="minDate" @change="checkAvailability" />
              <p v-if="dateMessage" class="date-message" :class="{ error: !dateAvailable }">{{ dateMessage }}</p>
            </div>
          </div>

          <div class="appointment-form-row">
            <div class="appointment-form-test">
              <label>Time:</label>
              <select v-model="selectedTime" :disabled="!dateAvailable || loadingTimeSlots">
                <option value="">{{ loadingTimeSlots ? 'Loading...' : 'Choose a time' }}</option>
                <option v-for="slot in availableTimeSlots" :key="slot.display" :value="slot">
                  {{ slot.display }}
                </option>
              </select>
              <p v-if="availableTimeSlots.length === 0 && selectedDate && !loadingTimeSlots" class="no-slots">
                No available time slots for this date.
              </p>
            </div>
          </div>

          <div class="appointment-form-row">
            <div class="appointment-form-test">
              <label>Treatment Type:</label>
              <select v-model="selectedTreatment">
                <option value="">Select Treatment</option>
                <option v-for="type in treatmentTypes" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>
          </div>

          <div class="appointment-form-row">
            <div class="appointment-form-test">
              <label>Appointment Type:</label>
              <select v-model="selectedAppointmentType">
                <option value="">Select Type</option>
                <option value="online">Online</option>
                <option value="physical">Physical</option>
              </select>
            </div>
          </div>

          <button class="appointment-submit-btn" @click="confirmAppointment" :disabled="submitting || !dateAvailable">
            {{ submitting ? 'Processing...' : 'Update Appointment' }}
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import axios from '../axios';
import Sidebar from "./Sidebar.vue";
import { useAuth } from "../composables/useAuth";
import Swal from 'sweetalert2';

const router = useRouter();
const auth = useAuth();
const BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000";
const API_URL = "/api";

const loading = ref(true);
const activeTab = ref("all");
const activeReportTab = ref("reports");
const appointmentTabs = [
  { value: 'all', label: 'All Appointments' },
  { value: 'upcoming', label: 'Upcoming' },
  { value: 'completed', label: 'Completed' }
];

const userProfile = ref({ full_name: "", email: "", appointment_count: 0, image: null });
const form = ref({
  gender: "", phone_number: "", barangay: "",
  date_of_birth: "", house_number: "", city_municipality: ""
});
const isEditing = ref(false);
const fileInput = ref(null);

const medicalRecords = ref([]);
const notesList = ref([]);
const appointments = ref([]);
const appointmentMenuOpen = ref(null);
const selectedAppointment = ref(null);

const showEvalModal = ref(false);
const showAppointmentModal = ref(false);
const selectedAppt = ref(null);
const feedbackMessage = ref("");
const feedbackQuestions = ref([
  { label: "1. Quality of Service", value: 0, key: "quality_of_service" },
  { label: "2. Responsiveness", value: 0, key: "responsiveness" },
  { label: "3. Effectiveness", value: 0, key: "effectiveness" },
  { label: "4. Organization", value: 0, key: "organization" },
  { label: "5. Recommendation", value: 0, key: "recommendation" }
]);

const activeDoctor = ref(null);
const selectedDate = ref("");
const selectedTime = ref("");
const selectedTreatment = ref("");
const selectedAppointmentType = ref("");
const dateAvailable = ref(true);
const dateMessage = ref("");
const availableTimeSlots = ref([]);
const loadingTimeSlots = ref(false);
const submitting = ref(false);
const treatmentTypes = ["Individual Therapy", "Group Therapy", "Family Therapy", "Assessment", "Follow-up"];

const profileImageUrl = computed(() => 
  userProfile.value.image ? `${BASE_URL}/storage/${userProfile.value.image}` : null
);

const userInitials = computed(() => {
  const name = userProfile.value.full_name || "";
  const names = name.trim().split(" ");
  if (!name) return "U";
  if (names.length === 1) return names[0].charAt(0).toUpperCase();
  return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase();
});

const currentFiles = computed(() => 
  activeReportTab.value === "reports" ? medicalRecords.value : notesList.value
);

const filteredAppointments = computed(() => {
  if (activeTab.value === "all") return appointments.value;
  if (activeTab.value === "upcoming") return appointments.value.filter(a => ["pending", "approved"].includes(a.status));
  if (activeTab.value === "completed") return appointments.value.filter(a => a.status === "completed");
  return appointments.value;
});

const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

const availableDaysDisplay = computed(() => {
  if (!activeDoctor.value?.availableDays) return 'N/A';
  return activeDoctor.value.availableDays.join(', ');
});

// Helper function
const formatTime12Hour = (startTime, endTime) => {
  const formatSingle = (time) => {
    const [hour, minute] = time.split(':');
    let h = parseInt(hour);
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    return `${h}:${minute} ${ampm}`;
  };
  return `${formatSingle(startTime)} - ${formatSingle(endTime)}`;
};

// API Functions
const fetchProfile = async () => {
  const { data } = await axios.get(`${API_URL}/profile`);
  userProfile.value = data;
  form.value = {
    gender: data.gender || "",
    phone_number: data.phone_number || "",
    barangay: data.barangay || "",
    date_of_birth: data.date_of_birth?.split("T")[0] || "",
    house_number: data.house_number || "",
    city_municipality: data.city_municipality || ""
  };
};

const fetchMedicalRecords = async () => {
  const { data } = await axios.get(`${API_URL}/medical-records`);
  medicalRecords.value = data;
};

const fetchNotes = async () => {
  const { data } = await axios.get(`${API_URL}/notes`);
  notesList.value = data;
};

const fetchAppointments = async () => {
  const { data } = await axios.get(`${API_URL}/appointments/my-appointments`);
  appointments.value = await Promise.all(data.map(async (appt) => {
    let evaluated = false;
    if (appt.status === "completed") {
      try {
        const { data: evalData } = await axios.get(`${API_URL}/evaluations/check/${appt.id}`);
        evaluated = evalData.has_evaluation;
      } catch (e) { console.error("Error checking evaluation:", e); }
    }
    return {
      id: appt.id,
      date: new Date(appt.appointment_date).toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" }),
      rawDate: appt.appointment_date,
      treatment: appt.treatment_session_type,
      time: `${appt.start_time} - ${appt.end_time}`,
      rawStartTime: appt.start_time,
      rawEndTime: appt.end_time,
      doctor: `${appt.therapist?.first_name || ""} ${appt.therapist?.last_name || ""}`.trim() || "N/A",
      type: appt.appointment_type,
      status: appt.status,
      evaluated
    };
  }));
};

const loadMedicalHistoryData = async () => {
  try {
    loading.value = true;
    await Promise.all([fetchProfile(), fetchMedicalRecords(), fetchNotes(), fetchAppointments()]);
  } catch (error) {
    console.error("Error loading data:", error);
    if (error.response?.status === 401) router.push('/login');
  } finally {
    loading.value = false;
  }
};

// Profile Actions
const saveChanges = async () => {
  try {
    await axios.put(`${API_URL}/profile`, form.value);
    isEditing.value = false;
    Swal.fire({ title: "Profile updated!", icon: "success" });
    fetchProfile();
  } catch (error) {
    console.error("Error updating profile:", error);
    Swal.fire({ title: "Failed to update profile", icon: "error" });
  }
};

const uploadProfileImage = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const formData = new FormData();
  formData.append("image", file);
  try {
    await axios.post(`${API_URL}/profile/image`, formData, { headers: { "Content-Type": "multipart/form-data" } });
    Swal.fire({ title: "Profile image updated!", icon: "success" });
    fetchProfile();
  } catch (error) {
    console.error("Error uploading image:", error);
    Swal.fire({ title: "Failed to upload image", icon: "error" });
  }
};

// File Actions
const downloadFile = async (file) => {
  const endpoint = activeReportTab.value === "reports" ? "medical-records" : "notes";
  try {
    const { data, headers } = await axios.get(`${API_URL}/${endpoint}/${file.id}/download`, { 
      responseType: 'blob' 
    });
    
    let filename = file.file_name;
    const contentDisposition = headers['content-disposition'];
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
      if (filenameMatch && filenameMatch[1]) {
        filename = filenameMatch[1].replace(/['"]/g, '');
      }
    }
    
    const url = window.URL.createObjectURL(new Blob([data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
    
    Swal.fire('Success', `${activeReportTab.value === 'reports' ? 'Medical record' : 'Note'} downloaded!`, 'success');
  } catch (error) {
    console.error('Error downloading file:', error);
    Swal.fire('Error', 'Failed to download file.', 'error');
  }
};

const deleteFile = async (id) => {
  const type = activeReportTab.value === "reports" ? "medical record" : "note";
  const result = await Swal.fire({
    title: `Delete this ${type}?`,
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#75BDE5',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  });
  if (!result.isConfirmed) return;
  
  const endpoint = activeReportTab.value === "reports" ? "medical-records" : "notes";
  try {
    await axios.delete(`${API_URL}/${endpoint}/${id}`);
    
    if (activeReportTab.value === "reports") {
      medicalRecords.value = medicalRecords.value.filter(f => f.id !== id);
    } else {
      notesList.value = notesList.value.filter(n => n.id !== id);
    }
    
    Swal.fire('Deleted!', `${type.charAt(0).toUpperCase() + type.slice(1)} has been deleted.`, 'success');
  } catch (error) {
    console.error('Error deleting file:', error);
    Swal.fire('Error', `Failed to delete ${type}.`, 'error');
  }
};

// Appointment Actions
const toggleMenu = (apptId) => {
  if (appointmentMenuOpen.value === apptId) {
    appointmentMenuOpen.value = null;
    return;
  }
  
  const appt = filteredAppointments.value.find(a => a.id === apptId);
  selectedAppointment.value = appt;
  appointmentMenuOpen.value = apptId;
};

const dropdownPosition = computed(() => {
  if (!appointmentMenuOpen.value) return {};
  
  const button = document.querySelector(`[data-appt-id="${appointmentMenuOpen.value}"]`);
  if (!button) return {};
  
  const rect = button.getBoundingClientRect();
  return {
    position: 'fixed',
    top: `${rect.bottom + 5}px`,
    left: `${rect.left - 100}px`, 
    zIndex: 10001
  };
});

const openEditAppointment = async (appt) => {
  appointmentMenuOpen.value = null;
  
  try {
    const { data: appointmentData } = await axios.get(`${API_URL}/appointments/${appt.id}/edit`);
    
    if (!appointmentData?.therapist) {
      Swal.fire('Error', 'Appointment details not found', 'error');
      return;
    }
    
    const therapist = appointmentData.therapist;
    
    activeDoctor.value = {
      id: therapist.id,
      name: `${therapist.first_name} ${therapist.last_name}`,
      appointmentData: { id: appointmentData.id },
      availableDays: therapist.availabilities?.map(a => a.day_of_week) || [],
      availableTimes: therapist.availabilities?.map(a => ({
        day: a.day_of_week,
        start: a.start_time,
        end: a.end_time
      })) || []
    };
    
    selectedDate.value = appointmentData.appointment_date;
    selectedTreatment.value = appointmentData.treatment_session_type;
    selectedAppointmentType.value = appointmentData.appointment_type;
    
    showAppointmentModal.value = true;
    await checkAvailability();
    
    const formatTo12Hour = (time) => {
      const [hour, minute] = time.split(':');
      let h = parseInt(hour);
      const ampm = h >= 12 ? 'PM' : 'AM';
      h = h % 12 || 12;
      return `${h}:${minute.padStart(2, '0')} ${ampm}`;
    };

    selectedTime.value = {
      start: appointmentData.start_time,
      end: appointmentData.end_time,
      display: `${formatTo12Hour(appointmentData.start_time)} - ${formatTo12Hour(appointmentData.end_time)}`
    };

  } catch (error) {
    console.error('Error opening edit appointment:', error);
    Swal.fire('Error', 'Failed to load appointment details', 'error');
  }
};

const cancelAppointment = async (id) => {
  const result = await Swal.fire({
    title: 'Cancel this appointment?',
    text: "Are you sure?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#75BDE5',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, cancel it!'
  });
  if (!result.isConfirmed) return;
  
  try {
    await axios.delete(`${API_URL}/appointments/${id}`);
    Swal.fire('Cancelled!', 'Appointment has been cancelled.', 'success');
    appointmentMenuOpen.value = null;
    fetchAppointments();
  } catch (error) {
    console.error("Error cancelling appointment:", error);
    Swal.fire('Error', 'Failed to cancel appointment', 'error');
  }
};

const deleteAppointment = async (id) => {
  const result = await Swal.fire({
    title: 'Delete this appointment?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#75BDE5',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  });
  if (!result.isConfirmed) return;
  
  try {
    await axios.delete(`${API_URL}/appointments/${id}`);
    Swal.fire('Deleted!', 'Appointment deleted.', 'success');
    fetchAppointments();
  } catch (error) {
    console.error("Error deleting appointment:", error);
    Swal.fire('Error', 'Failed to delete appointment', 'error');
  }
};

// Evaluation
const openPostEval = (appt) => {
  selectedAppt.value = appt;
  showEvalModal.value = true;
  feedbackQuestions.value.forEach(q => q.value = 0);
  feedbackMessage.value = "";
};

const submitPostEval = async () => {
  if (!feedbackQuestions.value.every(q => q.value > 0)) {
    Swal.fire('Error', 'Please rate all questions.', 'error');
    return;
  }
  try {
    await axios.post(`${API_URL}/evaluations`, {
      appointment_id: selectedAppt.value.id,
      quality_of_service: feedbackQuestions.value[0].value,
      responsiveness: feedbackQuestions.value[1].value,
      effectiveness: feedbackQuestions.value[2].value,
      organization: feedbackQuestions.value[3].value,
      recommendation: feedbackQuestions.value[4].value,
      message: feedbackMessage.value || null
    });
    Swal.fire({ title: 'Thank you!', text: 'Evaluation submitted.', icon: 'success', confirmButtonColor: '#75BDE5' });
    showEvalModal.value = false;
    fetchAppointments();
  } catch (error) {
    console.error("Error submitting evaluation:", error);
    Swal.fire('Error', error.response?.data?.message || 'Failed to submit evaluation', 'error');
  }
};

// Appointment Modal Functions
const checkAvailability = async () => {
  if (!selectedDate.value || !activeDoctor.value) return;
  loadingTimeSlots.value = true;
  dateMessage.value = "";
  selectedTime.value = "";
  availableTimeSlots.value = [];
  
  try {
    const { data } = await axios.get(`${API_URL}/appointments/available-slots`, {
      params: { therapist_id: activeDoctor.value.id, date: selectedDate.value }
    });
    dateAvailable.value = data.available;
    
    availableTimeSlots.value = data.available ? data.timeSlots.map(slot => ({
      start: slot.value.start,
      end: slot.value.end,
      display: slot.display
    })) : [];
    
    dateMessage.value = data.available ? `Available on ${data.dayOfWeek}` : (data.message || 'Not available');
  } catch (err) {
    console.error('Error checking availability:', err);
    dateAvailable.value = false;
    dateMessage.value = 'Error checking availability';
  } finally {
    loadingTimeSlots.value = false;
  }
};

const closeAppointmentModal = () => {
  showAppointmentModal.value = false;
  activeDoctor.value = null;
  selectedDate.value = "";
  selectedTime.value = "";
  selectedTreatment.value = "";
  selectedAppointmentType.value = "";
  dateAvailable.value = true;
  dateMessage.value = "";
  availableTimeSlots.value = [];
};

const confirmAppointment = async () => {
  if (!selectedDate.value || !selectedTime.value || !selectedTreatment.value || !selectedAppointmentType.value) {
    Swal.fire('Error', 'Please fill in all fields.', 'error');
    return;
  }
  if (!dateAvailable.value) {
    Swal.fire('Error', 'Selected date is not available.', 'error');
    return;
  }
  
  submitting.value = true;
  try {
    const payload = {
      appointment_date: selectedDate.value,
      start_time: selectedTime.value.start,
      end_time: selectedTime.value.end,
      treatment_session_type: selectedTreatment.value,
      appointment_type: selectedAppointmentType.value
    };
    
    const { data } = await axios.put(`${API_URL}/appointments/${activeDoctor.value.appointmentData.id}`, payload);
    
    Swal.fire('Success', data.message, 'success');
    closeAppointmentModal();
    fetchAppointments();
  } catch (err) {
    Swal.fire('Error', err.response?.data?.message || 'An error occurred', 'error');
  } finally {
    submitting.value = false;
  }
};

const handleClickOutside = (e) => {
  if (!e.target.closest('.action-btn') && !e.target.closest('.dropdown-menu')) {
    appointmentMenuOpen.value = null;
  }
};

// Lifecycle
onMounted(async () => {
  if (!auth.isAuthenticated.value) {
    try {
      await auth.fetchUser();
    } catch (error) {
      console.error('Auth failed:', error);
      router.push('/login');
      return;
    }
  }
  await loadMedicalHistoryData();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped src="../assets/CSS Students/med-history.css"></style>