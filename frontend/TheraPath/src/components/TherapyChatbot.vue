<template>
  <div class="chatbot-container">
    <transition name="fade-slide">
      <!-- Floating Chat Button -->
      <button
        v-if="!isOpen"
        @click="isOpen = true"
        class="chat-button"
        @mouseenter="buttonHover = true"
        @mouseleave="buttonHover = false"
        :style="{ 
          backgroundColor: buttonHover ? '#85C3E8' : '#75BDE5',
          transform: buttonHover ? 'scale(1.1)' : 'scale(1)'
        }"
        aria-label="Open chat"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFFCFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
      </button>

      <!-- Chat Window -->
      <div v-else class="chat-window">
        <div class="chat-header">
          <div>
            <h3 class="chat-title">Therapy Assistant</h3>
            <p class="chat-subtitle">Here to support you</p>
          </div>
          <button 
            @click="isOpen = false" 
            class="close-button"
            aria-label="Close chat"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFFCFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <!-- Messages -->
        <div class="messages-container" ref="messagesContainer">
          <div
            v-for="(msg, idx) in messages"
            :key="idx"
            class="message-wrapper"
            :class="msg.role"
          >
            <div class="message-bubble" :class="msg.role">
              {{ msg.content }}
            </div>
          </div>

          <div v-if="isTyping" class="message-wrapper assistant">
            <div class="message-bubble assistant typing-indicator">
              <div class="typing-dots">
                <span></span>
                <span></span>
                <span></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Input -->
        <div class="input-container">
          <textarea
            ref="inputField"
            v-model="input"
            @keydown.enter.exact.prevent="sendMessage"
            @keydown.shift.enter.stop
            placeholder="Share your thoughts..."
            rows="1"
            class="chat-input"
            aria-label="Message input"
          />
          <button
            @click="sendMessage"
            :disabled="!input.trim() || isLoading"
            class="send-button"
            @mouseenter="sendHover = true"
            @mouseleave="sendHover = false"
            :style="{
              backgroundColor: input.trim() && !isLoading ? (sendHover ? '#85C3E8' : '#75BDE5') : '#E5E1DA',
              cursor: input.trim() && !isLoading ? 'pointer' : 'not-allowed'
            }"
            aria-label="Send message"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" :stroke="input.trim() && !isLoading ? '#FFFCFB' : '#999'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="22" y1="2" x2="11" y2="13"></line>
              <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue';
import api from "../axios"; 

const isOpen = ref(false);
const messages = ref([
  {
    role: 'assistant',
    content: "Hello! I'm here to provide mental health support. How are you feeling today?"
  }
]);
const input = ref('');
const isLoading = ref(false);
const isTyping = ref(false);
const messagesContainer = ref(null);
const inputField = ref(null);
const buttonHover = ref(false);
const sendHover = ref(false);

// Backend API configuration
const MODEL = 'meta-llama/llama-3.1-8b-instruct:free';

// Scroll to bottom on new messages
const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTo({
        top: messagesContainer.value.scrollHeight,
        behavior: 'smooth'
      });
    }
  });
};

watch(messages, scrollToBottom, { deep: true });
watch(isTyping, scrollToBottom);
watch(isOpen, (newVal) => {
  if (newVal) nextTick(() => inputField.value?.focus());
});

// Crisis keyword detection
const checkForCrisis = (text) => {
  const crisisKeywords = [
    'suicide', 'suicidal', 'kill myself', 'end my life', 
    'hurt myself', 'want to die', 'don\'t want to live'
  ];
  return crisisKeywords.some(k => text.toLowerCase().includes(k));
};

// Typing animation
const typeMessage = async (text) => {
  isTyping.value = true;
  const newMessage = { role: 'assistant', content: '' };
  messages.value.push(newMessage);

  for (let i = 0; i < text.length; i++) {
    await new Promise(resolve => setTimeout(resolve, 20));
    messages.value[messages.value.length - 1].content += text[i];
  }
  
  isTyping.value = false;
};

// Empathetic fallback responses
const getEmpatheticResponse = (userMessage) => {
  const responses = [
    "I hear you. That sounds really challenging. Can you tell me more about what you're experiencing?",
    "Thank you for sharing that with me. Your feelings are valid. What's been on your mind lately?",
    "I understand this is difficult. I'm here to listen. How have you been coping with this?",
    "That must be tough to deal with. What kind of support would be most helpful for you right now?",
    "I appreciate you opening up. Sometimes talking helps. What else would you like to share?",
    "It sounds like you're going through a lot. How are you taking care of yourself?",
    "I'm here for you. Your wellbeing matters. What's been the hardest part about this?"
  ];
  return responses[Math.floor(Math.random() * responses.length)];
};

// Send message
const sendMessage = async () => {
  if (!input.value.trim() || isLoading.value) return;

  const userMessage = input.value.trim();
  input.value = '';
  messages.value.push({ role: 'user', content: userMessage });
  isLoading.value = true;

  // Check for crisis keywords
  if (checkForCrisis(userMessage)) {
    await typeMessage(
      "I'm really concerned about what you just shared. Your safety is the most important thing right now. Please reach out for immediate help:\n\n• National Suicide Prevention Lifeline: 988\n• Crisis Text Line: Text HOME to 741741\n• Emergency Services: 911\n\nYou don't have to go through this alone. Would you be willing to reach out to one of these resources?"
    );
    isLoading.value = false;
    return;
  }

  try {
    const conversationHistory = messages.value
      .slice(-11)
      .filter(m => m.content)
      .map(m => ({
        role: m.role,
        content: m.content
      }));

    const systemPrompt = {
      role: 'system',
      content: `You are a compassionate and empathetic mental health support assistant. Your role is to:
- Listen actively and validate emotions
- Provide supportive, thoughtful responses
- Offer coping strategies and resources
- Recognize when professional help is needed
- Respond naturally, warmly, and concisely (2–4 sentences)`
    };

    // Send to Laravel backend with custom timeout (70 seconds to match backend)
    const response = await api.post('/api/chat', {
      model: MODEL,
      messages: [systemPrompt, ...conversationHistory],
      temperature: 0.7,
      max_tokens: 300,
    }, {
      timeout: 70000 // 70 seconds
    });

    // Validate response structure
    if (!response.data || !response.data.choices || !response.data.choices[0]) {
      console.error('Unexpected API response:', response.data);
      throw new Error('Invalid API response structure');
    }

    const botResponse = response.data.choices[0].message.content.trim();
    await typeMessage(botResponse);
    
  } catch (err) {
    console.error('Chat error:', err);
    
    // Show user-friendly error message based on error type
    let errorMessage;
    if (err.code === 'ECONNABORTED' || err.message?.includes('timeout')) {
      errorMessage = "I'm having trouble connecting right now. Let me try to help you anyway: " + getEmpatheticResponse(userMessage);
    } else if (err.response?.status === 429) {
      errorMessage = "I'm getting a lot of requests right now. Please wait a moment and try again.";
    } else if (err.response?.status === 504) {
      errorMessage = "The response is taking longer than expected. Let me help you directly: " + getEmpatheticResponse(userMessage);
    } else if (err.response?.data?.message) {
      errorMessage = "I'm having a technical issue, but I'm still here for you: " + getEmpatheticResponse(userMessage);
    } else {
      errorMessage = getEmpatheticResponse(userMessage);
    }
    
    await typeMessage(errorMessage);
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped src="../assets/CSS Files/chatbot.css"></style>