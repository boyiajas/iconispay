import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue'; // Main Vue component that includes router-view
import router from './components/Router'; // Import Vue Router
import { ZiggyVue } from 'ziggy-js'; // Import ZiggyVue plugin
//import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import '../css/app.css';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import axios from 'axios';
import LoginModal from './components/authentication/LoginModal.vue';

/* var VueResource = require('vue-resource');
Vue.use(VueResource);

import Home from './components/Home.vue';

Vue.http.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
const app = new Vue({
    el: '#app',
    components: { Home }
});
 */

/* import { createApp } from "vue";

import Home from './components/Home.vue';

createApp(Home).mount("#app"); */

// Get Ziggy's routes from the global window object
/* const ziggyOptions = {
    ...window.Ziggy,
}; */

// Create Vue app and use Vue Router
const app = createApp(App);

// Register LoginModal globally
app.component('LoginModal', LoginModal);

// Global event bus to show login modal
app.config.globalProperties.$eventBus = app;


app.use(Toast, {
    position: 'top-right',
    timeout: 3000,
    closeOnClick: true,
    pauseOnHover: true,
  });
  
app.use(router);
app.use(ZiggyVue); // Use the Ziggy plugin
app.mount('#app');


// Axios interceptor for handling session expiry
axios.interceptors.response.use(
    response => response,
    error => {
      if (error.response && error.response.status === 401) {
        // Trigger the login modal when 401 error is detected
        app.config.globalProperties.$eventBus.emit('show-login-modal');
      }
      return Promise.reject(error);
    }
  );