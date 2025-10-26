import { createRouter, createWebHistory } from "vue-router";

// === Import your views and components ===
import login from "../views/login.vue";
import dashboard from "../views/dashboard.vue";
import signup from "../views/signup.vue";
import medHistory from "../components/Med-History.vue";
import TherapistList from "../components/Therapist-list.vue";
import ForgotPass from "../views/ForgotPass.vue";
import dashboardTherapist from "../views/dashboard-Therapist.vue";
import PatientsTherapist from "../components/PatientsTherapist.vue";
import ProfileTherapist from "../components/ProfileTherapist.vue";
import PatientList from "../components/PatientList.vue";
import NotificationPanel from "../components/NotificationPanel.vue";
import LogoutModal from "../components/LogoutModal.vue";
import LandingPage from "../views/LandingPage.vue";
import StudentForum from "../components/StudentForum.vue";
import TherapistForum from "../components/TherapistForum.vue";
import loginTherapist from "../views/loginTherapist.vue";

// === Define routes ===
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "LandingPage",
      component: LandingPage,
    },
    {
      path: "/login",
      name: "login",
      component: login,
    },
    {
      path:"/loginTherapist",
      name: "loginTherapist",
      component: loginTherapist,
    },  
    {
      path: "/signup",
      name: "signup",
      component: signup,
    },
    {
      path: "/ForgotPass",
      name: "ForgotPass",
      component: ForgotPass,
    },
    {
      path: "/dashboard",
      name: "dashboard",
      component: dashboard,
      meta: { requiresAuth: true, role: "student" },
    },
    {
      path: "/DashboardTherapist",
      name: "DashboardTherapist",
      component: dashboardTherapist,
      meta: { requiresAuth: true, role: "therapist" },
    },
    {
      path: "/medHistory",
      name: "medHistory",
      component: medHistory,
      meta: { requiresAuth: true, role: "student" },
    },
    {
      path: "/TherapistList",
      name: "TherapistList",
      component: TherapistList,
      meta: { requiresAuth: true, role: "student" },
    },
    {
      path: "/PatientsTherapist",
      name: "PatientsTherapist",
      component: PatientsTherapist,
      meta: { requiresAuth: true, role: "therapist" },
    },
    {
      path: "/ProfileTherapist",
      name: "ProfileTherapist",
      component: ProfileTherapist,
      meta: { requiresAuth: true, role: "therapist" },
    },
    {
      path: "/PatientList/:id",
      name: "PatientList",
      component: PatientList,
      props: true,
      meta: { requiresAuth: true, role: "therapist" },
    },
    {
      path: "/NotificationPanel",
      name: "NotificationPanel",
      component: NotificationPanel,
      meta: { requiresAuth: true },
    },
    {
      path: "/LogoutModal",
      name: "LogoutModal",
      component: LogoutModal,
      meta: { requiresAuth: true },
    },
    {
      path: "/StudentForum",
      name: "StudentForum",
      component: StudentForum,
      meta: { requiresAuth: true, role: "student" },
    },
    {
      path: "/TherapistForum",
      name: "TherapistForum",
      component: TherapistForum,
      meta: { requiresAuth: true, role: "therapist" },
    },
  ],
});


// === 🔒 Global Navigation Guard ===
router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem("user"));
  const isAuthenticated = !!user;

  // 🟠 Redirect logged-in users away from login/signup
  if ((to.name === "login" || to.name === "signup") && isAuthenticated) {
    if (user.role === "therapist") {
      return next({ name: "DashboardTherapist" });
    } else {
      return next({ name: "dashboard" });
    }
  }

  // 🟠 Protect routes that require authentication
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: "login" });
  }

  // 🟠 Role-based access control
  if (to.meta.role && user?.role !== to.meta.role) {
    return next({ name: "login" });
  }

  next();
});

export default router;
