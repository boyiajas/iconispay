<template>
    <!-- Matters Section -->
    <div class="mt-4">
        <h4 class="section-title">Requisition</h4>
        
        <!-- Status Cards for Matters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>New Requisition <span class="pull-right"><button class="btn btn-white btn-sm" @click="resetForm">Refresh</button></span></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <form @submit.prevent="submitRequisition">
                            
                            <div class="form-group row">
                                <label for="file-reference" class="form-label col-sm-2">File Reference: *</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="form.file_reference" class="form-control" id="file-reference" placeholder="Enter file reference" maxlength="150" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="reason" class="form-label col-sm-2">Reason</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="form.reason" class="form-control" id="reason" placeholder="Enter an optional reason for this payment Requisition e.g. Levies, Final Settlement">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="parties" class="form-label col-sm-2">Parties</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="form.parties" class="form-control" id="parties" placeholder="Enter Party description">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="properties" class="form-label col-sm-2">Properties</label>
                                <div class="col-sm-10">
                                    <input type="text" v-model="form.properties" class="form-control" id="properties" minlength="3" maxlength="250" placeholder="Enter property description">
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <label for="" class="form-label col-sm-2"></label>
                                <div class="col-sm-10">
                                    <button type="submit" class="btn btn-info">Save</button>
                                    <button type="button" class="btn btn-secondary ml-1" @click="cancel">Cancel</button>
                                </div>
                            </div>
                            
                            <!-- <router-link to="/home" class="btn btn-secondary ml-1">Cancel</router-link> -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { useToast } from 'vue-toastification';

export default {
    data() {
        return {
            form: {
                file_reference: '',
                reason: '',
                parties: '',
                properties: ''
            }
        };
    },
    setup() {
        // Initialize toast
        const toast = useToast();
        return { toast };
    },
    methods: {
        // Submit the requisition form
        submitRequisition() {
            // Send form data to the API endpoint
            axios.post('/api/requisitions', this.form)
                .then(response => {
                    // Assuming response contains the newly created requisition ID
                    const requisitionId = response.data.id;
                    //const matterId = response.data.matter_id;

                    // Show success toast
                    this.toast.success('Requisition created successfully!', {
                        title: 'Success'
                    });
                    // Redirect to the Details page for the newly created requisition
                    /* this.$router.push({ name: 'requisitionDetails', params: { matterId, requisitionId } }); */
                    this.$router.push({ name: 'detailsrequisition', params: { requisitionId } });

                })
                .catch(error => {
                    console.error('Error submitting requisition:', error);

                    // Show error toast
                    this.toast.error('Failed to create requisition. Please try again.', {
                        title: 'Error'
                    });
                });
        },

        // Handle cancel button
        cancel() {
            this.$router.go(-1);  // Navigate back
        },
        // Reset the form fields
        resetForm() {
            this.form = {
                file_reference: '',
                reason: '',
                parties: '',
                properties: ''
            };
        }
    }
};
</script>

<style scoped>
.section-title {
    font-weight: bold;
    margin-bottom: 20px;
}
</style>
