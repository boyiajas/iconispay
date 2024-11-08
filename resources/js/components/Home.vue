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
                                        {{ incompleteRequisitions }}
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Awaiting Authorisation')">
                                <li class="list-group-item">Awaiting Authorisation 
                                    <span :class="['pull-right', 'badge', 'badge-pill', awaitingAuthorizationRequisitions > 0 ? 'badge-danger' : 'bg-default']">
                                        {{ awaitingAuthorizationRequisitions }}
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Awaiting Funding')">
                                <li class="list-group-item">Awaiting Funding 
                                    <span :class="['pull-right', 'badge', 'badge-pill', awaitingFundingRequisitions > 0 ? 'badge-danger' : 'bg-default']">
                                        {{ awaitingFundingRequisitions }}
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Ready for Payment')">
                                <li class="list-group-item">Ready for Payment 
                                    <span :class="['pull-right', 'badge', 'badge-pill', readyForPaymentRequisitions > 0 ? 'badge-danger' : 'bg-default']">
                                        {{ readyForPaymentRequisitions }}
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Pending Payment Confirmation')">
                                <li class="list-group-item">Pending Payment Confirmation
                                    <span :class="['pull-right', 'badge', 'badge-pill', pendingPaymentConfirmations > 0 ? 'badge-danger' : 'bg-default']">
                                        {{ pendingPaymentConfirmations }}
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Settled Today')">
                                <li class="list-group-item">Settled Today 
                                    <span class="pull-right badge badge-pill bg-default">0</span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Settlement Failed')">
                                <li class="list-group-item">Settlement Failed 
                                    <span class="pull-right badge badge-pill bg-default">0</span>
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
            incompleteRequisitions: 0, // Store the count of incomplete requisitions
            awaitingAuthorizationRequisitions: 0,
            readyForPaymentRequisitions: 0,
            awaitingFundingRequisitions: 0,
            pendingPaymentConfirmations: 0,
        };
    },
    methods: {
        // Method to load the incomplete requisitions
        loadIncompleteRequisitions() {
            //console.log(this.user);
            axios.get('/api/requisitions/incomplete')
            .then(response => {
                // Assuming the response contains the count of incomplete requisitions
                this.incompleteRequisitions = response.data.count;
            })
            .catch(error => {
                console.error("There was an error fetching the requisition data: ", error);
            });
        },
        loadAwaitingFunding() {
            axios.get('/api/requisitions/awaiting-funding')
                .then(response => {
                    // Assuming the response contains the count of requisitions with unfunded deposits
                    this.awaitingFundingRequisitions = response.data.count;
                })
                .catch(error => {
                    console.error("There was an error fetching the awaiting funding requisition data: ", error);
                });
        },
        // Method to load the Awaiting Authorization requisitions
        loadAwaitingAuthorizationRequisitions() {
            axios.get('/api/requisitions/awaiting-authorization')
            .then(response => {
                // Assuming the response contains the count of incomplete requisitions
                this.awaitingAuthorizationRequisitions = response.data.count;
                //console.log(response.data);
            })
            .catch(error => {
                console.error("There was an error fetching the requisition data: ", error);
            });
        },
        loadPendingPaymentConfirmationRequisitions(){
            axios.get('/api/requisitions/pending-payment-confirmation')
            .then(response => {
                // Assuming the response contains the count of incomplete requisitions
                this.pendingPaymentConfirmations = response.data.count;
                //console.log(response.data);
            })
            .catch(error => {
                console.error("There was an error fetching the requisition data: ", error);
            });
        },
        loadReadyForPaymentRequisitions(){
            axios.get('/api/requisitions/ready-for-payment')
            .then(response => {
                // Assuming the response contains the count of incomplete requisitions
                this.readyForPaymentRequisitions = response.data.count;
                //console.log(response.data);
            })
            .catch(error => {
                console.error("There was an error fetching the requisition data: ", error);
            });
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
