<template>
    <div class="container mt-4">
        <!-- Accounts Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Accounts <span class="pull-right"><button class="btn btn-white btn-sm" @click="refreshData">Refresh</button></span></h5>
            </div>
            <div class="card-body">
                
                <div class="table-responsive">
                    <table id="accounts-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                        <thead>
                            <tr class="table-secondary">
                                <th>Method</th>
                                <th>Account</th>
                                <th>Institution</th>
                                <th>Account Number</th>
                                <th>Ready for Payment</th>
                                <th>Pending Confirmation Files</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Confirmation Files Table -->
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

        <!-- Recently Closed Files Table -->
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
    name: 'Account',
    data() {
        return {
            fromDate: '',
            toDate: '',
            accountsTable: null,
            pendingConfirmationFilesTable: null,
            recentlyClosedFilesTable: null,
        };
    },
    mounted() {
        this.initializeAccountsTable();
        this.initializePendingConfirmationFilesTable();
        this.initializeRecentlyClosedFilesTable();
    },
    methods: {
        // Initialize the Accounts Table
        initializeAccountsTable() {
            this.accountsTable = $('#accounts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/accounts',
                    type: 'GET',
                },
                columns: [
                    { 
                        data: 'method',
                        render: (data) => {
                            let badgeClass = data === 'Manual' ? 'bg-default' : 'bg-success';
                            return `<span class="badge ${badgeClass} pr-3 pl-3">${data}</span>`;
                        }
                    },
                    { data: 'display' },
                    { data: 'institution_name' },
                    { data: 'account_number' },
                    { 
                        data: 'ready_for_payment',
                        render: (data) => {
                            let badgeClass = data === 'Manual' ? 'bg-default' : 'bg-success';
                            return `${data} <span class="btn btn-primary btn-sm ml-4 px-1 py-0">Generate <span class="badge bg-white text-info rounded-pill">6    </span></span>`;
                        }
                    },
                    { data: 'pending_confirmation_files' }
                ],
                responsive: true,
                destroy: true,
            });
        },

        // Initialize the Pending Confirmation Files Table
        initializePendingConfirmationFilesTable() {
            this.pendingConfirmationFilesTable = $('#pending-confirmation-files-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/pending-confirmation-files',
                    type: 'GET',
                },
                columns: [
                    { data: 'display' },
                    { data: 'file_name' },
                    { data: 'payments' },
                    { data: 'date_generated' },
                    { data: 'total_amount', render: $.fn.dataTable.render.number(',', '.', 2, 'R ') },
                    { data: 'status' }
                ],
                responsive: true,
                destroy: true,
            });
        },

        // Initialize the Recently Closed Files Table
        initializeRecentlyClosedFilesTable() {
            this.recentlyClosedFilesTable = $('#recently-closed-files-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/recently-closed-files',
                    type: 'GET',
                    data: (d) => {
                        d.from_date = this.fromDate;
                        d.to_date = this.toDate;
                    }
                },
                columns: [
                    { data: 'display' },
                    { data: 'file_name' },
                    { data: 'payments' },
                    { data: 'date_completed' },
                    { data: 'total_amount', render: $.fn.dataTable.render.number(',', '.', 2, 'R ') },
                    { data: 'status' }
                ],
                responsive: true,
                destroy: true,
            });
        },

        // Refresh all data
        refreshData() {
            this.accountsTable.ajax.reload();
            this.pendingConfirmationFilesTable.ajax.reload();
            this.recentlyClosedFilesTable.ajax.reload();
        },

        // Filter Recently Closed Files based on date range
        filterClosedFiles() {
            this.recentlyClosedFilesTable.ajax.reload();
        }
    }
};
</script>

<style scoped>
.section-title {
    font-weight: bold;
    margin-bottom: 20px;
}

.table-responsive {
    border-radius: 5px;
    overflow: hidden;
}

.table thead th {
    font-weight: bold;
    text-align: center;
}

.table tbody td {
    text-align: center;
}

.table-secondary {
    background-color: #f2f2f2;
}

.form-control {
    max-width: 150px;
}
</style>
