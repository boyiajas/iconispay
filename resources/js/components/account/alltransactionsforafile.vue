<template>
    <div class="container mt-4 mb-5">
        <!-- Requisition Header -->
         
        <h4 class="section-title mb-2">
            Requisition: <span style="color:#999;font-weight: normal;font-size: 20px;">{{ requisition?.file_reference }} - {{ requisition?.reason }}</span>
            
        </h4>
    </div>



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
                                        R{{ parseFloat(deposit.amount).toFixed(2) }}
                                        
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
                            <div class="col-md-3">
                                <span v-if="payment.beneficiary_account && payment.beneficiary_account.verification_status == 'successful'">
                                    <i class="far fa-check-circle mr-2 mt-1 text-success" ref="popoverIcon" 
                                    data-bs-toggle="popover" 
                                    data-bs-placement="top" 
                                    title="Account details complete, AVS verified account" 
                                    data-bs-content="Account details complete, No AVS verification done"></i>
                                </span>
                                <span v-else-if="payment.beneficiary_account.verified && payment.beneficiary_account.verified != 'successful'">
                                    <i class="fas fa-ban mr-2 mt-1 text-danger" ref="popoverIcon" 
                                    data-bs-toggle="popover" 
                                    data-bs-placement="top" 
                                    title="AVS verification failed, Invalid account / details" 
                                    data-bs-content="Account details complete, No AVS verification done"></i>
                                </span>
                                <span v-else-if="!payment.verified">
                                    <i class="far fa-square bg-green mr-2 mt-1 text-success" ref="popoverIcon" 
                                    data-bs-toggle="popover" 
                                    data-bs-placement="top" 
                                    title="Account details complete, No AVS verification done" 
                                    data-bs-content="Account details complete, No AVS verification done"></i>
                                </span>
                                {{ payment.description }}
                            </div>
                            <div class="col-md-3" v-bind:style="payment.beneficiary_account && payment.beneficiary_account.authorised === 1 ? { background: '#f2f2f2', border: '1px solid #ddd', padding: '6px 12px', fontSize: '14px'/* , color: '#666' */ } : {}">
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
                            <div class="col-md-4">
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
                
                    <div class="row ml-0 p-0">
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
</template>