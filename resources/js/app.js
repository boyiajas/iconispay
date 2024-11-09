import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue'; // Main Vue component
import router from './components/Router'; // Vue Router
import { ZiggyVue } from 'ziggy-js'; // Ziggy for route generation
import 'bootstrap/dist/css/bootstrap.min.css'; // Bootstrap CSS
import 'bootstrap/dist/js/bootstrap.bundle.min.js'; // Bootstrap JS
import '../css/app.css'; // Custom CSS
import Toast from 'vue-toastification'; // Toast for notifications
import 'vue-toastification/dist/index.css'; // Toast CSS
import axios from 'axios';
import $ from 'jquery'; // Import jQuery for DataTables AJAX interception
import LoginModal from './components/authentication/LoginModal.vue'; // Login Modal component

// Create Vue app
const app = createApp(App);

// Configure Toast notifications
app.use(Toast, {
    position: 'top-right',
    timeout: 3000,
    closeOnClick: true,
    pauseOnHover: true,
});

// Use Vue Router and Ziggy for routing
app.use(router);
app.use(ZiggyVue); // Use Ziggy for Laravel route handling

// Set up an Axios interceptor to handle session expiry and server errors
axios.interceptors.response.use(
  response => response, // Pass through successful responses
  error => {
      
      if (error.response && (error.response.status === 401 || error.response.status === 500)) {
          
          const appComponent = app._instance.proxy;
          appComponent.showLoginModal();
      }
      return Promise.reject(error); // Return a rejected promise to handle the error further
  }
);

// Set up a global AJAX interceptor using jQuery
/* $.ajaxSetup({
  error: (xhr, status, error) => {
    if (xhr.status === 401 || xhr.status === 500) {
      const appComponent = app._instance.proxy;
      appComponent.showLoginModal();
    }
  }
}); */
// Set up a global AJAX interceptor using $.ajaxPrefilter
$.ajaxPrefilter((options, originalOptions, jqXHR) => {
  jqXHR.fail((xhr) => {
    if (xhr.status === 401 || xhr.status === 500) {
      // Access the Vue app instance and call the showLoginModal method
      const appComponent = app._instance.proxy;
      appComponent.showLoginModal();
    }
  });
});

// Mount the Vue app
app.mount('#app');
