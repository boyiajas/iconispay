<template>
    <div class="container mt-4">
      <!-- Page Title -->
      <h2 class="mb-4 pb-2 section-title">
        Payment Report <span style="color:#999;font-weight: normal;font-size: 20px;">Ready for Payment</span>
      </h2>
  
      <!-- Source Account Form -->
      <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Ready for Payment <span class="pull-right"><router-link to="/reports" class="btn btn-white btn-sm">Back</router-link></span></h5>
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

      generateReport() {
        axios
            .post("/api/generate-excel-report", 
                { firmAccountId: this.selectedSourceAccount }, 
                { responseType: "blob" } // Important: Expect a binary response
            )
            .then((response) => {
                // Create a URL for the binary data
                const url = window.URL.createObjectURL(new Blob([response.data]));

                // Extract the filename from the response headers
                const contentDisposition = response.headers["content-disposition"];
                const fileName = contentDisposition
                    ? contentDisposition.split("filename=")[1].replace(/"/g, "")
                    : "report.xlsx";

                // Create a link element and trigger download
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", fileName);
                document.body.appendChild(link);
                link.click();
                link.remove();
            })
            .catch((error) => {
                console.error("Error generating report:", error.response?.data || error.message);
            });
    },
    cancelButton() {
            this.$router.go(-1);
        }
    },
    mounted() {
      this.loadSourceAccount(); // Load source accounts when the component is mounted
    },
  };
  </script>
  
  <style scoped>
    
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
  