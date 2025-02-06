<template>
    <div class="container mt-4">
        <h2 class="section-title">Legal Associates Firm Setup</h2>
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-0" id="setupTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="users-tab" data-bs-toggle="tab" href="#users" role="tab" aria-controls="users" aria-selected="true" @click="loadUsers">Users</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="beneficiary-accounts-tab" data-bs-toggle="tab" href="#beneficiary-accounts" role="tab" aria-controls="beneficiary-accounts" aria-selected="false" @click="initializeBeneficiaryAccounts">Beneficiary Accounts</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="firm-accounts-tab" data-bs-toggle="tab" href="#firm-accounts" role="tab" aria-controls="firm-accounts" aria-selected="false" @click="initializeFirmAccounts">Firm Accounts</a>
            </li>
            
            <PermissionControl :roles="['admin']">
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="onceoff-accounts-tab" data-bs-toggle="tab" href="#onceoff-accounts" role="tab" aria-controls="onceoff-accounts" aria-selected="false" @click="initializeOnceOffAccounts">Once Off Accounts</a>
                </li>
            </PermissionControl>
            <PermissionControl :roles="['admin']">
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="audit-trail-tab" data-bs-toggle="tab" href="#audit-trail" role="tab" aria-controls="audit-trail" aria-selected="false">Audit Trail</a>
                </li>
            </PermissionControl>
            <PermissionControl :roles="['admin']">
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="deactivated-users-tab" data-bs-toggle="tab" href="#deactivated-users" role="tab" aria-controls="deactivated-users" aria-selected="false" @click="loadDeactivatedUsers">Deactivated Users</a>
                </li>
            </PermissionControl>
            
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="setupTabContent">

            <!-- Firm Accounts Tab -->
            <div class="tab-pane fade wrap" id="firm-accounts" role="tabpanel" aria-labelledby="firm-accounts-tab" width="100%">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Source Accounts</h5>
                        <div>
                            <button class="btn btn-white btn-sm me-2" @click="openImportAccountModal('firm')"><i class="fas fa-upload"></i> Import Firm Accounts</button>
                            <router-link to="/firmaccount/new" class="btn btn-white btn-sm">+ New Firm Account</router-link>
                        </div>
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
                        <div>
                            <button class="btn btn-white btn-sm me-2" @click="openImportModal"><i class="fas fa-upload"></i> Import Users</button>
                            <button class="btn btn-white btn-sm" @click="addUser"><i class="fas fa-user-plus"></i> New User</button>
                        </div>
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
                        <div>
                            <button class="btn btn-white btn-sm me-2" @click="openImportAccountModal('beneficiary')"><i class="fas fa-upload"></i> Import Beneficiary Accounts</button>
                            <router-link to="/beneficiary/new" class="btn btn-white btn-sm">+ New Beneficiary Account</router-link>
                        </div>
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

            <!-- Once off Accounts Tab -->
            <div class="tab-pane fade" id="onceoff-accounts" role="tabpanel" aria-labelledby="onceoff-accounts-tab">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5>Once Off Accounts</h5>
                        <div>
                            <button class="btn btn-white btn-sm me-2" @click="openImportAccountModal('onceoff')"><i class="fas fa-upload"></i> Import Once Off Accounts</button>
                            <router-link to="/onceoff/new" class="btn btn-white btn-sm">+ New Once Off Account</router-link>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="onceoff-accounts-table" class="table table-bordered table-striped display nowrap" style="width:100%">
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
                        <div>
                            <button class="btn btn-white btn-sm me-2" @click="openImportModal"><i class="fas fa-upload"></i> Import Users</button>
                            <button class="btn btn-white btn-sm" @click="addUser"><i class="fas fa-user-plus"></i> New User</button>
                        </div>
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
                                <button type="submit" class="btn btn-primary mt-2 mr-1 btn-sm">Save</button>
                                <button type="button" class="btn btn-secondary mt-2 btn-sm" @click="closeModal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Update User</h5>
                        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="updateUser">
                            <div class="pl-2 p-3 mb-3" style="background-color:#eee;border-radius: 5px;">
                                <!-- Account -->
                                <div class="mb-0 row">
                                    <label for="account" class="form-label col-sm-5">Username:</label>
                                    <div class="col-sm-7 text-secondary">
                                        {{ editUser.email }} 
                                    </div>
                                </div>
                                <div class="mb-0 row">
                                    <label for="account" class="form-label col-sm-5">Email Confirmed:</label>
                                    <div class="col-sm-7 text-secondary">
                                        {{ (editUser.email_verify) ? 'Yes' : 'No'  }} 
                                    </div>
                                </div>
                                <div class="mb-0 row">
                                    <label for="account" class="form-label col-sm-5">Account Locked Out:</label>
                                    <div class="col-sm-7 text-secondary">
                                        {{ (editUser.active == 'active') ? 'No' : 'Yes'  }} 
                                    </div>
                                </div>
                                <div class="mb-0 row">
                                    <label for="account" class="form-label col-sm-5">Has Certificate:</label>
                                    <div class="col-sm-7 text-secondary">
                                        {{ (editUser.latest_certificate) ? 'Yes' : 'No'}} 
                                    </div>
                                </div>
                            </div>

                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="editName" class="form-label">Full Name:</label>
                                <input type="text" v-model="editUser.name" id="editName" class="form-control" required />
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email:</label>
                                <input type="email" v-model="editUser.email" id="editEmail" class="form-control" required />
                            </div>

                            <div class="form-group mb-3">
                                <label for="cell_number">Cell Number:</label>
                                <input type="text" v-model="editUser.cell_number" class="form-control" id="editNumber" placeholder="Enter cell phone number">
                            </div>

                            <!-- Role Checkboxes -->
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" v-model="editUser.is_admin" id="editIsAdmin">
                                <label class="form-check-label" for="editIsAdmin">Is Administrator</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" v-model="editUser.authoriser_role" id="editAuthoriserRole">
                                <label class="form-check-label" for="editAuthoriserRole">Request Authoriser Role</label>
                            </div>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" v-model="editUser.bookkeeper_role" id="editBookkeeperRole">
                                <label class="form-check-label" for="editBookkeeperRole">Request Bookkeeper Role</label>
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex justify-content-end mt-2">
                                <button type="submit" class="btn btn-primary me-2 btn-sm">Save</button>
                                <button type="button" class="btn btn-default-default me-2 btn-sm" @click="resetUserPassword"><span class="fas fa-sync-alt"></span> Reset Password</button>

                                <button type="button" class="btn btn-danger me-2 btn-sm" v-if="editUser.active == 'active'" @click="lockUserAccount"><span class="fa fa-lock"></span> Lock</button>
                                <button type="button" class="btn btn-danger me-2 btn-sm" v-else @click="unlockUserAccount"><span class="fa fa-unlock"></span> Un-Lock</button>
                                <button type="button" class="btn btn-secondary btn-sm" @click="closeModal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import {{ importType }} Accounts</h5>
                    <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="importAccounts">
                    <div class="mb-3">
                        <label for="fileType" class="form-label">File Type:</label>
                        <select v-model="importAccountFileType" class="form-select" id="fileType" required>
                        <option value="" disabled>Select file type</option>
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="csv">CSV (.csv)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="importAccountFile" class="form-label">Upload File:</label>
                        <input type="file" ref="importAccountFile" id="importAccountFile" class="form-control" accept=".xlsx, .csv" required>
                    </div>
                    <a :href="sampleFileUrl" class="btn btn-link" download>Download Sample File</a>
                   
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2 btn-sm"><span id="importAccountButtonSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Import</button>
                        <button type="button" class="btn btn-secondary btn-sm" @click="closeModal">Cancel</button>
                    </div>
                    </form>
                </div>
                </div>
            </div>
        </div>

        <!-- Unified Edit Account Modal -->
        <div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAccountLabel">Edit {{ editingAccountType }} Account</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="updateAccount">
                            <!-- Display Text Field -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="displayText">Display Text: *</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="accountData.display_text" class="form-control" id="displayText" placeholder="Enter a display name for the account" required>
                                </div>   
                            </div>

                            <!-- Account Category Dropdown -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="category">Account Category: *</label>
                                <div class="col-sm-9">
                                    <select v-model="accountData.category_id" class="form-control" id="category" required>
                                        <option value="">Select a category</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="number_of_authorizer">Number of Authorisations:</label>
                                <div class="col-sm-9">
                                    <input type="number" v-model="accountData.number_of_authorizer" class="form-control" id="number_of_authorizer" placeholder="Specify the number of authorisations required for this account">
                                </div>   
                            </div>

                            <!-- Account Holder Section -->
                            <h5>Account Holder</h5>
                            <hr class="mt-0"/>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="accountNumber">Account #: *</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="accountData.account_number" class="form-control" id="accountNumber" placeholder="Enter the account number" required>
                                </div>
                            </div>

                            <!-- Account Holder Type Radio Buttons -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3">Account Holder Type: *</label>
                                <div class="col-sm-9">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" v-model="accountData.account_holder_type" id="natural" value="natural">
                                        <label class="form-check-label" for="natural">Natural Person</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" v-model="accountData.account_holder_type" id="juristic" value="juristic">
                                        <label class="form-check-label" for="juristic">Juristic Person</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-0 row form-group">
                                <label for="account_holder" class="form-label col-sm-3" v-if="accountData.account_holder_type == 'natural'">Account Holder: *</label>
                                <div class="col-sm-9 row pr-0 mb-1" v-if="accountData.account_holder_type == 'natural'">
                                    <div class="col-sm-2 pr-0" id="initials">
                                        <input type="text" v-model="accountData.initials" class="form-control" placeholder="Initials" required>
                                    </div>
                                    <div class="col-sm-9 pr-0 mb-1">
                                        <input type="text" v-model="accountData.surname" class="form-control" placeholder="Surname" required>
                                    </div>
                                </div>
                                <label for="account_holder" class="form-label col-sm-3" v-if="accountData.account_holder_type == 'juristic'">Company Name: *</label>
                                <div class="col-sm-9 mb-2" v-if="accountData.account_holder_type == 'juristic'">
                                    <input type="text" v-model="accountData.company_name" class="form-control" id="companyName" placeholder="Enter the name of the Company">
                                </div>
                            </div>
                            
                            <div class="mb-2 row form-group" v-if="accountData.account_holder_type == 'natural'">
                                <label for="id_number" class="form-label col-sm-3">ID No. / Passport No.:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="accountData.id_number" class="form-control" placeholder="Enter ID Number or leave blank if not known">
                                </div>
                            </div>
                            <div class="mb-2 row form-group" v-if="accountData.account_holder_type == 'juristic'">
                                <label for="id_number" class="form-label col-sm-3">Registration #.:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="accountData.registration_number" class="form-control" id="registrationNumber" placeholder="Enter the registration number or leave blank if not applicable">
                                </div>
                            </div>

                            <!-- Account Type Dropdown -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="institution">Account Type: *</label>
                                <div class="col-sm-9">
                                    <select v-model="accountData.account_type_id" class="form-control" id="institution" required>
                                        <option value="">Select an Account Type</option>
                                        <option v-for="accountType in accountTypes" :key="accountType.id" :value="accountType.id">{{ accountType.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Institution Dropdown -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="institution">Institution: *</label>
                                <div class="col-sm-9">
                                    <select v-model="accountData.institution_id" class="form-control" id="institution" required>
                                        <option value="">Select an institution</option>
                                        <option v-for="institution in institutions" :key="institution.id" :value="institution.id">{{ institution.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Branch Code Field -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="branchCode">Branch Code: *</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="accountData.branch_code" class="form-control" id="branchCode" placeholder="Enter the branch code" required>
                                </div>
                            </div>

                            <!-- Verification Section -->
                            <h5>Verification</h5>
                            <hr class="mt-0"/>
                            <div class="form-group mb-3 row">
                                <label for="verified" class="form-label col-sm-3">Verify Account:</label>
                                <div class="col-sm-9 pl-4">
                                    <input type="checkbox" v-model="accountData.verified" class="form-check-input">
                                    <span>Verify account holder and details</span>
                                </div>
                            </div>

                            <!-- Email & Phone -->
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="emailAddress">Email Address:</label>
                                <div class="col-sm-9">
                                    <input type="email" v-model="accountData.email_address" class="form-control" placeholder="Enter email address">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label class="form-label col-sm-3" for="phoneNumber">Phone Number:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="accountData.phone_number" class="form-control" placeholder="Enter phone number">
                                </div>
                            </div>

                            <!-- Save and Delete Buttons -->
                            <div class="form-group mb-3 row">
                                <div class="col-sm-10 offset-sm-2" style="text-align: end;">
                                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                    <button type="button" class="btn btn-danger ml-1 btn-sm" @click="confirmDelete">Delete</button>
                                    <button type="button" class="btn btn-secondary ml-1 btn-sm" @click="closeModal">Cancel</button>
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

        <!-- Import Users Modal -->
        <div class="modal fade" id="importUsersModal" tabindex="-1" aria-labelledby="importUsersModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importUsersModalLabel">Import Users</h5>
                        <button type="button" class="btn-close" @click="closeModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="importUsers">
                            <div class="mb-3">
                                <label for="fileType" class="form-label">File Type:</label>
                                <select v-model="importFileType" class="form-select" id="fileType" required>
                                    <option value="" disabled>Select file type</option>
                                    <option value="excel">Excel (.xlsx)</option>
                                    <option value="csv">CSV (.csv)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="importFile" class="form-label">Upload File:</label>
                                <input type="file" ref="importFile" id="importFile" class="form-control" accept=".xlsx, .csv" required>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-2 btn-sm"><span id="importButtonSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Import</button>
                                <button type="button" class="btn btn-secondary btn-sm" @click="closeModal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Firm Account Details Modal -->
        <div class="modal fade" id="firmAccountModal" tabindex="-1" role="dialog" aria-labelledby="firmAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="firmAccountModalLabel">Firm Account Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Account Holder Section -->
                        <div class="account-holder-section">
                            <h4>{{ firmAccount?.name }} - {{ firmAccount?.accountNumber }}</h4>
                            <p class="mb-2 mt-4">Account Holder <span class="text-secondary">({{ firmAccount?.holderType }})</span></p>
                            <div class="pl-2 p-2 mb-3" style="background-color:#eee; border-radius: 5px;">
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <span class="bold">Name: </span>
                                    </div>
                                    <div class="col-md-7">
                                        <span class="text-secondary">{{ firmAccount?.name }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <span class="bold">Registration # </span>
                                    </div>
                                    <div class="col-md-7">
                                        <span class="text-secondary">{{ firmAccount?.registrationNumber }}</span>
                                    </div>
                                </div>                            
                            </div>
                        </div>

                        <!-- Account Details Section -->
                        <div class="account-details-section">
                            <p class="mb-2 mt-4">Account Details</p>
                            <div class="pl-2 p-2 mb-3" style="background-color:#eee; border-radius: 5px;">
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <span class="bold">Account #: </span>
                                    </div>
                                    <div class="col-md-7">
                                        <span class="text-secondary">{{ firmAccount?.accountNumber }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <span class="bold">Institution: </span>
                                    </div>
                                    <div class="col-md-7">
                                        <span class="text-secondary">{{ firmAccount?.institution }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <span class="bold">Branch: </span>
                                    </div>
                                    <div class="col-md-7">
                                        <span class="text-secondary">{{ firmAccount?.branch }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <span class="bold">Account Description: </span>
                                    </div>
                                    <div class="col-md-7">
                                        <span class="text-secondary">{{ firmAccount?.accountDescription }}</span>
                                    </div>
                                </div> 
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <span class="bold">File Type: </span>
                                    </div>
                                    <div class="col-md-7">
                                        <span class="text-secondary">{{ firmAccount?.fileType }}</span>
                                    </div>
                                </div> 
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <span class="bold">Method: </span>
                                    </div>
                                    <div class="col-md-7">
                                        <span class="text-secondary">{{ firmAccount?.method }}</span>
                                    </div>
                                </div>                            
                            </div>
                        </div>

                        <!-- AVS Section -->
                        <div class="avs-section alert alert-success pb-3 pt-2">
                            AVS Successfully Matched on {{ firmAccount?.avsMatchedAt }}
                            <button class="btn btn-sm btn-light btn-default-default pull-right" @click="showAvsDetails">Show Details</button>
                        </div>

                        <!-- Authorizations Section -->
                        <div class="authorization-section">
                            <h6>Authorisations</h6>
                            <div class="authorization-box alert alert-success pb-3 pt-2">
                                <strong>{{ firmAccount?.authorizer?.name }}</strong> on {{ firmAccount?.authorizedAt }}
                                <p>Logged in as <em>{{ firmAccount?.authorizer?.email }}</em></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" @click="closeModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Firm Account Details Modal Ends Here -->

         <!-- Beneficiary Account Details Modal start here -->
         <!-- Modal -->
         <div class="modal fade" id="beneficiaryAccountModal" tabindex="-1" role="dialog" aria-labelledby="beneficiaryAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="beneficiaryAccountModalLabel">Beneficiary Account Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeModal">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Account Holder Section -->
                    <div class="account-holder-section">
                        <h4>{{ beneficiaryAccount?.name }} - {{ beneficiaryAccount?.accountNumber }}</h4>
                        <p class="mb-2 mt-4">Account Holder <span class="text-secondary">({{ beneficiaryAccount?.holderType }})</span></p>
                        <div class="pl-2 p-2 mb-3" style="background-color:#eee;border-radius: 5px;">
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <span class="bold">Name: </span>
                                </div>
                                <div class="col-md-7">
                                    <span class="text-secondary">{{ beneficiaryAccount?.holderName }}</span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <span class="bold">Registration # </span>
                                </div>
                                <div class="col-md-7">
                                    <span class="text-secondary">{{ beneficiaryAccount?.registrationNumber }}</span>
                                </div>
                            </div>                            
                        </div>
                    </div>

                    <!-- Account Details Section -->
                    <div class="account-details-section">
                        <p class="mb-2 mt-4">Account Details </p>
                        <div class="pl-2 p-2 mb-3" style="background-color:#eee;border-radius: 5px;">
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <span class="bold">Account #: </span>
                                </div>
                                <div class="col-md-7">
                                    <span class="text-secondary">{{ beneficiaryAccount?.accountNumber }}</span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <span class="bold">Institution: </span>
                                </div>
                                <div class="col-md-7">
                                    <span class="text-secondary">{{ beneficiaryAccount?.institution }}</span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <span class="bold">Branch: </span>
                                </div>
                                <div class="col-md-7">
                                    <span class="text-secondary">{{ beneficiaryAccount?.branch }}</span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <span class="bold">Account Description: </span>
                                </div>
                                <div class="col-md-7">
                                    <span class="text-secondary">{{ beneficiaryAccount?.accountDescription }}</span>
                                </div>
                            </div> 
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <span class="bold">File Type: </span>
                                </div>
                                <div class="col-md-7">
                                    <span class="text-secondary">{{ beneficiaryAccount?.fileType }}</span>
                                </div>
                            </div> 
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <span class="bold">Method: </span>
                                </div>
                                <div class="col-md-7">
                                    <span class="text-secondary">{{ beneficiaryAccount?.method }}</span>
                                </div>
                            </div>                            
                        </div>
                        
                    </div>

                    <!-- AVS Section -->
                    <div class="avs-section alert alert-success pb-3 pt-2">
                        AVS Successfully Matched on {{ beneficiaryAccount?.avsMatchedAt }}
                        <button class="btn btn-sm btn-light btn-default-default pull-right" @click="showAvsDetails">Show Details</button>
                    </div>

                    <!-- Authorizations Section -->
                    <div class="authorization-section">
                    <h6>Authorisations</h6>
                    <div class="authorization-box alert alert-success pb-3 pt-2">
                        <strong>{{ beneficiaryAccount?.authorizer?.name }}</strong> on {{ beneficiaryAccount?.authorizedAt }}
                        <p>logged in as <em>{{ beneficiaryAccount?.authorizer?.email }}</em></p>
                    </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" @click="closeModal">Close</button>
                </div>
                </div>
            </div>
        </div>

        <!-- Beneficiary Account Details Modal ends here -->

    </div>
</template>

<script>
import axios from 'axios';
import $ from 'jquery';
import PermissionControl from '../permission/PermissionControl.vue';
/* import 'datatables.net';
import 'datatables.net-bs5'; */
import { useToast } from 'vue-toastification';

export default {
    name: 'Setup',
    //components: { VueMultiselect },
    props: {
        user: {
            type: Object,
            required: true
        },
        
    },
    components: {
        PermissionControl
    },
    data() {
        return {
            editingAccountType: '',
            accountData: {},
            categories: [],
            accountTypes: [],
            institutions: [],
            importFileType: "",
            importAccountFileType: "",
            importType: "", // 'beneficiary' or 'firm'
            sampleFileUrl: "/sample-files/sample-import-template.xlsx",

            activeTab: 0,
            usersTable: [],
            users: [], // Users list
            editUser: {
                id: null,
                name: "",
                email: "",
                email_verify: "",
                password: "",
                password_confirmation: "",
                is_admin: false,
                authoriser_role: false,
                bookkeeper_role: false, 
                latest_certificate: false,
                active: false,
            }, // Object to hold user data for editing
            sourceAccountsTable: [],
            
            auditTrails: [],
            deactivatedUsersTable: [],
            viewFirmAccountModalInstance: null,
            viewBeneficiaryAccountModalInstance: null,
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
            editModalInstance: null,
            editAccountModalInstance: null,
            importModalInstance: null,
            firmAccount: {}, // Store the selected firm account data for editing
            beneficiaryAccount: {},
            beneficiaryAccountsTable: [],
        };
    },
    mounted() {
        this.loadUsers();
        this.loadCategories();
        this.loadInstitutions();
        this.loadAccountTypes();
    },
    setup() {
        // Initialize toast
        const toast = useToast();
        return { toast };
    },
    methods: {
        async resetUserPassword() {
            try {

                const response = await axios.post(`/api/users/reset-password`, {email:this.editUser.email});
                this.toast.success(response.data.message || 'User account reset password successfully!');
                this.closeModal();
                //alert(response.data.message);
            } catch (error) {
                console.error("Error resetting password:", error.response.data);
                this.toast.error(error.response.data.message || 'An error occurred while trying to reset password.');
                this.closeModal();
            }
        },
        async lockUserAccount() {
            try {
                const response = await axios.post(`/api/users/${this.editUser.id}/deactivate`);
                this.toast.success(response.data.message || 'User account de-activated successfully!');
                this.loadUsers();
                this.closeModal();

            } catch (error) {

                this.toast.error(error.response.data.message || 'An error occurred while trying to de-activate user account.');
                console.error("Error locking account:", error.response?.data || error.message);
               
                this.loadUsers();
                this.closeModal();
            }
        },
        async unlockUserAccount() {
            try {
                const response = await axios.post(`/api/users/${this.editUser.id}/activate`);
                
                this.toast.success(response.data.message || 'User account activated successfully!');
                this.closeModal();
                this.loadDeactivatedUsers();

            } catch (error) {
                this.toast.error(error.response.data.message || 'An error occurred while trying to activate user account.');
                console.error("Error activate account:", error.response?.data || error.message);
                //message	"User does not have the right roles."
                this.closeModal();
            }
            
        },
        loadCategories() {
            axios.get('/api/categories').then(response => {
                this.categories = response.data;
            });
        },
        loadAccountTypes(){
            axios.get('/api/accounttypes')
                .then(response => {
                    this.accountTypes = response.data;
                })
                .catch(error => {
                    console.error('Error loading accountTypes:', error);
                });
        },
        loadInstitutions() {
            axios.get('/api/institutions').then(response => {
                this.institutions = response.data;
            });
        },
        openEditModal(accountType, id) {
            
            this.editingAccountType = accountType;
            axios.get(`/api/${accountType}-accounts/${id}`).then(response => {
                this.accountData = response.data;
                console.log(this.accountData);
                this.editAccountModalInstance = new bootstrap.Modal(document.getElementById("editAccountModal"));
                this.editAccountModalInstance.show();
            });
        },
        updateAccount() {
            axios.put(`/api/${this.editingAccountType}-accounts/${this.accountData.id}`, this.accountData)
                .then(response => {
                   // Response contains the updated payment
                    if(response.data){
                        // Find the index of the payment in the payments array
                        this.toast.success(response.data.message, {
                            title: 'Success'
                        });
                    }else{
                        this.toast.error(error.response ? error.response.data : 'No response data', {
                            title: 'Error'
                        });
                    }

                   
                    // Close the modal
                    this.closeModal();
                })
                .catch(error => {
                    console.error('Error updating account:', error);
                    this.toast.error(error.response ? error.response.data : 'An error occurred while updating account', {
                            title: 'Error'
                        });
                    this.closeModal();
                });

                (this.editingAccountType == 'firm') ? this.initializeFirmAccounts() : this.initializeBeneficiaryAccounts();
        },
        openImportAccountModal(type) {
            this.importType = type === "beneficiary" ? "Beneficiary" : "Firm";
            this.importModalInstance = new bootstrap.Modal(document.getElementById("importModal"));
            this.importModalInstance.show();
        },
        importAccounts() {
            const fileInput = this.$refs.importAccountFile.files[0];
            if (!fileInput) {
                alert("Please select a file to upload.");
                return;
            }

            const importAccountButtonSpinner = document.getElementById('importAccountButtonSpinner');
            importAccountButtonSpinner.classList.remove('d-none');

            const formData = new FormData();
            formData.append("file", fileInput);
            formData.append("fileType", this.importFileType);

            const apiUrl = this.importType === "Beneficiary" ? "/api/import-beneficiary-accounts" : "/api/import-firm-accounts";

            axios
                .post(apiUrl, formData, {
                headers: { "Content-Type": "multipart/form-data" },
                })
                .then((response) => {
                    //alert(`${this.importType} Accounts imported successfully!`);
                    // Display the server response message in a toast
                    const importedCount = response.data.imported_accounts?.length || 0; // Get the number of imported users
                    const message = `${response.data.message} Imported Accounts: ${importedCount}.`;
                    this.toast.success(message);
                    importAccountButtonSpinner.classList.add('d-none');
                    this.closeModal();
                })
                .catch((error) => {
                    console.error(`Error importing ${this.importType} accounts:`, error.response?.data || error.message);
                    const errorMessage = error.response?.data?.message || "An error occurred during the import process.";
                    this.toast.error(errorMessage);
                    importAccountButtonSpinner.classList.add('d-none');
                    this.closeModal();
                });
        },
        openEditUserModal() {
                          
            this.editModalInstance = new bootstrap.Modal(document.getElementById("editUserModal"));
            this.editModalInstance.show();
            
        },
        // Update User
        updateUser() {
            // Map roles from checkboxes
            const roles = [];
            if (this.editUser.is_admin) roles.push("admin");
            if (this.editUser.authoriser_role) roles.push("authoriser");
            if (this.editUser.bookkeeper_role) roles.push("bookkeeper");

            const payload = {
                ...this.editUser,
                roles: roles.join(","), // Comma-separated roles
            };

            axios
                .put(`/api/users/${this.editUser.id}`, payload)
                .then((response) => {

                   
                    this.toast.success(response.data.message || 'User updataed successfully!');
           
                    this.closeModal();
                    this.loadUsers(); // Refresh user list
                    //$('#users-list-table').DataTable().ajax.reload(null, false);
                })
                .catch((error) => {
                    this.toast.error(error.response.data.message || 'An error occurred while trying to update user information.');
                    console.error("Error updating user:", error.response?.data || error.message);
                }); 
        },
        openImportModal() {
            this.modalInstance = new bootstrap.Modal(document.getElementById("importUsersModal"));
            this.modalInstance.show();
        },
        importUsers() {
            const fileInput = this.$refs.importFile.files[0];
            if (!fileInput) {
                alert("Please select a file to upload.");
                return;
            }

            const importButtonSpinner = document.getElementById('importButtonSpinner');
            importButtonSpinner.classList.remove('d-none');

            const formData = new FormData();
            formData.append("file", fileInput);
            formData.append("fileType", this.importFileType);

            axios.post("/api/import-users", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            })
            .then((response) => {
                // Display the server response message in a toast
                const importedCount = response.data.imported_users?.length || 0; // Get the number of imported users
                const message = `${response.data.message} Imported Users: ${importedCount}.`;
                this.toast.success(message);
                importButtonSpinner.classList.add('d-none');
                this.loadUsers(); // Refresh user list
                this.closeModal();

            })
            .catch((error) => {
                // Handle errors and show an error message in a toast
                const errorMessage = error.response?.data?.message || "An error occurred during the import process.";
                this.toast.error(errorMessage);
                console.error("Error importing users:", error.response?.data || error.message);
                importButtonSpinner.classList.add('d-none');
                this.loadUsers();
                this.closeModal();
            });
        },
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
                            const certificateId = data.latest_certificate?.id ?? null;

                           
                            
                            if (!certificateId) {
                                
                                return `
                                    <button class="btn btn-outline-info btn-sm me-2 edit-user-btn" data-user-id="${data.id}" data-certificate-id="${certificateId}" title="Edit User"><i class="fas fa-user-edit"></i></button>
                                    ${!hasOnlyUserRole ? `
                                    <button class="btn btn-outline-info btn-sm me-2 generate-certificate-btn" data-user-id="${data.id}" title="Generate Certificate"><i class="fas fa-certificate"></i></button>` : ''}
                                    <button class="btn btn-outline-danger btn-sm delete-user-btn" data-user-id="${data.id}" title="Delete user"><i class="fas fa-trash" ></i></button>
                                `;
                            } else {
                                return `
                                    <button class="btn btn-outline-info btn-sm me-2 edit-user-btn" data-user-id="${data.id}" data-certificate-id="${certificateId}" title="Edit User"><i class="fas fa-user-edit" ></i></button>
                                    <button class="btn btn-outline-success btn-sm me-2 download-certificate-btn" data-certificate-id="${certificateId}" title="Download Certificate"><i class="fas fa-download"></i></button>
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

                    $(row).find('.edit-user-btn').on('click', (event) => { 
                        
                        const userId = $(event.currentTarget).data('user-id');
                        const certificateId = $(event.currentTarget).data('certificate-id') || null; // Ensure it's null if not found
                       
                        const user = this.usersTable
                            .data()
                            .toArray()
                            .find((u) => u.id === userId); 

                        if (user) {

                            // Normalize roles
                            const roles = Array.isArray(user.roles)
                                ? user.roles.map((role) => (typeof role === "object" ? role.name : role))
                                : [];

                             // Populate `editUser`
                            this.editUser = {
                                id: user.id || null, // Ensure the user ID is included for updates
                                name: user.name || "",
                                email: user.email || "",
                                email_verify: user.email_verified_at || "",
                                active: user.status,
                                password: "", // Reset password fields
                                password_confirmation: "",
                                is_admin: roles.includes("admin"),
                                authoriser_role: roles.includes("authoriser"),
                                bookkeeper_role: roles.includes("bookkeeper"),
                                latest_certificate: certificateId,
                            };
                            console.log(this.editUser);

                            this.openEditUserModal();
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

                            const certificateId = data.latest_certificate?.id ?? null;
                            const hasOnlyUserRole = data.roles.length === 1 && data.roles[0].name === "user";

                            if (!certificateId) {
                                
                                return `
                                    <button class="btn btn-outline-info btn-sm me-2 edit-user-btn" data-user-id="${data.id}" data-certificate-id="${certificateId}" title="Edit User"><i class="fas fa-user-edit"></i></button>
                                    ${!hasOnlyUserRole ? `
                                    <button class="btn btn-outline-info btn-sm me-2 generate-certificate-btn" data-user-id="${data.id}" title="Generate Certificate"><i class="fas fa-certificate"></i></button>` : ''}
                                    <button class="btn btn-outline-danger btn-sm delete-user-btn" data-user-id="${data.id}" title="Delete user"><i class="fas fa-trash" ></i></button>
                                `;
                            } else {
                                return `
                                    <button class="btn btn-outline-info btn-sm me-2 edit-user-btn" data-user-id="${data.id}" data-certificate-id="${certificateId}" title="Edit User"><i class="fas fa-user-edit" ></i></button>
                                    <button class="btn btn-outline-success btn-sm me-2 download-certificate-btn" data-certificate-id="${certificateId}" title="Download Certificate"><i class="fas fa-download"></i></button>
                                    <button class="btn btn-outline-danger btn-sm delete-user-cert-btn" data-user-id="${data.id}" title="Delete user certificate"><i class="fas fa-user-times" ></i></button>
                                    <button class="btn btn-outline-danger btn-sm delete-user-btn" data-user-id="${data.id}" title="Delete user"><i class="fas fa-trash" ></i></button>
                                `;
                            }
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

            // Event listener for delete button
            $('#deactivated-users-table tbody').on('click', '.activate-account-btn', (event) => {
                const id = $(event.currentTarget).data('user-id');
                this.unlockUserAccount(id);
            });

            $('#deactivated-users-table tbody').on('click', '.edit-user-btn', (event) => { 
                        
                const userId = $(event.currentTarget).data('user-id');
                const certificateId = $(event.currentTarget).data('certificate-id') || null; // Ensure it's null if not found
                
                const user = this.deactivatedUsersTable
                    .data()
                    .toArray()
                    .find((u) => u.id === userId); 

                if (user) {

                    // Normalize roles
                    const roles = Array.isArray(user.roles)
                        ? user.roles.map((role) => (typeof role === "object" ? role.name : role))
                        : [];

                        // Populate `editUser`
                    this.editUser = {
                        id: user.id || null, // Ensure the user ID is included for updates
                        name: user.name || "",
                        email: user.email || "",
                        email_verify: user.email_verified_at || "",
                        password: "", // Reset password fields
                        password_confirmation: "",
                        is_admin: roles.includes("admin"),
                        authoriser_role: roles.includes("authoriser"),
                        bookkeeper_role: roles.includes("bookkeeper"),
                        latest_certificate: certificateId,
                        active: user.status,
                    };

                    this.openEditUserModal();
                }
            });
        },
        addUser() {
            this.modalInstance = new bootstrap.Modal(document.getElementById('newUserModal'));
            this.modalInstance.show();
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
            if (this.editModalInstance) {
                this.editModalInstance.hide();
            }

            if (this.editAccountModalInstance) {
                this.editAccountModalInstance.hide();
            }

            if (this.importModalInstance) {
                this.importModalInstance.hide();
            }

            if(this.viewBeneficiaryAccountModalInstance){
                this.viewBeneficiaryAccountModalInstance.hide();
            }

            if(this.viewFirmAccountModalInstance){
                this.viewFirmAccountModalInstance.hide();
            }
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

            const isAdmin = this.user.roles.some(role => role.name.toLowerCase().includes('admin'));

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
                    { data: 'company_name' },
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
                            
                            //const isAdmin = this.user.roles.some(role => role.name.toLowerCase().includes('admin'));
                            return `
                                 ${showCheckIcon ? `<button class='btn btn-outline-info btn-sm authorize-firmaccount-btn' data-toggle='tooltip' title='Authorise this firm Account' data-id='${data.id}'><i class='fas fa-check text-success'></i></button>` : ''}
                                 ${isAdmin ? `<button class="btn btn-outline-secondary btn-sm edit-firmaccount-btn" data-toggle="tooltip" title="Edit this firm Account" data-id="${row.id}"><i class="fas fa-edit"></i></button>` : ''}
                                  ${!showCheckIcon ? `<button class="btn btn-outline-info btn-sm view-firmaccount-btn" data-toggle="tooltip" title="View this firm Account" data-id="${row.id}"><i class="fas fa-search"></i></button>` : ''}
                                
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

             // Attach click event listener for edit button
            $('#source-accounts-table tbody').on('click', '.edit-firmaccount-btn', (event) => {
                const id = $(event.currentTarget).data('id');
                this.openEditModal('firm', id);
            });

            // Event listener for delete button
            $('#source-accounts-table tbody').on('click', '.view-firmaccount-btn', (event) => {
                const id = $(event.currentTarget).data('id');

                const firmaccountId = $(event.currentTarget).data('id');
                //this.confirmDelete(id);
                //alert('testing view beneficiary ');
                const firmaccount = this.sourceAccountsTable
                    .data()
                    .toArray()
                    .find((f) => f.id === firmaccountId);  console.log(firmaccount);

                    if (firmaccount) {
                        this.firmAccount = {
                            name: firmaccount.display_text || "N/A",
                            accountNumber: firmaccount.account_number || "N/A",
                            holderType: firmaccount.account_holder_type || "N/A",
                            holderName: firmaccount.company_name || firmaccount.surname || "N/A",
                            registrationNumber: firmaccount.registration_number || "N/A",
                            institution: firmaccount.institution?.name || "N/A",
                            branch: firmaccount.branch_name || "N/A",
                            accountDescription: firmaccount.category?.name || "N/A",
                            fileType: firmaccount.account_type?.name || "N/A",
                            method: firmaccount.my_reference || "N/A",
                            avsMatchedAt: firmaccount.avs_verified_at || "N/A",
                            authorizer: {
                                name: firmaccount.authorized_user?.name || "N/A",
                                email: firmaccount.authorized_user?.email || "N/A",
                            },
                            authorizedAt: firmaccount.authorized_at || "N/A",
                        };

                        this.viewBeneficiaryAccountModalInstance = new bootstrap.Modal(document.getElementById("firmAccountModal"));
                        this.viewBeneficiaryAccountModalInstance.show();
                    }
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

            const isAdmin = this.user.roles.some(role => role.name.toLowerCase().includes('admin'));

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
                            


                            //const isAdmin = this.user.roles.some(role => role.name.toLowerCase().includes('admin'));
                            return `
                                 ${showCheckIcon ? `<button class='btn btn-outline-info btn-sm authorize-beneficiary-btn' data-toggle='tooltip' title='Authorise this beneficiary Account' data-id='${data.id}'><i class='fas fa-check text-success'></i></button>` : ''}
                                 ${isAdmin ? `<button class="btn btn-outline-secondary btn-sm edit-beneficiary-btn" data-toggle="tooltip" title="Edit this beneficiary Account" data-id="${data.id}"><i class="fas fa-edit"></i></button>` : ''}
                                  ${!showCheckIcon ? `<button class="btn btn-outline-info btn-sm view-beneficiary-btn" data-toggle="tooltip" title="View this beneficiary Account" data-id="${data.id}"><i class="fas fa-search"></i></button>` : ''}
                                
                                <button class="btn btn-outline-danger btn-sm delete-beneficiary-btn" data-toggle="tooltip" title="Delete this beneficiary Account" data-id="${data.id}"><i class="fas fa-trash"></i></button>
                            `;
                        }
                    }
                ],
                createdRow: (row, data, dataIndex) => {
                    // Apply style to all <td> elements
                    //$('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');

                    $(row).find('.view-beneficiary-btn').on('click', (event) => { 
                        
                        const beneficiaryId = $(event.currentTarget).data('id');
                        //this.confirmDelete(id);
                        //alert('testing view beneficiary ');
                        const beneficiary = this.beneficiaryAccountsTable
                            .data()
                            .toArray()
                            .find((b) => b.id === beneficiaryId); console.log(beneficiaryId);

                            if (beneficiary) {
                                this.beneficiaryAccount = {
                                    name: beneficiary.display_text || "N/A",
                                    accountNumber: beneficiary.account_number || "N/A",
                                    holderType: beneficiary.account_holder_type || "N/A",
                                    holderName: beneficiary.company_name || beneficiary.surname || "N/A",
                                    registrationNumber: beneficiary.registration_number || "N/A",
                                    institution: beneficiary.institution?.name || "N/A",
                                    branch: beneficiary.branch_name || "N/A",
                                    accountDescription: beneficiary.category?.name || "N/A",
                                    fileType: beneficiary.account_type?.name || "N/A",
                                    method: beneficiary.my_reference || "N/A",
                                    avsMatchedAt: beneficiary.avs_verified_at || "N/A",
                                    authorizer: {
                                        name: beneficiary.authorized_user?.name || "N/A",
                                        email: beneficiary.authorized_user?.email || "N/A",
                                    },
                                    authorizedAt: beneficiary.authorized_at || "N/A",
                                };

                                this.viewBeneficiaryAccountModalInstance = new bootstrap.Modal(document.getElementById("beneficiaryAccountModal"));
                                this.viewBeneficiaryAccountModalInstance.show();
                            }
                    });

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
                //this.editBeneficiaryAccount(id);
                this.openEditModal('beneficiary', id);
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
        // Initialize the Beneficiary Accounts DataTable
       initializeOnceOffAccounts() {
            if ($.fn.dataTable.isDataTable('#onceoff-accounts-table')) {
                $('#onceoff-accounts-table').DataTable().destroy(); // Destroy existing instance if already initialized
            }

            const isAdmin = this.user.roles.some(role => role.name.toLowerCase().includes('admin'));

            this.onceoffAccountsTable = $('#onceoff-accounts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/onceoff-accounts',
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
                            


                            //const isAdmin = this.user.roles.some(role => role.name.toLowerCase().includes('admin'));
                            return `
                                 ${showCheckIcon ? `<button class='btn btn-outline-info btn-sm authorize-onceoff-btn' data-toggle='tooltip' title='Authorise this onceoff Account' data-id='${data.id}'><i class='fas fa-check text-success'></i></button>` : ''}
                                 ${isAdmin ? `<button class="btn btn-outline-secondary btn-sm edit-onceoff-btn" data-toggle="tooltip" title="Edit this onceoff Account" data-id="${data.id}"><i class="fas fa-edit"></i></button>` : ''}
                                  ${!showCheckIcon ? '<button class="btn btn-outline-info btn-sm view-onceoff-btn" data-toggle="tooltip" title="View this onceoff Account"><i class="fas fa-search"></i></button>' : ''}
                                
                                <button class="btn btn-outline-danger btn-sm delete-onceoff-btn" data-toggle="tooltip" title="Delete this onceoff Account" data-id="${data.id}"><i class="fas fa-trash"></i></button>
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

            // Event listener for edit button
            $('#onceoff-accounts-table tbody').on('click', '.edit-onceoff-btn', (event) => {
                
                const id = $(event.currentTarget).data('id');
                //this.editBeneficiaryAccount(id);
                this.openEditModal('onceoff', id);
            });

            // Event listener for delete button
            $('#onceoff-accounts-table tbody').on('click', '.delete-onceoff-btn', (event) => {
                const id = $(event.currentTarget).data('id');
                this.confirmDelete(id);
            });

            // Event listener for delete button
            $('#onceoff-accounts-table tbody').on('click', '.view-onceoff-btn', (event) => {
                //const id = $(event.currentTarget).data('id');
                //this.confirmDelete(id);
                //alert('testing view onceoff ');
                this.viewBeneficiaryAccountModalInstance = new bootstrap.Modal(document.getElementById('onceoffAccountModal'));
                this.viewBeneficiaryAccountModalInstance.show();
            });

             // Event listener for delete button
             $('#onceoff-accounts-table tbody').on('click', '.authorize-onceoff-btn', (event) => {
               
                const id = $(event.currentTarget).data('id');
                this.confirmOnceOffAuhtorize(id);
            });
        },
        // Method to load both accounts when Firm Accounts tab is clicked
        loadAccounts() {
            this.initializeSourceAccounts();
            this.initializeBeneficiaryAccounts();
            this.initializeOnceOffAccounts();
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

.nav-link{
    color: #0097b2bf !important;
}

.nav-link.active{
    color: #333 !important;
}


tbody tr td {
    text-wrap: wrap !important;
}

div.dt-processing>div:last-child {
    color: #0097b2bf !important;
}
</style>
