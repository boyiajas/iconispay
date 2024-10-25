<template>
    <div class="mt-4">
        <h4 class="section-title">Home  <router-link to="/requisition/new" class="btn btn-primary btn-sm ml-4">+ New Requisition</router-link></h4>
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
                                    <span :class="['pull-right', 'badge', 'badge-pill', incompleteRequisitions > 0 ? 'badge-danger' : 'badge-secondary']">
                                        {{ incompleteRequisitions }}
                                    </span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Awaiting Authorisation')">
                                <li class="list-group-item">Awaiting Authorisation 
                                    <span class="pull-right badge badge-pill badge-secondary">0</span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Awaiting Funding')">
                                <li class="list-group-item">Awaiting Funding 
                                    <span class="pull-right badge badge-pill badge-secondary">0</span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Ready for Payment')">
                                <li class="list-group-item">Ready for Payment 
                                    <span class="pull-right badge badge-pill badge-secondary">0</span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Pending Payment Confirmation')">
                                <li class="list-group-item">Pending Payment Confirmation
                                    <span class="pull-right badge badge-pill badge-secondary">0</span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Settled Today')">
                                <li class="list-group-item">Settled Today 
                                    <span class="pull-right badge badge-pill badge-secondary">0</span>
                                </li>
                            </a>
                            <a href="#" @click.prevent="loadRequisitionsByStatus('Settlement Failed')">
                                <li class="list-group-item">Settlement Failed 
                                    <span class="pull-right badge badge-pill badge-danger">1</span>
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

export default {
    data() {
        return {
            incompleteRequisitions: 0, // Store the count of incomplete requisitions
        };
    },
    methods: {
        // Method to load the incomplete requisitions
        loadIncompleteRequisitions() {
            axios.get('/api/requisitions/incomplete')
            .then(response => {
                // Assuming the response contains the count of incomplete requisitions
                this.incompleteRequisitions = response.data.count;
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
