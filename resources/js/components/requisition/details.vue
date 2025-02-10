<template>
    <div class="container mt-4 mb-5">
        <!-- Requisition Header -->
         
        <h4 class="section-title mb-2">
            Requisition: <span style="color:#999;font-weight: normal;font-size: 20px;">{{ requisition.file_reference }} - {{ requisition.reason }}</span>
            <span class="pull-right">
                <router-link :to="{ name: 'emailrequestor', params: { requisitionId: requisitionId } }" class="btn btn-white btn-default-default btn-sm ml-1">Email Requestor</router-link>
                <router-link :to="{ name: 'emailsignatory', params: { requisitionId: requisitionId } }" class="btn btn-white btn-default-default btn-sm ml-1">Email Signatory</router-link>
                <button class="btn btn-white btn-default-default btn-sm ml-1" @click="navigateToAllTransactionsForAFile(requisition?.file_upload_id ?? 0)">File History</button>
                <button class="btn btn-light btn-default-default btn-sm ml-1" @click="printPage"><i class="fas fa-print"></i> Print</button>
                <!-- <router-link to="/requisition/new" class="btn btn-primary btn-sm ml-1"><i class="fas fa-print"></i> Print</router-link> -->
            </span>
        </h4>

        <!-- Status Sections -->
        <div class="row mb-4 requisition-status">
            
            <div class="col-md-3 box" :class="statusClass">
                <div class="status-card row pt-2">
                    <div class="col-md-10">
                        <h6 class="fw-bold">Capturing</h6>
                        <p class="status-value mb-0 mt-3">
                            <span v-if="requisition.status_id === 1">
                                Transaction value: {{ requisition.transaction_value }}
                            </span>
                            <span v-else-if="requisition.status_id === 2">

                                <span class="" v-if="requisition && requisition.deposits && requisition.deposits.length > 0 && !requisition.payments">
                                    <i class="fa fa-info-circle text-warning"></i> Not balanced
                                </span>
                                <span class="" v-else-if="requisition && requisition.payments && requisition.payments.length > 0">
                                    <i class="fa fa-info-circle text-warning"></i> Not balanced <br/>
                                    <div class="btn btn-white btn-default-default btn-sm mt-0 mb-1" data-toggle="tooltip" data-placement="bottom" title="Balance the matter by adding a default source / deposit entry" @click="balancePaymentDontFund"><i class="fas fa-balance-scale"></i> Balance</div>
                                </span>
                                <span v-else>No entries captured</span>
                            </span>
                            <span v-else-if="requisition.status_id == 3 || requisition.status_id == 4 || requisition.status_id >= 5">
                                Transaction value: R{{ parseFloat(totalDepositAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                            </span>
                           
                        </p>
                    </div>
                    <!-- <div class="col-md-3" v-if="requisition.status_id !== 1">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div> -->
                    <div class="col-md-2" v-if="requisition.status_id == 2">
                        <span class="badge bg-info" v-if="requisition && requisition.deposits && requisition.deposits.length > 0 && requisition.payments && requisition.payments.length > 0">
                            {{requisition.deposits.length + requisition.payments.length}} / {{requisition.deposits.length + requisition.payments.length}}
                        </span>
                        <span class="badge bg-info" v-else-if="requisition && requisition.deposits && requisition.deposits.length > 0">
                            {{requisition.deposits.length}} / {{requisition.deposits.length}}
                        </span>
                        <span class="badge bg-info" v-else-if="requisition && requisition.payments && requisition.payments.length > 0">
                            {{requisition.payments.length}} / {{requisition.payments.length}}
                        </span>
                        <span class="badge bg-info" v-else>0 / 0</span>
                    </div>
                    <div class="col-md-2" v-if="requisition.status_id >= 3">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div>
                    
                </div>
            </div>
            <div class="col-md-3 incomplete box" :class="{'current': requisition.status_id == 3, 'complete': requisition.status_id >= 5, 'complete': (requisition.status_id == 4 && requisition.authorization_status), 'complete': requisition.authorization_status}">
                <div class="status-card row pt-2">
                    <div class="col-md-10">
                        <h6 class="fw-bold">Authorization</h6>
                        <div v-if="requisition.status_id == 3">
                            <p class="status-value mb-0 mt-3">&nbsp;</p>
                            <PermissionControl :roles="['admin', 'authoriser']">
                                <div class="mb-2"><a class="btn btn-sm btn-white btn-default-default" @click="approveRequisition(requisition.id)"><span id="approveBtnSpinner1" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Approve</a></div>
                            </PermissionControl>
                            <!-- <PermissionControl :roles="['user']">
                                <div class="mb-2"><a class="btn btn-sm btn-white btn-default-default disabled">Approve</a></div>
                            </PermissionControl> -->
                            
                            
                        </div>
                        <div v-else-if="requisition.status_id == 4" class="txt-xs mb-0 mt-3">
                            Last signed on: {{ formatDate(requisition.authorized_at) }}
                        </div>
                        <div v-else-if="requisition.status_id >= 5" class="txt-xs mb-0 mt-3">
                            Last signed on: {{ formatDate(requisition.authorized_at) }}
                        </div>
                        <!-- <p v-else class="status-value mb-0 mt-3">{{ authorizationStatus }} 2</p> -->
                    </div>
                    <div class="col-md-2" v-if="requisition.status_id == 3">
                        <span class="badge bg-info" v-if="requisition && requisition.payments && requisition.payments.length > 0">
                            0 / {{requisition.payments.length}}
                        </span>
                        <span class="badge bg-info" v-else>0 / 0</span>                                
                    </div>
                    <div class="col-md-2" v-if="requisition.status_id == 4">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div>
                    <div class="col-md-2" v-if="requisition.status_id >= 5">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div>
                </div>
                
            </div>
            <div class="col-md-3 box" :class="{'complete': requisition.funding_status, 'incomplete': !requisition.funding_status, 'current': requisition.deposits && requisition.deposits.length > 0}">
                <div class="status-card row pt-2">
                    <div class="col-md-10">
                        <h6 class="fw-bold">Funding</h6>
                        <p class="status-value mb-0 mt-3" v-if="requisition.deposits && requisition.deposits.length > 0 && requisition.funding_status">
                            <span>
                                {{ requisition.funding_status ? 'Completed on '+formatDate(requisition.deposits[requisition.deposits.length - 1].created_at) : fundingStatus }}
                            </span>
                            
                            
                        </p>
                        <p class="status-value mb-0 mt-3" v-else-if="requisition.deposits && requisition.deposits.length > 0 && !requisition.funding_status">
                            <span>Mark as received on entry(s)</span><br/>
                            <span class="mb-2"><a class="btn btn-sm btn-white btn-default-default mb-2" @click="fundDeposit">Fund</a></span>
                            
                        </p>
                       <!--  <p class="status-value mb-0 mt-3" v-else="requisition.funding_status">
                            {{ fundingStatus }}
                        </p> -->
                    </div>
                    <div class="col-md-2" v-if="requisition.funding_status">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div>
                    <div class="col-md-2" v-else-if="requisition.deposits && requisition.deposits.length > 0 && !requisition.funding_status">
                        <span class="badge bg-info">
                            {{requisition.deposits ? requisition.deposits.filter(deposit => deposit.funded).length : 0}}
                            / 
                            {{requisition.deposits.length}}
                        </span>                               
                    </div>
                    
                </div>
            </div>
            <div class="col-md-3 box" :class="{'incomplete': requisition.status_id !== 4, 'almostcomplete': requisition.status_id <= 6, 'complete': requisition.status_id == 7}">
                <div class="status-card row pt-2">
                    <div class="col-md-10">
                        <h6 class="fw-bold">Settlement</h6>
                        <p class="status-value mt-3" v-if="requisition.status_id < 5">{{ settlementStatus }}</p>
                        <p class="status-value mt-3" v-else-if="requisition.status_id == 5">Pending payment</p>
                        <p class="status-value mt-3" v-else-if="requisition.status_id == 7">
                            Completed on: {{ formatDate(requisition.completed_at) }}, in file: 
                            {{ selectedSourceAccount.institution.short_name }} - {{ selectedSourceAccount.account_number }}
                        </p>
                    </div>
                    <div class="col-md-2" v-if="requisition.status_id == 5">
                        <i class="fa fa-hourglass-half text-primary" aria-hidden="true"></i>
                    </div>
                    <div class="col-md-2" v-else-if="requisition.status_id == 7">
                        <i class="fa fa-check-circle bg-green" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="requisition.status_id == 3 && !requisition.locked"><a class="pull-right btn btn-white btn-default-default btn-sm" @click="lockRequisition(requisition.id)"><i class="fa fa-lock"></i> Lock</a></div>
        <div v-else-if="requisition.locked" style="display:flow-root;background-color: #eee;padding:3px;border-radius: 5px;border-left: solid 5px orange;padding-left: 10px;margin-bottom: 10px;margin-top: -10px;">
            <span><i class="fa fa-lock"></i><b> This matter is locked.</b> 
                <span v-if="requisition.status_id < 7"> It is pending payment</span>
                <span v-else-if="requisition.status_id == 7"> Payment was made successfully and no further changes may be made.</span>
            </span>
            <PermissionControl :roles="['admin','authoriser']">
                <a v-if="requisition.status_id < 7" class="pull-right btn btn-white btn-primary btn-sm" @click="unlockRequisition(requisition.id)"> <span id="unlockBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> <i class="fa fa-lock-open"></i> Unlock</a>
            </PermissionControl>
            
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
                                    <input type="text" v-model="requisition.file_reference" class="form-control" :disabled="requisition.locked" id="file-reference">
                                </div>
                            </div>
                            <!-- Reason -->
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="reason">Reason:</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="requisition.reason" class="form-control" :disabled="requisition.locked" id="reason">
                                </div>
                            </div>
                            <!-- Parties -->
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="parties">Parties:</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="requisition.parties" class="form-control" :disabled="requisition.locked" id="parties">
                                </div>
                            </div>
                            <!-- Properties -->
                            <div class="row mb-3">
                                <label class="col-sm-2 col-form-label" for="properties">Properties:</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="requisition.properties" class="form-control" :disabled="requisition.locked" id="properties">
                                </div>
                            </div>
                            <!-- Created By (Safely handle if user is undefined) -->
                            <div class="row mb-3" v-if="requisition.user">
                                <label class="col-sm-2 col-form-label" for="created-by">Created By:</label>
                                <div class="col-sm-10">
                                    <div class="form-control" style="border: 0px;">{{ requisition.user.name }}</div>
                                </div>
                            </div>
                            <div class="row mb-3" v-if="requisition.locked">
                                <label class="col-sm-2 col-form-label" for="created-by">Locked By:</label>
                                <div class="col-sm-10">
                                    <div class="form-control" style="border: 0px;">{{ requisition.locked_by?.name }} on {{ requisition.locked_at }}</div>
                                </div>
                            </div>
                        </form>
                        <div class="row mb-3" v-if="!this.requisition.locked">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-10">
                                <button class="btn btn-primary" @click="saveRequisition">Save</button>
                                <button class="btn btn-danger ml-1" @click="confirmDeleteRequisition">Delete</button>
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
                            <div class="">
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
                                    <strong>{{ selectedSourceAccount.display_text }} - {{ selectedSourceAccount.account_number }}</strong>
                                    <span class="ml-2"><i>Bank:  {{ selectedSourceAccount.institution.name}} - ({{ selectedSourceAccount.branch_code }})</i></span>
                                    <span v-if="requisition && !requisition.locked" class="ml-2" id="editChoosedSourceAccount" title="Edit or delete the payment group" data-bs-toggle="popover" data-bs-placement="top"  @click="openSourceAccountModal"><i class="fas fa-edit"></i></span>
                                </span>
                            </div>
                            <div class="pull-right">
                                <div v-if="requisition && requisition.locked" style="width: 200px;">&nbsp;</div>
                                <button v-if="requisition && !requisition.locked" class="btn btn-white btn-sm" @click="openNewDepositModal"><i class="fa fa-plus" aria-hidden="true"></i> New Deposit</button>
                                <button v-if="requisition && !requisition.locked" class="btn btn-white btn-sm ml-1" @click="openCreatePaymentModal"><i class="fa fa-plus" aria-hidden="true"></i> New Payment</button>
                            </div>
                            
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div>Deposits</div>

                                    <!-- Loop through deposits if they exist -->
                                        <div v-if="requisition && requisition.deposits && requisition.deposits.length > 0" class="deposit-section row ml-0 mt-1 p-0">
                                            <div v-for="deposit in requisition.deposits" :key="deposit.id" class="p-0">
                                                <div v-if="deposit.funded" class="col-md-12 row mb-2 lighthover p-0">
                                                    <div class="col-md-3">
                                                        <span>
                                                            <i class="fa fa-check mr-2 text-success"></i></span>{{ deposit.description }}
                                                    </div>
                                                    <div class="col-md-6">
                                                        <!-- Display the user's name who made the deposit -->
                                                        <div class="bg-light p-1 rounded txt-xs" style="background-color: #e0ffe0 !important;border: solid 1px #a5f2a5;">Marked as received by {{ deposit.user ? deposit.user.name : 'Unknown User' }} on {{ formatDate(deposit.created_at) }}</div>
                                                    </div>
                                                    <div class="col-md-3 pl-4">
                                                        R{{ parseFloat(deposit.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}

                                                        <span v-if="requisition && !requisition.locked" class="pull-right"><i class="fa fa-edit text-primary" @click="openEditDepositModal(deposit)"></i></span>
                                                    </div>
                                                </div>
                                                <div v-if="!deposit.funded" class="col-md-12 row mb-2 lighthover p-0">
                                                    <div class="col-md-3">
                                                        <span>
                                                            <i class="fa fa-donate mr-2 text-fade"></i></span>{{ deposit.description }}
                                                    </div>
                                                    <div class="col-md-6">
                                                        <!-- Display the user's name who made the deposit -->
                                                        <div class="bg-light p-1 rounded txt-xs" style="background-color: #f2f2f2 !important;border: solid 1px #f2f2f2;"> Not funded</div>
                                                    </div>
                                                    <div class="col-md-3 pl-4"> 
                                                        R{{ parseFloat(deposit.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                                        
                                                        <span v-if="requisition && !requisition.locked" class="pull-right"><i class="fa fa-edit text-primary" @click="openEditDepositModal(deposit)"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Display a message if no deposits are found -->
                                        <div v-if="!requisition.deposits" class="txt-xs">No deposits have been added</div>
                               
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
                                    <div v-if="requisition && requisition.payments && requisition.payments.length > 0" class="deposit-section row ml-0 mt-1 mb-0 p-0">
                                        <div v-for="payment in requisition.payments" :key="payment.id" class="col-md-12 row mb-2 lighthover p-0">
                                            <div class="col-md-3" @click="openViewPaymentModal(payment)">
                                                <span v-if="payment.beneficiary_account && payment.beneficiary_account.verification_status == 'successful'">
                                                    <i class="far fa-check-circle mr-2 mt-1 text-success" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="Account details complete, AVS verified account" 
                                                    data-bs-content="Account details complete, AVS verified account"></i>
                                                </span>
                                                <span v-else-if="payment.beneficiary_account.verified == '1' && payment.beneficiary_account.verification_status == 'failed'">
                                                    <i class="fas fa-times-circle mr-2 mt-1 text-danger" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="AVS verification failed, Invalid account / details" 
                                                    data-bs-content="AVS verification failed, Invalid account / details"></i>
                                                </span>
                                                <span v-else-if="payment.beneficiary_account.verification_status == 'pending'">
                                                    <i class="fas fa-exclamation-circle text-warning mr-2 mt-1" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="Account details complete, No AVS verification done" 
                                                    data-bs-content="Account details complete, No AVS verification done"></i>
                                                </span>
                                                <span v-else-if="payment.beneficiary_account.verified != '1'">
                                                    <i class="far fa-square bg-green mr-2 mt-1 text-success" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="Account details complete, No AVS verification done" 
                                                    data-bs-content="Account details complete, No AVS verification done"></i>
                                                </span>
                                                {{ payment.description }}
                                            </div>
                                            <div class="col-md-3" @click="openViewPaymentModal(payment)" v-bind:style="payment.beneficiary_account && payment.beneficiary_account.authorised === 1 ? { background: '#f2f2f2', border: '1px solid #ddd', padding: '6px 12px', fontSize: '14px'/* , color: '#666' */ } : {}">
                                                <div>
                                                    <b>{{ payment.beneficiary_account?.account_holder_type === 'natural' 
                                                        ? payment.beneficiary_account?.initials + " " + payment.beneficiary_account?.surname 
                                                        : payment.beneficiary_account?.company_name }}
                                                    </b>
                                                </div>
                                                <div>
                                                    {{ payment.beneficiary_account?.institution?.short_name }} 
                                                    ({{ payment.beneficiary_account?.branch_code }}) - 
                                                    {{ payment.beneficiary_account?.account_number }}
                                                </div>
                                            </div>
                                            <div class="col-md-4" @click="openViewPaymentModal(payment)">
                                                <div><span class="text-secondary">My Ref:</span> {{ payment.my_reference }}</div>
                                                <div><span class="text-secondary">Recipient Ref:</span> {{ payment.recipient_reference }}</div>
                                            </div>
                                            <div class="col-md-2 pl-4" style="display:flex;justify-content:flex-end;">
                                                                                               
                                                - R{{ parseFloat(payment.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                                <span v-if="requisition && !requisition.locked" class="pull-right"> &nbsp;&nbsp;
                                                    <i class="fa fa-edit text-primary" @click="openEditPaymentModal(payment)"></i>
                                                </span>
                                                <span v-else class="pull-right"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                    
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-else class="txt-xs">No Payments have been added</div>
                                
                                    <div class="row ml-0 p-0 mb-0">
                                        <div class="col-md-3">
                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div><br/><br/></div>
                                            <div class="pull-right" v-if="requisition && requisition.deposits  && requisition.deposits.length">Net Balance: </div>
                                        </div>
                                        <div class="col-md-3 row pr-0">

                                            <div v-if="requisition && requisition.deposits && requisition.deposits.length > 0 && !requisition.payments">
                                                <hr class="mb-1"/>
                                                <div style="display:flex;justify-content:flex-end;">

                                                       &nbsp; R{{ parseFloat(totalDepositAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}                                                  
                                                    
                                                </div>
                                                <hr class="mb-0 mt-1"/>
                                            </div>
                                           
                                            <div v-if="requisition && requisition.deposits && requisition.deposits.length > 0 || requisition.payments && requisition.payments.length > 0" class="pl-0">
                                                <hr class="mb-1"/>
                                                <div 
                                                     :style="{ display: 'flex', flexDirection: 'row', justifyContent: requisition.deposits && requisition.deposits.length > 0 ? 'space-between' : 'flex-end' }"
                                                    class="mr-4"
                                                >

                                                    <div  v-if="requisition.deposits  && requisition.deposits.length > 0">&nbsp; R{{ parseFloat(totalDepositAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} </div>   <div v-if="requisition.payments && requisition.payments.length > 0">-  R{{ parseFloat(totalPaymentAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }} </div>
                                                    
                                                </div>
                                                <hr class="mb-0 mt-1"/>
                                                <span class="pull-right mr-4" v-if="requisition.payments  && requisition.payments.length > 0" :class="netBalance > 0 ? 'orange' : null">&nbsp; R {{ parseFloat(totalDepositAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                                                <div v-if="requisition.payments  && requisition.payments.length > 0  && requisition.status_id === 2" class="btn btn-white btn-default-default btn-sm mt-1" data-toggle="tooltip" data-placement="bottom" title="Balance the matter by adding a default source / deposit entry" @click="balancePaymentAndFund"><i class="fas fa-balance-scale"></i> Balance and Fund</div>
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
                            <div v-if="requisition.status_id == 3 && !requisition.authorization_status">
                                <PermissionControl :roles="['admin', 'authoriser']">
                                    <div class="approve-box p-2 pull-right" style="border: solid 1px #40b1c5;background: #eafcff;">
                                        <div class="pl-0 pr-0">
                                            <div class="txt-xs">Click here to approve as </div>
                                            <h6>{{ user.name }}</h6>
                                        </div>
                                        <div class=""><a class="btn btn-sm btn-primary" @click="approveRequisition(requisition.id)"><span id="approveBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Approve</a></div>
                                    </div>
                                </PermissionControl>
                                <PermissionControl :roles="['user']" v-if="requisition.locked">
                                    <div class="approve-box p-2 pull-right">
                                        <div class="txt-xs">Approval required by Authoriser</div>
                                        <div><a class="btn btn-sm btn-white btn-default-default disabled">Approve</a></div>
                                    </div>
                                </PermissionControl>
                                
                            </div>
                            <div v-else-if="requisition.status_id >= 5 || requisition.authorization_status">
                                <div class="approve-box p-2 pull-right" style="border: solid 1px #40b1c5;background: #eafcff;justify-content: flex-start;">
                                    <div class=""> <i class="fa fa-check mr-2 text-success"></i></div>
                                    <div class="pl-0 pr-0">
                                        <h6>{{ requisition.authorized_by.name }}</h6>
                                        <div class="txt-xs">on {{ formatDateAndTime(requisition?.authorized_at) }} </div>
                                        <div class="txt-xs">logged in as {{ requisition?.authorized_by.email }} </div>
                                    </div>
                                    
                                </div>
                            </div>
                            <div v-else class="txt-xs">Not ready, capture incomplete.</div>
                        </div>
                        
                    </div>

                    <div class="card mt-0" style="display: flex; flex-direction: row;">
                        <div class="card-body"> 
                            <span class="btn btn-sm btn-white btn-default-default mr-1" @click="cancel"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to List</span> 
                            <router-link to="/requisition/new" class="btn btn-primary btn-sm"><i class="fa fa-plus" aria-hidden="true"></i> New Requisition</router-link>
                        </div>
                        <div class="pr-3">
                            
                            <div class="input-group mb-3 mt-2">  
                                <p v-if="searchError" class="text-danger mr-2">{{ searchError }}</p>                              
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="File reference" 
                                    v-model="searchQuery"
                                    @keyup.enter="searchRequisition"
                                />
                                <div class="input-group-append">
                                    <button class="btn btn-primary" @click="searchRequisition" type="button"><span id="searchBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Go</button>
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
                        <button class="btn btn-white btn-sm" @click="openUploadModal" v-if="!this.requisition.locked"><i class="fa fa-plus" aria-hidden="true"></i> New Document</button>
                    </div>
                    <div class="card-body">
                        <!-- Documents DataTable -->
                        
                        <table id="documents-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                            <thead>
                                <tr class="table-secondary">
                                    <th width="15%">User</th>
                                    <th>Description</th>
                                    <th>File Name</th>
                                    <th width="15%">Date Uploaded</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                        </table>
                        
                    </div>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div class="tab-pane fade" id="notifications" role="tabpanel" aria-labelledby="notifications-tab">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h6>Notifications Content</h6>
                    </div>
                    <div class="card-body">

                        <div class="alert alert-info p-2" role="alert">
                            <div>Select contacts to receive email ( <i class="fa fa-envelope"></i> ) or SMS ( <i class="fa fa-phone"></i> ) notifications.</div>
                            <div class="d-none">No cell phone number has been captured for you. Ask the firm administrator to capture your cell phone number in the system.</div>
                        </div>
                        
                        <!-- Notifications content goes here -->
                        <div class="">
                            <form @submit.prevent="saveNotifications">
                                <div v-for="(field, key) in notificationFields" :key="key" class="row mb-3">
                                    <label class="col-sm-2 col-form-label form-label" :for="key">{{ field.label }}</label>
                                    <div class="col-sm-10">
                                        <vue-multiselect
                                            v-model="field.selected"
                                            :options="users"
                                            :multiple="true"
                                            :close-on-select="false"
                                            :clear-on-select="false"
                                            label="name"
                                            track-by="id"
                                            placeholder="Add contact"
                                            class="notification-multiselect"
                                        ></vue-multiselect>

                                    </div>
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-10">
                                        <button type="button" @click="addCurrentUser(key)" class="btn btn-link btn-sm text-primary">
                                            Add me: <i class="fa fa-envelope"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary"><span id="buttonSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Save</button>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- History Log Tab -->
            <div class="tab-pane fade" id="history-log" role="tabpanel" aria-labelledby="history-log-tab">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h6>History Log</h6>
                        <button class="btn btn-white btn-sm" @click="openUploadModal">Show More Details</button>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="history-table">
                            <thead>
                                <tr class="table-secondary">
                                    <th>User Name</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
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
                        <h5 class="modal-title" id="sourceAccountModalLabel">Change a Source Account</h5>
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
                                                <th width="10%">Authorisations</th>
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
                        The maximum file size is 20 MB. Only PDFs and images may be uploaded.
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
                            <button type="submit" class="btn btn-primary"><span id="uploadbuttonSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Upload</button>
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
                                <input type="text" v-model="depositForm.description" class="form-control" id="description" minlength="2"  maxlength="150"  required placeholder="Enter a description for the entry">
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
                        <div class="row mb-2 position-relative">    
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
                                <span>
                                    <i :class="account.authorised === 1 ? 'fa fa-check text-success' : 'fa fa-university'" aria-hidden="true"></i>&nbsp;
                                </span>
                                <!-- Render display_text with highlighted search term -->
                                <span v-html="highlightMatch(account.display_text, paymentForm.search)"></span> |

                                <!-- Render institution.short_name with highlighted search term -->
                                <span v-html="highlightMatch(account?.institution?.short_name, paymentForm.search)"></span> -

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
                        <div class="mb-2 row">
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
                                        <option v-for="beneficiary in categoryBeneficiaries" :key="beneficiary.id" :value="`${beneficiary.id}_${beneficiary.account_number}`">{{ beneficiary.display_text }}</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <!-- Hidden section for authorised Beneficiary Account details (visible when an authorised beneficiary account is selected) -->
                        <div v-if="showAccountDetails && beneficiaryDetails && beneficiaryDetails.authorised" class="mb-4" style="background: rgb(242, 242, 242);border: 1px solid rgb(221, 221, 221);padding: 6px 6px 6px 12px;font-size: 14px;color: rgb(102, 102, 102);">
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <p>Beneficiary Account</p>
                                </div>
                                <div class="col-sm-9"></div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <h6>Payment Institution:</h6>
                                </div>
                                <div class="col-sm-9">
                                    {{ beneficiaryDetails.institution.short_name }}
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <h6>Account Details:</h6>
                                </div>
                                <div class="col-sm-9">
                                    {{ beneficiaryDetails.account_number }} ({{ beneficiaryDetails.branch_code }})
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <h6>Account Holder:</h6>
                                </div>
                                <div class="col-sm-9">
                                    {{ beneficiaryDetails.display_text }}
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-3">
                                    <h6>Authorisations:</h6>
                                </div>
                                <div class="col-sm-9">
                                    {{ beneficiaryDetails.authorisations }} <a href="#"  @click="toggleCollapseApproveShow" data-bs-target="#approveDropdown" :aria-expanded="showApprove" aria-controls="approveDropdown">Show more</a><br/>
                                    
                                    <div :class="['collapse', { show: showPayments }]" id="approveDropdown">
                                        <!-- Loop through each authorizer and create an approve-box for each -->
                                        <div v-for="(authorizer, index) in beneficiaryDetails.authorizers" :key="index" class="approve-box p-2 mb-2" style="border: solid 1px #40b1c5; background: #eafcff; justify-content: flex-start;">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-check mr-2 text-success"></i>
                                                <div class="pl-0 pr-0">
                                                    <h6>{{ authorizer?.user?.name }}</h6>
                                                    <div class="txt-xs">on {{ formatDate(authorizer?.created_at) }}</div>
                                                    <div class="txt-xs">logged in as {{ authorizer?.user?.email }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden section for New Account details (visible when a new account is selected) -->
                        <div v-else>
                            <h6 class="mb-1">Account Details (Ad-hoc)</h6>
                            <hr class="mt-0"/>
                            <div class="mb-3 row">
                                <label for="account_number" class="form-label col-sm-3">Account No.: *</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="paymentForm.account_number" class="form-control" required placeholder="Enter the account number" @input="validateAccountNumber">
                                    <div class="round mt-1" :style="{background:'#f2f2f2',border:'solid 1px #ddd',padding:'6px',paddingLeft:'12px',fontSize: '14px',color: (paymentForm.payments && paymentForm.payments.length > 0) ? '#097386' : '#666'}" >

                                        <b>Previously Paid:</b> {{paymentForm.previously_paid}}
                                        <button v-if="paymentForm && paymentForm.payments" class="btn btn-link p-0 float-end" type="button"  @click="toggleCollapsePaymentShow" data-bs-target="#previousPaymentsDropdown" :aria-expanded="showPayments" aria-controls="previousPaymentsDropdown">
                                            <i :class="showPayments ? 'fa fa-chevron-up' : 'fa fa-chevron-down'"></i>
                                        </button>

                                        <!-- Collapsible list of previous payments -->
                                        <div :class="['collapse', { show: showPayments }]" id="previousPaymentsDropdown" v-if="paymentForm && paymentForm.payments">
                                            <ul class="list-unstyled mt-2">
                                                <li v-for="payment in paymentForm.payments.slice(0, 3)" :key="payment.id" class="row">
                                                    <div class="col-md-4"><i class="far fa-check-square bg-green mr-2" aria-hidden="true"></i> {{ formatDate(payment.created_at) }}</div>
                                                    <div class="col-md-4">R{{ parseFloat( payment.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
                                                    <div class="col-md-4"><a :href="`/matters/requisitions/${payment.requisition_id}/details`">{{ payment.description }}</a></div>                                                    
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-0 row">
                                <label for="account_holder" v-if="this.paymentForm.account_holder_type" class="form-label col-sm-3">Account Holder: *</label>
                                <div class="col-sm-9 row pr-0 mb-1" v-if="this.paymentForm.account_holder_type == 'natural'">
                                    <div class="col-sm-2 pr-0" id="initials">
                                        <input type="text" v-model="paymentForm.initials" class="form-control" placeholder="Initials" required>
                                    </div>
                                    <div class="col-sm-10 pr-0 mb-1">
                                        <input type="text" v-model="paymentForm.surname" class="form-control" placeholder="Surname" required>
                                    </div>
                                </div>
                                <div class="col-sm-9 mb-2" v-if="this.paymentForm.account_holder_type == 'juristic'">
                                    <input type="text" v-model="paymentForm.company_name" class="form-control" placeholder="Enter the name of the Company">
                                </div>
                                
                            </div>
                            
                            <div class="mb-2 row" v-if="this.paymentForm.account_holder_type == 'natural'">
                                <label for="id_number" class="form-label col-sm-3">ID No. / Passport No.:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="paymentForm.id_number" class="form-control" placeholder="Enter ID Number or leave blank if not known">
                                </div>
                            </div>
                            <div class="mb-2 row" v-if="this.paymentForm.account_holder_type == 'juristic'">
                                <label for="id_number" class="form-label col-sm-3">Registration No.:</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="paymentForm.registration_number" class="form-control" 
                                        @input="validateRegistrationNumber"
                                        maxlength="15"
                                        placeholder="Enter registration no 1234/123456/12 or leave blank if not applicable"
                                    >
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label for="account_type" class="form-label col-sm-3">Account Type: *</label>
                                <div class="col-sm-9">
                                    <select v-model="paymentForm.account_type.id" class="form-select" required>
                                        <option v-for="account_type in accountTypes" :key="account_type.id" :value="account_type.id">{{ account_type.name }}</option>
                                    </select>
                                    <!-- <input type="text" v-model="paymentForm.account_type" class="form-control" required> -->
                                </div>
                            </div>
                            <div class="mb-2 row">
                                <label for="institution" class="form-label col-sm-3">Institution: *</label>
                                <div class="col-sm-9">
                                    <select v-model="paymentForm.institution.id" class="form-select" required @change="onInstitutionChange">
                                        <option v-for="institution in institutions" :key="institution.id" :value="institution.id">{{ institution.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2 row">
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
                                            <span v-if="paymentForm.verified === 1">
                                                <input  class="form-check-input" type="checkbox" id="create-payment-verify-Check"  :checked="paymentForm.verified === 1" :disabled="paymentForm.verified === 1">
                                                <label class="form-check-label" for="gridCheck">
                                                    Completed for account holder and account details
                                                </label>
                                            </span>
                                            <span v-else>
                                                <input class="form-check-input" type="checkbox" id="create-payment-verify-Check" v-model="paymentForm.verified">
                                                <label class="form-check-label" for="gridCheck">
                                                    Verify account holder and account details
                                                </label>
                                            </span>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        
                        <!-- Transaction Information -->
                        <h6 class="mb-1">Transaction Information</h6>
                        <hr class="mt-0"/>
                        <div class="mb-2 row">
                            <label for="description" class="form-label col-sm-3">Description: *</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="paymentForm.description" class="form-control" minlength="2" maxlength="150"  required placeholder="Enter a description for the entry">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="amount" class="form-label col-sm-3">Amount: *</label>
                            <div class="col-sm-9">
                                <input type="number" step="0.01" v-model="paymentForm.amount" class="form-control" required placeholder="Enter the amount available for the transaction">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="my_reference" class="form-label col-sm-3">My Reference: *</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="paymentForm.my_reference" class="form-control" maxlength="20"  placeholder="Enter a reference for our bank account">
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label for="recipient_reference" class="form-label col-sm-3">Recipient Reference:</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="paymentForm.recipient_reference" class="form-control" maxlength="20"  placeholder="Enter a reference for the recipient's bank account">
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="createAndNewPayment">Save</button>
                            <button type="button" class="btn btn-primary" @click="createAndNewPayment">Save & New</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

     <!-- Edit Payment Modal -->
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
                                        <option v-for="beneficiary in categoryBeneficiaries" :key="beneficiary.id" :value="`${beneficiary.id}_${beneficiary.account_number}`">{{ beneficiary.display_text }}</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Hidden section for New Account details (visible when a new account is selected) -->
                        <div v-if="showEditAccountDetails">
                            <div class="mb-1 mt-4">Account Details (Ad-hoc) 
                                <span class="pull-right text-danger" v-if="editPaymentForm.verification_status == 'failed'">
                                    <i class="fas fa-times-circle mr-0 mt-1 text-danger" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="AVS verification failed, Invalid account / details" 
                                                    data-bs-content="AVS verification failed, Invalid account / details"></i>
                                                    AVS {{ 'Warning - Invalid Account details' }} <span class="btn btn-default-default btn-sm text-primary" @click="viewAVSResult(editPaymentForm)">View</span>
                                </span>
                                <span class="pull-right text-success" v-if="editPaymentForm.verification_status == 'successful'">
                                    <i class="far fa-check-circle mr-0 mt-1 text-success" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="Account details complete, AVS verified account" 
                                                    data-bs-content="Account details complete, AVS verified account"></i>
                                                    AVS - {{ 'Successfully Matched' }} <span class="btn btn-default-default btn-sm text-primary" @click="viewAVSResult(editPaymentForm)">View</span>
                                </span>
                                <span class="pull-right text-primary" v-if="editPaymentForm.verification_status == 'pending'">
                                    <i class="fas fa-info-circle mr-0 mt-1 text-primary" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="AVS Account verificaiton pending" 
                                                    data-bs-content="AVS Account verificaiton pending"></i>
                                                    AVS - {{ 'Account verificaiton pending' }} <span class="btn btn-default-default btn-sm text-primary" @click="viewAVSResult(editPaymentForm)">View</span>
                                </span>
                            </div>
                            <hr class="mt-3"/>
                            <div class="mb-3 row">
                                <label for="account_number" class="form-label col-sm-3">Account No.: *</label>
                                <div class="col-sm-9">
                                    <input type="text" v-model="editPaymentForm.account_number" class="form-control" required placeholder="Enter the account number" @input="validateAccountNumber">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="account_holder" v-if="this.editPaymentForm.account_holder_type" class="form-label col-sm-3">Account Holder: *</label>
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
                                    <input type="text" v-model="editPaymentForm.registration_number" class="form-control" 
                                        @input="validateRegistrationNumber"
                                        maxlength="15"
                                        placeholder="Enter registration no 1234/123456/12 or leave blank if not applicable"
                                    >
                                    
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

                            <div class="mb-3 row" v-if="editPaymentForm.verified !== 1">
                                <label for="branch_code" class="form-label col-sm-3">verification:</label>
                                <div class="col-sm-9">
                                    <div class="pl-2" style="background-color:#eee;border-radius: 5px;">
                                        <div class="form-check pt-2 pb-2">
                                            <span>
                                                <input class="form-check-input" type="checkbox" id="gridCheck" v-model="editPaymentForm.verified">
                                                <label class="form-check-label" for="gridCheck">
                                                    Verify account holder and account details
                                                </label>
                                            </span>
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
                                <input type="text" v-model="editPaymentForm.my_reference" class="form-control" maxlength="20" placeholder="Enter a reference for our bank account">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="recipient_reference" class="form-label col-sm-3">Recipient Reference:</label>
                            <div class="col-sm-9">
                                <input type="text" v-model="editPaymentForm.recipient_reference" class="form-control" maxlength="20" placeholder="Enter a reference for the recipient's bank account">
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

    <!-- View Payment Modal -->
    <div class="modal fade" id="viewPaymentModal" tabindex="-1" aria-labelledby="viewPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewPaymentModalLabel">View Payment <span class="txt-xs text-white">for payment number # {{ viewPaymentForm.id }}</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form>
                                               
                        <!-- Category -->
                        <div class="mb-1 row">
                            <label for="category" class="form-label col-sm-3">Category: </label>
                            <div class="col-sm-9 text-secondary">
                                {{ viewPaymentForm.category_name }}
                                
                            </div>
                        </div>
                        
                        <!-- Account -->
                        <div class="mb-1 row">
                            <label for="account" class="form-label col-sm-3">Account:</label>
                            <div class="col-sm-9 text-secondary">
                                -- {{ viewPaymentForm.account_holder_type }} Person --
                            </div>
                        </div>
                        
                        <!-- Hidden section for New Account details (visible when a new account is selected) -->
                        <div v-if="showEditAccountDetails">
                            <div class="mb-1 mt-4">Account Details (Ad-hoc) 
                                <span class="pull-right text-danger" v-if="viewPaymentForm.verification_status == 'failed'">
                                    <i class="fas fa-times-circle mr-0 mt-1 text-danger" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="AVS verification failed, Invalid account / details" 
                                                    data-bs-content="AVS verification failed, Invalid account / details"></i>
                                                    AVS {{ 'Warning - Invalid Account details' }} <span class="btn btn-default-default btn-sm text-primary" @click="viewAVSResult(viewPaymentForm)">View</span>
                                </span>
                                <span class="pull-right text-success" v-if="viewPaymentForm.verification_status == 'successful'">
                                    <i class="far fa-check-circle mr-0 mt-1 text-success" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="Account details complete, AVS verified account" 
                                                    data-bs-content="Account details complete, AVS verified account"></i>
                                                    AVS - {{ 'Successfully Matched' }} <span class="btn btn-default-default btn-sm text-primary" @click="viewAVSResult(viewPaymentForm)">View</span>
                                </span>
                                <span class="pull-right text-primary" v-if="viewPaymentForm.verification_status == 'pending'">
                                    <i class="fas fa-info-circle mr-0 mt-1 text-primary" ref="popoverIcon" 
                                                    data-bs-toggle="popover" 
                                                    data-bs-placement="top" 
                                                    title="AVS Account verificaiton pending" 
                                                    data-bs-content="AVS Account verificaiton pending"></i>
                                                    AVS - {{ 'Account verificaiton pending' }} <span class="btn btn-default-default btn-sm text-primary" @click="viewAVSResult(viewPaymentForm)">View</span>
                                </span>
                            </div>
                            <hr class="mt-3"/>
                            <div class="mb-1 row">
                                <label for="account_number" class="form-label col-sm-3">Account No.: </label>
                                <div class="col-sm-9 text-secondary">
                                    {{ viewPaymentForm.account_number }}

                                    <div class="round mt-1" :style="{background:'#f2f2f2',border:'solid 1px #ddd',padding:'6px',paddingLeft:'12px',fontSize: '14px',color: (paymentForm.payments && paymentForm.payments.length > 0) ? '#097386' : '#666'}" >

                                        <b>Previously Paid:</b> {{paymentForm.previously_paid}}
                                        <button v-if="paymentForm && paymentForm.payments" class="btn btn-link p-0 float-end" type="button"  @click="toggleCollapsePaymentShow" data-bs-target="#previousPaymentsDropdown" :aria-expanded="showPayments" aria-controls="previousPaymentsDropdown">
                                            <i :class="showPayments ? 'fa fa-chevron-up' : 'fa fa-chevron-down'"></i>
                                        </button>

                                        <!-- Collapsible list of previous payments -->
                                        <div :class="['collapse', { show: showPayments }]" id="previousPaymentsDropdown" v-if="paymentForm && paymentForm.payments">
                                            <ul class="list-unstyled mt-2">
                                                <li v-for="payment in paymentForm.payments.slice(0, 3)" :key="payment.id" class="row">
                                                    <div class="col-md-4"><i class="far fa-check-square bg-green mr-2" aria-hidden="true"></i> {{ formatDate(payment.created_at) }}</div>
                                                    <div class="col-md-4">R{{ payment.amount }}</div>
                                                    <div class="col-md-4"><a :href="`/matters/requisitions/${payment.requisition_id}/details`">{{ payment.description }}</a></div>                                                    
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-1 row">
                                <label for="account_holder" v-if="this.viewPaymentForm.account_holder_type" class="form-label col-sm-3">Account Holder: </label>
                                <div class="col-sm-9 text-secondary pr-0" v-if="this.viewPaymentForm.account_holder_type == 'natural'">
                                    {{ viewPaymentForm.initials }} {{ viewPaymentForm.surname }}
                                    
                                </div>
                                <div class="col-sm-9 text-secondary" v-if="this.viewPaymentForm.account_holder_type == 'juristic'">
                                    {{ viewPaymentForm.company_name }}
                                   
                                </div>
                                
                            </div>
                            
                            <div class="mb-1 row" v-if="this.viewPaymentForm.account_holder_type == 'natural'">
                                <label for="id_number" class="form-label col-sm-3">ID No. / Passport No.:</label>
                                <div class="col-sm-9 text-secondary">
                                    {{ viewPaymentForm.id_number }}
                                </div>
                            </div>
                            <div class="mb-1 row" v-if="this.viewPaymentForm.account_holder_type == 'juristic'">
                                <label for="id_number" class="form-label col-sm-3">Registration No.:</label>
                                <div class="col-sm-9 text-secondary">
                                    {{ viewPaymentForm.registration_number }}
                                    
                                </div>
                            </div>
                            <div class="mb-1 row">
                                <label for="account_type" class="form-label col-sm-3">Account Type: </label>
                                <div class="col-sm-9 text-secondary">
                                    {{ viewPaymentForm.account_type.name }}
                                </div>
                            </div>
                            <div class="mb-1 row">
                                <label for="institution" class="form-label col-sm-3">Institution: </label>
                                <div class="col-sm-9 text-secondary">

                                    {{ viewPaymentForm.institution.name }}
                                    
                                </div>
                            </div>
                            <div class="mb-1 row">
                                <label for="branch_code" class="form-label col-sm-3">Branch Code: </label>
                                <div class="col-sm-9 text-secondary">
                                    {{ viewPaymentForm.branch_code }}
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Transaction Information -->
                        <div class="mb-1 mt-4">Transaction Information</div>
                        <hr class="mt-0"/>
                        <div class="mb-2 row">
                            <label for="description" class="form-label col-sm-3">Description: </label>
                            <div class="col-sm-9 text-secondary">
                                {{ viewPaymentForm.description }}
                                
                            </div>
                        </div>
                        <div class="mb-1 row">
                            <label for="amount" class="form-label col-sm-3">Amount: </label>
                            <div class="col-sm-9 text-secondary">
                                R{{ parseFloat(viewPaymentForm.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                
                            </div>
                        </div>
                        <div class="mb-1 row">
                            <label for="my_reference" class="form-label col-sm-3">My Reference: </label>
                            <div class="col-sm-9 text-secondary">
                                {{ viewPaymentForm.my_reference }}
                                
                            </div>
                        </div>
                        <div class="mb-1 row">
                            <label for="recipient_reference" class="form-label col-sm-3">Recipient Reference:</label>
                            <div class="col-sm-9 text-secondary">
                                {{ viewPaymentForm.recipient_reference }}
                                
                            </div>
                        </div>
                        <!-- Payment Status -->
                        <div class="mb-1 mt-4">Payment Status</div>
                        <hr class="mt-0"/>
                        <div class="mb-1 row">
                            <label for="description" class="form-label col-sm-3">Status Date: </label>
                            <div class="col-sm-9 text-secondary">
                                -- {{ 'Not Available' }} --
                                
                            </div>
                        </div>
                        <div class="mb-1 row">
                            <label for="amount" class="form-label col-sm-3">File Status Description: </label>
                            <div class="col-sm-9 text-secondary">
                                {{  }}
                                
                            </div>
                        </div>
                        <hr class="mt-0 mb-0"/>
                        <div class="mb-0 mt-2 row">
                            <div class="col-sm-3">
                                
                            </div>
                            <div class="form-check col-sm-9">
                               
                                <button type="button" class="btn btn-secondary pull-right btn-sm" data-bs-dismiss="modal" @click="closeModal">Cancel</button>
                                
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- AVS Result Modal -->
    <div class="modal fade" id="avsResultModal" tabindex="-1" aria-labelledby="avsResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="avsResultModalLabel">Account Holder Verification {{ avsResult ? avsResult.avs_verified_at : null }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div v-if="avsResult && avsResult.avs_verified_at" class="account-verification-result">
                        <!-- <p class="form-label mt-3 mb-4">The entry was successfully saved</p> -->
                        <div class="alert alert-success p-2 mb-4" role="alert" v-if="getAvsStatus(
                            avsResult.account_found, 
                            avsResult.account_open, 
                            avsResult.account_open_gt_three_months,
                            avsResult.branch_code_match
                            )">
                            <h6>The account holder matched the account details</h6>
                        </div>
                        <div class="alert alert-danger p-2 mb-4" role="alert" v-else>
                            <h6>The account information fields did not match</h6>
                        </div>
                        <div>Account Results 
                            <span class="pull-right">

                                <span  class="text-success mr-4" v-if="getAvsStatus(avsResult.account_found)"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i>Found</span>
                                <span  class="text-danger mr-4" v-else><i class="fas fa-times-circle mr-1" aria-hidden="true"></i>Found</span>

                                <span  class="text-success" v-if="getAvsStatus(avsResult.account_open_gt_three_months)"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i>Open (for 3+ months)</span>
                                <span  class="text-danger" v-else><i class="fas fa-times-circle mr-1" aria-hidden="true"></i>Open (for 3+ months)</span>
                                
                            </span>
                        </div>
                        <hr class="mt-0 mb-2">
                        <div class="row mb-0">
                                <div class="col-sm-3"><span class="pull-right"><strong>Number:</strong></span></div>
                                <div class="col-sm-5 text-secondary">{{ avsResult.account_number }}</div>
                                <div class="col-sm-4">
                                    <span class="text-success" v-if="getAvsStatus(avsResult.account_found)"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span>
                                    <span class="text-danger" v-else><i class="fas fa-times-circle mr-1" aria-hidden="true"></i> Matched</span>
                                </div>
                        </div>
                        <div class="row mb-0">
                                <div class="col-sm-3"><span class="pull-right"><strong>Branch Code:</strong></span></div>
                                <div class="col-sm-5 text-secondary">{{ avsResult?.branch_code }}</div>
                                <div class="col-sm-4">
                                    <span class="text-success" v-if="getAvsStatus(avsResult.branch_code_match)"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span>
                                    <span class="text-danger" v-else><i class="fas fa-times-circle mr-1" aria-hidden="true"></i> Matched</span>
                                </div>
                        </div>
                        <div class="row mb-0">
                                <div class="col-sm-3"><span class="pull-right"><strong>Account Type:</strong></span></div>
                                <div class="col-sm-5 text-secondary">{{ avsResult?.account_type?.name }}</div>
                                <div class="col-sm-4">
                                    <span class="text-success" v-if="getAvsStatus(avsResult.account_found)"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span>
                                    <span class="text-danger" v-else><i class="fas fa-times-circle mr-1" aria-hidden="true"></i> Matched</span>
                                </div>
                        </div>
                        <br/><br/>
                        <div>Account Holder Results</div>
                        <hr class="mt-0 mb-2">
                        <div v-if="avsResult && avsResult.account_holder_type == 'natural'">
                            <div class="row mb-0">
                                <div class="col-sm-3"><span class="pull-right"><strong>Initials:</strong></span></div>
                                <div class="col-sm-5 text-secondary">{{ avsResult.initials }}</div>
                                <div class="col-sm-4"> 
                                    <span class="text-success" v-if="getAvsStatus(avsResult.holder_initials_match)"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span>
                                    <span class="text-danger" v-else><i class="fas fa-times-circle mr-1" aria-hidden="true"></i> Matched</span>
                                </div>
                            </div>
                            <div class="row mb-0">
                                <div class="col-sm-3"><span class="pull-right"><strong>Name:</strong></span></div>
                                <div class="col-sm-5 text-secondary">{{ avsResult.surname }}</div>
                                <div class="col-sm-4">
                                    <span class="text-success" v-if="getAvsStatus(avsResult.holder_name_match)"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span>
                                    <span class="text-danger" v-else><i class="fas fa-times-circle mr-1" aria-hidden="true"></i> Matched</span>
                                </div>
                            </div>
                            <div class="row mb-0">
                                <div class="col-sm-3"><span class="pull-right"><strong>Id Number:</strong></span></div>
                                <div class="col-sm-5 text-secondary">{{ avsResult.id_number }}</div>
                                <div class="col-sm-4">
                                    <span class="text-fade"><i class="far fa-square mr-1 disabled" aria-hidden="true"></i> Not Supplied</span>
                                    
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div class="row mb-0">
                                <div class="col-sm-3"><span class="pull-right"><strong>Name:</strong></span></div>
                                <div class="col-sm-5 text-secondary">{{ avsResult.company_name }}</div>
                                <div class="col-sm-4"> 
                                    <span class="text-success" v-if="getAvsStatus(avsResult.holder_matched)"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span>
                                    <span class="text-danger" v-else><i class="fas fa-times-circle mr-1" aria-hidden="true"></i> Matched</span>
                                </div>
                            </div>
                            <div class="row mb-0">
                                <div class="col-sm-3"><span class="pull-right"><strong>Registration No.:</strong></span></div>
                                <div class="col-sm-5 text-secondary">{{ avsResult.registration_number }}</div>
                                <div class="col-sm-4"> 
                                    <span class="text-fade" v-if="!avsResult.registration_number"><i class="far fa-square mr-1 disabled" aria-hidden="true"></i> Not Supplied</span>

                                </div>
                            </div>
                        </div>
                       
                       
                       <!--  <div class="alert alert-success" v-if="avsResult.match">
                            The account holder matched the account details
                        </div> -->

                        
                       <!--  <p><strong>Number:</strong> {{ avsResult.account_number }} <span v-if="avsResult.found" class="text-success">✔ Found</span> <span v-if="avsResult.open" class="text-success">✔ Open (for 3+ months)</span></p>
                        <p><strong>Branch Code:</strong> {{ avsResult.branch_code }}</p>
                        <p><strong>Account Type:</strong> {{ avsResult.account_type }} <span v-if="avsResult.matched" class="text-success">✔ Matched</span></p>

                        <h6>Account Holder Results</h6>
                        <p><strong>Name:</strong> {{ avsResult.holder_name }} <span v-if="avsResult.holder_matched" class="text-success">✔ Matched</span></p>
                        <p><strong>Registration No.:</strong> {{ avsResult.registration_no || 'Not Supplied' }}</p> -->
                    </div>
                    <div v-else class="account-verification-inprocess">
                        <p class="mt-3 mb-4 text-secondary">The entry was successfully saved</p>
                        <div class="alert alert-info p-2 pb-0" role="alert">
                            <h6 class="text-primary"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying AVS</h6>
                        </div>
                        <p class="mt-4 mb-0 text-secondary">This may take up to <b>2 minutes</b>. You may close this dialog and continue working, editing this entry later to see the result</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" @click="closeModal">Close</button>
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
import { useToast } from 'vue-toastification';
import VueMultiselect from "vue-multiselect";
import 'vue-multiselect/dist/vue-multiselect.min.css'; // Import styles
import { useRequisitionStore } from '../store/datastore';

export default {
    name: 'RequisitionDetails',
    //components: { VueMultiselect },
    props: {
        requisitionId: {
            type: Number,
            required: true
        },
        user: {
            type: Object,
            required: true
        },
        
    },
    
    components: {
        PermissionControl, VueMultiselect
    },
    data() {
        return {
            users: [
                { id: 1, name: "Default User 1" },
                { id: 2, name: "Default User 2" },
            ], // Users to populate multiselect
            notificationFields: {
                matter_authorised: {
                label: "Matter Authorised",
                selected: [],
                },
                matter_unlocked: {
                label: "Matter Unlocked",
                selected: [],
                },
                payment_successful: {
                label: "Payment Processing Successful",
                selected: [],
                },
                payment_failed: {
                label: "Payment Processing Failed",
                selected: [],
                },
            },

            loading: true,
            showPayments: false,
            showApprove: false,
            loadingHtml: '<div class="loading-spinner" style="position:fixed;top:50%;left: 50%;transform: translate(-50%, -50%);font-size: 2em;color: #0097b2bf;text-align: center;z-index: 1000;background: rgba(64, 177, 197, 0.05);padding: 40px;border: 5px;"><i class="fa fa-spinner fa-spin"></i> Loading...</div>',
            contentHtml: '',
            requisition: {},  // Initialize as an empty object
            avsResult: {},  // Store the AVS result data
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
            viewPaymentModalInstance: null,
            createPaymentModalInstance: null,
            beneficiaryDetails: null, // Object to hold selected beneficiary details
            showAvsModelInstance: null,
            documentForm: {
                description: '',
                file: null
            },
            formatDate(dateString) {
                return moment(dateString).format('DD MMM YYYY');
            },
            formatDateAndTime(dateString) {
                return moment(dateString).format('Y-MM-D h:m:s');
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
            showBeneficiaryDetails: false,
            showEditAccountDetails: true,
            showAvsModal: false,  // Control visibility of the AVS Result Modal
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
                verification_status: '',
                description: '',
                amount: '',
                my_reference: '',  // Example reference
                recipient_reference: '',
                requisition_id: '',
                firm_account_id: '',
                previously_paid: '',
                payments: '',
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
                verification_status: '',
                description: '',
                amount: '',
                my_reference: '',  // Example reference
                recipient_reference: '',
                requisition_id: '',
                firm_account_id: '',
            },
            viewPaymentForm: {
                category_name: '',
                category_id: '',
                account_holder_type: '',
                account_number: '',
                initials: '',
                surname: '',
                company_name: '',
                id_number: '',
                registration_number: '',
                account_type: { id: null, name: null},
                institution: { id: null},
                branch_code: '',
                verified: null,
                verification_status: null,
                avs_verified_at: null,
                description: '',
                amount: '',
                my_reference: '',  // Example reference
                recipient_reference: '',
                requisition_id: '',
                firm_account_id: '',
            },
            searchQuery: "",
            searchError: "",
            errors: {}
        };
    },
    computed: {
        totalDepositAmount() {
            if (this.requisition.deposits && this.requisition.deposits.length > 0) {
                return this.requisition.deposits.reduce((sum, deposit) => {
                    return parseFloat(parseFloat(sum, 2) + parseFloat(deposit.amount, 2)).toFixed(2);
                }, 0);
            }
            return 0;  // Return 0 if no deposits
        },
        totalPaymentAmount() {
            if (this.requisition.payments && this.requisition.payments.length > 0) {
                return this.requisition.payments.reduce((sum, payment) => {
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
        this.fetchUsers();
        this.fetchSavedNotifications();
       /*  this.showPaymentsTabContent(); */
        this.loadHistoryData();
        //this.checkStoredSourceAccount(); // Check if there is a stored source account
    },
    setup() {
        // Initialize toast
        const toast = useToast();
        return { toast };
    },
    created() {
        // Fetch requisition data when the component is created
        //const requisitionStore = useRequisitionStore();
        //this.requisition = requisitionStore.requisition;
    },
    watch: {
        categories: {
            handler() {
                if (this.viewPaymentForm.category_id) {
                    let selectedCategory = this.categories.find(cat => cat.id === this.viewPaymentForm.category_id);
                    this.viewPaymentForm.category_name = selectedCategory ? selectedCategory.name : '';
                }
            },
            deep: true,
            immediate: true
        }
    },
    methods: {
        getAvsStatus(...codes) {
            return codes.every(code => code === "00");
        },
        /**
         * Returns AVS result based on AVS code.
         * @param {string} code - The AVS code (00, 01, 99).
         * @returns {string} - "Matched", "Did Not Match", or "Unable to Verify".
         */
        getAvsStatusDetail(code) {
            if (code === "00") {
                return "Matched";
            } else if (code === "01") {
                return "Did Not Match";
            } else if (code === "99") {
                return "Unable to Verify";
            } else {
                return "Unknown Status";
            }
        },
        validateRegistrationNumber() {
            const value = this.paymentForm.registration_number;
            const pattern = /^\d{4}\/\d{6}\/\d{2}$/;

            // If the input doesn't match the pattern, show an error message
            if (!pattern.test(value)) {
                this.paymentForm.registration_number = value.replace(/[^0-9/]/g, ''); // Allow only numbers and "/"
            }
        },
        validateAccountNumber(event) {
            // Remove any non-numeric characters
            this.editPaymentForm.account_number = this.editPaymentForm.account_number.replace(/\D/g, '');
            this.paymentForm.account_number = this.paymentForm.account_number.replace(/\D/g, '');
        },
        // Fetch all users for selection
        fetchUsers() {
            axios.get('/api/recipients')
                .then(response => {
                    this.users = response.data;
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                });
       /*  axios
            .get("/api/users")
            .then((response) => {
            this.users = response.data;
            })
            .catch((error) => console.error("Error fetching users:", error)); */
        },
        // Fetch saved notification settings
        fetchSavedNotifications() {
            axios
                .get(`/api/requisitions/${this.requisitionId}/notifications`)
                .then((response) => {
                for (const key in response.data) {

                    if (this.notificationFields[key]) {
                    this.notificationFields[key].selected = response.data[key];
                    }
                }
                })
                .catch((error) =>
                console.error("Error fetching saved notifications:", error)
                );
        },
        // Add the current user to the notification list
        addCurrentUser(fieldKey) {
            /* const currentUser = this.users.find(
                (user) => user.id === this.user.id
            ); */

            const currentUser = this.user;
            if (currentUser && !this.notificationFields[fieldKey].selected.includes(currentUser)) {
                this.notificationFields[fieldKey].selected.push(currentUser);
            }
        },
        // Save notification settings
        saveNotifications() {
            const buttonSpinner = document.getElementById('buttonSpinner');
            buttonSpinner.classList.remove('d-none');          

            const payload = {};
            for (const key in this.notificationFields) {
                payload[key] = this.notificationFields[key].selected.map(
                (user) => user.id
                );
            }
            axios.post(`/api/requisitions/${this.requisitionId}/notifications`, payload)
                .then(response => {
                    buttonSpinner.classList.add('d-none');
                    this.toast.success(response.data.message, {
                        title: 'Success'
                    });
                })
                .catch(error => {
                    if (error.response && error.response.data.errors) {
                        buttonSpinner.classList.add('d-none');
                        //this.errors = error.response.data.errors;
                        this.toast.error(error.response ? error.response.data : 'No response data', {
                            title: 'Error'
                        });
                    } else {
                        console.error('Failed to update notifications.', error);
                        buttonSpinner.classList.add('d-none');
                    }
                });

        },
        // Load requisition details based on matter ID and requisition ID
        loadRequisitionDetails() {
            this.contentHtml = this.loadingHtml;  // Show loading spinner
            axios.get(`/api/requisitions/${this.requisitionId}`)
                .then(response => {
                    
                    console.log(response.data);
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

                    if(error){
                        this.toast.error(error.response ? error.response.data : 'No response data', {
                            title: 'Error'
                        });
                    }             
                    
                });
        },
        toggleCollapsePaymentShow()
        {
            const collapseElement = document.getElementById("previousPaymentsDropdown");

            // Toggle the visibility in Vue's data property
            this.showPayments = !this.showPayments;

            // Directly remove or add the 'show' class based on `showPayments`
            if (this.showPayments) {
                collapseElement.classList.add("show");
            } else {
                collapseElement.classList.remove("show");
            }
        },
        toggleCollapseApproveShow()
        {
            const collapseElement = document.getElementById("approveDropdown");

            // Toggle the visibility in Vue's data property
            this.showApprove = !this.showApprove;

            // Directly remove or add the 'show' class based on `showPayments`
            if (this.showApprove) {
                collapseElement.classList.add("show");
            } else {
                collapseElement.classList.remove("show");
            }
        },
        approveRequisition(requisitionId) {

            const approveBtnSpinner = document.getElementById('approveBtnSpinner');
            const approveBtnSpinner1 = document.getElementById('approveBtnSpinner1');
            approveBtnSpinner.classList.remove('d-none');
            approveBtnSpinner1.classList.remove('d-none');

            axios.put(`/api/requisitions/${requisitionId}/approve`)
                .then(response => {
                    //console.log(this.requisition);
                    //console.log("Requisition approved successfully:", response.data);
                    // Optionally update local data or trigger a refresh
                    //this.$emit('requisitionUpdated', response.data);
                    approveBtnSpinner.classList.add('d-none');
                    approveBtnSpinner1.classList.add('d-none');
                    this.toast.success('Requisition approved successfully!', {
                        title: 'Success'
                    });

                    this.requisition = response.data;

                    // Update the requisition object directly
                    //this.requisition = { ...response.data }; // Spread the response data to ensure reactivity

                })
                .catch(error => {
                    approveBtnSpinner.classList.add('d-none');
                    approveBtnSpinner1.classList.add('d-none');
                    console.error("Error approving requisition:", error);
                    this.toast.error(error.response ? error.response.data : 'No response data', {
                        title: 'Error'
                    });
                    //alert("There was an error approving the requisition. Please try again.");
                });
        },
        // Method to unlock a requisition
        unlockRequisition(requisitionId) {

            const unlockBtnSpinner = document.getElementById('unlockBtnSpinner');
            unlockBtnSpinner.classList.remove('d-none');

            axios.put(`/api/requisitions/${requisitionId}/unlock`)
                .then(response => {
                    console.log("Requisition unlocked successfully:", response.data);
                    unlockBtnSpinner.classList.add('d-none');
                    this.toast.success('Requisition unlocked successfully!', {
                        title: 'Success'
                    });

                    // Update the requisition object or handle the response as needed
                    this.requisition = { ...response.data }; // Ensure reactivity
                })
                .catch(error => {
                    unlockBtnSpinner.classList.add('d-none');
                    console.error("Error unlocking requisition:", error);
                    this.toast.error(error.response ? error.response.data : 'No response data', {
                        title: 'Error'
                    });
                });
        },

        // Method to lock a requisition
        lockRequisition(requisitionId) {
            axios.put(`/api/requisitions/${requisitionId}/lock`)
                .then(response => {
                    console.log("Requisition locked successfully:", response.data);
                    this.toast.success('Requisition locked successfully!', {
                        title: 'Success'
                    });

                    // Update the requisition object or handle the response as needed
                    this.requisition = { ...response.data }; // Ensure reactivity
                })
                .catch(error => {
                    console.error("Error locking requisition:", error);
                    this.toast.error(error.response ? error.response.data : 'No response data', {
                        title: 'Error'
                    });
                });
        },

        openViewPaymentModal(payment) {


            this.showAccountDetails = true;
            this.loadCategories();
            this.loadAccountTypes();
            this.loadInstitutions();
            //console.log(payment);

            // Set the edit form data to the selected deposit values
            this.viewPaymentForm.id = payment.id;
            this.viewPaymentForm.description = payment.description;
            this.viewPaymentForm.amount = payment.amount;
            this.viewPaymentForm.verified = payment.beneficiary_account?.verified;
            this.viewPaymentForm.verification_status = payment.beneficiary_account?.verification_status;
            this.viewPaymentForm.avs_verified_at = payment.beneficiary_account?.avs_verified_at;
            this.viewPaymentForm.account_type_verified = payment.beneficiary_account?.account_type_verified;
            this.viewPaymentForm.account_type_match = payment.beneficiary_account?.account_type_match;
            this.viewPaymentForm.holder_name_match = payment.beneficiary_account?.holder_name_match;
            this.viewPaymentForm.holder_initials_match = payment.beneficiary_account?.holder_initials_match;
            this.viewPaymentForm.branch_code_match = payment.beneficiary_account?.branch_code_match;
            this.viewPaymentForm.account_found = payment.beneficiary_account?.account_found;
            this.viewPaymentForm.account_open = payment.beneficiary_account?.account_open;
            this.viewPaymentForm.account_open_gt_three_months = payment.beneficiary_account?.account_open_gt_three_months;
            this.viewPaymentForm.my_reference = payment.my_reference;
            this.viewPaymentForm.recipient_reference = payment.recipient_reference;
            this.viewPaymentForm.account_number = payment.beneficiary_account?.account_number;
            this.viewPaymentForm.account_holder_type = payment.beneficiary_account?.account_holder_type;
            this.viewPaymentForm.initials = payment.beneficiary_account?.initials;
            this.viewPaymentForm.surname = payment.beneficiary_account?.surname;
            this.viewPaymentForm.company_name = payment.beneficiary_account?.company_name;
            this.viewPaymentForm.id_number = payment.beneficiary_account?.id_number;
            this.viewPaymentForm.registration_number = payment.beneficiary_account?.registration_number;
            this.viewPaymentForm.institution = payment.beneficiary_account?.institution;
            this.viewPaymentForm.branch_code = payment.beneficiary_account?.branch_code;
           
            // Set category_id before checking category name
            this.viewPaymentForm.category_id = payment.category_id;
            this.viewPaymentForm.account_type = payment.beneficiary_account?.account_type;

            this.loadBeneficiaryDetails(payment?.beneficiary_account_id, payment.beneficiary_account?.account_number);
    
            // Ensure categories exist before finding the name
            if (this.categories.length > 0) {
                let selectedCategory = this.categories.find(cat => cat.id === payment.category_id);
                this.viewPaymentForm.category_name = selectedCategory ? selectedCategory.name : '';
            }
            // Show the modal
            this.viewPaymentModalInstance = new bootstrap.Modal(document.getElementById('viewPaymentModal'));
            this.viewPaymentModalInstance.show();

            //console.log(deposit);
        },
        
        openEditPaymentModal(payment) {


            this.showAccountDetails = true;
            this.loadCategories();
            this.loadAccountTypes();
            this.loadInstitutions();
            //console.log(payment);

            // Set the edit form data to the selected deposit values
            this.editPaymentForm.id = payment.id;
            this.editPaymentForm.description = payment.description;
            this.editPaymentForm.amount = payment.amount;
            this.editPaymentForm.verified = payment.beneficiary_account?.verified;
            this.editPaymentForm.verification_status = payment.beneficiary_account?.verification_status;
            this.editPaymentForm.avs_verified_at = payment.beneficiary_account?.avs_verified_at;
            this.editPaymentForm.account_type_verified = payment.beneficiary_account?.account_type_verified;
            this.editPaymentForm.account_type_match = payment.beneficiary_account?.account_type_match;
            this.editPaymentForm.holder_name_match = payment.beneficiary_account?.holder_name_match;
            this.editPaymentForm.holder_initials_match = payment.beneficiary_account?.holder_initials_match;
            this.editPaymentForm.branch_code_match = payment.beneficiary_account?.branch_code_match;
            this.editPaymentForm.account_found = payment.beneficiary_account?.account_found;
            this.editPaymentForm.account_open = payment.beneficiary_account?.account_open;
            this.editPaymentForm.account_open_gt_three_months = payment.beneficiary_account?.account_open_gt_three_months;
            this.editPaymentForm.my_reference = payment.my_reference;
            this.editPaymentForm.recipient_reference = payment.recipient_reference;
            this.editPaymentForm.account_number = payment.beneficiary_account?.account_number;
            this.editPaymentForm.account_holder_type = payment.beneficiary_account?.account_holder_type;
            this.editPaymentForm.initials = payment.beneficiary_account?.initials;
            this.editPaymentForm.surname = payment.beneficiary_account?.surname;
            this.editPaymentForm.company_name = payment.beneficiary_account?.company_name;
            this.editPaymentForm.id_number = payment.beneficiary_account?.id_number;
            this.editPaymentForm.registration_number = payment.beneficiary_account?.registration_number;
            this.editPaymentForm.account_type.id = payment.beneficiary_account?.account_type_id;
            this.editPaymentForm.institution = payment.beneficiary_account?.institution;
            this.editPaymentForm.branch_code = payment.beneficiary_account?.branch_code;
            this.editPaymentForm.category = payment.category_id;
            this.editPaymentForm.account_type = payment.beneficiary_account?.account_type;
            console.log(this.editPaymentForm);
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

        updatePayment() {
            const verifiedStatus = this.editPaymentForm.verified;
            this.editPaymentForm.verified = 0;
            axios.put(`/api/payments/${this.editPaymentForm.id}`, this.editPaymentForm)
                .then(response => {
                   // Response contains the updated payment
                    if(response.data){
                        this.requisition = response.data.requisitionData;
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

                    if(verifiedStatus && this.editPaymentForm.verification_status !== 'successful'){
                        // Show the AVS Result Modal after verification
                        this.showAvsModelInstance = new bootstrap.Modal(document.getElementById('avsResultModal'));
                        this.showAvsModelInstance.show();

                        this.performAvsVerification(this.editPaymentForm);
                    }
                })
                .catch(error => {
                    if (error.response && error.response.data.errors) {
                        //this.errors = error.response.data.errors;
                        this.toast.error(error.response ? error.response.data : 'No response data', {
                            title: 'Error'
                        });
                    }
                });
        },


        updateDeposit() {
            axios.put(`/api/deposits/${this.editDepositForm.id}`, this.editDepositForm)
                .then(response => {
                    // Remove the deposit from the local list
                    this.requisition = response.data;

                    //console.log('Deposit deleted successfully:', response.data);

                    if(response.data){
                        // Update the requisition object with the new source_account_id
                        //this.requisition.funding_status = response.data.funding_status;
                        this.closeModal();
                        this.toast.success('Deposit update successfully!', {
                            title: 'Success'
                        });
                    }
                   
                })
                .catch(error => {
                    if (error.response && error.response.data.errors) {
                        //this.errors = error.response.data.errors;
                        this.toast.error(error.response ? error.response.data : 'No response data', {
                            title: 'Error'
                        });
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
                    this.requisition = response.data;

                    //console.log('Deposit deleted successfully:', response.data);

                    if(response.data){
                        // Update the requisition object with the new source_account_id
                        //this.requisition.funding_status = response.data.funding_status;
                        this.closeModal();
                        this.toast.success('Deposit deleted successfully!', {
                            title: 'Success'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error deleting deposit:', error);
                    this.toast.error(error.response ? error.response.data : 'No response data', {
                        title: 'Error'
                    });
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
                    this.requisition.payments = this.requisition.payments.filter(payment => payment.id !== this.editPaymentForm.id);

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
            
            if (this.paymentForm.account_holder_type === 'natural' || this.paymentForm.account_holder_type === 'juristic') {
                this.showAccountDetails = false;
                this.loadAccountTypes();
                this.loadInstitutions();
            } else {
                
                this.showAccountDetails = true; // Hide for existing beneficiaries
                //this.showBeneficiaryDetails = true;
                console.log(this.paymentForm)
                const [beneficiaryId, accountNumber] = this.paymentForm.account_holder_type.split('_');
                this.loadBeneficiaryDetails(beneficiaryId, accountNumber);
            }

            
        },
        loadBeneficiaryDetails(beneficiaryId, accountNumber) {
            axios.get(`/api/beneficiary-accounts/${beneficiaryId}/${accountNumber}`)
                .then(response => {
                    const selectedBeneficiary = response.data; 
                    if (selectedBeneficiary) {
                        this.paymentForm.account_number = selectedBeneficiary.account_number;
                        this.paymentForm.company_name = selectedBeneficiary.company_name;
                        this.paymentForm.initials = selectedBeneficiary.initials;
                        this.paymentForm.surname = selectedBeneficiary.surname;
                        this.paymentForm.id_number = selectedBeneficiary.id_number;
                        this.paymentForm.account_holder_type = selectedBeneficiary.account_holder_type;
                        this.paymentForm.registration_number = selectedBeneficiary.registration_number;
                        this.paymentForm.verified = selectedBeneficiary.verified == 1;
                        //this.paymentForm.payments = selectedBeneficiary.payments; 
                        this.paymentForm.authorised = selectedBeneficiary.authorised;
                        this.paymentForm.display_text = selectedBeneficiary.display_text;
                        this.paymentForm.authorizers = selectedBeneficiary.authorizers;

                        // Format all payment amounts correctly
                        this.paymentForm.payments = selectedBeneficiary.payments.map(payment => ({
                            ...payment,
                            amount: parseFloat(payment.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        }));

                        this.viewPaymentForm.payments = this.paymentForm.payments;

                        // Populate the previously paid field
                        if (selectedBeneficiary.payments.length > 0) {
                            const firstPayment = selectedBeneficiary.payments[0];
                            this.paymentForm.previously_paid = `${this.formatDate(firstPayment.created_at)} - R${parseFloat(firstPayment.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                            this.viewPaymentForm.previously_paid = this.paymentForm.previously_paid;
                        } else {
                            this.paymentForm.previously_paid = "No previous payment";
                            this.viewPaymentForm.previously_paid = "No previous payment";
                        }

                        this.beneficiaryDetails = this.paymentForm;
                       

                        this.paymentForm.category = selectedBeneficiary.category.id;
                        this.paymentForm.account_type = selectedBeneficiary.account_type;
                        this.paymentForm.institution = selectedBeneficiary.institution;
                        this.paymentForm.branch_code = selectedBeneficiary.branch_code;

                        this.beneficiaryDetails = this.paymentForm;
                        console.log("beneficiary details ", this.beneficiaryDetails);

                        this.showAccountDetails = true;

                        this.loadCategories();
                        this.loadAccountTypes();
                        this.loadInstitutions();
                    }
                    console.log('Beneficiary details loaded successfully:', response.data);
                })
                .catch(error => {
                    console.error('Error loading beneficiary details:', error);
                });
        },
        getStatusClass(statusId, fundingStatus, selectedSourceAccount) {
            let statusClass = '';
            if(this.requisition.authorization_status){
                return statusClass = 'complete';
            }

            if (statusId === 1) {
                statusClass = 'incomplete';
            } else if (statusId === 2) {
                statusClass = 'incomplete current';
            } else if (statusId == 3 && !this.requisition.authorization_status) {
                return statusClass = 'patialcomplete';
            } else if (statusId >= 5){
                return statusClass = 'complete';
            }
            // Add 'incomplete' if funding_status is set (not null or empty)
            if (fundingStatus) {
                statusClass = 'patialcomplete';
            }

            if (this.requisition && this.requisition.payments && this.requisition.payments.length > 0 || this.requisition.deposits && this.requisition.deposits.length > 0){
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
                    data: (json) => json, // Return full JSON response for DataTables
                    error: (xhr, error, thrown) => {
                        // Check if the error status is 401 (Unauthorized)
                        if (error.response && error.response.status === 401) {
                            // Emit the 'show-login-modal' event to show the login modal
                            //this.$eventBus.emit('show-login-modal');
                            //alert('handle the connection error successful');
                        } else{
                            console.error('Error fetching data:', error, thrown);
                        }
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'user_name', name: 'user_name' },  // Assuming the API returns `user_name`
                    { data: 'description', name: 'description' },
                    /* { data: 'file_name', name: 'file_name' },
                    { data: 'uploaded_at', name: 'uploaded_at', render: (data) => new Date(data).toLocaleString() }  // Format date */
                    {
                        data: 'file_name',
                        name: 'file_name',
                        render: (data, type, row) => {
                            return `<a href="/api/documents/${row.id}/download" class="text-primary">${data}</a>`;
                        }
                    },
                    { data: 'uploaded_at', name: 'uploaded_at', render: (data) => new Date(data).toLocaleString() },
                    {
                        data: null,
                        orderable: false,
                        render: (data, type, row) => {
                            if (!this.requisition.locked) {
                                return `
                                    <button class="btn btn-sm btn-primary view-document-btn" data-id="${row.id}">View</button>
                                    <button class="btn btn-sm btn-danger delete-document-btn" data-id="${row.id}">Delete</button>
                                `;
                            } else {
                                return `
                                    <button class="btn btn-sm btn-primary view-document-btn" data-id="${row.id}">View</button>
                                `;
                            }
                        }
                    }
                ],
                responsive: true,
                destroy: true,  // Reinitialize the table if needed
                createdRow: (row, data, dataIndex) => {
                    // Attach click event to the View button
                    $(row).find('.view-document-btn').on('click', (event) => {
                        const documentId = $(event.currentTarget).data('id');
                        if (documentId) {
                            this.viewDocument(documentId);
                        }
                    });

                    // Attach click event to the Delete button
                    $(row).find('.delete-document-btn').on('click', (event) => {
                        const documentId = $(event.currentTarget).data('id');
                        if (documentId) {
                            this.deleteDocument(documentId);
                        }
                    });

                    $('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');
                },
               
                /* drawCallback: () => {
                    // Re-attach event listeners when the table is redrawn
                    $('#documents-table').off('click', '.view-document-btn').on('click', '.view-document-btn', (event) => {
                        const documentId = $(event.currentTarget).data('id');
                        if (documentId) {
                            this.viewDocument(documentId);
                        }
                    });

                    $('#documents-table').off('click', '.delete-document-btn').on('click', '.delete-document-btn', (event) => {
                        const documentId = $(event.currentTarget).data('id');
                        if (documentId) {
                            this.deleteDocument(documentId);
                        }
                    });
                } */
            });
        },
        viewDocument(documentId) {
            // Implement the logic to view the document details (e.g., open a modal)
            //alert(`Viewing document ID: ${documentId}`);
            // Construct the URL to view the document
            const documentUrl = `/api/documents/${documentId}/view`;

            // Open the document in a new browser tab
            window.open(documentUrl, '_blank');
        },
        deleteDocument(documentId) {
            if (confirm('Are you sure you want to delete this document?')) {
                axios.delete(`/api/documents/${documentId}`)
                    .then(response => {
                        
                        this.toast.success('Document deleted successfully!', {
                            title: 'Success'
                        });
                        this.documentsTable.ajax.reload();
                    })
                    .catch(error => {
                        this.toast.error(error.response ? error.response.data : 'An error occurred while deleting the document', {
                                                title: 'Error'
                                            });
                        console.error(error);
                    });
            }
        },
        // Handle file selection
        handleFileUpload(event) {
            this.documentForm.file = event.target.files[0];
        },

        loadHistoryData() {
            if (this.historyTable) {
                this.historyTable.ajax.reload(); // Reload the table if already initialized
                return;
            }

            this.historyTable = $('#history-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `/api/requisitions/${this.requisitionId}/history`, // Endpoint for history data
                    type: 'GET', // HTTP method
                    data: (json) => json, // Return full JSON response for DataTables
                    error: (xhr, error, thrown) => {
                        // Check if the error status is 401 (Unauthorized)
                        if (error.response && error.response.status === 401) {
                            // Emit the 'show-login-modal' event to show the login modal
                            //this.$eventBus.emit('show-login-modal');
                            //alert('handle the connection error successful');
                        } else{
                            console.error('Error fetching data:', error, thrown);
                            alert('An error occurred while fetching the history data. Please try again later.');
                        }
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'user.email', name: 'user.email' }, // User email
                    { data: 'action', name: 'action' }, // Action performed
                    { data: 'details', name: 'details' }, // Details of the action
                    { data: 'created_at', name: 'created_at', render: (data) => new Date(data).toLocaleString() }, // Date and time
                ],
                responsive: true, // Enable responsive layout
                destroy: true, // Allow reinitialization
                createdRow: (row, data, dataIndex) => {
                    // Apply styles or event listeners to rows if needed
                    $('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');
                },
                order: [[3, 'desc']], // Order by created_at descending
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
                    data: (json) => json, // Return full JSON response for DataTables
                    error: (xhr, error, thrown) => {
                        // Check if the error status is 401 (Unauthorized)
                        if (error.response && error.response.status === 401) {
                            // Emit the 'show-login-modal' event to show the login modal
                            //this.$eventBus.emit('show-login-modal');
                            //alert('handle the connection error successful');
                        } else{
                            console.error('Error fetching data:', error, thrown);
                            alert('An error occurred while fetching the history data. Please try again later.');
                        }
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'display_text', name: 'display_text' },
                    { data: 'institution.name', name: 'institution.name' },
                    { data: 'account_number', name: 'account_number' },
                    { data: 'branch_code', name: 'branch_code' },
                    { data: 'account_holder', name: 'account_holder' },
                    { data: 'authorised', name: 'authorised' }
                ],
                createdRow: function(row, data, dataIndex) {
                    // Apply style to all <td> elements
                    $('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');
                },
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
                    //console.log("this is payments data");
                    //console.log(this.selectedSourceAccount.payments);
                    // Update the requisition with the selected source account ID
                    //this.updateRequisitionSourceAccount(sourceAccountId);
                })
                .catch(error => {
                    console.error('Error fetching source account details:', error);
                });
        },

        // Submit the document form to the backend via Axios
        uploadDocument() {
            const uploadbuttonSpinner = document.getElementById('uploadbuttonSpinner');
            uploadbuttonSpinner.classList.remove('d-none');

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
                uploadbuttonSpinner.classList.add('d-none');
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
                uploadbuttonSpinner.classList.add('d-none');
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
                    //this.requisition.status_id = 2;
                    this.closeModal();
                    if(this.requisition.status_id == 1){ //this is to allow the create payment only when a new requisition select source account
                        this.openCreatePaymentModal();
                    }else{
                        //console.log('Source Account updated successfully:', response.data);
                        this.toast.success('Source Account updated successfully!', {
                            title: 'Success'
                        });
                    }
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
                //this.fetchSourceAccountDetails(this.requisition.firm_account_id);
                this.selectedSourceAccount = this.requisition.firm_account ? this.requisition.firm_account : null;  // Store the fetched details
                this.showSourceAccountDetails = true;

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
                    data: (json) => json, // Return full JSON response for DataTables
                    error: (xhr, error, thrown) => {
                        // Check if the error status is 401 (Unauthorized)
                        if (error.response && error.response.status === 401) {
                            // Emit the 'show-login-modal' event to show the login modal
                            //this.$eventBus.emit('show-login-modal');
                            //alert('handle the connection error successful');
                        } else{
                            console.error('Error fetching data:', error, thrown);
                        }
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'display_text', name: 'display_text' },
                    { data: 'institution.name', name: 'institution.name' },
                    { data: 'account_number', name: 'account_number' },
                    { data: 'branch_code', name: 'branch_code' },
                    { data: 'account_holder', name: 'account_holder' },
                    { data: 'authorised', name: 'authorised' }
                    /* { data: 'aggregated', render: (data) => (data ? 'Yes' : 'No') } */
                ],
                createdRow: function(row, data, dataIndex) {
                    // Apply style to all <td> elements
                    $('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');
                },
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

         // Open modal to choose source account
         viewAVSResult(accountData) {
            
            this.closeModal();
            this.avsResult.verified = accountData.verified;
            this.avsResult.avs_verified_at = accountData.avs_verified_at;
            this.avsResult.verification_status = accountData.verification_status;
            this.avsResult.account_type_verified = accountData?.account_type_verified;
            this.avsResult.account_type_match = accountData?.account_type_match;
            this.avsResult.holder_name_match = accountData?.holder_name_match;
            this.avsResult.holder_initials_match = accountData?.holder_initials_match;
            this.avsResult.branch_code_match = accountData?.branch_code_match;
            this.avsResult.branch_code = accountData?.branch_code;
            this.avsResult.account_found = accountData?.account_found;
            this.avsResult.account_open = accountData?.account_open;
            this.avsResult.account_open_gt_three_months = accountData?.account_open_gt_three_months;
            this.avsResult.account_number = accountData?.account_number;
            this.avsResult.registration_number = accountData?.registration_number;
            this.avsResult.surname = accountData?.surname;
            this.avsResult.initials = accountData?.initials;
            this.avsResult.id_number = null;
            this.avsResult.account_holder_type = accountData?.account_holder_type;
            this.avsResult.account_type = accountData?.account_type;
            this.avsResult.company_name = accountData?.company_name;

            this.showAvsModelInstance = new bootstrap.Modal(document.getElementById('avsResultModal'));
            this.showAvsModelInstance.show();
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
            if(this.viewPaymentModalInstance){
                this.viewPaymentModalInstance.hide();
            }
            if(this.createPaymentModalInstance){
                this.createPaymentModalInstance.hide();
            }
            if(this.showAvsModelInstance){
                this.showAvsModelInstance.hide();
            }
        },
        // Save requisition (stub method for future implementation)
        saveRequisition() {
            //console.log('Save requisition:', this.requisition);
            axios.put(`/api/requisitions/${this.requisition.id}/update`, this.requisition)
                .then(response => {
                    console.log('Requisition saved successfully:', response.data);
                    this.toast.success('Requisition saved successfully!', {
                        title: 'Success'
                    });
                })
                .catch(error => {
                    console.error('Error saving requisition:', error);
                    this.toast.error(error.response ? error.response.data.message : 'Error saving requisition', {
                        title: 'Error'
                    });
                });
        },

        // Delete requisition (stub method for future implementation)
        confirmDeleteRequisition() {
            if (confirm('Are you sure you want to delete this requisition?')) {
                this.deleteRequisition(this.requisition.id);
            }
        },
        deleteRequisition(requisitionId){
            axios.delete(`/api/requisitions/${requisitionId}`)
                .then(response => {

                   
                        this.closeModal();
                        this.toast.success('Requisition deleted successfully!', {
                            title: 'Success'
                        });

                        // Navigate to the new Vue component, passing the status as a parameter
                        this.$router.push({ name: 'home' });
                })
                .catch(error => {
                    console.error('Error deleting requisition:', error);
                    this.toast.error(error.response ? error.response.data : 'No response data', {
                        title: 'Error'
                    });
                });
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
                    //console.log('Deposit created successfully:', response.data);
                    this.toast.success('Deposit created successfully!', {
                        title: 'Success'
                    });

                    // Add the newly created deposit to the local deposits list
                    this.requisition = response.data;

                    if(response.data){
                        // Update the requisition object with the new source_account_id
                        //this.requisition.funding_status = 1;
                        this.closeModal();
                    }

                    // Reset the form after submission
                    this.resetForm();

                    // Optionally reload deposit data if required
                    // this.loadDeposits();
                })
                .catch(error => {


                    this.toast.error(error.response ? error.response.data : 'No response data', {
                        title: 'Error creating deposit:'
                    });
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
        balancePaymentAndFund(){
            this.balanceFundDeposit.description = "Deposit";
            this.balanceFundDeposit.requisition_id = this.requisitionId;
            this.balanceFundDeposit.firm_account_id = this.selectedSourceAccount.id;
            this.balanceFundDeposit.funded =  true;
            if(parseFloat(this.totalDepositAmount) < parseFloat(this.totalPaymentAmount)){
                this.balanceFundDeposit.amount = parseFloat(this.totalPaymentAmount) - parseFloat(this.totalDepositAmount);
            }else{
                this.balanceFundDeposit.amount = parseFloat(this.totalDepositAmount) - parseFloat(this.totalPaymentAmount);
            }

            axios.post('/api/deposits/balance-payment-fund', this.balanceFundDeposit)
                .then(response => {
                    console.log('Payment balanced and funded successfully:', response.data);

                    // Add the newly created deposit to the local deposits list
                    this.requisition = response.data;

                    if(response.data){
                        // Update the requisition object with the new source_account_id
                        //this.requisition.funding_status = 1;
                        this.closeModal();
                        // Show success toast
                        this.toast.success('Payment balanced and funded successfully!', {
                            title: 'Success'
                        });
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
                        this.toast.error(error.response ? error.response.data : 'No response data', {
                            title: 'Error'
                        });
                    }
                });
        },
        balancePaymentDontFund(){
            this.balanceFundDeposit.description = "Deposit";
            this.balanceFundDeposit.requisition_id = this.requisitionId;
            this.balanceFundDeposit.firm_account_id = this.selectedSourceAccount.id;
            this.balanceFundDeposit.funded =  false;
            if(parseFloat(this.totalDepositAmount) < parseFloat(this.totalPaymentAmount)){
                this.balanceFundDeposit.amount = parseFloat(this.totalPaymentAmount) - parseFloat(this.totalDepositAmount);
            }else{
                this.balanceFundDeposit.amount = parseFloat(this.totalDepositAmount) - parseFloat(this.totalPaymentAmount);
            }

            axios.post('/api/deposits/balance-payment-dont-fund', this.balanceFundDeposit)
                .then(response => {
                    console.log('Payment balanced successfully:', response.data);

                    // Add the newly created deposit to the local deposits list
                    //this.selectedSourceAccount.deposits.push(response.data);
                    this.requisition = response.data;

                    if(response.data){
                        // Update the requisition object with the new source_account_id
                        //this.requisition.funding_status = 1;
                        this.closeModal();
                        // Show success toast
                        this.toast.success('Payment balanced successfully!', {
                            title: 'Success'
                        });
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
                        this.toast.error(error.response ? error.response.data : 'No response data', {
                            title: 'Error'
                        });
                    }
                });
        },
        fundDeposit() {
            axios.post('/api/deposits/fund-deposits', { requisition_id: this.requisitionId })
                .then(response => {
                    console.log('Deposit Funding successfully:', response.data);
                    
                    // Show success toast
                    this.toast.success('Deposit Funding successfully!', {
                        title: 'Success'
                    });

                    // Update the funding status in the local requisition object
                    this.requisition.funding_status = 1;

                    // Update each deposit in the local deposits list to reflect the funded status
                    this.requisition.deposits.forEach(deposit => {
                        deposit.funded = true;
                    });

                    this.closeModal();

                    // Reset the form after submission
                    this.resetForm();
                })
                .catch(error => {
                    console.error('Error funding deposit:', error);
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
            this.showAccountDetails = false;
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
                        this.filteredAccounts = response.data; //console.log("this is the account_type", this.filteredAccounts);
                    })
                    .catch(error => {
                        console.error('Error fetching accounts:', error);
                    });
                
                this.showSuggestions = true;

                    // Update label based on whether search is account number or name
                this.newAccountLabel = this.isSearchNumeric() 
                    ? `Account number` 
                    : `Account holder name`;
                

            }else if (this.paymentForm.search.length === 0) {
                // Clear filtered accounts and hide suggestions if search input is empty
                this.filteredAccounts = [];
                this.showSuggestions = false;
            } else {
                this.filteredAccounts = [];
                this.showSuggestions = false;  // Hide suggestions if query is too short
            }
        },
        // Handle account selection from dropdown
        selectAccount(account) {
            console.log("selected account information ", account);
            this.paymentForm.account_number = account.account_number;  // Set selected account number
            this.paymentForm.company_name = account.company_name;  // Optionally set the account holder
            this.paymentForm.search = `${account.account_number} - ${account.company_name ? account.company_name : account.surname}`;
            this.paymentForm.account_holder_type = account.account_holder_type;
            this.showSuggestions = false;  // Hide dropdown after selection
            this.filteredAccounts = [];
            this.paymentForm.initials = account.initials;
            this.paymentForm.surname = account.surname;
            this.paymentForm.id_number = account.id_number;
            this.paymentForm.registration_number = account.registration_number;
            this.paymentForm.verified = account.verified;
            
            this.paymentForm.payments = account.payments;
            this.paymentForm.authorised = account.authorised;

            if (account.payments.length > 0) {
                const firstPayment = account.payments[0];
                this.paymentForm.previously_paid = `${this.formatDate(firstPayment.created_at)} - R${firstPayment.amount}`;
            } else {
                this.paymentForm.previously_paid = "No previous payment"; // Fallback if there are no payments
            }
            /* this.paymentForm.previously_paid = this.formatDate(account.payments.created_at)+' - R'+account.payments.amount//"22 Oct 24 - R20,000.00"; */

            this.loadAccountTypes();
            this.loadInstitutions();

            // Update additional fields based on the selected account
            this.paymentForm.category = account.category_id;  // Assuming category_id is returned from API
            this.paymentForm.account_type = account.account_type;  // Assuming account_type is returned from API
            this.paymentForm.institution = account.institution;  // Assuming institution name is returned from API
            this.paymentForm.branch_code = account.branch_code;  // Assuming branch code is returned from API
            this.beneficiaryDetails = account;

            this.showAccountDetails = true;
            this.showBeneficiaryDetails = false;
            //console.log(this.paymentForm);
            //this.loadAccountTypes();
            //this.loadInstitutions();

            //this.fetchData();  // Fetch data when component is mounted
        },
        // Method to highlight matching text in the result
        highlightMatch(text, searchTerm) {
            // If text is undefined or null, return an empty string
            if (!text) return '';

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
            //console.log(this.paymentForm);
            
            // Use beneficiary details if an existing beneficiary is selected
            if (this.paymentForm.account_holder_type && this.beneficiaryDetails) {
                this.paymentForm.account_number = this.beneficiaryDetails.account_number;
                this.paymentForm.branch_code = this.beneficiaryDetails.branch_code;
                this.paymentForm.account_holder = this.beneficiaryDetails.display_text;
                this.paymentForm.account_type = this.beneficiaryDetails.account_type;
                this.paymentForm.institution = this.beneficiaryDetails.institution;
                this.paymentForm.existing_beneficiary = true; // Indicate an existing beneficiary

                this.showBeneficiaryDetails = false;
            } else {
                this.paymentForm.existing_beneficiary = false; // Indicate a new beneficiary
            }

            const verifiedStatus = this.paymentForm.verified;
            this.paymentForm.verified = 0;

            axios.post('/api/payments', this.paymentForm)
                .then(response => {
                    //console.log('Payment created successfully:', response.data);
                     // Show success toast
                     this.toast.success('Payment created successfully!', {
                        title: 'Success'
                    });

                    //console.log('this is the complete data back ===> ', response.data);

                     // Add the newly created deposit to the local deposits list
                     this.requisition = response.data;

                    // Close the modal if "Save" was clicked
                    if (!stayInModal) {
                        //this.createPaymentModalInstance = bootstrap.Modal.getInstance(document.getElementById('createPaymentModal'));
                        this.createPaymentModalInstance.hide();
                    }
                    this.closeModal();
                    
                    if(verifiedStatus && !this.beneficiaryDetails.verification_status !== 'successful'){
                        // Show the AVS Result Modal after verification
                        this.showAvsModelInstance = new bootstrap.Modal(document.getElementById('avsResultModal'));
                        this.showAvsModelInstance.show();

                        this.performAvsVerification(this.paymentForm);
                    }else{
                        this.resetPaymentForm();
                    }
                    
                })
                .catch(error => {
                    //console.error('Error creating payment:', error);
                    //console.error('Response data:', error.response ? error.response.data : 'No response data');
                    // Show error toast
                    this.toast.error(error.response ? error.response.data : 'No response data', {
                        title: 'Error'
                    });
                    
                    /* if (error.response && error.response.data.errors) {
                        this.errors = error.response.data.errors;  // Show validation errors
                        console.error('Error creating payment:', error);
                    } */
                }); 
        },

        // Perform AVS Verification using Axios
        performAvsVerification(paymentForm) { 
            axios.post('/api/avs/verify', {
                    account_number: paymentForm.account_number,
                    branch_code: paymentForm.branch_code,
                    account_holder: paymentForm.account_holder,
                    account_holder_type: paymentForm.account_holder_type,
                    registration_number: paymentForm?.registrationNumber,
                    id_number: paymentForm?.idNumber,
                })
                .then(response => {
                    this.avsResult = response.data; console.log("this is the value of avs result " , response.data);
                    if (response.success && response.data.errmsg) {
                        this.toast.error(response.data ? response.errmsg : 'No response data', {
                            title: 'Error'
                        });
                        
                    }else{

                    }
                    //this.showAvsModal = true;
                    
                    
                    // Reset the form after submission
                    this.resetPaymentForm();
                })
                .catch(error => {
                    console.error('AVS Verification failed:', error);
                    //alert('AVS Verification failed. Please try again.');
                    if (error.response && error.response.data.errors) {
                        this.errors = error.response.data.errors;  // Show validation errors
                    }
                });
        },

        navigateToAllTransactionsForAFile(fileId) {
            //console.log("requisition: ", this.requisition);

            const requisitionStore = useRequisitionStore();
            requisitionStore.setRequisition(this.requisition); // Store data in Pinia
            
            this.$router.push({
                name: 'alltransactionsforafile',
                params: { id: fileId }
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

        async searchRequisition() {
            const searchBtnSpinner = document.getElementById('searchBtnSpinner');
            searchBtnSpinner.classList.remove('d-none');           

            this.searchError = "";
            if (!this.searchQuery.trim()) {
                this.searchError = "File reference is required.";
                searchBtnSpinner.classList.add('d-none');
                return;
            }

            try {
                const response = await axios.post('/api/requisitions/search', {
                    file_reference: this.searchQuery
                });

                const store = useRequisitionStore();

                if (response.data?.redirect) {
                    window.location.href = response.data.redirect;
                } else if (response.data?.requisitions?.length > 1) {
                    store.setRequisitions(response.data.requisitions); // Store multiple results
                    this.$router.push({ name: "filteredmatters" }); // Navigate to matters page
                    /* this.$router.push({ 
                        name: "filteredmatters", 
                        params: { requisitions: response.data?.requisitions } 
                    }); */
                } else {
                    this.searchError = "No requisition found.";
                    //alert("No requisitions found.");
                }

                /* if (response.status === 200 && response.data) {
                    const requisitionId = response.data.id;
                    window.location.href = `/matters/requisitions/${requisitionId}/details`;
                } else {
                    this.searchError = "No requisition found.";
                } */
            } catch (error) {
                this.searchError = "Error searching for requisition.";
                console.error(error);
            }
            searchBtnSpinner.classList.add('d-none');
        },
        // Print the current page
        printPage() {
            window.print();
        },
        // Handle cancel button
        cancel() {
            this.$router.go(-1);  // Navigate back
        },
    }
};
</script>

<style scoped>

.notification-form-container {
  padding: 20px;
  background: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.notification-form-container h5 {
  font-size: 16px;
  margin-bottom: 10px;
  color: #333;
}

.notification-form-container p {
  font-size: 14px;
  margin-bottom: 20px;
  color: #666;
}

.form-label {
  font-weight: bold;
  color: #555;
}

.notification-multiselect {
  margin-bottom: 10px;
}

.btn-link {
  font-size: 14px;
  color: #007bff;
  text-decoration: none;
}

.btn-link:hover {
  text-decoration: underline;
}

.btn-primary {
  background-color: #007bff;
  border-color: #007bff;
}

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

.orange{
    color: rgb(182, 140, 62) !important;
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
