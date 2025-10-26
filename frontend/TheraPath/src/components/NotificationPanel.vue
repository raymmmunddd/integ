<template>
  <transition name="slide">
    <div v-if="show" class="notif-panel">
      <div class="notif-header">
        <h4 class="notif-title">Notifications</h4>
        <button class="close-panel-btn" @click="$emit('close')">✕</button>
      </div>

      <!-- No notifications -->
      <div v-if="notifications.length === 0" class="empty">No notifications</div>

      <!-- Notification List -->
      <div
        v-for="n in notifications"
        :key="n.id"
        class="notif-item"
        :class="{
          'notif-created': n.type === 'appointment_created',
          'notif-updated': n.type === 'appointment_updated',
          'notif-cancelled': n.type === 'appointment_cancelled'
        }"
      >
        <div class="notif-content">
          <span class="notif-message">{{ n.message }}</span>
          <div class="notif-meta">
            <span v-if="n.type" class="notif-type">{{ formatType(n.type) }}</span>
            <small class="notif-time">{{ formatDate(n.created_at) }}</small>
          </div>
        </div>

        <button class="remove-btn" @click.stop="$emit('remove', n.id)">✕</button>
      </div>

      <button
        v-if="notifications.length"
        class="clear-btn"
        @click="$emit('clear')"
      >
        Clear All
      </button>
    </div>
  </transition>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
  notifications: Array
});

/** Convert type to readable label */
function formatType(type) {
  if (type === 'appointment_created') return 'Created';
  if (type === 'appointment_updated') return 'Updated';
  if (type === 'appointment_cancelled') return 'Cancelled';
  return 'Notification';
}

/** Format timestamp */
function formatDate(date) {
  if (!date) return '';
  const d = new Date(date);
  return d.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}
</script>

<style scoped>
:root {
  --background-color: #FFFCFB;
  --main-color: #75BDE5;
}

/* Panel transition */
.slide-enter-active, .slide-leave-active {
  transition: all 0.35s cubic-bezier(0.25, 1, 0.5, 1);
}
.slide-enter-from { transform: translateX(-320px); opacity: 0; }
.slide-leave-to { transform: translateX(-320px); opacity: 0; }

/* Panel container */
.notif-panel {
  position: fixed;
  top: 0;
  left: 0;
  width: 260px;
  height: 100%;
  background: var(--background-color);
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.25);
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 1000;
  overflow-y: auto;
}

/* Header */
.notif-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.close-panel-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  color: #333;
}
.close-panel-btn:hover { color: #dc3545; }

/* Notification items */
.notif-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: #f8f9fa;
  border-left: 4px solid var(--main-color);
  border-radius: 8px;
  padding: 10px 12px;
  transition: background 0.2s;
}
.notif-item:hover { background: #eef2f4; }

/* Type-based colors */
.notif-created { border-left-color: #75BDE5; }   /* green */
.notif-updated { border-left-color: #2563eb; }   /* blue */
.notif-cancelled { border-left-color: #dc2626; } /* red */

/* Message + metadata */
.notif-content {
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 4px;
}
.notif-message {
  font-size: 0.95rem;
  color: #212529;
}
.notif-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #6b7280;
}
.notif-type {
  font-weight: 600;
  color: #f97316;
}
.notif-time {
  font-style: italic;
}

/* Buttons */
.remove-btn {
  background: none;
  border: none;
  color: #dc3545;
  font-size: 0.9rem;
  cursor: pointer;
  margin-left: 8px;
}
.remove-btn:hover { color: #a71d2a; }

.clear-btn {
  padding: 8px 12px;
  background: var(--main-color);
  color: #fff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background 0.2s;
}
.clear-btn:hover { background: #63a1c2; }

/* Empty state */
.empty {
  padding: 15px 0;
  color: #888;
  text-align: center;
  font-style: italic;
}
</style>
