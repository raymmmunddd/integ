<template>
  <div v-if="show" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Logout</h3>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>
      <p>Are you sure you want to logout?</p>
      <div class="modal-actions">
        <button class="cancel-btn" @click="$emit('close')">Cancel</button>
        <button class="confirm-btn" @click="logout">Logout</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  show: Boolean,
});

const emit = defineEmits(["close"]);

const logout = () => {
  alert("Logged out!"); // Replace with actual logout logic
  emit("close");
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.modal-content {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  width: 350px;
  text-align: center;
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-btn {
  background: none;
  border: none;
  font-size: 22px;
  cursor: pointer;
}

.modal-actions {
  margin-top: 20px;
  display: flex;
  justify-content: space-around;
}

.cancel-btn {
  background: #ccc;
  border: none;
  padding: 8px 18px;
  border-radius: 6px;
  cursor: pointer;
}

.confirm-btn {
  background: #f75b5b;
  color: #fff;
  border: none;
  padding: 8px 18px;
  border-radius: 6px;
  cursor: pointer;
}
</style>
