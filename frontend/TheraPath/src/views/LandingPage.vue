<template>
  <div>
    <!-- Navigation -->
    <nav>
         <!-- Floating Background Shapes -->
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
      <div class="nav-content container">
        <div class="logo">Therapath</div>

        <!-- Nav links (mobile close button placed first) -->
        <ul class="nav-links" :class="{ active: menuOpen }">
          <li class="nav-close-mobile">
            <button class="close-nav-btn" @click="closeMenu" aria-label="Close menu">×</button>
          </li>

          <li><a href="#hero" @click="closeMenu">Home</a></li>
          <li><a href="#features" @click="closeMenu">Features</a></li>
          <li><a href="#works" @click="closeMenu">How It Works</a></li>
          <li><a href="#FAQ" @click="closeMenu">FAQ</a></li>
        </ul>

        <!-- Menu toggle -->
        <div class="menu-toggle" @click="openMenu" aria-label="Open menu">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </div>
    </nav>

    <!-- overlay that dims the page when nav is open -->
    <div v-if="menuOpen" class="nav-overlay" @click="closeMenu"></div>

    <!-- Hero Section -->
    <section class="hero" id="hero">
      <div class="container">
        <div class="hero-text">
          <h1>Mental Health Consultation Management System</h1>
          <p>Therapath connects clients and therapists for seamless, private, and AI-assisted sessions.</p>
          <router-link to="/login" class="btn btn-primary">Get Started</router-link>
        </div>
        <div class="hero-visual">
          <!-- Floating cards -->
          <div class="floating-card card-1">
            <div class="card-icon">🗂️</div>
            <div class="card-text">Role-Based Management</div>
          </div>
          <div class="floating-card card-2">
            <div class="card-icon">⏰</div>
            <div class="card-text">Automated Reminders</div>
          </div>
          <div class="floating-card card-3">
            <div class="card-icon">📝</div>
            <div class="card-text">Custom Intake Forms</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
      <h2 class="section-title">Key Features</h2>
      <div class="features-grid container">
        <div v-for="feature in features" :key="feature.id" class="feature-card">
          <div class="feature-icon">{{ feature.icon }}</div>
          <h3>{{ feature.title }}</h3>
          <p>{{ feature.description }}</p>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="works">
      <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="steps-container">
          <div v-for="step in steps" :key="step.id" class="step-card">
            <div class="step-number">{{ step.number }}</div>
            <h4>{{ step.title }}</h4>
            <p>{{ step.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq" id="FAQ">
      <div class="container">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <div class="faq-list">
          <div v-for="(faq, index) in faqs" :key="faq.id" class="faq-item" :class="{ active: activeFaq === index }">
            <div class="faq-question" @click="toggleFaq(index)">{{ faq.question }}</div>
            <div class="faq-answer">
              <div class="faq-answer-content">{{ faq.answer }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer>
      <div class="footer-bottom container">
        <p>&copy; 2025 TheraPATH. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from "vue";

// --- Menu toggle ---
const menuOpen = ref(false);

function openMenu() {
  menuOpen.value = true;
  document.body.classList.add("menu-open");
}

function closeMenu() {
  menuOpen.value = false;
  document.body.classList.remove("menu-open");
}

// --- FAQ ---
const activeFaq = ref(null);
function toggleFaq(index) {
  activeFaq.value = activeFaq.value === index ? null : index;
}

// --- Data ---
const faqs = [
  { id: 1, question: "What is Therapath?", answer: "Therapath is a secure platform for mental health consultations connecting clients and therapists." },
  { id: 2, question: "How do I book a session?", answer: "Select a therapist, choose a time slot, and confirm your appointment in your dashboard." },
  { id: 3, question: "Is my information private?", answer: "Yes. All your personal data is encrypted and fully confidential." },
  { id: 4, question: "Can I reschedule sessions?", answer: "Absolutely! You can reschedule through your client dashboard at any time." },
  { id: 5, question: "Do you provide AI assistance?", answer: "Yes. Therapath includes AI-assisted consultation recommendations for therapists." }
];

const features = [
  { id: 1, icon: "🗂️", title: "Role-Based Management", description: "Custom dashboards for therapists and clients ensuring streamlined operations." },
  { id: 2, icon: "⏰", title: "Automated Reminders", description: "Never miss a session with automatic notifications and alerts." },
  { id: 3, icon: "📝", title: "Custom Intake Forms", description: "Personalized forms to gather client info efficiently." },
  { id: 4, icon: "📊", title: "Exportable Reports", description: "Download and analyze reports and notes easily." },
  { id: 5, icon: "⭐", title: "Feedback System", description: "Receive and manage client ratings and comments." },
  { id: 6, icon: "🤖", title: "AI Support", description: "AI-assisted consultation and assessment recommendations." }
];

const steps = [
  { id: 1, number: 1, title: "Sign Up & Create Profile", description: "Therapists and clients create secure accounts." },
  { id: 2, number: 2, title: "Book Sessions", description: "Clients can schedule appointments easily." },
  { id: 3, number: 3, title: "Receive Reminders", description: "Get notifications for upcoming sessions." },
  { id: 4, number: 4, title: "Track Progress", description: "Monitor therapy notes, feedback, and reports." }
];

// --- Cleanup ---
onUnmounted(() => {
  document.body.classList.remove("menu-open");
});
</script>

<style scoped src="../assets/CSS Files/landingPage.css"></style>