<template>
  <Sidebar />
  <div class="main-container">
    <h2 class="welcome-title">Community Forum</h2>

    <div v-if="loading" class="loading-state">Loading forum...</div>

    <div v-else class="forum-container">
      <!-- Post Question Section -->
      <div class="post-question-card">
        <h3 class="card-title">Ask a Question</h3>
        <p class="card-subtitle">Your question will be posted anonymously</p>
        
        <textarea 
          v-model="newQuestion"
          placeholder="What's on your mind? Ask anything..."
          class="question-input"
          maxlength="500"
        ></textarea>
        
        <div class="post-footer">
          <span class="char-count">{{ newQuestion.length }}/500</span>
          <button 
            class="post-btn" 
            @click="postQuestion"
            :disabled="!newQuestion.trim() || posting"
          >
            {{ posting ? 'Posting...' : 'Post Question' }}
          </button>
        </div>
      </div>

      <!-- Questions Feed -->
      <div class="questions-feed">
        <h3 class="feed-title">Recent Questions & Answers</h3>

        <div v-if="questions.length === 0" class="empty-state">
          <i class='bx bx-message-dots empty-icon'></i>
          <p>No questions yet. Be the first to ask!</p>
        </div>

        <div v-else class="questions-list">
          <div 
            v-for="question in questions" 
            :key="question.id"
            class="question-card"
          >
            <!-- Question Header -->
            <div class="question-header">
              <div class="anonymous-avatar">
                <i class='bx bx-user'></i>
              </div>
              <div class="question-meta">
                <span class="anonymous-name">Anonymous</span>
                <span class="question-time">{{ formatTime(question.created_at) }}</span>
              </div>
              <span 
                v-if="question.user_id === currentUserId"
                class="your-post-badge"
              >
                Your Post
              </span>
            </div>

            <!-- Question Content -->
            <div class="question-content">
              <p>{{ question.content }}</p>
            </div>

            <!-- Answers Section -->
            <div v-if="question.answers && question.answers.length > 0" class="answers-section">
              <div class="answers-header">
                <i class='bx bx-message-square-detail'></i>
                <span>{{ question.answers.length }} {{ question.answers.length === 1 ? 'Answer' : 'Answers' }}</span>
              </div>

              <div 
                v-for="answer in question.answers" 
                :key="answer.id"
                class="answer-card"
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
                    <span class="therapist-name">{{ answer.therapist.name }}</span>
                    <span class="therapist-title">Therapist</span>
                  </div>
                  <span class="answer-time">{{ formatTime(answer.created_at) }}</span>
                </div>
                <div class="answer-content">
                  <p>{{ answer.content }}</p>
                </div>
              </div>
            </div>

            <!-- No Answers State -->
            <div v-else class="no-answers">
              <i class='bx bx-time'></i>
              <span>Waiting for therapist response...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import Sidebar from "../components/Sidebar.vue";
import { useAuth } from "../composables/useAuth";
import Swal from 'sweetalert2';

const router = useRouter();
const auth = useAuth();

/* ---------------- State ---------------- */
const loading = ref(true);
const posting = ref(false);
const userName = ref("Guest");
const currentUserId = ref(null);
const newQuestion = ref("");
const questions = ref([]);

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

/* ---------------- Format Time ---------------- */
const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000); // seconds

  if (diff < 60) return 'Just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
  
  return date.toLocaleDateString();
};

/* ---------------- Post Question ---------------- */
const postQuestion = async () => {
  if (!newQuestion.value.trim()) return;

  posting.value = true;
  try {
    await auth.postQuestion(newQuestion.value);
    newQuestion.value = "";
    await loadQuestions();
    
    Swal.fire({
      icon: 'success',
      title: 'Question Posted!',
      text: 'Your question has been posted anonymously.',
      confirmButtonColor: '#75BDE5',
      timer: 2000,
      showConfirmButton: false
    });
  } catch (error) {
    console.error('Failed to post question:', error);
    Swal.fire({
      icon: 'error',
      title: 'Failed to Post',
      text: 'Unable to post your question. Please try again.',
      confirmButtonColor: '#75BDE5'
    });
  } finally {
    posting.value = false;
  }
};

/* ---------------- Load Questions ---------------- */
const loadQuestions = async () => {
  try {
    loading.value = true;
    const data = await auth.fetchForumQuestions();
    questions.value = data.questions;
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
  userName.value = user.first_name;
  currentUserId.value = user.id;
  
  // Load questions
  await loadQuestions();
});
</script>

<style scoped src="../assets/CSS Students/forum.css"></style>