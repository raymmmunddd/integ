import { ref, computed } from 'vue';
import api from '../axios';

const user = ref(JSON.parse(localStorage.getItem('user')) || null);
const dashboardData = ref(null);
const isAuthenticated = computed(() => user.value !== null);
const isLoading = ref(false);

export function useAuth() {
  const fetchUser = async () => {
    isLoading.value = true;
    try {
      const response = await api.get('/api/user');
      user.value = response.data;
      localStorage.setItem('user', JSON.stringify(user.value)); 
      return user.value;
    } catch (error) {
      console.error('Failed to fetch user:', error);
      user.value = null;
      localStorage.removeItem('user'); 
      throw error;
    } finally {
      isLoading.value = false;
    }
  };

  const fetchTherapistDashboard = async () => {
    try {
      const response = await api.get('/api/therapist/dashboard');
      return response.data;
    } catch (error) {
      console.error('Failed to fetch therapist dashboard:', error);
      throw error;
    }
  };

  const fetchAppointmentDetails = async (appointmentId) => {
    try {
      const response = await api.get(`/api/therapist/appointments/${appointmentId}`);
      console.log('Appointment details response:', response.data);
      return response.data;
    } catch (error) {
      console.error('Error fetching appointment details:', error);
      throw error;
    }
  };

  const fetchDashboard = async () => {
    try {
      const response = await api.get('/api/dashboard');
      dashboardData.value = response.data;
      return dashboardData.value;
    } catch (error) {
      console.error('Failed to fetch dashboard:', error);
      throw error;
    }
  };

  const getJournal = async () => {
    try {
      const response = await api.get('/api/journal/today');
      return response.data;
    } catch (error) {
      console.error('Failed to fetch journal:', error);
      throw error;
    }
  };

  const saveJournal = async (content) => {
    try {
      const response = await api.post('/api/journal/save', { content });
      return response.data;
    } catch (error) {
      console.error('Failed to save journal:', error);
      throw error;
    }
  };

  const login = async (email, password) => {
    try {
      await api.get('/sanctum/csrf-cookie');
      await new Promise(resolve => setTimeout(resolve, 100));

      await api.post('/api/login', { email, password });
      await fetchUser(); 

      return user.value;
    } catch (error) {
      console.error('Login failed:', error);
      user.value = null;
      localStorage.removeItem('user');
      throw error;
    }
  };

  const logout = async () => {
    try {
      await api.post('/api/logout');
    } catch (error) {
      console.error('Logout failed:', error);
    } finally {
      user.value = null;
      dashboardData.value = null;
      localStorage.removeItem('user'); 
    }
  };

  const updateUser = (userData) => {
    user.value = { ...user.value, ...userData };
    localStorage.setItem('user', JSON.stringify(user.value));
  };

  /**
   * Fetch all forum questions with answers
   */
  const fetchForumQuestions = async () => {
    try {
      const response = await api.get('/api/forum/questions');
      return response.data;
    } catch (error) {
      console.error('Error fetching forum questions:', error);
      throw error;
    }
  };

  /**
   * Post a new question (Students only)
   */
  const postQuestion = async (content) => {
    try {
      const response = await api.post('/api/forum/questions', { content });
      return response.data;
    } catch (error) {
      console.error('Error posting question:', error);
      throw error;
    }
  };

  /**
   * Post an answer to a question (Therapists only)
   */
  const postAnswer = async (questionId, content) => {
    try {
      const response = await api.post(`/api/forum/questions/${questionId}/answers`, { content });
      return response.data;
    } catch (error) {
      console.error('Error posting answer:', error);
      throw error;
    }
  };

  return {
    user,
    dashboardData,
    isAuthenticated,
    isLoading,
    fetchUser,
    fetchDashboard,
    getJournal,
    saveJournal,
    login,
    logout,
    updateUser,
    fetchTherapistDashboard,
    fetchAppointmentDetails,
    fetchForumQuestions,
    postQuestion,
    postAnswer,
  };
}