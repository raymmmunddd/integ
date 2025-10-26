<!-- PatientList.vue -->
<template>
  <SidebarTherapist/>

  <!-- MAIN -->
  <main class="main-container">
    <div v-if="loading" class="loading-container">
      <p>Loading appointment details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="loadingError" class="error-container">
      <p>{{ loadingError }}</p>
      <button @click="$router.push('/PatientsTherapist')">Back to Patients</button>
    </div>

    <!-- Main Content -->
    <template v-else>
      <div class="name-search-separation">
        <h2 class="patients-title">
          Patients &gt; {{ selectedPatient?.patient?.name || "Unknown Patient" }}
        </h2>

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
      </div>

      <!-- CONTENT SPLIT -->
      <div class="whole-container">
        <div class="container-separation">
          <!-- Patient Card -->
          <div class="patient-card">
            <div class="header">
              <img v-if="patientImageUrl" :src="patientImageUrl" class="avatar" />
              <div v-else class="avatar initials-avatar">{{ patientInitials }}</div>
              <div class="info">
                <h2 class="name">{{ selectedPatient?.patient?.name || "Unknown Patient" }}</h2>
                <p class="email">{{ selectedPatient?.patient?.number || "No Contact" }}</p>
              </div>
            </div>

            <hr class="divider" />

            <div class="details">
              <div class="detail-item">
                <span class="label">Date of Birth</span>
                <span class="value">{{ selectedPatient?.patient?.dob || "MM/DD/YYYY" }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Gender</span>
                <span class="value">{{ selectedPatient?.patient?.gender || "Unknown" }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Age</span>
                <span class="value">{{ selectedPatient?.patient?.age || "-" }}</span>
              </div>
              <div class="detail-item">
                <span class="label">House No.</span>
                <span class="value">{{ selectedPatient?.patient?.house || "-" }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Barangay</span>
                <span class="value">{{ selectedPatient?.patient?.barangay || "-" }}</span>
              </div>
              <div class="detail-item">
                <span class="label">City/Municipality</span>
                <span class="value">{{ selectedPatient?.patient?.city || "-" }}</span>
              </div>
            </div>
          </div>

          <!-- Appointments -->
          <div class="appointment-container">
            <div class="buttons-add-separation">
              <div class="switch-container">
                <div class="switch-bg" :class="activeTab"></div>
                <button
                  class="switch-btn"
                  :class="{ active: activeTab === 'upcoming' }"
                  @click="activeTab = 'upcoming'"
                >
                  Upcoming Appointment
                </button>
                <button
                  class="switch-btn"
                  :class="{ active: activeTab === 'post' }"
                  @click="activeTab = 'post'"
                >
                  Post Appointment
                </button>
              </div>

              <!-- Add Appointment button -->
              <h5 class="appointment-add-btn" @click="openAddModal">
                + Add Appointment
              </h5>
            </div>

            <!-- Add Appointment Modal -->
            <div v-if="appointmentShowModal" class="appointment-modal-overlay" @click.self="appointmentShowModal = false">
              <div class="appointment-modal-content">
                <div class="appointment-modal-header">
                  <h3>Add Appointment</h3>
                  <span class="appointment-close" @click="appointmentShowModal = false">×</span>
                </div>

                <div class="appointment-modal-body">
                  <div class="appointment-form-row">
                    <div class="appointment-form-test">
                      <label>Date:</label>
                      <input type="date" v-model="appointmentNew.date" :min="minDate" @change="checkAvailabilityAdd" />
                      <p v-if="addDateMessage" class="date-message" :class="{ error: !addDateAvailable }">{{ addDateMessage }}</p>
                    </div>
                  </div>

                  <div class="appointment-form-row">
                    <div class="appointment-form-test">
                      <label>Time:</label>
                      <select v-model="appointmentNew.time" :disabled="!addDateAvailable || loadingAddTimeSlots">
                        <option value="">{{ loadingAddTimeSlots ? 'Loading...' : 'Choose a time' }}</option>
                        <option v-for="slot in addAvailableTimeSlots" :key="slot.display" :value="slot">
                          {{ slot.display }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="appointment-form-row">
                    <div class="appointment-form-test">
                      <label>Treatment Type:</label>
                      <select v-model="appointmentNew.treatmentType">
                        <option value="">Select Treatment</option>
                        <option value="Follow-up">Follow-up</option>
                      </select>
                    </div>
                  </div>

                  <div class="appointment-form-row">
                    <div class="appointment-form-test">
                      <label>Appointment Type:</label>
                      <select v-model="appointmentNew.appointmentType">
                        <option value="">Select Type</option>
                        <option value="physical">Face-to-Face</option>
                      </select>
                    </div>
                  </div>

                  <button class="appointment-submit-btn" @click="addAppointment" :disabled="submittingAdd || !addDateAvailable">
                    {{ submittingAdd ? 'Creating...' : 'Submit' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Edit Appointment Modal -->
            <div v-if="editModalIndex !== null" class="appointment-modal-overlay" @click.self="cancelEditModal">
              <div class="appointment-modal-content">
                <div class="appointment-modal-header">
                  <h3>Edit Appointment</h3>
                  <span class="appointment-close" @click="cancelEditModal">×</span>
                </div>

                <div class="appointment-modal-body">
                  <div class="appointment-form-row">
                    <div class="appointment-form-test">
                      <label>Date:</label>
                      <input type="date" v-model="editAppointment.date" :min="minDate" @change="checkAvailabilityEdit" />
                      <p v-if="editDateMessage" class="date-message" :class="{ error: !editDateAvailable }">{{ editDateMessage }}</p>
                    </div>
                  </div>

                  <div class="appointment-form-row">
                    <div class="appointment-form-test">
                      <label>Time:</label>
                      <select v-model="editAppointment.time" :disabled="!editDateAvailable || loadingEditTimeSlots">
                        <option value="">{{ loadingEditTimeSlots ? 'Loading...' : 'Choose a time' }}</option>
                        <option v-for="slot in editAvailableTimeSlots" :key="slot.display" :value="slot">
                          {{ slot.display }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="appointment-form-row">
                    <div class="appointment-form-test">
                      <label>Treatment Type:</label>
                      <select v-model="editAppointment.treatmentType">
                        <option value="">Select Treatment</option>
                        <option value="Individual Therapy">Individual Therapy</option>
                        <option value="Group Therapy">Group Therapy</option>
                        <option value="Family Therapy">Family Therapy</option>
                        <option value="Assessment">Assessment</option>
                        <option value="Follow-up">Follow-up</option>
                      </select>
                    </div>
                  </div>

                  <div class="appointment-form-row">
                    <div class="appointment-form-test">
                      <label>Appointment Type:</label>
                      <select v-model="editAppointment.appointmentType">
                        <option value="">Select Type</option>
                        <option value="online">Online</option>
                        <option value="physical">Face-to-Face</option>
                      </select>
                    </div>
                  </div>

                  <button class="appointment-submit-btn" @click="saveEditedAppointment" :disabled="submittingEdit || !editDateAvailable">
                    {{ submittingEdit ? 'Saving...' : 'Save' }}
                  </button>
                </div>
              </div>
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

            <!-- Appointment Cards -->
            <div v-for="(appt, index) in filteredAppointments" :key="appt.id" class="appt-card">
              <div class="appt-row">
                <div class="patient-border" :class="getStatusClass(appt.status)">
                  <span class="patient-status-text">{{ prettyStatus(appt.status) }}</span>
                </div>

                <div class="appt-field">
                  <span class="appt-label">Appointment:</span>
                  <span class="appt-value">{{ appt.title }}</span>
                </div>

                <div class="appt-field">
                  <span class="appt-label">Patient:</span>
                  <span class="appt-value">{{ appt.patient.name }}</span>
                </div>

                <div class="appt-field">
                  <span class="appt-label">Date:</span>
                  <span class="appt-value">{{ appt.date }} {{ appt.time }}</span>
                </div>

                <div class="appt-status">
                  <div class="status-dot" :class="getStatusClass(appt.status)"></div>
                </div>

                <!-- Menu (Edit / Delete) - Only show Edit for upcoming -->
                <div class="appt-menu-icon" @click="toggleMenu(index)">
                  <span>⋮</span>
                  <div v-if="menuIndex === index" class="appt-menu">
                    <div v-if="activeTab === 'upcoming'" class="appt-menu-item" @click.stop="openEditModal(index)">Edit</div>
                    <div class="appt-menu-item delete" @click="deleteAppt(index)">Delete</div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="filteredAppointments.length === 0" class="no-results">
              No {{ activeTab }} appointments found.
            </div>
          </div>

          <!-- Feedback -->
          <div class="feedback-container">
            <h3 class="feedback-title">Post Evaluation Analytics</h3>
            <div class="feedback-content">
              <h3>Average Ratings per Question</h3>
              <div v-if="ratings.length > 0">
                <div class="feedback-rating-item" v-for="item in ratings" :key="item.label">
                  <span class="feedback-label">{{ item.label }}:</span>
                  <span class="feedback-stars">
                    <i v-for="n in 5" :key="n" class="feedback-star" :class="{ 'feedback-star-filled': n <= item.value }">★</i>
                  </span>
                  <span class="feedback-score">({{ item.value.toFixed(1) }})</span>
                </div>
              </div>
              <div v-else class="no-results">
                No evaluation data available yet.
              </div>
            </div>
          </div>
        </div>
        <!-- END LEFT -->

        <!-- RIGHT SIDE -->
        <div class="right-side">
          <!-- Notes -->
          <div class="records-container">
            <div class="recordsTitle-add-separation">
              <h3 class="records-title">Notes</h3>
              <h3 class="add-record" @click="openModal">+ Add Files</h3>
            </div>

            <div v-if="notesLoading" class="loading-container">
              <p>Loading notes...</p>
            </div>

            <div v-else-if="files.length === 0" class="no-results">
              No notes available.
            </div>

            <div v-else class="file-list">
              <div v-for="file in files" :key="file.id" class="file-item">
                <div class="file-left">
                  <span class="file-name">{{ file.file_name }}</span>
                  <span class="file-date">{{ file.date }}</span>
                </div>
                <div class="file-actions">
                  <i class="bxr bx-arrow-down-circle" id="icon-btn" @click="downloadFile(file)"></i>
                  <i class="bx bx-trash" id="delete-btn" @click="deleteFile(file.id)"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes Modal -->
          <div v-if="showModal" class="modal-overlay">
            <div class="modal">
              <div class="modal-header">
                <h3>Upload your Documents</h3>
                <button class="close-btn" @click="closeModal">X</button>
              </div>
              <div class="modal-body">
                <label>Choose File:</label>
                <input type="file" accept=".pdf,.doc,.docx,.txt" @change="handleFileUploadNote" />
                <button class="submit-btn" @click="submitFile" :disabled="noteUploading">
                  {{ noteUploading ? 'Uploading...' : 'Submit' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Medical Records -->
          <div class="medical-records-container">
            <div class="medical-container">
              <div class="medicalTitle-add-separation">
                <h3 class="medical-records-title">Medical Records</h3>
                <h3 class="add-record" @click="showAddModal = true">+ Add Files</h3>
              </div>

              <div v-if="medicalRecordsLoading" class="loading-container">
                <p>Loading medical records...</p>
              </div>

              <div v-else-if="medicalRecordsfiles.length === 0" class="no-results">
                No medical records available.
              </div>

              <div v-else class="medical-records-list">
                <div v-for="file in medicalRecordsfiles" :key="file.id" class="medical-record-item">
                  <div class="file-left">
                    <div class="record-field">
                      <span class="label-record">Date</span>
                      <span class="value-record"><b>{{ file.date }}</b></span>
                    </div>
                    <div class="record-field">
                      <span class="label-record">Symptoms</span>
                      <span class="value-record"><b>{{ file.symptoms }}</b></span>
                    </div>
                    <div class="record-field">
                      <span class="label-record">Specialist:</span>
                      <span class="value-record"><b>{{ file.specialist }}</b></span>
                    </div>
                  </div>

                  <div class="file-actions">
                    <i class="bx bx-arrow-down-circle" id="icon-btn" @click="downloadMedRecordFile(file)"></i>
                    <i class="bx bx-trash" id="delete-btn" @click="deleteMedRecordFile(file.id)"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Add Medical Record Modal -->
          <div v-if="showAddModal" class="modal-overlay">
            <div class="modal">
              <div class="modal-header">
                <h3>Upload your Medical Records</h3>
                <span class="close-btn" @click="showAddModal = false">&times;</span>
              </div>
              <div class="modal-body-records">
                <div class="form-row">
                  <label>Date:</label>
                  <input type="date" v-model="newRecord.date" />
                  <label>Symptoms:</label>
                  <input type="text" v-model="newRecord.symptoms" />
                </div>
                <div class="form-row">
                  <label>Specialist:</label>
                  <input type="text" v-model="newRecord.specialist" />
                  <label>Choose File:</label>
                  <input type="file" accept=".pdf,.doc,.docx,.txt" @change="handleFileUploadMedical" />
                </div>
              </div>
              <div class="modal-footer">
                <button @click="saveMedicalRecord" :disabled="medicalRecordUploading">
                  {{ medicalRecordUploading ? 'Uploading...' : 'Submit' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Patient Reflections -->
          <div class="container">
            <h1 class="title">Patient Reflections</h1>
            <div v-if="reflectionsLoading" class="loading-container">
              <p>Loading reflections...</p>
            </div>
            <div v-else-if="reflections.length === 0" class="no-results">
              No patient reflections yet.
            </div>
            <div v-else class="reflections-list">
              <div v-for="reflection in reflections" :key="reflection.id" class="reflection-card">
                <div class="date-label">{{ reflection.date }}</div>
                <p class="reflection-text">{{ reflection.text }}</p>
                <p class="appointment-date-label">From appointment: {{ reflection.appointment_date }}</p>
              </div>
            </div>
          </div>
        </div>
        <!-- END RIGHT -->
      </div>
    </template>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import SidebarTherapist from "../components/SidebarTherapist.vue";
import { useAuth } from "../composables/useAuth";
import api from "../axios";
import Swal from 'sweetalert2';

const { fetchAppointmentDetails, fetchTherapistDashboard } = useAuth();
const route = useRoute();
const router = useRouter();
const BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000";

const appointmentId = route.params.id;
const loading = ref(true);
const loadingError = ref(null);

// Main data
const selectedPatient = ref(null);
const appointments = ref([]);
const evaluationData = ref(null);
const studentId = ref(null);
const therapistId = ref(null);

// Patient image
const patientImageUrl = computed(() => 
  selectedPatient.value?.patient?.image ? `${BASE_URL}/storage/${selectedPatient.value.patient.image}` : null
);

const patientInitials = computed(() => {
  const name = selectedPatient.value?.patient?.name || "";
  const names = name.trim().split(" ");
  if (!name) return "U";
  if (names.length === 1) return names[0].charAt(0).toUpperCase();
  return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase();
});

const minDate = computed(() => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  return tomorrow.toISOString().split('T')[0];
});

// Fetch appointment details on mount
onMounted(async () => {
  try {
    loading.value = true;
    
    const detailsData = await fetchAppointmentDetails(appointmentId);
    
    selectedPatient.value = { patient: detailsData.patient };
    evaluationData.value = detailsData.evaluation;
    studentId.value = detailsData.patient?.id;
    
    const user = await api.get('/api/user');
    therapistId.value = user.data.id;
    
    const dashboardData = await fetchTherapistDashboard();
    const patientName = detailsData.patient?.name;
    
    if (dashboardData.appointments) {
      appointments.value = dashboardData.appointments
        .filter(appt => (appt.patient_details?.name || appt.patient) === patientName)
        .map(appt => ({
          id: appt.id,
          title: appt.title || 'Appointment',
          status: appt.status || 'pending',
          date: formatDisplayDate(appt.date),
          time: appt.time || '',
          type: isUpcoming(appt.date, appt.status) ? 'upcoming' : 'post',
          patient: {
            name: appt.patient_details?.name || appt.patient || patientName || 'Unknown',
            number: appt.patient_details?.number || appt.patient_contact || 'N/A',
          },
          treatmentType: appt.treatment_type || 'consultation',
          appointmentType: appt.appointment_type || 'physical',
        }));
    }
    
    if (detailsData.evaluation) {
      ratings.value = [
        { label: "Quality Service", value: detailsData.evaluation.quality_of_service || 0 },
        { label: "Responsiveness", value: detailsData.evaluation.responsiveness || 0 },
        { label: "Effectiveness", value: detailsData.evaluation.effectiveness || 0 },
        { label: "Organization", value: detailsData.evaluation.organization || 0 },
        { label: "Recommendation", value: detailsData.evaluation.recommendation || 0 },
      ];
    }
    
    await Promise.all([fetchNotes(), fetchMedicalRecords(), fetchReflections()]);
    
  } catch (error) {
    console.error('Error fetching appointment details:', error);
    loadingError.value = 'Failed to load appointment details. Please try again.';
  } finally {
    loading.value = false;
  }
});

const isUpcoming = (dateStr, status) => {
  const apptDate = new Date(dateStr);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return apptDate >= today || ['pending', 'approved'].includes(status.toLowerCase());
};

const formatDisplayDate = (dateStr) => {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  if (isNaN(d)) return dateStr;
  return d.toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
};

const normalizeStatus = (status) => (status || "").toString().toLowerCase().trim();

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

const prettyStatus = (status) => {
  const s = normalizeStatus(status);
  if (!s) return "";
  return s.charAt(0).toUpperCase() + s.slice(1);
};

const activeTab = ref("upcoming");
const statusFilter = ref("");

const filteredAppointments = computed(() =>
  appointments.value
    .filter((appt) => appt.type === activeTab.value)
    .filter((appt) => statusFilter.value ? normalizeStatus(appt.status) === normalizeStatus(statusFilter.value) : true)
);

/* =================== ADD APPOINTMENT =================== */
const appointmentShowModal = ref(false);
const appointmentNew = ref({ date: "", time: "", treatmentType: "", appointmentType: "" });
const submittingAdd = ref(false);
const addDateAvailable = ref(true);
const addDateMessage = ref("");
const addAvailableTimeSlots = ref([]);
const loadingAddTimeSlots = ref(false);

function openAddModal() {
  appointmentShowModal.value = true;
  appointmentNew.value = { date: "", time: "", treatmentType: "", appointmentType: "" };
  addDateAvailable.value = true;
  addDateMessage.value = "";
  addAvailableTimeSlots.value = [];
}

async function checkAvailabilityAdd() {
  if (!appointmentNew.value.date || !therapistId.value) return;
  
  loadingAddTimeSlots.value = true;
  addDateMessage.value = "";
  appointmentNew.value.time = "";
  addAvailableTimeSlots.value = [];
  
  try {
    const { data } = await api.get('/api/appointments/available-slots', {
      params: { 
        therapist_id: therapistId.value, 
        date: appointmentNew.value.date 
      }
    });
    
    addDateAvailable.value = data.available;
    addAvailableTimeSlots.value = data.available ? data.timeSlots.map(slot => ({
      start: slot.value.start,
      end: slot.value.end,
      display: slot.display
    })) : [];
    
    addDateMessage.value = data.available ? `Available on ${data.dayOfWeek}` : (data.message || 'Not available');
  } catch (err) {
    console.error('Error checking availability:', err);
    addDateAvailable.value = false;
    addDateMessage.value = 'Error checking availability';
  } finally {
    loadingAddTimeSlots.value = false;
  }
}

async function addAppointment() {
  const a = appointmentNew.value;
  
  if (!a.date || !a.time || !a.treatmentType || !a.appointmentType) {
    Swal.fire('Error', 'Please fill in all fields.', 'error');
    return;
  }
  
  if (!addDateAvailable.value) {
    Swal.fire('Error', 'Selected date is not available.', 'error');
    return;
  }
  
  submittingAdd.value = true;
  
  try {
    const payload = {
      student_id: studentId.value,
      therapist_id: therapistId.value,
      appointment_date: a.date,
      start_time: a.time.start,
      end_time: a.time.end,
      treatment_session_type: a.treatmentType,
      appointment_type: a.appointmentType
    };
    
    await api.post('/api/appointments', payload);
    
    Swal.fire('Success', 'Appointment created successfully!', 'success');
    
    appointmentShowModal.value = false;
    appointmentNew.value = { date: "", time: "", treatmentType: "", appointmentType: "" };
    
    await refreshAppointments();
  } catch (error) {
    console.error('Error creating appointment:', error);
    Swal.fire('Error', error.response?.data?.message || 'Failed to create appointment', 'error');
  } finally {
    submittingAdd.value = false;
  }
}

/* =================== EDIT APPOINTMENT =================== */
const menuIndex = ref(null);
const editModalIndex = ref(null);
const editAppointment = ref({ date: "", time: "", treatmentType: "", appointmentType: "" });
const submittingEdit = ref(false);
const editDateAvailable = ref(true);
const editDateMessage = ref("");
const editAvailableTimeSlots = ref([]);
const loadingEditTimeSlots = ref(false);
const currentEditAppointmentId = ref(null);

const toggleMenu = (index) => {
  menuIndex.value = menuIndex.value === index ? null : index;
};

async function openEditModal(filteredIndex) {
  const apptObj = filteredAppointments.value[filteredIndex];
  const globalIdx = appointments.value.findIndex((a) => a.id === apptObj.id);
  if (globalIdx === -1) return;
  
  currentEditAppointmentId.value = apptObj.id;
  
  try {
    const { data: appointmentData } = await api.get(`/api/appointments/${apptObj.id}/edit`);
    
    if (!appointmentData?.therapist) {
      Swal.fire('Error', 'Appointment details not found', 'error');
      return;
    }
    
    editAppointment.value = {
      date: appointmentData.appointment_date,
      time: "",
      treatmentType: appointmentData.treatment_session_type,
      appointmentType: appointmentData.appointment_type,
    };
    
    editModalIndex.value = globalIdx;
    menuIndex.value = null;
    
    await checkAvailabilityEdit();
    
    const formatTo12Hour = (time) => {
      const [hour, minute] = time.split(':');
      let h = parseInt(hour);
      const ampm = h >= 12 ? 'PM' : 'AM';
      h = h % 12 || 12;
      return `${h}:${minute.padStart(2, '0')} ${ampm}`;
    };
    
    editAppointment.value.time = {
      start: appointmentData.start_time,
      end: appointmentData.end_time,
      display: `${formatTo12Hour(appointmentData.start_time)} - ${formatTo12Hour(appointmentData.end_time)}`
    };
    
  } catch (error) {
    console.error('Error opening edit modal:', error);
    Swal.fire('Error', 'Failed to load appointment details', 'error');
  }
}

async function checkAvailabilityEdit() {
  if (!editAppointment.value.date || !therapistId.value) return;
  
  loadingEditTimeSlots.value = true;
  editDateMessage.value = "";
  editAvailableTimeSlots.value = [];
  
  try {
    const { data } = await api.get('/api/appointments/available-slots', {
      params: { 
        therapist_id: therapistId.value, 
        date: editAppointment.value.date 
      }
    });
    
    editDateAvailable.value = data.available;
    editAvailableTimeSlots.value = data.available ? data.timeSlots.map(slot => ({
      start: slot.value.start,
      end: slot.value.end,
      display: slot.display
    })) : [];
    
    editDateMessage.value = data.available ? `Available on ${data.dayOfWeek}` : (data.message || 'Not available');
  } catch (err) {
    console.error('Error checking availability:', err);
    editDateAvailable.value = false;
    editDateMessage.value = 'Error checking availability';
  } finally {
    loadingEditTimeSlots.value = false;
  }
}

async function saveEditedAppointment() {
  const e = editAppointment.value;
  
  if (!e.date || !e.time || !e.treatmentType || !e.appointmentType) {
    Swal.fire('Error', 'Please fill in all fields.', 'error');
    return;
  }
  
  if (!editDateAvailable.value) {
    Swal.fire('Error', 'Selected date is not available.', 'error');
    return;
  }
  
  submittingEdit.value = true;
  
  try {
    const payload = {
      appointment_date: e.date,
      start_time: e.time.start,
      end_time: e.time.end,
      treatment_session_type: e.treatmentType,
      appointment_type: e.appointmentType
    };
    
    await api.put(`/api/appointments/${currentEditAppointmentId.value}`, payload);
    
    Swal.fire('Success', 'Appointment updated successfully!', 'success');
    
    editModalIndex.value = null;
    currentEditAppointmentId.value = null;
    
    await refreshAppointments();
  } catch (error) {
    console.error('Error updating appointment:', error);
    Swal.fire('Error', error.response?.data?.message || 'Failed to update appointment', 'error');
  } finally {
    submittingEdit.value = false;
  }
}

const cancelEditModal = () => {
  editModalIndex.value = null;
  currentEditAppointmentId.value = null;
};

async function deleteAppt(index) {
  const apptObj = filteredAppointments.value[index];
  
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
    await api.delete(`/api/appointments/${apptObj.id}`);
    appointments.value = appointments.value.filter(a => a.id !== apptObj.id);
    Swal.fire('Deleted!', 'Appointment has been deleted.', 'success');
  } catch (error) {
    console.error('Error deleting appointment:', error);
    Swal.fire('Error', 'Failed to delete appointment', 'error');
  }
  
  menuIndex.value = null;
}

async function refreshAppointments() {
  const dashboardData = await fetchTherapistDashboard();
  const patientName = selectedPatient.value.patient.name;
  
  if (dashboardData.appointments) {
    appointments.value = dashboardData.appointments
      .filter(appt => (appt.patient_details?.name || appt.patient) === patientName)
      .map(appt => ({
        id: appt.id,
        title: appt.title || 'Appointment',
        status: appt.status || 'pending',
        date: formatDisplayDate(appt.date),
        time: appt.time || '',
        type: isUpcoming(appt.date, appt.status) ? 'upcoming' : 'post',
        patient: {
          name: appt.patient_details?.name || appt.patient || patientName || 'Unknown',
          number: appt.patient_details?.number || appt.patient_contact || 'N/A',
        },
        treatmentType: appt.treatment_type || 'consultation',
        appointmentType: appt.appointment_type || 'physical',
      }));
  }
}

/* =================== NOTES =================== */
const showModal = ref(false);
const newFile = ref(null);
const files = ref([]);
const notesLoading = ref(false);
const noteUploading = ref(false);

async function fetchNotes() {
  if (!studentId.value) return;
  
  try {
    notesLoading.value = true;
    const response = await api.get('/api/notes', {
      params: { student_id: studentId.value }
    });
    files.value = response.data;
  } catch (error) {
    console.error('Error fetching notes:', error);
  } finally {
    notesLoading.value = false;
  }
}

function openModal() { 
  showModal.value = true; 
}

function closeModal() { 
  showModal.value = false; 
  newFile.value = null; 
}

function handleFileUploadNote(e) { 
  const file = e.target.files[0];
  if (!file) return;

  const allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];

  const ext = file.name.split('.').pop().toLowerCase();

  if (!allowedExtensions.includes(ext)) {
    Swal.fire('Error', 'Only PDF, Word (DOC/DOCX), and TXT files are allowed.', 'error');
    e.target.value = ''; 
    return;
  }

  const maxSize = 10 * 1024 * 1024; 
  if (file.size > maxSize) {
    Swal.fire('Error', 'File size must be 10MB or less.', 'error');
    e.target.value = '';
    return;
  }

  newFile.value = file;
}


async function submitFile() {
  if (!newFile.value) { 
    Swal.fire('Error', 'Please choose a file before submitting.', 'error');
    return; 
  }
  
  if (!studentId.value) {
    Swal.fire('Error', 'Student ID not found', 'error');
    return;
  }
  
  try {
    noteUploading.value = true;
    const formData = new FormData();
    formData.append('note_file', newFile.value);
    formData.append('student_id', studentId.value);
    
    const response = await api.post('/api/notes', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    files.value.push(response.data.note);
    Swal.fire('Success', 'Note uploaded successfully', 'success');
    closeModal();
  } catch (error) {
    console.error('Error uploading note:', error);
    
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat().join('\n');
      Swal.fire('Validation Error', errors, 'error');
    } else {
      Swal.fire('Error', error.response?.data?.message || 'Failed to upload note', 'error');
    }
  } finally {
    noteUploading.value = false;
  }
}

async function downloadFile(file) {
  try {
    const response = await api.get(`/api/notes/${file.id}/download`, {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', file.file_name);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Error downloading note:', error);
    Swal.fire('Error', 'Failed to download note', 'error');
  }
}

async function deleteFile(id) { 
  const result = await Swal.fire({
    title: 'Delete this note?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#75BDE5',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  });
  
  if (!result.isConfirmed) return;
  
  try {
    await api.delete(`/api/notes/${id}`);
    files.value = files.value.filter(f => f.id !== id);
    Swal.fire('Deleted!', 'Note has been deleted.', 'success');
  } catch (error) {
    console.error('Error deleting note:', error);
    Swal.fire('Error', 'Failed to delete note', 'error');
  }
}

/* =================== MEDICAL RECORDS =================== */
const medicalRecordsfiles = ref([]);
const medicalRecordsLoading = ref(false);
const showAddModal = ref(false);
const newRecord = ref({ date: "", symptoms: "", specialist: "", file: null });
const medicalRecordUploading = ref(false);

async function fetchMedicalRecords() {
  if (!studentId.value) return;
  
  try {
    medicalRecordsLoading.value = true;
    const response = await api.get('/api/medical-records', {
      params: { student_id: studentId.value }
    });
    medicalRecordsfiles.value = response.data;
  } catch (error) {
    console.error('Error fetching medical records:', error);
  } finally {
    medicalRecordsLoading.value = false;
  }
}

function handleFileUploadMedical(e) { 
  const file = e.target.files[0];
  if (!file) return;

  const allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
  
  const ext = file.name.split('.').pop().toLowerCase();

  if (!allowedExtensions.includes(ext)) {
    Swal.fire('Error', 'Only PDF, Word (DOC/DOCX), and TXT files are allowed.', 'error');
    e.target.value = ''; 
    return;
  }

  const maxSize = 10 * 1024 * 1024; 
  if (file.size > maxSize) {
    Swal.fire('Error', 'File size must be 10MB or less.', 'error');
    e.target.value = '';
    return;
  }

  newRecord.value.file = file;
}

async function saveMedicalRecord() {
  if (!newRecord.value.date || !newRecord.value.symptoms) {
    Swal.fire('Error', 'Please fill in date and symptoms', 'error');
    return;
  }
  
  if (!studentId.value) {
    Swal.fire('Error', 'Student ID not found', 'error');
    return;
  }
  
  try {
    medicalRecordUploading.value = true;
    const formData = new FormData();
    formData.append('date', newRecord.value.date);
    formData.append('symptoms', newRecord.value.symptoms);
    formData.append('specialist', newRecord.value.specialist || '');
    formData.append('student_id', studentId.value);
    
    if (newRecord.value.file) {
      formData.append('record_file', newRecord.value.file);
    }
    
    const response = await api.post('/api/medical-records', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    medicalRecordsfiles.value.push(response.data.record);
    Swal.fire('Success', 'Medical record uploaded successfully', 'success');
    
    newRecord.value = { date: "", symptoms: "", specialist: "", file: null };
    showAddModal.value = false;
  } catch (error) {
    console.error('Error uploading medical record:', error);
    
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat().join('\n');
      Swal.fire('Validation Error', errors, 'error');
    } else {
      Swal.fire('Error', error.response?.data?.message || 'Failed to upload medical record', 'error');
    }
  } finally {
    medicalRecordUploading.value = false;
  }
}

async function downloadMedRecordFile(file) {
  try {
    const response = await api.get(`/api/medical-records/${file.id}/download`, {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', file.file_name);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Error downloading medical record:', error);
    Swal.fire('Error', 'Failed to download medical record', 'error');
  }
}

async function deleteMedRecordFile(id) { 
  const result = await Swal.fire({
    title: 'Delete this medical record?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#75BDE5',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  });
  
  if (!result.isConfirmed) return;
  
  try {
    await api.delete(`/api/medical-records/${id}`);
    medicalRecordsfiles.value = medicalRecordsfiles.value.filter(f => f.id !== id);
    Swal.fire('Deleted!', 'Medical record has been deleted.', 'success');
  } catch (error) {
    console.error('Error deleting medical record:', error);
    Swal.fire('Error', 'Failed to delete medical record', 'error');
  }
}

/* =================== FEEDBACK/RATINGS =================== */
const ratings = ref([]);

/* =================== PATIENT REFLECTIONS =================== */
const reflections = ref([]);
const reflectionsLoading = ref(false);

async function fetchReflections() {
  if (!studentId.value) return;
  
  try {
    reflectionsLoading.value = true;
    const response = await api.get(`/api/appointments/patient-reflections/${studentId.value}`);
    reflections.value = response.data;
  } catch (error) {
    console.error('Error fetching reflections:', error);
    reflections.value = [];
  } finally {
    reflectionsLoading.value = false;
  }
}
</script>

<style scoped src="../assets/CCS Therapist/patientList.css"></style>