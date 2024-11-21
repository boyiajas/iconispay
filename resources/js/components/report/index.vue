<template>
    <div class="container mt-4">
      <!-- Page Title -->
      <h2 class="mb-4 pb-2 border-bottom">Reports</h2>
  
      <!-- Payment Reports Section -->
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Payment Reports</h5>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <!-- Ready for Payment -->
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <router-link to="/reports/ready-for-payment" class="text-primary text-decoration-none">
                Ready for Payment
              </router-link>
            </li>
  
            <!-- Paid by Date -->
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <router-link to="/reports/paid-by-date" class="text-primary text-decoration-none">
                Paid by Date
              </router-link>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: "Report",
  };
  </script>
  
  <style scoped>
  /* Optional Custom Styles */
  
  /* Add bottom border to the title for emphasis */
  h2 {
    font-size: 24px;
    font-weight: bold;
    color: #333;
  }
  
  /* List group item hover effect */
  .list-group-item:hover {
    background-color: #f8f9fa;
  }
  
  /* Footer logo alignment */
  img {
    height: auto;
  }
  </style>
  