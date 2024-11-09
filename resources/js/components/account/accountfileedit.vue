<template>
    <div class="container mt-4">
        <!-- Account Header -->
        <div class="account-header mb-4">
            <h4>Account Files <span style="color:#999;font-weight: normal;font-size: 20px;">{{ accountData.display }} - {{ accountData.account_number }} ({{ accountData.branch_code	 }})</span></h4>
            <p>Account Holder: {{ accountData.account_holder }}</p>
        </div>

        <!-- Pending Confirmation Files Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Pending Confirmation Files</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="pending-confirmation-files-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                        <thead>
                            <tr class="table-secondary">
                                <th>Account</th>
                                <th>File</th>
                                <th>Payments</th>
                                <th>Date Generated</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recently Closed Files Section -->
        <div class="card">
            <div class="card-header">
                <h5>Recently Closed Files</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <label for="fromDate" class="me-2">From:</label>
                    <input type="date" v-model="fromDate" class="form-control me-3" id="fromDate">
                    <label for="toDate" class="me-2">To:</label>
                    <input type="date" v-model="toDate" class="form-control me-3" id="toDate">
                    <button class="btn btn-primary" @click="filterClosedFiles">Go</button>
                </div>
                <div class="table-responsive">
                    <table id="recently-closed-files-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                        <thead>
                            <tr class="table-secondary">
                                <th>Account</th>
                                <th>File</th>
                                <th>Payments</th>
                                <th>Date Completed</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-bs5';

export default {
    name: 'AccountFileEdit',
    data() {
        return {
            accountData: {},
            fromDate: '',
            toDate: '',
            pendingConfirmationFilesTable: null,
            recentlyClosedFilesTable: null,
        };
    },
    mounted() {
        this.loadAccountData();
        this.initializePendingConfirmationFilesTable();
        this.initializeRecentlyClosedFilesTable();
    },
    methods: {
        // Load account data based on the passed ID
        loadAccountData() {
            axios.get(`/api/firm-accounts/${this.$route.params.id}`)
                .then(response => {
                    this.accountData = response.data;
                    //console.log(response);
                })
                .catch(error => {
                    console.error("Error fetching account data:", error);
                });
        },

        // Initialize Pending Confirmation Files Table
        initializePendingConfirmationFilesTable() {
            this.pendingConfirmationFilesTable = $('#pending-confirmation-files-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `/api/firm-accounts/${this.$route.params.id}/pending-confirmation-files`,
                    type: 'GET',
                    error: (xhr, error, thrown) => {
                        console.error('Error fetching data:', error, thrown);
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'display' },
                    /* { data: 'file_name' }, */
                    { data: 'file_name' },
                    { data: 'payments' },
                    { data: 'date_generated' },
                    { data: 'total_amount', render: $.fn.dataTable.render.number(',', '.', 2, 'R ') },
                    { data: 'status' },
                ],
                responsive: true,
                destroy: true,
            });
        },

        // Initialize Recently Closed Files Table
        initializeRecentlyClosedFilesTable() {
            this.recentlyClosedFilesTable = $('#recently-closed-files-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `/api/firm-accounts/${this.$route.params.id}/recently-closed-files`,
                    type: 'GET',
                    data: (d) => {
                        d.from_date = this.fromDate;
                        d.to_date = this.toDate;
                    },
                    error: (xhr, error, thrown) => {
                        console.error('Error fetching data:', error, thrown);
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'display' },
                    {
                        data: 'files',
                        render: (data, row) => {
                            console.log(data);
                             if (data && data.length > 0) {
                                return data.map(file => `
                                    <a href="/secure-download/${file.file_id}" class="btn btn-link">
                                        ${file.file_name}
                                    </a>
                                `).join('');
                            } else {
                                return `No open files`;
                            } 
                        }
                    },
                    { data: 'payments' },
                    { data: 'date_completed' },
                    { data: 'total_amount', render: $.fn.dataTable.render.number(',', '.', 2, 'R ') },
                    { data: 'status' },
                ],
                responsive: true,
                destroy: true,
            });
        },

        // Filter Recently Closed Files based on date range
        filterClosedFiles() {
            this.recentlyClosedFilesTable.ajax.reload();
        }
    }
};
</script>

<style scoped>
.account-header h4 {
    font-weight: bold;
    color: #333;
}

.card-header h5 {
    font-weight: bold;
}

.table thead th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.table-responsive {
    border-radius: 5px;
    overflow: hidden;
}
</style>
