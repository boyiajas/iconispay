<template>
    <div>
        <!-- Navbar -->
        <nav class="navbar navbar-light bg-light">
            <div class="container">
                <div class="navbar-row">
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
                        <router-link to="/matters" class="mr-3">Accounts</router-link>
                        <router-link to="/matters" class="mr-3">Reports</router-link>
                        <router-link to="/setup" class="mr-3">Setup</router-link>
                    </PermissionControl>
                    <!-- Use a regular link for Contact since it's a Laravel route -->
                    <a href="{{ route('contact') }}" class="mr-3">Contact</a>
                </div>
            </div>
        </nav>

        <!-- This is where the Vue Router will render components -->
        <div class="container mt-4">
            <router-view :user="user"> </router-view>
        </div>
    </div>
</template>

<script>
import PermissionControl from './permission/PermissionControl.vue';  // Import the Can component
export default {
    name: 'App',
    components: {
        PermissionControl
    },
    data() {
        return {
            // Access the user data from the global `window` object
            user: window.Laravel.user || { name: 'Guest' } // Default to 'Guest' if user is not available
        };
    },
    created() {
        // Redirect to the home route if no specific route is matched
       /*  if (this.$route.path === '/') {
            this.$router.push({ name: 'home' });
        } */
    }
};
</script>

<style scoped>
/* Styles for the App component */
.navbar {
    border-bottom: 3px solid red;
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

</style>
