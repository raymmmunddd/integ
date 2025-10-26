<template>
  <routerView>
    
  </routerView>

  <TherapyChatbot v-if="!['/login', '/signup', '/ForgotPass', '/', '/loginTherapist'].includes(route.path)" />

</template>

<script setup>
  import TherapyChatbot from './components/TherapyChatbot.vue';
  import { useRoute } from 'vue-router'

  const route = useRoute()
</script>

<style>
  * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: Poppins;
  scroll-behavior: smooth;
}
</style>