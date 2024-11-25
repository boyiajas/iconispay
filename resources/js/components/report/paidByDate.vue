<template>
    <div class="container mt-4">
      <!-- Page Title -->
      <h2 class="mb-4 pb-2 section-title">
        Payment Report <span style="color:#999;font-weight: normal;font-size: 20px;">Paid By Date</span>
      </h2>
  
      <!-- Source Account Form -->
      <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Paid By Date <span class="pull-right"><router-link to="/reports" class="btn btn-white btn-sm">Back</router-link></span></h5>
        </div>
        <div class="card-body">
          <form>
            <div class="row">
              <!-- Source Account Dropdown -->
              <div class="col-md-8">
                <label for="sourceAccount" class="form-label">Select Paid Date:</label>
                <div class="d-flex align-items-center mb-3">
                    <label for="fromDate" class="me-2">From:</label>
                    <input type="date" v-model="fromDate" class="form-control me-3" id="fromDate">
                    <label for="toDate" class="me-2">To:</label>
                    <input type="date" v-model="toDate" class="form-control me-3" id="toDate">
                    <button class="btn btn-primary"  type="button" @click="generateReport">Generate Report</button>
                </div>
               
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
    name: "PaidByDate",
    data() {
      return {
        fromDate: "", // Selected from date
        toDate: "", // Selected to date
      };
    },
    methods: {
     
    generateReport() {
        if (!this.fromDate || !this.toDate) {
            alert("Please select both from and to dates.");
            return;
        }

        axios
            .post(
                "/api/reports/paid-by-date",
                { fromDate: this.fromDate, toDate: this.toDate },
                { responseType: "blob" } // Binary response
            )
            .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const contentDisposition = response.headers["content-disposition"];
                const fileName = contentDisposition
                    ? contentDisposition.split("filename=")[1].replace(/"/g, "")
                    : "Paid_By_Date_Report.xlsx";

                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", fileName);
                document.body.appendChild(link);
                link.click();
                link.remove();
            })
            .catch((error) => {
                console.error(
                    "Error generating report:",
                    error.response?.data || error.message
                );
            });
    },
    cancelButton() {
            this.$router.go(-1);
        }
    },
    mounted() {
      
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
  