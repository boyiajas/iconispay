<template>
    <div class="container mt-4">
      <!-- Page Title -->
      <h2 class="mb-4 border-bottom pb-2">
        Payment Report <span class="text-muted">Ready for Payment</span>
      </h2>
  
      <!-- Source Account Form -->
      <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Ready for Payment</h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="generateReport">
            <div class="row">
              <!-- Source Account Dropdown -->
              <div class="col-md-8">
                <label for="sourceAccount" class="form-label">Source Account:</label>
                <select
                  id="sourceAccount"
                  class="form-select"
                  v-model="selectedSourceAccount"
                >
                  <option value="">-- All Source Accounts --</option>
                  <option
                    v-for="account in sourceAccounts"
                    :key="account.id"
                    :value="account.id"
                  >
                    {{ account.name }}
                  </option>
                </select>
              </div>
              <!-- Generate Report Button -->
              <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                  Generate Report
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    name: "ReadyForPayment",
    data() {
      return {
        sourceAccounts: [], // List of source accounts
        selectedSourceAccount: "", // Selected source account ID
      };
    },
    methods: {
      // Load source accounts from the backend
      loadSourceAccount() {
        axios
          .get("/api/firm-accounts/all-accounts")
          .then((response) => {
            
            this.sourceAccounts = response.data.accounts;
          })
          .catch((error) => {
            console.error("Error loading source accounts:", error);
          });
      },
      // Generate report based on selected source account
      generateReport() {
        if (this.selectedSourceAccount) {
          console.log(
            `Generating report for source account ID: ${this.selectedSourceAccount}`
          );
        } else {
          console.log("Generating report for all source accounts");
        }
        // Add further logic for report generation
      },
    },
    mounted() {
      this.loadSourceAccount(); // Load source accounts when the component is mounted
    },
  };
  </script>
  
  <style scoped>
  h2 {
    font-size: 24px;
    font-weight: bold;
  }
    
  .form-select {
    width: 100%;
    padding: 10px;
  }
  
  .btn-primary {
    width: 100%;
  }
  
  .img-fluid {
    height: 50px;
  }
  </style>
  