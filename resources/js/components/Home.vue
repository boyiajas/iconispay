<template>
    <div class="mt-4">
        
        <h4 class="section-title">Home  
            <PermissionControl :roles="['admin', 'authoriser', 'user']">
                    <router-link to="/requisition/new" class="btn btn-primary btn-sm ml-4">+ New Requisition</router-link>
            </PermissionControl>
            
        </h4>
        <!-- Status Cards for Matters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Matters <span class="pull-right"><button class="btn btn-white btn-sm" @click="loadIncompleteRequisitions">Refresh</button></span></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <ul class="list-group list-group-flush">
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Incomplete')">
                                <li class="list-group-item">Incomplete 
                                    <span :class="['pull-right', 'badge', 'badge-pill', incompleteRequisitions > 0 ? 'badge-danger' : 'bg-default']">
                                        <span v-if="loadingIncompleteRequisitions" id="buttonSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span v-else>{{ incompleteRequisitions }}</span>
                                        
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Awaiting Authorisation')">
                                <li class="list-group-item">Awaiting Authorisation 
                                    <span :class="['pull-right', 'badge', 'badge-pill', awaitingAuthorizationRequisitions > 0 ? 'badge-danger' : 'bg-default']">
                                        <span v-if="loadingAwaitingAuthorization" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span v-else>{{ awaitingAuthorizationRequisitions }}</span>
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Awaiting Funding')">
                                <li class="list-group-item">Awaiting Funding 
                                    <span :class="['pull-right', 'badge', 'badge-pill', awaitingFundingRequisitions > 0 ? 'badge-danger' : 'bg-default']">
                                        <span v-if="loadingAwaitingFunding" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span v-else>{{ awaitingFundingRequisitions }}</span>
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Ready for Payment')">
                                <li class="list-group-item">Ready for Payment 
                                    <span :class="['pull-right', 'badge', 'badge-pill', readyForPaymentRequisitions > 0 ? 'badge-danger' : 'bg-default']">
                                        <span v-if="loadingReadyForPayment" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span v-else>{{ readyForPaymentRequisitions }}</span>
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Pending Payment Confirmation')">
                                <li class="list-group-item">Pending Payment Confirmation
                                    <span :class="['pull-right', 'badge', 'badge-pill', pendingPaymentConfirmations > 0 ? 'badge-danger' : 'bg-default']">
                                        <span v-if="loadingPendingPaymentConfirmations" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span v-else>{{ pendingPaymentConfirmations }}</span>
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Settled Today')">
                                <li class="list-group-item">Settled Today 
                                    <span :class="['pull-right', 'badge', 'badge-pill', settledTodayRequisitions > 0 ? 'badge-danger' : 'bg-default']">
                                        <span v-if="loadingSettledToday" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span v-else>{{ settledTodayRequisitions }}</span>
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Settlement Failed')">
                                <li class="list-group-item">Settlement Failed 
                                    <span :class="['pull-right', 'badge', 'badge-pill', settlementFailedRequisitions > 0 ? 'badge-danger' : 'bg-default']">
                                        <span v-if="loadingSettlementFailed" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span v-else>{{ settlementFailedRequisitions }}</span>
                                    </span>
                                </li>
                            </a>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5>Search by File Reference:</h5>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="File reference" aria-label="File reference">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">Go</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import PermissionControl from './permission/PermissionControl.vue';  // Import the Can component

export default {
    components: {
        PermissionControl
    },
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            loadingIncompleteRequisitions: false,
            loadingAwaitingAuthorization: false,
            loadingAwaitingFunding: false,
            loadingReadyForPayment: false,
            loadingPendingPaymentConfirmations: false,
            loadingSettledToday: false,
            loadingSettlementFailed: false,
            incompleteRequisitions: 0,
            awaitingAuthorizationRequisitions: 0,
            awaitingFundingRequisitions: 0,
            readyForPaymentRequisitions: 0,
            pendingPaymentConfirmations: 0,
            settledTodayRequisitions: 0,
            settlementFailedRequisitions: 0,
        };
    },
    methods: {
        // Method to load the incomplete requisitions
        loadIncompleteRequisitions() {
            this.loadingIncompleteRequisitions = true;
            //console.log(this.user);
            axios.get('/api/requisitions/incomplete')
            .then(response => {
                // Assuming the response contains the count of incomplete requisitions
                this.incompleteRequisitions = response.data.count;
            })
            .catch(error => {
                console.error("There was an error fetching the requisition data: ", error);
            })
            .finally(() => {
                // Set loading state to false after the request completes
                this.loadingIncompleteRequisitions = false;
            });
        },
        loadAwaitingFunding() {
            this.loadingAwaitingFunding = true;
            axios.get('/api/requisitions/awaiting-funding')
                .then(response => {
                    // Assuming the response contains the count of requisitions with unfunded deposits
                    this.awaitingFundingRequisitions = response.data.count;
                })
                .catch(error => {
                    console.error("There was an error fetching the awaiting funding requisition data: ", error);
                })
                .finally(() => (this.loadingAwaitingFunding = false));
        },
        // Method to load the Awaiting Authorization requisitions
        loadAwaitingAuthorizationRequisitions() {
            this.loadingAwaitingAuthorization = true;
            axios.get('/api/requisitions/awaiting-authorization')
            .then(response => {
                // Assuming the response contains the count of incomplete requisitions
                this.awaitingAuthorizationRequisitions = response.data.count;
                //console.log(response.data);
            })
            .catch(error => {
                console.error("There was an error fetching the requisition data: ", error);
            })
            .finally(() => (this.loadingAwaitingAuthorization = false));
        },
        loadPendingPaymentConfirmationRequisitions(){
            this.loadingPendingPaymentConfirmations = true;
            axios.get('/api/requisitions/pending-payment-confirmation')
            .then(response => {
                // Assuming the response contains the count of incomplete requisitions
                this.pendingPaymentConfirmations = response.data.count;
                //console.log(response.data);
            })
            .catch(error => {
                console.error("There was an error fetching the requisition data: ", error);
            })
            .finally(() => (this.loadingPendingPaymentConfirmations = false));
        },
        loadReadyForPaymentRequisitions(){
            this.loadingReadyForPayment = true;
            axios.get('/api/requisitions/ready-for-payment')
            .then(response => {
                // Assuming the response contains the count of incomplete requisitions
                this.readyForPaymentRequisitions = response.data.count;
                //console.log(response.data);
            })
            .catch(error => {
                console.error("There was an error fetching the requisition data: ", error);
            })
            .finally(() => (this.loadingReadyForPayment = false));
        },
        loadSettledTodayRequisitions() {
            this.loadingSettledToday = true;
            axios.get('/api/requisitions/settled-today')
                .then(response => {
                    // Assuming the response contains the count of settled requisitions
                    this.settledTodayRequisitions = response.data.count;
                })
                .catch(error => {
                    console.error("There was an error fetching the settled today requisition data: ", error);
                })
                .finally(() => (this.loadingSettledToday = false));
        },
        loadRequisitionsByStatus(status) {
            // Navigate to the new Vue component, passing the status as a parameter
            this.$router.push({ name: 'requisitionStatus', params: { status } });
        }
    },
    mounted() {
        // Load incomplete requisitions when the component is mounted
        this.loadIncompleteRequisitions();
        this.loadAwaitingAuthorizationRequisitions();
        this.loadReadyForPaymentRequisitions();
        this.loadPendingPaymentConfirmationRequisitions();
        this.loadAwaitingFunding();
        this.loadSettledTodayRequisitions();
    }
};
</script>

<style scoped>
a li{
    color:#4c4d4f;
}

a li:hover{
    background-color: #fafafa;
}
</style>
