<template>
  <Teleport to="body">
    <Transition name="logout-modal-fade">
      <div v-if="show" class="logout-modal-backdrop" @click.self="closeModal">
        <Transition name="logout-modal-slide">
          <div v-if="show" class="logout-modal-content">
            <h3 class="logout-modal-title">Confirm Logout</h3>
            <p class="logout-modal-text">Are you sure you want to logout?</p>

            <div class="logout-modal-actions">
              <button class="logout-modal-cancel-btn" @click="closeModal">Cancel</button>
              <button class="logout-modal-confirm-btn" @click="handleLogout" :disabled="isLoggingOut">
                {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
              </button>
            </div>

            <button class="logout-modal-close-x" @click="closeModal">✕</button>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, inject } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
  show: Boolean,
});

const emit = defineEmits(['update:show', 'confirm']);
const router = useRouter();
const auth = inject('auth');
const isLoggingOut = ref(false);

function closeModal() {
  emit('update:show', false);
}

async function handleLogout() {
  if (isLoggingOut.value) return;

  isLoggingOut.value = true;

  try {
    await auth.logout();
    router.push('/login');
    emit('confirm');
  } catch (error) {
    console.error('Logout failed:', error);
    router.push('/login');
  } finally {
    isLoggingOut.value = false;
    closeModal();
  }
}
</script>

<style scoped>
/* Backdrop fade transition */
.logout-modal-fade-enter-active,
.logout-modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.logout-modal-fade-enter-from,
.logout-modal-fade-leave-to {
  opacity: 0;
}

/* Modal content slide transition */
.logout-modal-slide-enter-active,
.logout-modal-slide-leave-active {
  transition: all 0.3s ease;
}

.logout-modal-slide-enter-from,
.logout-modal-slide-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(-20px);
}

.logout-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 3000;
}

.logout-modal-content {
  background: #fff;
  padding: 20px 25px;
  border-radius: 12px;
  position: relative;
  width: 320px;
  max-width: 90%;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.logout-modal-title {
  margin: 0 0 12px 0;
  padding: 0;
  font-size: 1.25rem;
  color: #333;
  font-weight: 600;
}

.logout-modal-text {
  margin: 0 0 20px 0;
  padding: 0;
  color: #555;
  font-size: 1rem;
}

.logout-modal-actions {
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.logout-modal-cancel-btn,
.logout-modal-confirm-btn {
  flex: 1;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: background 0.2s ease;
}

.logout-modal-cancel-btn {
  background: #e0e0e0;
  color: #333;
}

.logout-modal-cancel-btn:hover {
  background: #c0c0c0;
}

.logout-modal-confirm-btn {
  background: #dc3545;
  color: #fff;
}

.logout-modal-confirm-btn:hover:not(:disabled) {
  background: #a71d2a;
}

.logout-modal-confirm-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.logout-modal-close-x {
  position: absolute;
  top: 10px;
  right: 12px;
  background: none;
  border: none;
  font-size: 1.3rem;
  font-weight: bold;
  cursor: pointer;
  color: #666;
  padding: 4px;
  line-height: 1;
  transition: color 0.2s ease;
}

.logout-modal-close-x:hover {
  color: #dc3545;
}
</style>