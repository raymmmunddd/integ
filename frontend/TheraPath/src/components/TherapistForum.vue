<template>
  <SidebarTherapist />
  <div class="main-container">
    <h2 class="welcome-title">Community Forum</h2>

    <div v-if="loading" class="loading-state">Loading forum...</div>

    <div v-else class="forum-container">
      <!-- Stats Card -->
      <div class="stats-card">
        <div class="stat-item">
          <i class='bx bx-message-dots'></i>
          <div class="stat-content">
            <span class="stat-number">{{ stats.total_questions }}</span>
            <span class="stat-label">Total Questions</span>
          </div>
        </div>
        <div class="stat-item">
          <i class='bx bx-message-square-check'></i>
          <div class="stat-content">
            <span class="stat-number">{{ stats.answered }}</span>
            <span class="stat-label">Answered</span>
          </div>
        </div>
        <div class="stat-item">
          <i class='bx bx-time'></i>
          <div class="stat-content">
            <span class="stat-number">{{ stats.pending }}</span>
            <span class="stat-label">Pending</span>
          </div>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="filter-tabs">
        <button 
          class="tab-btn"
          :class="{ active: filter === 'all' }"
          @click="filter = 'all'"
        >
          All Questions
        </button>
        <button 
          class="tab-btn"
          :class="{ active: filter === 'unanswered' }"
          @click="filter = 'unanswered'"
        >
          Unanswered
        </button>
        <button 
          class="tab-btn"
          :class="{ active: filter === 'answered' }"
          @click="filter = 'answered'"
        >
          My Answers
        </button>
      </div>

      <!-- Questions Feed -->
      <div class="questions-feed">
        <div v-if="filteredQuestions.length === 0" class="empty-state">
          <i class='bx bx-message-dots empty-icon'></i>
          <p>No questions to display</p>
        </div>

        <div v-else class="questions-list">
          <div 
            v-for="question in filteredQuestions" 
            :key="question.id"
            class="question-card"
          >
            <!-- Question Header -->
            <div class="question-header">
              <div class="anonymous-avatar">
                <i class='bx bx-user'></i>
              </div>
              <div class="question-meta">
                <span class="anonymous-name">Anonymous Student</span>
                <span class="question-time">{{ formatTime(question.created_at) }}</span>
              </div>
              <span 
                v-if="hasAnswered(question)"
                class="answered-badge"
              >
                <i class='bx bx-check'></i> Answered
              </span>
            </div>

            <!-- Question Content -->
            <div class="question-content">
              <p>{{ question.content }}</p>
            </div>

            <!-- Existing Answers -->
            <div v-if="question.answers && question.answers.length > 0" class="answers-section">
              <div class="answers-header">
                <i class='bx bx-message-square-detail'></i>
                <span>{{ question.answers.length }} {{ question.answers.length === 1 ? 'Answer' : 'Answers' }}</span>
              </div>

              <div 
                v-for="answer in question.answers" 
                :key="answer.id"
                class="answer-card"
                :class="{ 'my-answer': answer.therapist_id === currentTherapistId }"
              >
                <div class="answer-header">
                  <!-- Therapist Avatar or Initials -->
                  <img 
                    v-if="getTherapistImage(answer.therapist)"
                    :src="getTherapistImage(answer.therapist)" 
                    :alt="answer.therapist.name"
                    class="therapist-avatar"
                  >
                  <div v-else class="therapist-avatar initials-avatar">
                    {{ getTherapistInitials(answer.therapist) }}
                  </div>
                  
                  <div class="answer-meta">
                    <div class="therapist-name-container">
                      <span class="therapist-name">{{ answer.therapist.name }}</span>
                      <span v-if="answer.therapist_id === currentTherapistId" class="you-badge">You</span>
                    </div>
                    <span class="therapist-title">Therapist</span>
                  </div>
                  <span class="answer-time">{{ formatTime(answer.created_at) }}</span>
                </div>
                <div class="answer-content">
                  <p>{{ answer.content }}</p>
                </div>
              </div>
            </div>

            <!-- Answer Input -->
            <div class="answer-input-section">
              <div v-if="!answeringQuestion[question.id]" class="answer-prompt">
                <button 
                  class="answer-btn"
                  @click="startAnswering(question.id)"
                >
                  <i class='bx bx-message-square-add'></i>
                  Answer this question
                </button>
              </div>

              <div v-else class="answer-form">
                <textarea 
                  v-model="answerText[question.id]"
                  placeholder="Type your answer here..."
                  class="answer-textarea"
                  maxlength="1000"
                ></textarea>
                <div class="answer-actions">
                  <span class="char-count">{{ (answerText[question.id] || '').length }}/1000</span>
                  <div class="action-buttons">
                    <button 
                      class="cancel-btn"
                      @click="cancelAnswering(question.id)"
                    >
                      Cancel
                    </button>
                    <button 
                      class="submit-btn"
                      @click="submitAnswer(question.id)"
                      :disabled="!answerText[question.id]?.trim() || submitting[question.id]"
                    >
                      {{ submitting[question.id] ? 'Posting...' : 'Post Answer' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuth } from "../composables/useAuth";
import SidebarTherapist from "./SidebarTherapist.vue";
import Swal from 'sweetalert2';

const router = useRouter();
const auth = useAuth();

/* ---------------- State ---------------- */
const loading = ref(true);
const userName = ref("Guest");
const currentTherapistId = ref(null);
const questions = ref([]);
const filter = ref('all');
const stats = ref({
  total_questions: 0,
  answered: 0,
  pending: 0
});

const answeringQuestion = ref({});
const answerText = ref({});
const submitting = ref({});

const BASE_URL = import.meta.env.VITE_APP_URL || "http://localhost:8000";

/* ---------------- Avatar Helpers ---------------- */
const getTherapistImage = (therapist) => {
  if (!therapist || !therapist.avatar) return null;
  // If avatar is already a full URL, use it directly
  if (therapist.avatar.startsWith('http')) return therapist.avatar;
  // Otherwise prepend the base URL
  return `${BASE_URL}/storage/${therapist.avatar}`;
};

const getTherapistInitials = (therapist) => {
  const name = therapist?.name || "";
  if (!name.trim()) return "T";
  const parts = name.trim().split(" ");
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
};

/* ---------------- Computed ---------------- */
const filteredQuestions = computed(() => {
  if (filter.value === 'all') {
    return questions.value;
  } else if (filter.value === 'unanswered') {
    return questions.value.filter(q => !q.answers || q.answers.length === 0);
  } else if (filter.value === 'answered') {
    return questions.value.filter(q => hasAnswered(q));
  }
  return questions.value;
});

/* ---------------- Methods ---------------- */
const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);

  if (diff < 60) return 'Just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
  
  return date.toLocaleDateString();
};

const hasAnswered = (question) => {
  if (!question.answers) return false;
  return question.answers.some(a => a.therapist_id === currentTherapistId.value);
};

const startAnswering = (questionId) => {
  answeringQuestion.value[questionId] = true;
};

const cancelAnswering = (questionId) => {
  answeringQuestion.value[questionId] = false;
  answerText.value[questionId] = '';
};

const submitAnswer = async (questionId) => {
  if (!answerText.value[questionId]?.trim()) return;

  submitting.value[questionId] = true;
  try {
    await auth.postAnswer(questionId, answerText.value[questionId]);
    
    // Reset form
    answeringQuestion.value[questionId] = false;
    answerText.value[questionId] = '';
    
    // Reload questions
    await loadQuestions();
    
    Swal.fire({
      icon: 'success',
      title: 'Answer Posted!',
      text: 'Your answer has been successfully posted.',
      confirmButtonColor: '#75BDE5',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    console.error('Failed to post answer:', error);
    Swal.fire({
      icon: 'error',
      title: 'Failed to Post',
      text: 'Unable to post your answer. Please try again.',
      confirmButtonColor: '#75BDE5'
    });
  } finally {
    submitting.value[questionId] = false;
  }
};

/* ---------------- Load Questions ---------------- */
const loadQuestions = async () => {
  try {
    loading.value = true;
    const data = await auth.fetchForumQuestions();
    questions.value = data.questions;
    
    // Calculate stats
    stats.value.total_questions = questions.value.length;
    stats.value.answered = questions.value.filter(q => q.answers && q.answers.length > 0).length;
    stats.value.pending = stats.value.total_questions - stats.value.answered;
  } catch (error) {
    console.error('Failed to load questions:', error);
  } finally {
    loading.value = false;
  }
};

/* ---------------- Lifecycle ---------------- */
onMounted(async () => {
  // Check authentication
  if (!auth.isAuthenticated.value) {
    try {
      await auth.fetchUser();
    } catch (error) {
      router.push('/login');
      return;
    }
  }

  // Get user data
  const user = await auth.fetchUser();
  userName.value = user.last_name;
  currentTherapistId.value = user.id;
  
  // Load questions
  await loadQuestions();
});
</script>

<style scoped src="../assets/CCS Therapist/forum.css"></style>