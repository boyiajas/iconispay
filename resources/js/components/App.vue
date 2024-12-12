<template>
    <div>
      <!-- Navbar -->
      <nav class="navbar navbar-light bg-light" v-if="user">
        <div class="container">
          <div class="mr-3 navbar-row">
            <router-link to="/" class="navbar-brand">Iconis® Pay</router-link>
            <div>
              <span>Logged in as: {{ user.name }} (Strauss Daly Incorporated)</span>
              <a :href="route('logout')" class="ml-3">Log out</a>
            </div>
          </div>
          <div class="navbar-links-row">
            <router-link to="/home" class="mr-3">Home</router-link>
            <router-link to="/matters" class="mr-3">Matters</router-link>
            <PermissionControl :roles="['admin', 'authoriser']">
              <router-link to="/accounts" class="mr-3">Accounts</router-link>
              <router-link to="/reports" class="mr-3">Reports</router-link>
              <router-link to="/setup" class="mr-3">Setup</router-link>
            </PermissionControl>
            <!-- Use a regular link for Contact since it's a Laravel route -->
            <a href="{{ route('contact') }}" class="mr-3">Contact</a>
          </div>
        </div>
      </nav>
  
      <!-- Vue Router content -->
      <div class="container mt-4">
        <router-view :user="user"></router-view>
      </div>
  
      <!-- Login modal for expired session or unauthenticated access -->
      <LoginModal ref="loginModal" @login="handleLogin" />
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  import LoginModal from './authentication/LoginModal.vue';
  import PermissionControl from './permission/PermissionControl.vue';
  
  export default {
    name: 'App',
    components: {
      PermissionControl,
      LoginModal
    },
    data() {
      return {
        user: window.Laravel.user || null
      };
    },
    methods: {
        // Method to show the login modal using $refs
        showLoginModal(){
            if (this.$refs.loginModal) {
                this.$refs.loginModal.show();
            }
        },
         // Method to handle login
        handleLogin(credentials) {
        axios
            .post('/login', credentials)
            .then((response) => {
            if (response.status === 200) {
                this.$refs.loginModal.hide();
                location.reload(); // Reload the page to restore the session
            }
            })
            .catch((error) => {
            console.error('Login failed:', error);
            });
        }
    },
   
    /* setup() {
      const loginModal = ref(null);
  
      // Function to handle login
      const handleLogin = async (credentials) => {
        try {
          // Perform login with the provided credentials
          const response = await axios.post('/login', credentials);
  
          if (response.status === 200) {
            loginModal.value.hide();
            // Optionally reload data or refresh the page to restore the session
            location.reload();
          }
        } catch (error) {
          console.error('Login failed:', error);
        }
      };
  
      onMounted(() => {
        // Show the login modal if user is null
        if (!window.Laravel.user) {
          loginModal.value.show();
        }
  
        // Use Axios interceptor to show the login modal on 401 error
        axios.interceptors.response.use(
          (response) => response,
          (error) => {
            if (error.response && error.response.status === 401) {
              loginModal.value.show();
            }
            return Promise.reject(error);
          }
        );
      });
  
      return { handleLogin, loginModal };
    } */
  };
  </script>
  
  <style scoped>
  /* Styles for the App component */
  .navbar {
    border-bottom: 3px solid #40b1c5;;
  }
  .navbar-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }
  .navbar-brand {
    font-size: 1.5rem;
    font-weight: bold;
  }
  .navbar-links-row {
    display: flex;
    justify-content: flex-end;
    margin-top: 5px;
    width: 100%;
  }
  .navbar-links-row a {
    margin-left: 20px;
    color: rgb(0, 151, 178);
    text-decoration: none;
  }
  a{
    color: #0097b2bf;
  }
  </style>
  