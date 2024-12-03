<template>
    <div class="container mt-4">
        <h2 class="section-title">Legal Associates Firm Setup</h2>
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-0" id="setupTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="users-tab" data-bs-toggle="tab" href="#users" role="tab" aria-controls="users" aria-selected="true">Users</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="beneficiary-accounts-tab" data-bs-toggle="tab" href="#beneficiary-accounts" role="tab" aria-controls="beneficiary-accounts" aria-selected="false" @click="initializeBeneficiaryAccounts">Beneficiary Accounts</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="firm-accounts-tab" data-bs-toggle="tab" href="#firm-accounts" role="tab" aria-controls="firm-accounts" aria-selected="false" @click="initializeFirmAccounts">Firm Accounts</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="audit-trail-tab" data-bs-toggle="tab" href="#audit-trail" role="tab" aria-controls="audit-trail" aria-selected="false">Audit Trail</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="deactivated-users-tab" data-bs-toggle="tab" href="#deactivated-users" role="tab" aria-controls="deactivated-users" aria-selected="false" @click="loadDeactivatedUsers">Deactivated Users</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="setupTabContent">

            <!-- Firm Accounts Tab -->
            <div class="tab-pane fade wrap" id="firm-accounts" role="tabpanel" aria-labelledby="firm-accounts-tab" width="100%">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Source Accounts</h5>
                        <router-link to="/firmaccount/new" class="btn btn-white btn-sm">+ New Source Account</router-link>
                    </div>
                    <div class="card-body">
                        <table id="source-accounts-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Display</th>
                                    <th>Category</th>
                                    <th>Account Holder</th>
                                    <th width="10%">Account</th>
                                    <th>Institution</th>
                                    <!-- <th width="9%">Aggregated</th> -->
                                    <th width="9%">Authorised</th>
                                    <!-- <th width="9%">Mandated</th> -->
                                    <th width="12%">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            

            <!-- Users Tab -->
            <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Users List</h5>
                        <button class="btn btn-white btn-sm" title="Create New User" @click="addUser"><i class="fas fa-user-plus"></i> New User</button>
                    </div>
                    <div class="card-body">
                        <table id="users-list-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Last Seen</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Beneficiary Accounts Tab -->
            <div class="tab-pane fade" id="beneficiary-accounts" role="tabpanel" aria-labelledby="beneficiary-accounts-tab">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Beneficiary Accounts</h5>
                        <router-link to="/beneficiary/new" class="btn btn-white btn-sm">+ New Beneficiary Account</router-link>
                    </div>
                    <div class="card-body">
                        <table id="beneficiary-accounts-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Display</th>
                                    <th>Category</th>
                                    <th>Account Holder</th>
                                    <th width="10%">Account</th>
                                    <th width="15%">Institution</th>
                                    <th width="10%">Authorised</th>
                                    <th width="12%">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Tab -->
            <div class="tab-pane fade show" id="audit-trail" role="tabpanel" aria-labelledby="audit-trail-tab">
                <!-- Audit Trail Content -->
                <div class="card">
                    <div class="card-header">
                        <h5>Audit Trail</h5>
                    </div>
                    <div class="card-body">
                        <!-- Date Range Filter -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="fromDate">From:</label>
                                <input type="date" v-model="filter.fromDate" class="form-control" id="fromDate" required>
                            </div>
                            <div class="col-md-3">
                                <label for="toDate">To:</label>
                                <input type="date" v-model="filter.toDate" class="form-control" id="toDate" required>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary" @click="filterAuditTrail">Go</button>
                            </div>
                        </div>

                        <!-- Audit Trail Table -->
                        <div>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>User Name</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="audit in auditTrails" :key="audit.id">
                                        <td>{{ audit.user_name }}</td>
                                        <td>{{ audit.action }}</td>
                                        <td>{{ audit.details }}</td>
                                        <td>{{ new Date(audit.created_at).toLocaleString() }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deactivated Users Tab -->
            <div class="tab-pane fade" id="deactivated-users" role="tabpanel" aria-labelledby="deactivated-users-tab">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Deactivated Users List</h5>
                        <button class="btn btn-white btn-sm" @click="addUser">+ New User</button>
                    </div>
                    <div class="card-body">
                        <table id="deactivated-users-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Last Seen</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- New User Modal -->
        <div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newUserModalLabel">Create a new user account</h5>
                        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveUser">
                            <div class="form-group mb-3">
                                <label for="email">Email:</label>
                                <input type="email" v-model="newUser.email" class="form-control" id="email" placeholder="Enter email used for user communication" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="name">Full Name:</label>
                                <input type="text" v-model="newUser.name" class="form-control" id="name" placeholder="Enter full name" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password">Password:</label>
                                <input type="password" v-model="newUser.password" class="form-control" id="password" placeholder="Enter password">
                            </div>
                            <div class="form-group mb-3">
                                <label for="password_confirmation">Confirm Password:</label>
                                <input type="password" v-model="newUser.password_confirmation" class="form-control" id="password_confirmation" placeholder="Enter confirm password">
                            </div>
                            <div class="form-group mb-3">
                                <label for="cell_number">Cell Number:</label>
                                <input type="text" v-model="newUser.cell_number" class="form-control" id="cell_number" placeholder="Enter cell phone number">
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" v-model="newUser.is_admin" id="is_admin">
                                <label class="form-check-label" for="is_admin">Is Administrator</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" v-model="newUser.authoriser_role" id="authoriser_role">
                                <label class="form-check-label" for="authoriser_role">Request Authoriser Role</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" v-model="newUser.bookkeeper_role" id="bookkeeper_role">
                                <label class="form-check-label" for="bookkeeper_role">Request Bookkeeper Role</label>
                            </div>
                            <div class="form-check mb-1 pull-right">
                                <button type="submit" class="btn btn-primary mt-2 mr-1">Save</button>
                                <button type="button" class="btn btn-secondary mt-2" @click="closeModal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- edit firmAccount Modal -->
        <div class="modal fade" id="editFirmAccountModal" tabindex="-1" aria-labelledby="editFirmAccountLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFirmAccountLabel">Edit Firm Account</h5>
                        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="updateFirmAccount">
                            <!-- Display Text Field -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="displayText">Display Text:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="firmAccount.displayText" class="form-control" id="displayText" required>
                                </div>
                            </div>

                            <!-- Account Category Dropdown -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="category">Account Category:</label>
                                <div class="col-sm-9">
                                    <select v-model="firmAccount.categoryId" class="form-control" id="category" required>
                                        <option value="">Select a category</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Account Holder and Account Number Fields -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="accountHolder">Account Holder:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="firmAccount.accountHolder" class="form-control" id="accountHolder" required>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="accountNumber">Account #:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="firmAccount.accountNumber" class="form-control" id="accountNumber" required>
                                </div>
                            </div>

                            <!-- Institution and Branch Code -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="institution">Institution:</label>
                                <div class="col-sm-9">
                                    <select v-model="firmAccount.institutionId" class="form-control" id="institution" required>
                                        <option value="">Select an institution</option>
                                        <option v-for="institution in institutions" :key="institution.id" :value="institution.id">{{ institution.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="branchCode">Branch Code:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="firmAccount.branchCode" class="form-control" id="branchCode" required>
                                </div>
                            </div>

                            <!-- Save and Delete Buttons -->
                            <div class="form-group mb-3 row">
                                <div class="col-sm-9 offset-sm-3">
                                    <button type="submit" class="btn btn-info">Save Changes</button>
                                    <button type="button" class="btn btn-danger ml-2" @click="confirmDelete">Delete</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Register Certificate Modal -->
        <div class="modal fade" id="registerCertificateModal" tabindex="-1" aria-labelledby="registerCertificateModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerCertificateModalLabel">Register Certificate</h5>
                        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="registerCertificate">
                            <div class="form-group mb-3">
                                <label for="certificateFile">Upload Certificate:</label>
                                <input type="file" id="certificateFile" ref="certificateFile" class="form-control" accept=".pem" required>
                            </div>
                            <div class="form-group d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                                <button type="button" class="btn btn-secondary ms-2 btn-sm" @click="closeModal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';
import $ from 'jquery';
/* import 'datatables.net';
import 'datatables.net-bs5'; */
import { useToast } from 'vue-toastification';

export default {
    data() {
        return {
            activeTab: 0,
            usersTable: [],
            sourceAccountsTable: [],
            beneficiaryAccounts: [],
            auditTrails: [],
            deactivatedUsersTable: [],
            newUser: {
                email: '',
                name: '',
                cell_number: '',
                password: '',
                password_confirmation: '',
                is_admin: false,
                authoriser_role: false,
                bookkeeper_role: false
            }, 
            filter: {
                fromDate: '', // Start date for filtering
                toDate: '',   // End date for filtering
            },
            modalInstance: null, // Store the modal instance
            firmAccount: {}, // Store the selected firm account data for editing
        };
    },
    mounted() {
        this.loadUsers();
    },
    setup() {
        // Initialize toast
        const toast = useToast();
        return { toast };
    },
    methods: {
        loadUsers() {
            
            if ($.fn.dataTable.isDataTable('#users-list-table')) {
                $('#users-list-table').DataTable().destroy(); // Destroy existing instance if already initialized
            }

            this.usersTable = $('#users-list-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/users',
                    type: 'GET',
                    dataSrc: 'data', // Assumes the API response has a `data` field
                    error: (xhr, error, thrown) => {
                        console.error('Error fetching data:', error, thrown);
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'email' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'role' },
                    { data: 'last_login' },
                    {
                        data: null,
                        render: function (data) { 

                             // Check if the user has only the 'user' role
                            const hasOnlyUserRole = data.roles.length === 1 && data.roles[0].name === "user";
                            
                            if (!data.latest_certificate) {
                                
                                return `
                                    <button class="btn btn-outline-info btn-sm me-2" title="Edit User"><i class="fas fa-user-edit"></i></button>
                                    ${!hasOnlyUserRole ? `
                                    <button class="btn btn-outline-info btn-sm me-2 generate-certificate-btn" data-user-id="${data.id}" title="Generate Certificate"><i class="fas fa-certificate"></i></button>` : ''}
                                    <button class="btn btn-outline-danger btn-sm delete-user-btn" data-user-id="${data.id}" title="Delete user"><i class="fas fa-trash" ></i></button>
                                `;
                            } else {
                                return `
                                    <button class="btn btn-outline-info btn-sm me-2" title="Edit User"><i class="fas fa-user-edit" ></i></button>
                                    <button class="btn btn-outline-success btn-sm me-2 download-certificate-btn" data-certificate-id="${data.latest_certificate?.id}" title="Download Certificate"><i class="fas fa-download"></i></button>
                                    <button class="btn btn-outline-danger btn-sm delete-user-cert-btn" data-user-id="${data.id}" title="Delete user certificate"><i class="fas fa-user-times" ></i></button>
                                    <button class="btn btn-outline-danger btn-sm delete-user-btn" data-user-id="${data.id}" title="Delete user"><i class="fas fa-trash" ></i></button>
                                `;
                            }
                        }
                    }
                ],
                createdRow: (row, data, dataIndex) => {
                    $(row).find('.generate-certificate-btn').on('click', (event) => {
                        const userId = $(event.currentTarget).data('user-id');
                        if (userId) {
                            //this.openCertificateModal(userId);
                            this.confirmGenerateCertificate(userId);
                        }
                    });
                    $(row).find('.download-certificate-btn').on('click', (event) => {
                        const certificateId = $(event.currentTarget).data('certificate-id');
                        if (certificateId) {
                            this.downloadCertificate(certificateId);
                        }
                    });
                    $(row).find('.delete-user-cert-btn').on('click', (event) => {
                        const userId = $(event.currentTarget).data('user-id');
                        if (userId) {
                            this.confirmDeleteCertificate(userId);
                        }
                    });
                    

                    $(row).find('.delete-user-btn').on('click', (event) => {
                        const userId = $(event.currentTarget).data('user-id');
                        if (userId) {
                            this.confirmUserDelete(userId);
                        }
                    });
                }, 
                responsive: true,
                paging: true,
                pageLength: 10,
                lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
                searching: true,
                autoWidth: true
            });
        },

        downloadCertificate(certificateId) {
            // Implement the logic to view the document details (e.g., open a modal)
            //alert(`Viewing document ID: ${documentId}`);
            // Construct the URL to view the document 
            const documentUrl = `/api/certificates/${certificateId}/download`;

            // Open the document in a new browser tab
            window.open(documentUrl, '_blank');
        },
        openCertificateModal(userId) {
            this.selectedUserId = userId; // Store the user ID for the certificate
            this.modalInstance = new bootstrap.Modal(document.getElementById('registerCertificateModal'));
            this.modalInstance.show();
        },
        confirmDeleteCertificate(userId) {
            if (confirm('Are you sure you want to delete this user certificate?')) {
                this.deleteCertificate(userId);
            }
        },

        confirmGenerateCertificate(id) {
            if (confirm('Are you sure you want to generate a certificate for this user?')) {
                this.generateCertificate(id);
            }
        },

        async deleteCertificate(userId) {
            this.selectedUserId = userId; // Store the user ID for the certificate
            
            try {
                const response = await axios.post(`/api/delete-certificate/${this.selectedUserId}`);
                this.toast.success(response.data.message || 'Certificate deleted successfully!');
                
            } catch (error) {
                this.toast.error(error.response.data.message || 'An error occurred while generating the certificate.');
            }
        },

        async generateCertificate(userId) {
            this.selectedUserId = userId; // Store the user ID for the certificate
            
            try {
                const response = await axios.post(`/api/generate-certificate/${this.selectedUserId}`);
                this.toast.success(response.data.message || 'Certificate generate successfully!');
                
            } catch (error) {
                this.toast.error(error.response.data.message || 'An error occurred while generating the certificate.');
            }
        },
        confirmUserDelete(id) {
            if (confirm('Are you sure you want to delete this account?')) {
                this.deleteUser(id);
            }
        },

        deleteUser(userId){
            axios.delete(`/api/users/${userId}`)
                .then(response => {
                    if (response.data) {
                        this.toast.success('User deleted successfully!', {
                            title: 'Success'
                        });
                    }
                    //this.modalInstance.hide();
                    this.loadUsers(); // Reload table data
                })
                .catch(error => {
                    this.toast.error(error.response ? error.response.data : 'Error deleting user', {
                        title: 'Error'
                    });
                    console.error('Error saving user:', error);
                });
            
        },        
        // Save new user to the database
        saveUser() {
            axios.post('/api/users', this.newUser)
                .then(response => {
                    //this.newUser.push(response.data);
                    if (response.data) {
                        this.toast.success('User created successfully!', {
                            title: 'Success'
                        });
                    }
                    console.log("feedback", response.data);
                    //this.modalInstance = new bootstrap.Modal(document.getElementById('newUserModal'));
                    this.modalInstance.hide();
                    this.resetNewUser();
                    this.loadUsers();
                })
                .catch(error => {
                    this.toast.error(error.response ? error.response.data : 'Error saving user', {
                        title: 'Error'
                    });
                    console.error('Error saving user:', error);
                });
        },
        
        // Load all audit trails or filtered audit trails based on date range
        loadAuditTrails() {
            const params = {
                fromDate: this.filter.fromDate,
                toDate: this.filter.toDate
            };

            axios.get('/api/audit-trails', { params }).then(response => {
                this.auditTrails = response.data;
            }).catch(error => {
                console.error('Error loading audit trails:', error);
            });
        },

        // Filter audit trails based on date range
        filterAuditTrail() {
            this.loadAuditTrails(); // Call the load method with updated filters
        },
        loadDeactivatedUsers() {

            if ($.fn.dataTable.isDataTable('#deactivated-users-table')) {
                $('#deactivated-users-table').DataTable().destroy(); // Destroy existing instance if already initialized
            }

            this.deactivatedUsersTable = $('#deactivated-users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/deactivated-users',
                    type: 'GET',
                    dataSrc: 'data' // Assumes the API response has a `data` field
                },
                columns: [
                    { data: 'email' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'role' },
                    { data: 'last_login' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <button class="btn btn-outline-info btn-sm me-2"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-outline-danger btn-sm"><i class="fas fa-lock"></i></button>
                            `;
                        }
                    }
                ],
                responsive: true,
                paging: true,
                pageLength: 10,
                lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
                searching: true,
                autoWidth: true
            });
        },
        addUser() {
            this.modalInstance = new bootstrap.Modal(document.getElementById('newUserModal'));
            this.modalInstance.show();
        },
         // Show the edit modal with populated data
        editFirmAccount(id) {
            axios.get(`/api/firm-accounts/${id}`)
                .then(response => {
                    this.firmAccount = response.data;
                    this.modalInstance = new bootstrap.Modal(document.getElementById('editFirmAccountModal'));
                    this.modalInstance.show();
                })
                .catch(error => console.error('Error fetching firm account:', error));
        },

        // Update firm account data
        updateFirmAccount() {
            axios.put(`/api/firm-accounts/${this.firmAccount.id}`, this.firmAccount)
                .then(response => {
                    this.modalInstance.hide();
                    this.initializeFirmAccounts(); // Reload table data
                })
                .catch(error => console.error('Error updating firm account:', error));
        },

        // Show Authorize confirmation
        confirmFirmAccountDelete(id) {
            if (confirm('Are you sure you want to delete this account?')) {
                this.deleteFirmAccount(id);
            }
        },

        // Show delete confirmation
        confirmFirmAccountAuhtorize(id) {
            if (confirm('Are you sure you want to Authorize this account?')) {
                this.authorizeFirmAccount(id);
            }
        },
        confirmBeneficiaryAuhtorize(id){
            if (confirm('Are you sure you want to Authorize this account?')) {
                this.authorizeBeneficiaryAccount(id);
            }
        },
        // Authorize firm account
        authorizeFirmAccount(id) {
            axios.post(`/api/firm-accounts/${id}/authorize`)
                .then(response => {
                    // Check the status of the response and display the appropriate toast message
                    if (response.status === 200) {
                        this.toast.success(response.data.message, {
                            title: 'Success'
                        });
                    } else if (response.status === 201) {
                        this.toast.info(response.data.message, {
                            title: 'Info'
                        });
                    } else if (response.status === 404) {
                        this.toast.warning('The firm account was not found.', {
                            title: 'Warning'
                        });
                    } else {
                        this.toast.error('An unexpected error occurred.', {
                            title: 'Error'
                        });
                    }

                    // Reload table data
                    this.initializeFirmAccounts();
                })
                .catch(error => {
                    // Handle any errors that occur during the request
                    if (error.response) {
                        // Server responded with a status other than 2xx
                        if (error.response.status === 400) {
                            this.toast.warning(error.response.data.message || 'Bad Request', {
                                title: 'Warning'
                            });
                        } else if (error.response.status === 404) {
                            this.toast.info('The firm account was not found.', {
                                title: 'Info'
                            });
                        } else if (error.response.status === 500) {
                            this.toast.error('Server error. Please try again later.', {
                                title: 'Error'
                            });
                        } else {
                            this.toast.error(error.response.data.message || 'An unexpected error occurred.', {
                                title: 'Error'
                            });
                        }
                    } else {
                        // Network error or request was not made
                        this.toast.error('Network error. Please check your connection.', {
                            title: 'Error'
                        });
                    }
                    console.error('Error authorizing firm account:', error);
                });
        },

        // Authorize beneficiary account
        authorizeBeneficiaryAccount(id) {
            axios.post(`/api/beneficiary-accounts/${id}/authorize`)
                .then(response => {
                    console.log(response);
                    // Check the status of the response and display the appropriate toast message
                    if (response.status === 200) {
                        this.toast.success(response.data.message, {
                            title: 'Success'
                        });
                    } else if (response.status === 201) {
                        this.toast.info(response.data.message, {
                            title: 'Info'
                        });
                    } else if (response.status === 404) {
                        this.toast.warning('The beneficiary account was not found.', {
                            title: 'Warning'
                        });
                    } else {
                        this.toast.error('An unexpected error occurred.', {
                            title: 'Error'
                        });
                    }

                    // Reload table data
                    this.initializeFirmAccounts();
                    this.initializeBeneficiaryAccounts();
                })
                .catch(error => {
                    // Handle any errors that occur during the request
                    if (error.response) {
                        // Server responded with a status other than 2xx
                        if (error.response.status === 400) {
                            this.toast.warning(error.response.data.message || 'Bad Request', {
                                title: 'Warning'
                            });
                        } else if (error.response.status === 404) {
                            this.toast.info('The beneficiary account was not found.', {
                                title: 'Info'
                            });
                        } else if (error.response.status === 500) {
                            this.toast.error('Server error. Please try again later.', {
                                title: 'Error'
                            });
                        } else {
                            this.toast.error(error.response.data.message || 'An unexpected error occurred.', {
                                title: 'Error'
                            });
                        }
                    } else {
                        // Network error or request was not made
                        this.toast.error('Network error. Please check your connection.', {
                            title: 'Error'
                        });
                    }
                    console.error('Error authorizing firm account:', error);
                });
        },

        // Delete firm account
        deleteFirmAccount(id) { 
            axios.delete(`/api/firm-accounts/${id}`)
                .then(response => {
                    //this.modalInstance.hide();
                    this.initializeFirmAccounts(); // Reload table data
                })
                .catch(error => console.error('Error deleting firm account:', error));
        },

        // Show delete confirmation
        confirmDelete(id) {
            if (confirm('Are you sure you want to delete this account?')) {
                this.deleteBeneficiaryAccount(id);
            }
        },

        // Delete firm account
        deleteBeneficiaryAccount(id) {
            axios.delete(`/api/beneficiary-accounts/${id}`)
                .then(response => {
                    this.modalInstance.hide();
                    this.initializeFirmAccounts(); // Reload table data
                })
                .catch(error => console.error('Error deleting firm account:', error));
        },
        closeModal() {
            //const newUserModal = bootstrap.Modal.getInstance(document.getElementById('newUserModal'));
            if (this.modalInstance) {
                this.modalInstance.hide();
            }
        },
        editUser(user) {
            alert(`Edit user ${user.username}`);
        },
        // Reset new user form fields
        resetNewUser() {
            this.newUser = {
                email: '',
                username: '',
                full_name: '',
                cell_number: '',
                is_admin: false,
                authoriser_role: false,
                bookkeeper_role: false
            };
        },
        reactivateUser(user) {
            alert(`Reactivate user ${user.username}`);
        },
         // Initialize the Source Accounts DataTable
         initializeFirmAccounts() {
            if ($.fn.dataTable.isDataTable('#source-accounts-table')) {
                $('#source-accounts-table').DataTable().destroy(); // Destroy existing instance if already initialized
            }

            this.sourceAccountsTable = $('#source-accounts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/firm-accounts',
                    type: 'GET',
                    dataSrc: 'data', // Assumes the API response has a `data` field
                    error: (xhr, error, thrown) => {
                        console.error('Error fetching data:', error, thrown);
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'display_text' },
                    { data: 'category.name' },
                    { data: 'account_holder' },
                    { data: 'account_number' },
                    { data: 'institution.name' },
                   /*  { data: 'aggregated', render: data => data ? '<i class="fas fa-check text-success"></i>' : '' }, */
                    { 
                        data: 'authorizer_progress',
                        render: (data, type, row) => {
                            if (row.authorised && row.authorised > 0) { //console.log("data is ");console.log(data); console.log("row data"); console.log(row);
                                return '<span class="custom-badge-success form-control"><i class="fas fa-check text-success"></i></span>';
                            }
                            return `<i class="custom-badge-primary form-control">${data}</i>`;
                        }
                    },
                    /* { data: 'mandated', render: data => data ? '<i class="fas fa-check text-success"></i>' : '' }, */
                    {
                        data: null,
                        render: function (data, type, row) {
                            // Conditionally display the fa-check icon
                            const showCheckIcon = (row.authorised === null || row.authorised === 0) && row.number_of_authorizer > 0;
                            return `
                                 ${showCheckIcon ? `<button class='btn btn-outline-info btn-sm authorize-firmaccount-btn' data-toggle='tooltip' title='Authorise this firm Account' data-id='${data.id}'><i class='fas fa-check text-success'></i></button>` : ''}
                                 ${showCheckIcon ? '<button class="btn btn-outline-secondary btn-sm" data-toggle="tooltip" title="Edit this firm Account" onclick="editFirmAccount(${row.id})"><i class="fas fa-edit"></i></button>' : ''}
                                  ${!showCheckIcon ? '<button class="btn btn-outline-info btn-sm" data-toggle="tooltip" title="View this firm Account"><i class="fas fa-search"></i></button>' : ''}
                                
                                <button class="btn btn-outline-danger btn-sm delete-firmaccount-btn" data-toggle="tooltip" title="Delete this firm Account" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                            `;
                        }
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    // Apply style to all <td> elements
                    $('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');
                },
                responsive: true,
                paging: true,
                pageLength: 10,
                lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
                searching: true,
                autoWidth: true,
                wrap: true,
            });

            // Event listener for delete button
            $('#source-accounts-table tbody').on('click', '.delete-firmaccount-btn', (event) => {
                const id = $(event.currentTarget).data('id');
                this.confirmFirmAccountDelete(id);
            });
            // Event listener for delete button
            $('#source-accounts-table tbody').on('click', '.authorize-firmaccount-btn', (event) => {
                const id = $(event.currentTarget).data('id');
                this.confirmFirmAccountAuhtorize(id);
            });
            
        },
       // Initialize the Beneficiary Accounts DataTable
       initializeBeneficiaryAccounts() {
            if ($.fn.dataTable.isDataTable('#beneficiary-accounts-table')) {
                $('#beneficiary-accounts-table').DataTable().destroy(); // Destroy existing instance if already initialized
            }

            this.beneficiaryAccountsTable = $('#beneficiary-accounts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/beneficiary-accounts',
                    type: 'GET',
                    dataSrc: 'data', // Assumes the API response has a `data` field
                    error: (xhr, error, thrown) => {
                        console.error('Error fetching data:', error, thrown);
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'display_text' },
                    { data: 'category.name' },
                    { data: 'company_name' },
                    { data: 'account_number' },
                    { data: 'institution.name' },
                    { 
                        data: 'authorizer_progress',
                        render: (data, type, row) => {
                            if (row.authorised && row.authorised > 0) { //console.log("data is ");console.log(data); console.log("row data"); console.log(row);
                                return '<span class="custom-badge-success form-control"><i class="fas fa-check text-success"></i></span>';
                            }
                            return `<i class="custom-badge-primary form-control">${data}</i>`;
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            // Conditionally display the fa-check icon
                            const showCheckIcon = (row.authorised === null || row.authorised === 0) && row.number_of_authorizer > 0;
                            return `
                                 ${showCheckIcon ? `<button class='btn btn-outline-info btn-sm authorize-beneficiary-btn' data-toggle='tooltip' title='Authorise this beneficiary Account' data-id='${data.id}'><i class='fas fa-check text-success'></i></button>` : ''}
                                 ${showCheckIcon ? '<button class="btn btn-outline-secondary btn-sm" data-toggle="tooltip" title="Edit this beneficiary Account" onclick="editFirmAccount(${row.id})"><i class="fas fa-edit"></i></button>' : ''}
                                  ${!showCheckIcon ? '<button class="btn btn-outline-info btn-sm" data-toggle="tooltip" title="View this beneficiary Account"><i class="fas fa-search"></i></button>' : ''}
                                
                                <button class="btn btn-outline-danger btn-sm delete-beneficiary-btn" data-toggle="tooltip" title="Delete this beneficiary Account" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                            `;
                        }
                    }
                    /* { data: 'authorised', render: data => data ? '<i class="fas fa-check text-success"></i>' : '' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                <button class="btn btn-outline-info btn-sm"><i class="fas fa-search"></i></button>
                                <button class="btn btn-outline-secondary btn-sm edit-beneficiary-btn" data-id="${data.id}"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-outline-danger btn-sm delete-beneficiary-btn" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                            `;
                        }
                    } */
                ],
                createdRow: function(row, data, dataIndex) {
                    // Apply style to all <td> elements
                    $('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');
                },
                responsive: true,
                paging: true,
                pageLength: 10,
                lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
                searching: true,
                autoWidth: true,
                wrap: true,
            });

            // Event listener for edit button
            $('#beneficiary-accounts-table tbody').on('click', '.edit-beneficiary-btn', (event) => {
                const id = $(event.currentTarget).data('id');
                this.editFirmAccount(id);
            });

            // Event listener for delete button
            $('#beneficiary-accounts-table tbody').on('click', '.delete-beneficiary-btn', (event) => {
                const id = $(event.currentTarget).data('id');
                this.confirmDelete(id);
            });
             // Event listener for delete button
             $('#beneficiary-accounts-table tbody').on('click', '.authorize-beneficiary-btn', (event) => {
               
                const id = $(event.currentTarget).data('id');
                this.confirmBeneficiaryAuhtorize(id);
            });
        },
        // Method to load both accounts when Firm Accounts tab is clicked
        loadAccounts() {
            this.initializeSourceAccounts();
            this.initializeBeneficiaryAccounts();
        }
    }
};
</script>

<style scoped>
.card-header h5 {
    font-size: 18px;
    font-weight: bold;
}
.table thead th {
    background-color: #f8f9fa;
    /* text-align: center; */
}

.table {
    table-layout: fixed;
    text-overflow: ellipsis; 
}


tbody tr td {
    text-wrap: wrap !important;
}
</style>
