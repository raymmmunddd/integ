<template>
  <!-- Sidebar -->
  <div :class="['sidebar', { 'sidebar-closed': isClosed }]">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <h2 class="title" v-if="!isClosed">TheraPath</h2>
      <button class="toggle-btn-inside" @click="toggleSidebar">☰</button>
    </div>

    <!-- Menu -->
    <ul class="menu">
      <li>
        <i class="bx bxs-dashboard"></i>
        <RouterLink to="/DashboardTherapist" class="a" v-if="!isClosed">Dashboard</RouterLink>
      </li>
      <li>
        <i class="bx bx-history"></i>
        <RouterLink to="/PatientsTherapist" class="a" v-if="!isClosed">Patients</RouterLink>
      </li>
      <li>
        <i class='bxr bx-message-question-mark'></i> 
        <RouterLink to="/TherapistForum" class="a" v-if="!isClosed">Forum</RouterLink>
      </li>
      <li>
        <i class='bxr bx-user-circle'></i> 
        <RouterLink to="/ProfileTherapist" class="a" v-if="!isClosed">Therapist Profile</RouterLink>
      </li>

      <!-- Notifications Trigger -->
      <li style="position: relative;">
        <div @click.stop="toggleNotifications" style="display:flex; align-items:center; cursor:pointer;">
          <i class="bx bx-bell"></i>
          <span v-if="!isClosed">Notifications</span>
          <!-- Only show badge if there are unread notifications -->
          <span 
            v-if="unreadCount > 0" 
            class="notif-count" 
            :class="{ 'sidebar-closed-badge': isClosed }"
          >
            {{ unreadCount }}
          </span>
        </div>
      </li>
    </ul>

    <!-- Logout -->
    <li class="logout" @click="showLogout = true" style="cursor: pointer;">
      <i class='bxr bx-door-open'></i>
      <span v-if="!isClosed">Logout</span>
    </li>
  </div>

  <!-- Notification Panel -->
  <NotificationPanel
    :show="showNotif"
    :notifications="notifications"
    @remove="removeNotification"
    @clear="clearNotifications"
    @close="showNotif = false"
  />

  <!-- Logout Modal -->
  <LogoutModal
    :show="showLogout"
    @update:show="showLogout = $event"
    @confirm="logoutUser"
  />
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import NotificationPanel from './NotificationPanel.vue';
import LogoutModal from './LogoutModal.vue';
import api from '../axios';

const isClosed = ref(true);
const showNotif = ref(false);
const showLogout = ref(false);

const notifications = ref([]);
const unreadCount = ref(0);

const router = useRouter();

/** Toggle sidebar open/close */
function toggleSidebar() {
  isClosed.value = !isClosed.value;
}

/** Toggle notification panel and mark all as read when opening */
async function toggleNotifications() {
  if (!showNotif.value) {
    // Opening the panel - mark all as read
    showNotif.value = true;
    await fetchNotifications(true); // Pass true to mark as read
  } else {
    // Closing the panel
    showNotif.value = false;
  }
}

/** Fetch notifications from backend */
async function fetchNotifications(markAsRead = false) {
  try {
    const url = markAsRead 
      ? '/api/notifications?mark_as_read=true' 
      : '/api/notifications';
    
    const response = await api.get(url);
    notifications.value = response.data?.notifications ?? [];
    unreadCount.value = response.data?.unread_count ?? 0;
  } catch (error) {
    console.error('Error fetching notifications:', error);
    notifications.value = [];
    unreadCount.value = 0;
  }
}

/** Remove a specific notification (delete it) */
async function removeNotification(id) {
  try {
    await api.delete(`/api/notifications/${id}`);
    notifications.value = notifications.value.filter(n => n.id !== id);
    // Recalculate unread count
    unreadCount.value = notifications.value.filter(n => !n.is_read).length;
  } catch (error) {
    console.error('Error deleting notification:', error);
  }
}

/** Clear all notifications */
async function clearNotifications() {
  try {
    await api.delete('/api/notifications/clear');
    notifications.value = [];
    unreadCount.value = 0;
  } catch (error) {
    console.error('Error clearing notifications:', error);
  }
}

/** Logout user */
function logoutUser() {
  showLogout.value = false;
  router.push('/loginTherapist');
  console.log("User logged out and redirected to /login");
}

// Watch for panel closing to refresh unread count
watch(showNotif, (newValue) => {
  if (!newValue) {
    // Panel closed, refresh to update unread count
    fetchNotifications(false);
  }
});

// Fetch notifications when component mounts (without marking as read)
onMounted(() => {
  fetchNotifications(false).then(() => {
    console.log('Notifications loaded:', notifications.value);
    console.log('Unread count:', unreadCount.value);
  });
});
</script>

<style scoped src="../assets/CSS Files/sidebar.css"></style>