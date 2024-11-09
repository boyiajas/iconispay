<template>
  <div v-if="isVisible" class="modal-overlay">
    <div class="card modal-content">
      <div class="card-header">
        <h5 class="card-title">Session Expired</h5>
      </div>
      <div class="card-body">
        <p class="card-text">Please log in to continue.</p>
        <form @submit.prevent="submitLogin">
          <div class="form-group">
            <label for="email">Username</label>
            <input
              type="email"
              v-model="email"
              id="email"
              class="form-control"
              required
              placeholder="Enter your email"
            />
          </div>
          <div class="form-group mt-3">
            <label for="password">Password</label>
            <input
              type="password"
              v-model="password"
              id="password"
              class="form-control"
              required
              placeholder="Enter your password"
            />
          </div>
          <button type="submit" class="btn btn-primary mt-4">Login</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isVisible: false,
      email: '',
      password: ''
    };
  },
  methods: {
    show() {
      this.isVisible = true;
    },
    hide() {
      this.isVisible = false;
    },
    submitLogin() {
      // Emit the login event with user credentials
      this.$emit('login', { email: this.email, password: this.password });
    }
  }
};
</script>

<style scoped>
/* Style for modal overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  border-radius: 5px;
  width: 500px;
  max-width: 90%; /* Ensure the modal is responsive on smaller screens */
  padding: 20px;
}
</style>
