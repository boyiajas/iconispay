import './bootstrap';
import { createApp } from 'vue';
import App from './components/App.vue'; // Main Vue component that includes router-view
import router from './components/Router'; // Import Vue Router
import { ZiggyVue } from 'ziggy-js'; // Import ZiggyVue plugin
//import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import '../css/app.css';



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
app.use(router);
app.use(ZiggyVue); // Use the Ziggy plugin
app.mount('#app');