<template>
    <div class="container mt-4">
        <!-- Requisition Header -->
         
        <h4 class="section-title mb-2">
            Requisition: <span style="color:#999;font-weight: normal;font-size: 20px;">{{ requisition.file_reference }} - {{ requisition.reason }}</span>
            <span class="pull-right">
                <router-link :to="{ name: 'emailsignatory', params: { requisitionId: requisitionId } }" class="btn btn-white btn-default-default btn-sm ml-1">Email Signatory</router-link>
                <router-link to="/requisition/new" class="btn btn-white btn-default-default btn-sm ml-1">File History</router-link>
                <button class="btn btn-light btn-default-default btn-sm ml-1" @click="printPage"><i class="fas fa-print"></i> Print</button>
                <!-- <router-link to="/requisition/new" class="btn btn-primary btn-sm ml-1"><i class="fas fa-print"></i> Print</router-link> -->
            </span>
        </h4>

        <!-- Status Sections -->
        <div class="row mb-4 requisition-status">
            
            <div class="col-md-3 box" :class="statusClass">
                <div class="status-card row pt-2">
                    <div class="col-md-9">
                        <h6 class="fw-bold">Capturing</h6>
                        <p class="status-value mb-0 mt-3">
                            <span v-if="requisition.status_id === 1">
                                Transaction value: {{ requisition.transaction_value }}
                            </span>
                            <span v-else-if="requisition.status_id === 2">

                                <span class="" v-if="selectedSourceAccount && selectedSourceAccount.deposits && selectedSourceAccount.deposits.length > 0">
                                    <i class="fa fa-info-circle text-warning"></i> Not balanced
                                </span>
                                <span class="" v-else-if="selectedSourceAccount && selectedSourceAccount.payments && selectedSourceAccount.payments.length > 0">
                                    <i class="fa fa-info-circle text-warning"></i> Not balanced <br/>
                                    <div class="btn btn-white btn-default-default btn-sm mt-0 mb-1" data-toggle="tooltip" data-placement="bottom" title="Balance the matter by adding a default source / deposit entry" @click="balancePaymentFund"><i class="fas fa-balance-scale"></i> Balance</div>
                                </span>
                                <span v-else>No entries captured</span>
                            </span>
                            <span v-else-if="requisition.status_id === 3">
                                Transaction value: R{{ totalDepositAmount }}  
                            </span>
                            <span v-else-if="requisition.status_id === 5">
                                Transaction value: R{{ totalPaymentAmount }}
                            </span>
                        </p>
                    </div>
                    <!-- <div class="col-md-3" v-if="requisition.status_id !== 1">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div> -->
                    <div class="col-md-3" v-if="requisition.status_id == 2">
                        <span class="badge bg-info" v-if="selectedSourceAccount && selectedSourceAccount.deposits && selectedSourceAccount.deposits.length > 0">
                            {{selectedSourceAccount.deposits.length}} / {{selectedSourceAccount.deposits.length}}
                        </span>
                        <span class="badge bg-info" v-else-if="selectedSourceAccount && selectedSourceAccount.payments && selectedSourceAccount.payments.length > 0">
                            {{selectedSourceAccount.payments.length}} / {{selectedSourceAccount.payments.length}}
                        </span>
                        <span class="badge bg-info" v-else>0 / 0</span>
                    </div>
                    <div class="col-md-3" v-if="requisition.status_id >= 3">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div>
                    
                </div>
            </div>
            <div class="col-md-3 incomplete box" :class="{'current': requisition.status_id == 3, 'complete': requisition.status_id == 5}">
                <div class="status-card row pt-2">
                    <div class="col-md-9">
                        <h6 class="fw-bold">Authorization</h6>
                        <div v-if="requisition.status_id == 3">
                            <p class="status-value mb-0 mt-3">&nbsp;</p>
                            <PermissionControl :roles="['admin', 'authoriser']">
                                <div class="mb-2"><a class="btn btn-sm btn-white btn-default-default" @click="approveRequisition(requisition.id)">Approve</a></div>
                            </PermissionControl>
                            <PermissionControl :roles="['user']">
                                <div class="mb-2"><a class="btn btn-sm btn-white btn-default-default disabled">Approve</a></div>
                            </PermissionControl>
                            
                            
                        </div>
                        <div v-else-if="requisition.status_id == 5" class="txt-xs mb-0 mt-3">
                            Last signed on: {{ formatDate(requisition.authorized_at) }}
                        </div>
                        <p v-else class="status-value mb-0 mt-3">{{ authorizationStatus }}</p>
                    </div>
                    <div class="col-md-3" v-if="requisition.status_id == 3">
                        <span class="badge bg-info" v-if="selectedSourceAccount && selectedSourceAccount.payments && selectedSourceAccount.payments.length > 0">
                            0 / {{selectedSourceAccount.payments.length}}
                        </span>
                        <span class="badge bg-info" v-else>0 / 0</span>                                
                    </div>
                    <div class="col-md-3" v-if="requisition.status_id == 5">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div>
                </div>
                
            </div>
            <div class="col-md-3 box" :class="{'complete': requisition.funding_status, 'incomplete': !requisition.funding_status}">
                <div class="status-card row pt-2">
                    <div class="col-md-9">
                        <h6 class="fw-bold">Funding</h6>
                        <p class="status-value mb-0 mt-3" v-if="selectedSourceAccount">
                            <span v-if="selectedSourceAccount.deposits && selectedSourceAccount.deposits.length > 0">
                                {{ requisition.funding_status ? 'Completed on '+formatDate(selectedSourceAccount.deposits[selectedSourceAccount.deposits.length - 1].created_at) : fundingStatus }}
                            </span>
                            
                        </p>
                        <p class="status-value mb-0 mt-3" v-else-if="requisition.funding_status">
                            {{ fundingStatus }}
                        </p>
                    </div>
                    <div class="col-md-3" v-if="requisition.funding_status">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3 box" :class="{'incomplete': requisition.status_id !== 4, 'almostcomplete': requisition.status_id == 5}">
                <div class="status-card row pt-2">
                    <div class="col-md-9">
                        <h6 class="fw-bold">Settlement</h6>
                        <p class="status-value mt-3" v-if="requisition.status_id < 5">{{ settlementStatus }}</p>
                        <p class="status-value mt-3" v-else-if="requisition.status_id == 5">Pending payment</p>
                    </div>
                    <div class="col-md-3" v-if="requisition.status_id == 5">
                        <i class="fa fa-hourglass-half text-primary" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="requisition.status_id == 3"><a class="pull-right btn btn-white btn-default-default btn-sm"><i class="fa fa-lock"></i> Lock</a></div>
        <div v-else-if="requisition.status_id == 5" style="display:flow-root;background-color: #eee;padding:3px;border-radius: 5px;border-left: solid 5px orange;padding-left: 10px;">
            <span><i class="fa fa-lock"></i><b> This matter is locked.</b> It is pending payment</span>
            <a class="pull-right btn btn-white btn-primary btn-sm"><i class="fa fa-lock-open"></i> Unlock</a>
        </div>
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-0" id="requisitionTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="details-tab" data-bs-toggle="tab" href="#details" role="tab" aria-controls="details" aria-selected="true">Details</a>
            </li>
            <li class="nav-item" role="presentation">
                <!-- <a class="nav-link" :class="{'active': requisition.status_id === 1}" id="payments-tab" data-bs-toggle="tab" href="#payments" role="tab" aria-controls="payments" aria-selected="false" @click="showPaymentsTabContent">Payments</a> -->
                <a class="nav-link active" id="payments-tab" data-bs-toggle="tab" href="#payments" role="tab" aria-controls="payments" aria-selected="false" @click="showPaymentsTabContent">Payments</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="documents-tab" data-bs-toggle="tab" href="#documents" role="tab" aria-controls="documents" aria-selected="false" @click="showDocumentTabContent">Documents</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="notifications-tab" data-bs-toggle="tab" href="#notifications" role="tab" aria-controls="notifications" aria-selected="false">Notifications</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="history-log-tab" data-bs-toggle="tab" href="#history-log" role="tab" aria-controls="history-log" aria-selected="false" @click="loadHistoryLogs">History Log</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="requisitionTabContent">
            <!-- Details Tab -->
            <!-- <div class="tab-pane fade" :class="{'show active': requisition.status_id !== 1}" id="details" role="tabpanel" aria-labelledby="details-tab"> -->
            <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6>Details - {{ requisition.file_reference }}</h6>
                    </div>
                    <div class="card-body">
                        <div v-html="contentHtml"></div>
                        <form>
                            <!-- File Reference -->
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="file-reference">File Reference:</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="requisition.file_reference" class="form-control" id="file-reference">
                                </div>
                            </div>
                            <!-- Reason -->
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="reason">Reason:</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="requisition.reason" class="form-control" id="reason">
                                </div>
                            </div>
                            <!-- Parties -->
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="parties">Parties:</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="requisition.parties" class="form-control" id="parties">
                                </div>
                            </div>
                            <!-- Properties -->
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="properties">Properties:</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="requisition.properties" class="form-control" id="properties">
                                </div>
                            </div>
                            <!-- Created By (Safely handle if user is undefined) -->
                            <div class="row mb-3" v-if="requisition.user">
                                <label class="col-sm-2 col-form-label" for="created-by">Created By:</label>
                                <div class="col-sm-10">
                                    <div class="form-control" style="border: 0px;">{{ requisition.user.name }}</div>
                                </div>
                            </div>
                        </form>
                        <div class="row mb-3">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-10">
                                <button class="btn btn-primary" @click="saveRequisition">Save</button>
                                <button class="btn btn-danger ml-1" @click="deleteRequisition">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Tab -->
           <!--  <div class="tab-pane fade" :class="{'show active': requisition.status_id === 1}" id="payments" role="tabpanel" aria-labelledby="payments-tab"> -->
            <div class="tab-pane fade show active" id="payments" role="tabpanel" aria-labelledby="payments-tab">
                <!-- Source Account DataTable or Details Section -->
                <div v-if="!showSourceAccountDetails">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6>Choose a Source Account</h6>
                        </div>
                        <div class="card-body">
                            <div v-html="contentHtml"></div>
                            <!-- Source Accounts DataTable -->
                            <div class="table-responsive">
                                <table id="source-accounts-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                                    <thead>
                                        <tr class="table-secondary">
                                            <th>Account Display Name</th>
                                            <th width="15%">Institution Name</th>
                                            <th width="15%">Account Number</th>
                                            <th width="10%">Branch Code</th>
                                            <th width="20%">Account Holder</th>
                                            <th width="10%">Authorisations</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <!-- Search Filter -->
                            <!-- <div class="mt-3">
                                <label for="sourceAccountFilter" class="form-label">Filter:</label>
                                <input type="text" class="form-control w-25 d-inline" id="sourceAccountFilter" placeholder="Search..." v-on:input="filterSourceAccounts">
                            </div> -->
                        </div>
                    </div>
                </div>

                <!-- Source Account Details Section -->
                <div v-else>
                    
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between">
                           
                            <h6>Source Account Details
                            </h6>
                            <div style="background: rgb(218, 249, 255);color: #333;padding-left: 10px;padding-right: 10px;border-radius: 3px;">
                                <span>
                                    <strong>{{ selectedSourceAccount.display }} - {{ selectedSourceAccount.account_number }}</strong>
                                    <span class="ml-2"><i>Bank:  {{ selectedSourceAccount.institution.name}} - ({{ selectedSourceAccount.branch_code }})</i></span>
                                    <span class="ml-2" id="editChoosedSourceAccount" @click="openSourceAccountModal"><i class="fas fa-edit"></i></span>
                                </span>
                            </div>
                            <div class="pull-right">
                                <button class="btn btn-white btn-sm" @click="openNewDepositModal"><i class="fa fa-plus" aria-hidden="true"></i> New Deposit</button>
                                <button class="btn btn-white btn-sm ml-1" @click="openCreatePaymentModal"><i class="fa fa-plus" aria-hidden="true"></i> New Payment</button>
                            </div>
                            
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div>Deposits</div>

                                    <!-- Loop through deposits if they exist -->
                                        <div v-if="selectedSourceAccount && selectedSourceAccount.deposits && selectedSourceAccount.deposits.length > 0" class="deposit-section row ml-0 mt-1">
                                            <div v-for="deposit in selectedSourceAccount.deposits" :key="deposit.id" class="col-md-12 row mb-2 lighthover">
                                                <div class="col-md-3">
                                                    <span>
                                                        <i class="fa fa-check mr-2 text-success"></i></span>{{ deposit.description }}
                                                </div>
                                                <div class="col-md-6">
                                                    <!-- Display the user's name who made the deposit -->
                                                    <div class="bg-light p-1 rounded">Marked as received by {{ deposit.user ? deposit.user.name : 'Unknown User' }} on {{ formatDate(deposit.created_at) }}</div>
                                                </div>
                                                <div class="col-md-3 pl-4">
                                                    R{{ parseFloat(deposit.amount).toFixed(2) }}

                                                    <span class="pull-right"><i class="fa fa-edit text-primary" @click="openEditDepositModal(deposit)"></i></span>
                                                </div>
                                                
                                            </div>
                                        </div>

                                        <!-- Display a message if no deposits are found -->
                                        <div v-else class="txt-xs">No deposits have been added</div>
                               
                                    <div class="mt-3 mb-1">Payments</div>
                                    <div class="payment-section row mb-0">
                                        <div class="col-md-3 txt-xs">
                                            Description
                                        </div>
                                        <div class="col-md-3 txt-xs">
                                            Account Details
                                        </div>
                                        <div class="col-md-6 txt-xs">
                                            References
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <!-- Loop through deposits if they exist -->
                                    <div v-if="selectedSourceAccount && selectedSourceAccount.payments && selectedSourceAccount.payments.length > 0" class="deposit-section row ml-0 mt-1 mb-0">
                                        <div v-for="payment in selectedSourceAccount.payments" :key="payment.id" class="col-md-12 row mb-2 lighthover">
                                            <div class="col-md-3">
                                                <span v-if="!payment.verified">
                                                    <i class="fa fa-square mr-2 mt-1 text-success" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="Status" 
                                                    data-bs-content="Account details complete, No AVS verification done"></i>
                                                </span>
                                                {{ payment.description }}
                                                
                                            </div>
                                            <div class="col-md-3">
                                               <div> <b>{{ (payment.account_holder_type  === 'natural') ?  payment.initials +" "+  payment.surname : payment.company_name }}</b></div>
                                               <div> {{ payment.institution ?  payment.institution.short_name : null }} ({{ payment.branch_code }}) - {{ payment.account_number }}</div>
                                            </div>
                                            <div class="col-md-4">
                                                <div><span class="text-secondary">My Ref:</span> {{ payment.my_reference }}</div>
                                                <div><span class="text-secondary">Recipient Ref:</span> {{ payment.recipient_reference }}</div>
                                                
                                            </div>
                                            <div class="col-md-2 pl-4" style="display:flex;justify-content:flex-end;">
                                                -  R{{ parseFloat(payment.amount).toFixed(2) }}

                                                <span class="pull-right">  &nbsp;&nbsp;<i class="fa fa-edit text-primary" @click="openEditPaymentModal(payment)"></i></span>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div v-else class="txt-xs">No Payments have been added</div>
                                
                                    <div class="row ml-0">
                                        <div class="col-md-3">
                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div><br/><br/></div>
                                            <div class="pull-right" v-if="!selectedSourceAccount.deposits  && !selectedSourceAccount.deposits.length > 0">Net Balance: </div>
                                        </div>
                                        <div class="col-md-3 row">

                                            <div v-if="selectedSourceAccount.deposits && selectedSourceAccount.deposits.length > 0 && !selectedSourceAccount.payments">
                                                <hr class="mb-1"/>
                                                <div style="display:flex;justify-content:flex-end;">

                                                       &nbsp; R{{ totalDepositAmount }}                                                    
                                                    
                                                </div>
                                                <hr class="mb-0 mt-1"/>
                                            </div>
                                           
                                            <div v-if="selectedSourceAccount.payments && selectedSourceAccount.payments.length > 0">
                                                <hr class="mb-1"/>
                                                <div 
                                                     :style="{ display: 'flex', flexDirection: 'row', justifyContent: selectedSourceAccount.deposits && selectedSourceAccount.deposits.length > 0 ? 'space-between' : 'flex-end' }"
                                                    class="mr-4"
                                                >

                                                    <div  v-if="selectedSourceAccount.deposits  && selectedSourceAccount.deposits.length > 0">R{{ totalDepositAmount }} </div>   <div>-  R{{ totalPaymentAmount }}</div>
                                                    
                                                </div>
                                                <hr class="mb-0 mt-1"/>
                                                <span class="pull-right mr-4" v-if="selectedSourceAccount.deposits  && selectedSourceAccount.deposits.length > 0">R {{ netBalance }}</span>
                                                <div v-if="requisition.status_id < 3" class="btn btn-white btn-default-default btn-sm mt-1" data-toggle="tooltip" data-placement="bottom" title="Balance the matter by adding a default source / deposit entry" @click="balancePaymentFund"><i class="fas fa-balance-scale"></i> Balance and Fund</div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            
                            
                            
                           
                            
                            <!-- <button class="btn btn-secondary" @click="showSourceAccountDetails = false">Back to Accounts</button> -->
                        </div>
                        
                    </div>
                    <div class="card mb-3">
                        <div class="card-header">
                           
                            <h6>Authorisations</h6>
                           
                            
                        </div>
                        <div class="card-body">
                            <div v-if="requisition.status_id == 3">
                                <PermissionControl :roles="['admin', 'authoriser']">
                                    <div class="approve-box p-2 pull-right" style="border: solid 1px #40b1c5;background: #eafcff;">
                                        <div class="pl-0 pr-0">
                                            <div class="txt-xs">Click here to approve as </div>
                                            <h6>{{ user.name }}</h6>
                                        </div>
                                        <div class=""><a class="btn btn-sm btn-primary" @click="approveRequisition(requisition.id)">Approve</a></div>
                                    </div>
                                </PermissionControl>
                                <PermissionControl :roles="['user']">
                                    <div class="approve-box p-2 pull-right">
                                        <div class="txt-xs">Approval required by Authoriser</div>
                                        <div><a class="btn btn-sm btn-white btn-default-default disabled">Approve</a></div>
                                    </div>
                                </PermissionControl>
                                
                            </div>
                            <div v-else class="txt-xs">Not ready, capture incomplete.</div>
                        </div>
                        
                    </div>

                    <div class="card mt-0" style="display: flex; flex-direction: row;">
                        <div class="card-body"> 
                            <router-link to="/requisition/new" class="btn btn-primary btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> New Requisition</router-link>
                        </div>
                        <div class="pr-3">
                            
                            <div class="input-group mb-3 mt-2">
                                
                                <input type="text" class="form-control ml-2" placeholder="File reference" aria-label="File reference">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button">Go</button>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </div>

            <!-- Documents Tab -->
            <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between">
                        <h6>Documents</h6>
                        <button class="btn btn-white btn-sm" @click="openUploadModal"><i class="fa fa-plus" aria-hidden="true"></i> New Document</button>
                    </div>
                    <div class="card-body">
                        <!-- Documents DataTable -->
                        <div class="table-responsive">
                            <table id="documents-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                                <thead>
                                    <tr class="table-secondary">
                                        <th>User</th>
                                        <th>Description</th>
                                        <th>File Name</th>
                                        <th>Date Uploaded</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                <h5>Notifications Content</h5>
                <!-- Notifications content goes here -->
            </div>

            <!-- History Log Tab -->
            <div class="tab-pane fade" id="history-log" role="tabpanel" aria-labelledby="history-log-tab">
                <div class="card">
                    <div class="card-body">
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
                                <tr v-for="log in historyLogs" :key="log.id">
                                    <td>{{ log.user_name }}</td>
                                    <td>{{ log.action }}</td>
                                    <td>{{ log.details }}</td>
                                    <td>{{ new Date(log.created_at).toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- Ending of the Tab-->

    </div>
    <!-- Source Account Modal -->
    <div class="modal fade" id="sourceAccountModal" tabindex="-1" aria-labelledby="sourceAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header card-header">
                        <h5 class="modal-title" id="sourceAccountModalLabel">Choose a Source Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- List of Source Accounts -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <!-- Source Accounts DataTable -->
                                <div class="table-responsive">
                                    <table id="source-accounts-table2" class="table table-bordered table-striped display nowrap" style="width:100%">
                                        <thead>
                                            <tr class="table-secondary">
                                                <th>Account Display Name</th>
                                                <th width="15%">Institution Name</th>
                                                <th width="15%">Account Number</th>
                                                <th width="10%">Branch Code</th>
                                                <th width="20%">Account Holder</th>
                                                <th width="10%">Aggregated</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <!-- Search Filter -->
                               <!--  <div class="mt-3">
                                    <label for="sourceAccountFilter" class="form-label">Filter:</label>
                                    <input type="text" class="form-control w-25 d-inline" id="sourceAccountFilter2" placeholder="Search..." v-on:input="filterSourceAccounts">
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Upload Document Modal -->
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        The maximum file size is 10 MB. Only PDFs and images may be uploaded.
                    </div>
                    <form @submit.prevent="uploadDocument">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description: *</label>
                            <input type="text" v-model="documentForm.description" class="form-control" id="description" required>
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">Select Document: *</label>
                            <input type="file" @change="handleFileUpload" class="form-control" id="file" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- New Deposit Modal -->
    <div class="modal fade" id="newDepositModal" tabindex="-1" aria-labelledby="newDepositModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newDepositModalLabel">Create Deposit Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="createDeposit">
                        <div class="mb-3 row">
                            <label for="description" class="form-label col-sm-3">Description: *</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="depositForm.description" class="form-control" id="description" required placeholder="Enter a description for the entry">
                                <div v-if="errors.description" class="text-danger">{{ errors.description }}</div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="amount" class="form-label col-sm-3">Amount (R): *</label>
                            <div class="col-sm-9">
                                <input type="number" v-model="depositForm.amount" class="form-control" id="amount" step="0.01" min="0" required placeholder="Enter the amount available for the transaction">
                                <div v-if="errors.amount" class="text-danger">{{ errors.amount }}</div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-sm-3"></div>
                            <div class="form-check col-sm-9">
                                <input class="form-check-input" type="checkbox" v-model="depositForm.funded" id="funded">
                                <label class="form-check-label" for="funded">Money is available in the account.</label>
                            </div>
                            <div v-if="!depositForm.funded" class="row">
                                <label for="deposit_date" class="form-label col-sm-3">Deposit Date:</label>
                                <div class="col-sm-9">
                                    <input type="date" v-model="depositForm.deposit_date" class="form-control" id="deposit_date">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer mb-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-primary" @click="createAndNewDeposit">Save & New</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposit Edit Modal -->
    <div class="modal fade" id="editDepositModal" tabindex="-1" aria-labelledby="editDepositModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDepositModalLabel">Edit Deposit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3 row">
                            <label for="description" class="form-label col-sm-3">Description:</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="editDepositForm.description" class="form-control" id="update_description" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="amount" class="form-label col-sm-3">Amount (R):</label>
                            <div class="col-sm-9">
                                <input type="number" v-model="editDepositForm.amount" class="form-control" id="update_amount" required step="0.01" min="0">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col-sm-3"></div>
                            <div class="form-check col-sm-9">
                                <input class="form-check-input" type="checkbox" v-model="editDepositForm.funded" id="update_funded">
                                <label class="form-check-label" for="funded">Money is available in the account.</label>
                            </div>
                            <div v-if="!editDepositForm.funded" class="row">
                                <label for="deposit_date" class="form-label col-sm-3">Deposit Date:</label>
                                <div class="col-sm-9">
                                    <input type="date" v-model="editDepositForm.deposit_date" class="form-control" id="update_deposit_date">
                                </div>
                            </div>
                        </div>
                        <div class="mb-0 row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-danger btn-sm" @click="confirmDepositDelete">Delete</button>
                            </div>
                            <div class="form-check col-sm-9">
                                <button type="button" class="btn btn-primary pull-right ml-1 btn-sm" @click="updateDeposit">Update</button>
                                <button type="button" class="btn btn-secondary pull-right btn-sm" data-bs-dismiss="modal" @click="closeModal">Cancel</button>
                                
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Payment Modal -->
    <div class="modal fade" id="createPaymentModal" tabindex="-1" aria-labelledby="createPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPaymentModalLabel">Create Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="createPayment">
                        <!-- Search -->
                        <div class="row mb-3 position-relative">    
                            <div class="input-group">
                                <input 
                                    class="form-control border-end-0 border" 
                                    type="search" 
                                    placeholder="Search for an account number or account name" 
                                    v-model="paymentForm.search"
                                    @input="fetchMatchingAccounts"
                                    >
                                <span class="input-group-append">
                                    <button class="btn btn-outline-secondary bg-white border-start-0 border-bottom-1 border ms-n5" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                            <!-- Autocomplete Dropdown -->
                            <ul v-if="showSuggestions || filteredAccounts.length" class="autocomplete-dropdown">
                                <li 
                                    v-for="(account, index) in filteredAccounts" 
                                    :key="index" 
                                    :value="account.id"
                                    @click="selectAccount(account)"
                                    class="dropdown-item"
                                >
                                <span><i class="fa fa-university" aria-hidden="true"></i>&nbsp;</span>
                                <!-- Render display_text with highlighted search term -->
                                <span v-html="highlightMatch(account.display_text, paymentForm.search)"></span> |

                                <!-- Render institution.short_name with highlighted search term -->
                                <span v-html="highlightMatch(account.institution.short_name, paymentForm.search)"></span> -

                                <!-- Render account_number with highlighted search term -->
                                <span v-html="highlightMatch(account.account_number, paymentForm.search)"></span>
                                </li>
                                <li class="dropdown-item" @click="setNewAccountType('juristic')"><span style="color:#0097b2bf;"><i class="fa fa-plus"></i> New Juristic Person </span> | {{ newAccountLabel }}: <strong>{{paymentForm.search}}</strong></li>
                                <li class="dropdown-item" @click="setNewAccountType('natural')"><span style="color:#0097b2bf;"><i class="fa fa-plus"></i> New Natural Person </span> | {{ newAccountLabel }}: <strong>{{paymentForm.search}}</strong></li>
                            </ul>
                        </div>
                       <!--  <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Search for an account number or account name" v-model="paymentForm.search">
                        </div> -->
                        
                        <!-- Category -->
                        <div class="mb-3 row">
                            <label for="category" class="form-label col-sm-3">Category: *</label>
                            <div class="col-sm-9">
                                <select v-model="paymentForm.category" class="form-select" required @change="onCategoryChange">
                                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Account -->
                        <div class="mb-3 row">
                            <label for="account" class="form-label col-sm-3">Account:</label>
                            <div class="col-sm-9">
                                <select v-model="paymentForm.account_holder_type" class="form-select" @change="onAccountChange">
                                    <optgroup label="New Account">
                                        <option value="natural">Natural Person</option>
                                        <option value="juristic">Juristic Person</option>
                                    </optgroup>
                                    <optgroup label="Existing Beneficiary">
                                        <option v-for="beneficiary in categoryBeneficiaries" :key="beneficiary.id" :value="beneficiary.id">{{ beneficiary.display_text }}</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Hidden section for New Account details (visible when a new account is selected) -->
                        <div v-if="showAccountDetails">
                            <h6 class="mb-1">Account Details (Ad-hoc)</h6>
                            <hr class="mt-0"/>
                            <div class="mb-3 row">
                                <label for="account_number" class="form-label col-sm-3">Account No.: *</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="paymentForm.account_number" class="form-control" required placeholder="Enter the account number">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="account_holder" class="form-label col-sm-3">Account Holder: *</label>
                                <div class="col-sm-9 row pr-0" v-if="this.paymentForm.account_holder_type == 'natural'">
                                    <div class="col-sm-2 pr-0" id="initials">
                                        <input type="text" v-model="paymentForm.initials" class="form-control" placeholder="Initials" required>
                                    </div>
                                    <div class="col-sm-10 pr-0">
                                        <input type="text" v-model="paymentForm.surname" class="form-control" placeholder="Surname" required>
                                    </div>
                                </div>
                                <div class="col-sm-9" v-if="this.paymentForm.account_holder_type == 'juristic'">
                                    <input type="text" v-model="paymentForm.company_name" class="form-control" placeholder="Enter the name of the Company">
                                </div>
                                
                            </div>
                            
                            <div class="mb-3 row" v-if="this.paymentForm.account_holder_type == 'natural'">
                                <label for="id_number" class="form-label col-sm-3">ID No. / Passport No.:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="paymentForm.id_number" class="form-control" placeholder="Enter ID Number or leave blank if not known">
                                </div>
                            </div>
                            <div class="mb-3 row" v-if="this.paymentForm.account_holder_type == 'juristic'">
                                <label for="id_number" class="form-label col-sm-3">Registration No.:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="paymentForm.registration_number" class="form-control" placeholder="Enter the registration number or leave blank if not applicable">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="account_type" class="form-label col-sm-3">Account Type: *</label>
                                <div class="col-sm-9">
                                    <select v-model="paymentForm.account_type.id" class="form-select" required>
                                        <option v-for="account_type in accountTypes" :key="account_type.id" :value="account_type.id">{{ account_type.name }}</option>
                                    </select>
                                    <!-- <input type="text" v-model="paymentForm.account_type" class="form-control" required> -->
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="institution" class="form-label col-sm-3">Institution: *</label>
                                <div class="col-sm-9">
                                    <select v-model="paymentForm.institution.id" class="form-select" required @change="onInstitutionChange">
                                        <option v-for="institution in institutions" :key="institution.id" :value="institution.id">{{ institution.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="branch_code" class="form-label col-sm-3">Branch Code: *</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="paymentForm.branch_code" class="form-control" required placeholder="Enter the branch code">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="branch_code" class="form-label col-sm-3">verification:</label>
                                <div class="col-sm-9">
                                    <div class="pl-2" style="background-color:#eee;border-radius: 5px;">
                                        <div class="form-check pt-2 pb-2">
                                            <input class="form-check-input" type="checkbox" id="gridCheck" v-model="paymentForm.verified">
                                            <label class="form-check-label" for="gridCheck">
                                                Verify account holder and account details
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                               
                            </div>
                        </div>
                        
                        <!-- Transaction Information -->
                        <h6 class="mb-1">Transaction Information</h6>
                        <hr class="mt-0"/>
                        <div class="mb-3 row">
                            <label for="description" class="form-label col-sm-3">Description: *</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="paymentForm.description" class="form-control" required placeholder="Enter a description for the entry">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="amount" class="form-label col-sm-3">Amount: *</label>
                            <div class="col-sm-9">
                                <input type="number" v-model="paymentForm.amount" class="form-control" required placeholder="Enter the amount available for the transaction">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="my_reference" class="form-label col-sm-3">My Reference: *</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="paymentForm.my_reference" class="form-control" placeholder="Enter a reference for our bank account">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="recipient_reference" class="form-label col-sm-3">Recipient Reference:</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="paymentForm.recipient_reference" class="form-control" placeholder="Enter a reference for the recipient's bank account">
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-primary" @click="createAndNewPayment">Save & New</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

     <!-- Create Payment Modal -->
     <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPaymentModalLabel">Edit Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="editPayment">
                                               
                        <!-- Category -->
                        <div class="mb-3 row">
                            <label for="category" class="form-label col-sm-3">Category: *</label>
                            <div class="col-sm-9">
                                <select v-model="editPaymentForm.category" class="form-select" required @change="onCategoryChange">
                                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Account -->
                        <div class="mb-3 row">
                            <label for="account" class="form-label col-sm-3">Account:</label>
                            <div class="col-sm-9">
                                <select v-model="editPaymentForm.account_holder_type" class="form-select" @change="onAccountChange">
                                    <optgroup label="New Account">
                                        <option value="natural">Natural Person</option>
                                        <option value="juristic">Juristic Person</option>
                                    </optgroup>
                                    <optgroup label="Existing Beneficiary">
                                        <option v-for="beneficiary in categoryBeneficiaries" :key="beneficiary.id" :value="beneficiary.id">{{ beneficiary.display_text }}</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Hidden section for New Account details (visible when a new account is selected) -->
                        <div v-if="showEditAccountDetails">
                            <h6 class="mb-1">Account Details (Ad-hoc)</h6>
                            <hr class="mt-0"/>
                            <div class="mb-3 row">
                                <label for="account_number" class="form-label col-sm-3">Account No.: *</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="editPaymentForm.account_number" class="form-control" required placeholder="Enter the account number">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="account_holder" class="form-label col-sm-3">Account Holder: *</label>
                                <div class="col-sm-9 row pr-0" v-if="this.editPaymentForm.account_holder_type == 'natural'">
                                    <div class="col-sm-2 pr-0" id="initials">
                                        <input type="text" v-model="editPaymentForm.initials" class="form-control" placeholder="Initials" required>
                                    </div>
                                    <div class="col-sm-10 pr-0">
                                        <input type="text" v-model="editPaymentForm.surname" class="form-control" placeholder="Surname" required>
                                    </div>
                                </div>
                                <div class="col-sm-9" v-if="this.editPaymentForm.account_holder_type == 'juristic'">
                                    <input type="text" v-model="editPaymentForm.company_name" class="form-control" placeholder="Enter the name of the Company">
                                </div>
                                
                            </div>
                            
                            <div class="mb-3 row" v-if="this.editPaymentForm.account_holder_type == 'natural'">
                                <label for="id_number" class="form-label col-sm-3">ID No. / Passport No.:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="editPaymentForm.id_number" class="form-control" placeholder="Enter ID Number or leave blank if not known">
                                </div>
                            </div>
                            <div class="mb-3 row" v-if="this.editPaymentForm.account_holder_type == 'juristic'">
                                <label for="id_number" class="form-label col-sm-3">Registration No.:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="editPaymentForm.registration_number" class="form-control" placeholder="Enter the registration number or leave blank if not applicable">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="account_type" class="form-label col-sm-3">Account Type: *</label>
                                <div class="col-sm-9">
                                    <select v-model="editPaymentForm.account_type.id" class="form-select" required>
                                        <option v-for="account_type in accountTypes" :key="account_type.id" :value="account_type.id">{{ account_type.name }}</option>
                                    </select>
                                    <!-- <input type="text" v-model="editPaymentForm.account_type" class="form-control" required> -->
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="institution" class="form-label col-sm-3">Institution: *</label>
                                <div class="col-sm-9">
                                    <select v-model="editPaymentForm.institution.id" class="form-select" required @change="onInstitutionChange">
                                        <option v-for="institution in institutions" :key="institution.id" :value="institution.id">{{ institution.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="branch_code" class="form-label col-sm-3">Branch Code: *</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="editPaymentForm.branch_code" class="form-control" required placeholder="Enter the branch code">
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="branch_code" class="form-label col-sm-3">verification:</label>
                                <div class="col-sm-9">
                                    <div class="pl-2" style="background-color:#eee;border-radius: 5px;">
                                        <div class="form-check pt-2 pb-2">
                                            <input class="form-check-input" type="checkbox" id="gridCheck" v-model="editPaymentForm.verified">
                                            <label class="form-check-label" for="gridCheck">
                                                Verify account holder and account details
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                               
                            </div>
                        </div>
                        
                        <!-- Transaction Information -->
                        <h6 class="mb-1">Transaction Information</h6>
                        <hr class="mt-0"/>
                        <div class="mb-3 row">
                            <label for="description" class="form-label col-sm-3">Description: *</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="editPaymentForm.description" class="form-control" required placeholder="Enter a description for the entry">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="amount" class="form-label col-sm-3">Amount: *</label>
                            <div class="col-sm-9">
                                <input type="number" v-model="editPaymentForm.amount" class="form-control" required placeholder="Enter the amount available for the transaction">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="my_reference" class="form-label col-sm-3">My Reference: *</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="editPaymentForm.my_reference" class="form-control" placeholder="Enter a reference for our bank account">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="recipient_reference" class="form-label col-sm-3">Recipient Reference:</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="editPaymentForm.recipient_reference" class="form-control" placeholder="Enter a reference for the recipient's bank account">
                            </div>
                        </div>
                        
                        <div class="mb-0 row">
                            <div class="col-sm-3">
                                <button type="button" class="btn btn-danger btn-sm" @click="confirmPaymentDelete">Delete</button>
                            </div>
                            <div class="form-check col-sm-9">
                                <button type="button" class="btn btn-primary pull-right ml-1 btn-sm" @click="updatePayment">Update</button>
                                <button type="button" class="btn btn-secondary pull-right btn-sm" data-bs-dismiss="modal" @click="closeModal">Cancel</button>
                                
                            </div>
                        </div>
                    </form>
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
import moment from 'moment';
import PermissionControl from '../permission/PermissionControl.vue';

export default {
    name: 'RequisitionDetails',
    props: {
        requisitionId: {
            type: Number,
            required: true
        },
        user: {
            type: Object,
            required: true
        }
    },
    components: {
        PermissionControl
    },
    data() {
        return {
            loading: true,
            loadingHtml: '<div class="loading-spinner" style="position:fixed;top:50%;left: 50%;transform: translate(-50%, -50%);font-size: 2em;color: #0097b2bf;text-align: center;z-index: 1000;background: rgba(64, 177, 197, 0.05);padding: 40px;border: 5px;"><i class="fa fa-spinner fa-spin"></i> Loading...</div>',
            contentHtml: '',
            requisition: {},  // Initialize as an empty object
            documentsTable: null,  // Reference to the DataTable instance for documents
            historyLogs: [],
            capturingStatus: 'Capturing',
            authorizationStatus: 'Not ready, capture incomplete.',
            fundingStatus: 'Not yet funded',
            settlementStatus: 'Settlement conditions unsatisfied',
            sourceAccountsTable: null, // Reference to the DataTable instance for source accounts
            sourceAccountsTable2: null,
            selectedSourceAccount: null,  // Store the selected source account details
            showSourceAccountDetails: false,  // Control visibility of account details
            allSourceAccounts: [],  // Store all available source accounts
            modalInstance: null,
            documentModalInstance: null,
            depositModalInstance: null,
            editDepositModalInstance: null,
            editPaymentModalInstance: null,
            createPaymentModalInstance: null,
            documentForm: {
                description: '',
                file: null
            },
            formatDate(dateString) {
                return moment(dateString).format('DD MMM YYYY');
            },
            depositForm: {
                description: '',
                amount: '',
                funded: false,
                deposit_date: null,
                requisition_id: this.requisitionId,
                firm_account_id: this.selectedSourceAccount,
            },
            editDepositForm: {
                id: null,
                description: '',
                amount: '',
                funded: false,
                deposit_date: null,
            },
            balanceFundDeposit: {
                description: '',
                amount: '',
                funded: false,
                requisition_id: '',
                firm_account_id: '',
            },
            categories: [],  // Will be populated with categories from the API
            accountTypes: [], //will be populated with account types from the API
            beneficiaries: [],  // Will be populated with existing beneficiaries from the API
            categoryBeneficiaries: [], // List of beneficiaries based on the selected category 
            institutions: [],
            showAccountDetails: false,  // Controls visibility of account details section
            showEditAccountDetails: true,
            searchQuery: '',
            filteredAccounts: [],  // List to hold matching account suggestions
            showSuggestions: false,  // Control visibility of suggestions dropdown
            newAccountLabel: '',  // Label that changes dynamically based on search input
            paymentForm: {
                search: '',
                category: '',
                account_holder_type: '',
                account_number: '',
                initials: '',
                surname: '',
                company_name: '',
                id_number: '',
                registration_number: '',
                account_type: { id: null},
                institution: { id: null},
                branch_code: '',
                verified: '',
                description: '',
                amount: '',
                my_reference: '',  // Example reference
                recipient_reference: '',
                requisition_id: '',
                firm_account_id: '',
            },
            editPaymentForm: {
                category: '',
                account_holder_type: '',
                account_number: '',
                initials: '',
                surname: '',
                company_name: '',
                id_number: '',
                registration_number: '',
                account_type: { id: null},
                institution: { id: null},
                branch_code: '',
                verified: '',
                description: '',
                amount: '',
                my_reference: '',  // Example reference
                recipient_reference: '',
                requisition_id: '',
                firm_account_id: '',
            },
            errors: {}
        };
    },
    computed: {
        totalDepositAmount() {
            if (this.selectedSourceAccount.deposits && this.selectedSourceAccount.deposits.length > 0) {
                return this.selectedSourceAccount.deposits.reduce((sum, deposit) => {
                    return parseFloat(parseFloat(sum, 2) + parseFloat(deposit.amount, 2)).toFixed(2);
                }, 0);
            }
            return 0;  // Return 0 if no deposits
        },
        totalPaymentAmount() {
            if (this.selectedSourceAccount.payments && this.selectedSourceAccount.payments.length > 0) {
                return this.selectedSourceAccount.payments.reduce((sum, payment) => {
                    return parseFloat(parseFloat(sum, 2) + parseFloat(payment.amount, 2)).toFixed(2);
                }, 0);
            }
            return 0;  // Return 0 if no deposits
        },
        netBalance() {
            // Convert to numbers and calculate the difference
            return (
                parseFloat(this.totalDepositAmount) - parseFloat(this.totalPaymentAmount)
            ).toFixed(2); // Optionally format to two decimal places
        },
        statusClass() {
            // Call getStatusClass and pass the necessary parameters
            return this.getStatusClass(this.requisition.status_id, this.requisition.funding_status, this.selectedSourceAccount);
        }
    },
    mounted() {
        
        this.loadRequisitionDetails();
       /*  this.showPaymentsTabContent(); */
        //this.loadHistoryLogs();
        //this.checkStoredSourceAccount(); // Check if there is a stored source account
    },
    methods: {
        // Load requisition details based on matter ID and requisition ID
        loadRequisitionDetails() {
            this.contentHtml = this.loadingHtml;  // Show loading spinner
            axios.get(`/api/requisitions/${this.requisitionId}`)
                .then(response => {
                    
                    this.requisition = response.data || {};  // Set requisition data or empty object
                    this.updateStatus();  // Update status based on requisition details
                    this.showPaymentsTabContent();
                   /*  if(this.requisition.status_id == 1)
                    {
                        this.loadSourceAccounts();
                        
                    } */
                    this.contentHtml = '';  // Show loading spinner
                })
                .catch(error => {
                    console.error('Error loading requisition details:', error);
                    this.contentHtml = '';  // Show loading spinner
                });
        },
        approveRequisition(requisitionId) {
            axios.put(`/api/requisitions/${requisitionId}/approve`)
                .then(response => {
                    console.log("Requisition approved successfully:", response.data);
                    // Optionally update local data or trigger a refresh
                    this.$emit('requisitionUpdated', response.data);
                })
                .catch(error => {
                    console.error("Error approving requisition:", error);
                    alert("There was an error approving the requisition. Please try again.");
                });
        },
        openEditPaymentModal(payment) {


            this.showAccountDetails = true;
            this.loadCategories();
            this.loadAccountTypes();
            this.loadInstitutions();

            // Set the edit form data to the selected deposit values
            this.editPaymentForm.id = payment.id;
            this.editPaymentForm.description = payment.description;
            this.editPaymentForm.amount = payment.amount;
            this.editPaymentForm.verified = payment.verified;
            this.editPaymentForm.my_reference = payment.my_reference;
            this.editPaymentForm.recipient_reference = payment.recipient_reference;
            this.editPaymentForm.account_number = payment.account_number;
            this.editPaymentForm.account_holder_type = payment.account_holder_type;
            this.editPaymentForm.initials = payment.initials;
            this.editPaymentForm.surname = payment.surname;
            this.editPaymentForm.company_name = payment.company_name;
            this.editPaymentForm.id_number = payment.id_number;
            this.editPaymentForm.registration_number = payment.registration_number;
            this.editPaymentForm.account_type.id = payment.account_type_id;
            this.editPaymentForm.institution = payment.institution;
            this.editPaymentForm.branch_code = payment.branch_code;
            this.editPaymentForm.category = payment.category_id;
            // Convert funded from 1 or 0 to boolean true/false
            //this.editDepositForm.funded = deposit.funded === 1;
            // Set the deposit date if available, format to 'YYYY-MM-DD' if necessary
            // Show the modal
            this.editPaymentModalInstance = new bootstrap.Modal(document.getElementById('editPaymentModal'));
            this.editPaymentModalInstance.show();
            
            //console.log(deposit);
        },
        openEditDepositModal(deposit) {
            // Set the edit form data to the selected deposit values
            this.editDepositForm.id = deposit.id;
            this.editDepositForm.description = deposit.description;
            this.editDepositForm.amount = deposit.amount;
            // Convert funded from 1 or 0 to boolean true/false
            this.editDepositForm.funded = deposit.funded === 1;
            // Set the deposit date if available, format to 'YYYY-MM-DD' if necessary
            this.editDepositForm.deposit_date = deposit.deposit_date 
                ? new Date(deposit.deposit_date).toISOString().split('T')[0]  // Format to YYYY-MM-DD
                : null;

            // Show the modal
            this.editDepositModalInstance = new bootstrap.Modal(document.getElementById('editDepositModal'));
            this.editDepositModalInstance.show();
            //console.log(deposit);
        },


        updateDeposit() {
            axios.put(`/api/deposits/${this.editDepositForm.id}`, this.editDepositForm)
                .then(response => {
                   // Response contains the updated deposit
                   const updatedDeposit = response.data;

                    // Find the index of the deposit in the deposits array
                    const index = this.selectedSourceAccount.deposits.findIndex(deposit => deposit.id === updatedDeposit.id);

                    // If found, update the deposit in the array
                    if (index !== -1) {
                        // Replace the old deposit with the updated one
                        this.selectedSourceAccount.deposits.splice(index, 1, updatedDeposit);
                    }
                    // Close the modal
                    this.closeModal();
                })
                .catch(error => {
                    if (error.response && error.response.data.errors) {
                        this.errors = error.response.data.errors;
                    }
                });
        },
        confirmDepositDelete(depositId) {
            if (confirm('Are you sure you want to delete this deposit?')) {
                this.deleteDeposit(depositId);
            }
        },
        deleteDeposit(depositId) {
            
            axios.delete(`/api/deposits/${this.editDepositForm.id}`)
                .then(response => {
                    // Remove the deposit from the local list
                    this.selectedSourceAccount.deposits = this.selectedSourceAccount.deposits.filter(deposit => deposit.id !== this.editDepositForm.id);

                    //console.log('Deposit deleted successfully:', response.data);

                    if(response.data){
                        // Update the requisition object with the new source_account_id
                        this.requisition.funding_status = response.data.funding_status;
                        this.closeModal();
                    }
                })
                .catch(error => {
                    console.error('Error deleting deposit:', error);
                });
        },

        confirmPaymentDelete()
        {
            if(confirm("Are you sure you want to delete this payment?")){
                this.deletePayment();
            }
        },

        deletePayment(){
            axios.delete(`/api/payments/${this.editPaymentForm.id}`)
                .then(response => {
                    // Remove the deposit from the local list
                    this.selectedSourceAccount.payments = this.selectedSourceAccount.payments.filter(payment => payment.id !== this.editPaymentForm.id);

                    console.log('Payment deleted successfully:', response.data);

                    if(response.data){
                        // Update the requisition object with the new source_account_id
                        this.requisition.funding_status = response.data.funding_status;
                        this.closeModal();
                    }
                })
                .catch(error => {
                    console.error('Error deleting deposit:', error);
                });
        },

        onCategoryChange(){
            //hide the Account Details and Beneficiary section again
            this.showAccountDetails = false;

            //here we want to get all the beneficiary for the selected category
            this.getCategoryExistingBeneficiaries(this.paymentForm.category)
        },  

        getCategoryExistingBeneficiaries(CategoryId)
        {
            axios.get(`/api/category/beneficiaries/${CategoryId}`)
                .then(response => {
                    this.categoryBeneficiaries = response.data;  // Populate the beneficiaries dropdown with the response
                    //console.log(this.categoryBeneficiaries);
                })
                .catch(error => {
                    console.error('Error fetching beneficiaries:', error);
                });
        },
        // Handle account type change based on the selected option
        onAccountChange() {
            if (this.paymentForm.account_holder_type === 'natural') {
                this.showAccountDetails = true;
                this.loadAccountTypes();
                this.loadInstitutions();
                this.handleNaturalPersonSelection();
            } else if (this.paymentForm.account_holder_type === 'juristic') {
                this.showAccountDetails = true;  // Show the form for Juristic Person too
                this.loadAccountTypes();
                this.loadInstitutions();
                this.handleJuristicPersonSelection();
            } else {
                this.showAccountDetails = false; // Hide for existing beneficiaries
                this.loadBeneficiaryDetails(this.paymentForm.account_holder_type);
            }

            
        },
        getStatusClass(statusId, fundingStatus, selectedSourceAccount) {
            let statusClass = '';

            if (statusId === 1) {
                statusClass = 'incomplete';
            } else if (statusId === 2) {
                statusClass = 'incomplete current';
            } else if (statusId === 3) {
                return statusClass = 'complete';
            } else if (statusId === 5){
                return statusClass = 'complete';
            }
            // Add 'incomplete' if funding_status is set (not null or empty)
            if (fundingStatus) {
                statusClass = 'patialcomplete';
            }

            if (selectedSourceAccount && selectedSourceAccount.payments && selectedSourceAccount.payments.length > 0){
                statusClass = 'patialcomplete';
            }
            return statusClass; // Default case if statusId is not matched
        },
        // Open the upload document modal
        openUploadModal() {
            this.documentModalInstance = new bootstrap.Modal(document.getElementById('uploadDocumentModal'));
            this.documentModalInstance.show();
        },

        // Initialize and load Documents DataTable when Documents tab is clicked
        loadDocumentsData() {
            if (this.documentsTable) {
                this.documentsTable.ajax.reload();
                return;
            }

            this.documentsTable = $('#documents-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `/api/requisitions/${this.requisitionId}/documents`,  // Your API endpoint to fetch documents
                    type: 'GET',
                    data: (json) => json // Return full JSON response for DataTables
                },
                columns: [
                    { data: 'user_name', name: 'user_name' },  // Assuming the API returns `user_name`
                    { data: 'description', name: 'description' },
                    { data: 'file_name', name: 'file_name' },
                    { data: 'uploaded_at', name: 'uploaded_at', render: (data) => new Date(data).toLocaleString() }  // Format date
                ],
                responsive: false,
                destroy: true,  // Reinitialize the table if needed
            });
        },

        // Handle file selection
        handleFileUpload(event) {
            this.documentForm.file = event.target.files[0];
        },

        // Load history logs for the requisition
        loadHistoryLogs() {
            axios.get(`/api/requisitions/${this.requisitionId}/history`)
                .then(response => {
                    this.historyLogs = response.data;
                })
                .catch(error => {
                    console.error('Error loading history logs:', error);
                });
        },

        // Update the status values based on requisition data
        updateStatus() {
            this.capturingStatus = this.requisition.capturing_status || 'Capturing';
            this.authorizationStatus = this.requisition.authorization_status || 'Not ready, capture incomplete.';
            this.fundingStatus = this.requisition.funding_status || 'Not yet funded';
            this.settlementStatus = this.requisition.settlement_status || 'Settlement conditions unsatisfied';

            
        },

        // Initialize and load Source Accounts DataTable when Payments tab is clicked
        loadSourceAccounts() {
            
            if (this.sourceAccountsTable) {
                this.sourceAccountsTable.ajax.reload();
                return;
            }

            this.sourceAccountsTable = $('#source-accounts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `/api/firm-accounts`,//`/api/requisitions/${this.requisitionId}/source-accounts`,
                    type: 'GET',
                    data: (json) => json // Return full JSON response for DataTables
                },
                columns: [
                    { data: 'display', name: 'display' },
                    { data: 'institution.name', name: 'institution.name' },
                    { data: 'account_number', name: 'account_number' },
                    { data: 'branch_code', name: 'branch_code' },
                    { data: 'account_holder', name: 'account_holder' },
                    { data: 'authorised', name: 'authorised' }
                ],
                responsive: false,
                destroy: true, // Reinitializes the table if needed
              
                rowCallback: (row, data) => {
                    // Add click event listener to each row
                    $(row).on('click', () => {
                        this.handleSourceAccountClick(data.id);  // Handle row click
                    });
                }
            });
        },
        // Filter the source accounts DataTable based on the search input
        filterSourceAccounts(event) {
            const filterValue = event.target.value;
            console.log(`Filter applied: ${filterValue}`);
            this.sourceAccountsTable.search(filterValue).draw();
        },

        // Handle row click to show source account details
        handleSourceAccountClick(sourceAccountId) {
            this.fetchSourceAccountDetailsAndUpdateRequisition(sourceAccountId);
        },

        // Fetch source account details using its ID
        fetchSourceAccountDetails(sourceAccountId) {
            axios.get(`/api/firm-accounts/${sourceAccountId}`)
                .then(response => {
                    this.selectedSourceAccount = response.data;  // Store the fetched details
                    this.showSourceAccountDetails = true;  // Show the details section
                    /* console.log(this.selectedSourceAccount.deposits);*/
                    console.log("this is payments data");
                    console.log(this.selectedSourceAccount.payments);
                    // Update the requisition with the selected source account ID
                    //this.updateRequisitionSourceAccount(sourceAccountId);
                })
                .catch(error => {
                    console.error('Error fetching source account details:', error);
                });
        },

        // Submit the document form to the backend via Axios
        uploadDocument() {
            const formData = new FormData();
            formData.append('description', this.documentForm.description);
            formData.append('file', this.documentForm.file);
            formData.append('requisition_id', this.requisitionId);

            axios.post('/api/documents', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                console.log('Document uploaded successfully:', response.data);
                // Close the modal and reset form
                this.closeModal();
                this.documentForm.description = '';
                this.documentForm.file = null;

                // Optionally reload the documents table to reflect the new document
                if (this.documentsTable) {
                    this.documentsTable.ajax.reload();
                }
            })
            .catch(error => {
                console.error('Error uploading document:', error);
            });
        },

        fetchSourceAccountDetailsAndUpdateRequisition(sourceAccountId)
        {
            axios.get(`/api/firm-accounts/${sourceAccountId}`)
                .then(response => {
                    this.selectedSourceAccount = response.data;  // Store the fetched details
                    this.showSourceAccountDetails = true;  // Show the details section

                    // Update the requisition with the selected source account ID
                    this.updateRequisitionSourceAccount(sourceAccountId);
                })
                .catch(error => {
                    console.error('Error fetching source account details:', error);
                });
        },
        // Update requisition with the selected source account ID
        updateRequisitionSourceAccount(sourceAccountId) {
            axios.put(`/api/requisitions/${this.requisitionId}`, {
                firm_account_id: sourceAccountId
            })
            .then(response => {
                console.log('Requisition updated successfully:', response.data);

                if(response.data){
                     // Update the requisition object with the new source_account_id
                    this.requisition.firm_account_id = response.data.requisition.firm_account_id;
                    this.requisition.status_id = 2;
                    this.closeModal();
                    this.openCreatePaymentModal();
                }
            })
            .catch(error => {
                console.error('Error updating requisition:', error);
            });
        },

        // Show Payments tab content based on source_account_id
        showPaymentsTabContent() {
            if (this.requisition.firm_account_id) {
                // If source_account_id is set, fetch and display the details
                this.fetchSourceAccountDetails(this.requisition.firm_account_id);
            } else { console.log('not set sorry');
                // If no source_account_id, display the Source Accounts DataTable
                this.showSourceAccountDetails = false;
                this.loadSourceAccounts();
            }
        },

        // Show Document tab content based on current user
        showDocumentTabContent(){
            this.loadDocumentsData();
        },

        // Clear the selected source account and show DataTable again
        clearSelectedSourceAccount() {
            this.showSourceAccountDetails = false;
            this.selectedSourceAccount = null;
        },

         // Fetch all source accounts when modal is opened
        loadAllSourceAccounts() {
            if (this.sourceAccountsTable2) {
                this.sourceAccountsTable2.ajax.reload();
                return;
            }

            this.sourceAccountsTable2 = $('#source-accounts-table2').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `/api/firm-accounts`,//`/api/requisitions/${this.requisitionId}/source-accounts`,
                    type: 'GET',
                    data: (json) => json // Return full JSON response for DataTables
                },
                columns: [
                    { data: 'display', name: 'display' },
                    { data: 'institution.name', name: 'institution.name' },
                    { data: 'account_number', name: 'account_number' },
                    { data: 'branch_code', name: 'branch_code' },
                    { data: 'account_holder', name: 'account_holder' },
                    { data: 'aggregated', render: (data) => (data ? 'Yes' : 'No') }
                ],
                responsive: false,
                destroy: true, // Reinitializes the table if needed
              
                rowCallback: (row, data) => {
                    // Add click event listener to each row
                    $(row).on('click', () => {
                        this.handleSourceAccountClick(data.id);  // Handle row click
                    });
                }
            });
        },

        // Open modal to choose source account
        openSourceAccountModal() {
            this.loadAllSourceAccounts();  // Fetch source accounts
            this.modalInstance = new bootstrap.Modal(document.getElementById('sourceAccountModal'));
            this.modalInstance.show();
        },
        
        closeModal() {
            //const newUserModal = bootstrap.Modal.getInstance(document.getElementById('newUserModal'));
            if (this.modalInstance) {
                this.modalInstance.hide();
            }
            if(this.documentModalInstance){
                this.documentModalInstance.hide();
            }
            if(this.depositModalInstance){
                this.depositModalInstance.hide();
            }
            if(this.editDepositModalInstance){
                this.editDepositModalInstance.hide();
            }
            if(this.editPaymentModalInstance){
                this.editPaymentModalInstance.hide();
            }
            if(this.createPaymentModalInstance){
                this.createPaymentModalInstance.hide();
            }
        },
        // Save requisition (stub method for future implementation)
        saveRequisition() {
            console.log('Save requisition:', this.requisition);
        },

        // Delete requisition (stub method for future implementation)
        deleteRequisition() {
            console.log('Delete requisition:', this.requisition);
        },

        // Open the modal for creating a new deposit
        openNewDepositModal() {
            this.resetForm();
            this.depositModalInstance = new bootstrap.Modal(document.getElementById('newDepositModal'));
            this.depositModalInstance.show();
        },

        // Handle the form submission for creating a new deposit
        createDeposit() {
            this.submitForm();
        },

        // Handle form submission and stay on the modal for adding a new deposit
        createAndNewDeposit() {
            this.submitForm(true);
        },

        // Submit the deposit form via Axios
        submitForm(stayInModal = false) {
            
            this.depositForm.requisition_id = this.requisitionId;
            this.depositForm.firm_account_id = this.selectedSourceAccount.id;
            //console.log(this.depositForm);
            axios.post('/api/deposits', this.depositForm)
                .then(response => {
                    console.log('Deposit created successfully:', response.data);

                    // Add the newly created deposit to the local deposits list
                    this.selectedSourceAccount.deposits.push(response.data);

                    if(response.data){
                        // Update the requisition object with the new source_account_id
                        this.requisition.funding_status = 1;
                        this.closeModal();
                    }

                    // Close the modal if "Save" was clicked
                   // if (!stayInModal) {
                        //this.fundingStatus = 'Completed on '+response.data.created_at;
                        //this.depositModalInstance.hide();
                    //}

                    // Reset the form after submission
                    this.resetForm();

                    // Optionally reload deposit data if required
                    // this.loadDeposits();
                })
                .catch(error => {
                    console.error('Error creating deposit:', error);
                    if (error.response && error.response.data.errors) {
                        this.errors = error.response.data.errors;  // Show validation errors
                    }
                });
        },

        // Reset the form fields and errors
        resetForm() {
            this.depositForm = {
                description: '',
                amount: '',
                funded: false,
                deposit_date: null
            };
            this.errors = {};
        },

        // process the balance payment fund button
        balancePaymentFund(){
            this.balanceFundDeposit.description = "Deposit";
            this.balanceFundDeposit.requisition_id = this.requisitionId;
            this.balanceFundDeposit.firm_account_id = this.selectedSourceAccount.id;
            this.balanceFundDeposit.funded =  true;
            this.balanceFundDeposit.amount = this.totalPaymentAmount;

            axios.post('/api/deposits/balance-payment-fund', this.balanceFundDeposit)
                .then(response => {
                    console.log('Deposit created successfully:', response.data);

                    // Add the newly created deposit to the local deposits list
                    this.selectedSourceAccount.deposits.push(response.data);

                    if(response.data){
                        // Update the requisition object with the new source_account_id
                        this.requisition.funding_status = 1;
                        this.closeModal();
                    }

                    // Close the modal if "Save" was clicked
                   // if (!stayInModal) {
                        //this.fundingStatus = 'Completed on '+response.data.created_at;
                        //this.depositModalInstance.hide();
                    //}

                    // Reset the form after submission
                    this.resetForm();

                    // Optionally reload deposit data if required
                    // this.loadDeposits();
                })
                .catch(error => {
                    console.error('Error creating deposit:', error);
                    if (error.response && error.response.data.errors) {
                        this.errors = error.response.data.errors;  // Show validation errors
                    }
                });
        },

        // Set the account type and update the form fields based on search
        setNewAccountType(accountType) {
            if (accountType === 'juristic') {
                this.paymentForm.account_holder_type = 'juristic';
            } else if (accountType === 'natural') {
                this.paymentForm.account_holder_type = 'natural';
            }

            // Set the account_number if the search is numeric, otherwise set the account_holder
            if (this.isSearchNumeric()) {
                this.paymentForm.account_number = this.paymentForm.search;
                this.paymentForm.company_name = '';
            } else {
                this.paymentForm.company_name = this.paymentForm.search;
                this.paymentForm.account_number = '';
            }

            this.showAccountDetails = true;  

            // Hide suggestions after selecting
            this.showSuggestions = false;
        },

        onInstitutionChange() {
            const selectedInstitution = this.institutions.find(institution => institution.id === this.paymentForm.institution.id);
            
            if (selectedInstitution) {
                this.paymentForm.branch_code = selectedInstitution.branch_code;
            } else {
                this.paymentForm.branch_code = ''; // Clear branch code if no institution is selected
            }
        },

        // Open the modal for creating a new payment
        openCreatePaymentModal() {
            
            this.resetPaymentForm();
            if(this.requisition){
                this.paymentForm.my_reference = this.requisition.file_reference;
            }
            
            this.createPaymentModalInstance = new bootstrap.Modal(document.getElementById('createPaymentModal'));
            this.createPaymentModalInstance.show();
            this.loadCategories();
            //this.loadAccounts();
        },

        // Load categories from API
        loadCategories() {
            axios.get('/api/categories')
                .then(response => {
                    this.categories = response.data;
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
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

        // Load beneficiaries from API
        loadBeneficiaries() {
            axios.get('/api/beneficiaries')
                .then(response => {
                    this.beneficiaries = response.data;
                })
                .catch(error => {
                    console.error('Error loading beneficiaries:', error);
                });
        },
        loadInstitutions()
        {
            axios.get('/api/institutions')
                .then(response => {
                    this.institutions = response.data;
                })
                .catch(error => {
                    console.error('Error loading beneficiaries:', error);
                });
        },
        // Fetch matching accounts based on search query
        fetchMatchingAccounts() {
            if (this.paymentForm.search.length >= 2) {
                this.filteredAccounts = [];
                axios.get(`/api/beneficiary-accounts/search?query=${this.paymentForm.search}`)
                    .then(response => {
                        this.filteredAccounts = response.data;
                    })
                    .catch(error => {
                        console.error('Error fetching accounts:', error);
                    });
                
                this.showSuggestions = true;

                    // Update label based on whether search is account number or name
                this.newAccountLabel = this.isSearchNumeric() 
                    ? `Account number` 
                    : `Account holder name`;
                

            } else {
                this.showSuggestions = false;  // Hide suggestions if query is too short
            }
        },
        // Handle account selection from dropdown
        selectAccount(account) {
            
            this.paymentForm.account_number = account.account_number;  // Set selected account number
            this.paymentForm.company_name = account.company_name;  // Optionally set the account holder
            this.paymentForm.search = `${account.account_number} - ${account.company_name}`;  // Set search query to selected account
            this.paymentForm.account_holder_type = account.account_holder_type;
            this.showSuggestions = false;  // Hide dropdown after selection
            this.filteredAccounts = [];

            this.loadAccountTypes();
            this.loadInstitutions();

            // Update additional fields based on the selected account
            this.paymentForm.category = account.category;  // Assuming category_id is returned from API
            this.paymentForm.account_type = account.accounttype;  // Assuming account_type is returned from API
            this.paymentForm.institution = account.institution;  // Assuming institution name is returned from API
            this.paymentForm.branch_code = account.branch_code;  // Assuming branch code is returned from API

            this.showAccountDetails = true;
            //console.log(this.paymentForm);
            //this.loadAccountTypes();
            //this.loadInstitutions();

            //this.fetchData();  // Fetch data when component is mounted
        },
        // Method to highlight matching text in the result
        highlightMatch(text, searchTerm) {
            if (!searchTerm) return text; // If no search term, return text as is

            const regex = new RegExp(`(${searchTerm})`, 'gi');  // Case-insensitive match
            return text.replace(regex, '<b style="color: #0097b2bf;">$1</b>');  // Bold the matching part
        },
       
        // Check if the search input is numeric (for account numbers)
        isSearchNumeric() {
            return /^\d+$/.test(this.paymentForm.search);  // Returns true if search contains only numbers
        },
        // Handle form submission for creating a payment
        createPayment() {
            this.submitPaymentForm();
        },

        // Handle form submission and stay on the modal for adding another payment
        createAndNewPayment() {
            this.submitPaymentForm(true);
        },

        // Submit the payment form via Axios
        submitPaymentForm(stayInModal = false) {
            this.paymentForm.requisition_id = this.requisitionId;
            this.paymentForm.firm_account_id = this.selectedSourceAccount.id;

            axios.post('/api/payments', this.paymentForm)
                .then(response => {
                    console.log('Payment created successfully:', response.data);

                    // Close the modal if "Save" was clicked
                    if (!stayInModal) {
                        //this.createPaymentModalInstance = bootstrap.Modal.getInstance(document.getElementById('createPaymentModal'));
                        this.createPaymentModalInstance.hide();
                    }
                    this.closeModal();
                    // Reset the form after submission
                    this.resetPaymentForm();
                })
                .catch(error => {
                    console.error('Error creating payment:', error);
                    if (error.response && error.response.data.errors) {
                        this.errors = error.response.data.errors;  // Show validation errors
                    }
                });
        },

        // Reset the form fields and errors
        resetPaymentForm() {
            this.paymentForm = {
                search: '',
                category: '',
                account_holder_type: '',
                account_number: '',
                initials: '',
                surname: '',
                company_name: '',
                id_number: '',
                registration_number: '',
                account_type: { id: null},
                institution: {
                    id: null
                },
                branch_code: '',
                verified: '',
                description: '',
                amount: '',
                my_reference: '',
                recipient_reference: '',
                requisition_id: '',
                firm_account_id: '',
            };
            this.errors = {};
            this.showAccountDetails = false;
        },
        // Print the current page
        printPage() {
            window.print();
        },
    }
};
</script>

<style scoped>

.approve-box{
    border-radius: 3px;
    border: solid 1px #999;
    height: 100px;
    width: 250px;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    background: #f2f2f2;
}
/* Style for autocomplete dropdown */
.autocomplete-dropdown {
    position: absolute;
    z-index: 1000;
    background-color: white;
    border: 1px solid #ced4da;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    padding: 0;
    margin: 0;
    list-style-type: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    top:45px;
}

.autocomplete-dropdown .dropdown-item {
    padding: 8px 16px;
    cursor: pointer;
}

.autocomplete-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}
.modal-body label{
    font-size:14px;
}
.btn-info{
    color: white !important;
}
label{
    font-weight: bold;
    color: #666;
}
.section-title {
    font-weight: bold;
    margin-bottom: 20px;
}

.requisition-status{
    margin:0px;
}

.requisition-status .box {
    border: 1px solid #0097b2bf;
    min-height: 75px;
   
}

.status-card{
    margin-bottom: 0px;
}

.status-card h6 {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 5px;
}

.status-card .status-value {
    font-size: 12px;
    color: #666;
}

.nav-tabs .nav-link {
    font-weight: bold;
    color: #333;
}

.tab-content {
   /*  padding: 15px; */
}

.table th, .table td {
    /* vertical-align: middle; */
}

.col-form-label{
    text-align: right;
}
.table {
    table-layout: fixed;
    text-overflow: ellipsis; 
}

.incomplete{
    border-top: 7px solid #eaeaea !important;
}

.patialcomplete{
    border-top: 7px solid rgb(255, 198, 94) !important;
}

.incomplete.current{
    border-top: 7px solid #999 !important;
}
.complete{
    border-top: 7px solid rgb(40, 168, 40) !important;
}
.almostcomplete{
    border-top: 7px solid #0097b2bf !important;
}

#editChoosedSourceAccount:hover, .fa-edit:hover{
    cursor: pointer;
}
.lighthover:hover{
    background-color: #f1fbfd;
    cursor: pointer;
}
.fa-square{
    color: #fff !important;
    border: solid 2px #ccc;
    border-radius: 3px;
}
</style>
