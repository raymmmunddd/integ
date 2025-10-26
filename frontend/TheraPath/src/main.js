import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import { useAuth } from './composables/useAuth';

const app = createApp(App);

const auth = useAuth();

app.provide('auth', auth);

auth.fetchUser().catch(() => {
  console.log('No authenticated user');
});

app.use(router);

app.mount("#app");
