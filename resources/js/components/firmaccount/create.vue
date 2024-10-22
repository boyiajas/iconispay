<template>
    <div class="container mt-4">
        <h4 class="section-title">Source Account <small>Create an account</small></h4>

        <!-- Beneficiary Account Form -->
        <div class="card">
            <div class="card-header">
                <h5>Create an account <span class="pull-right"><router-link to="/setup" class="btn btn-white btn-sm">Back</router-link></span></h5>
                
            </div>
            <div class="card-body">
                <form @submit.prevent="saveFirmAccount">
                    <!-- Display Text Field -->
                    
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="displayText">Display Text: *</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.displayText" class="form-control" id="displayText" placeholder="Enter a display name for the account" required>
                        </div>   
                    </div>

                    <!-- Account Category Dropdown -->
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="category">Account Category: *</label>
                        <div class="col-sm-10">
                            <select v-model="firmAccount.category" class="form-control" id="category" required>
                                <option value="">Select a category</option>
                                <option v-for="category in categories" :key="category" :value="category.id">{{ category.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Account Holder Section -->
                    <h5>Account Holder</h5>
                    <hr class="mt-0"/>
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="accountNumber">Account #: *</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.accountNumber" class="form-control" id="accountNumber" placeholder="Enter the account number" required>
                        </div>
                    </div>

                    <!-- Account Holder Type Radio Buttons -->
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2">Account Holder Type: *</label>
                        <div class="col-sm-10">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" v-model="firmAccount.accountHolderType" id="naturalPerson" value="natural">
                                <label class="form-check-label" for="naturalPerson">Natural Person</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" v-model="firmAccount.accountHolderType" id="juristicPerson" value="juristic">
                                <label class="form-check-label" for="juristicPerson">Juristic Person</label>
                            </div>
                        </div>
                    </div>

                    <!-- Company Name and Registration Number Fields -->
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="companyName">Company Name:</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.companyName" class="form-control" id="companyName" placeholder="Enter the name of the company">
                        </div>
                    </div>
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="registrationNumber">Registration #:</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.registrationNumber" class="form-control" id="registrationNumber" placeholder="Enter the registration number or leave blank if not applicable">
                        </div>
                    </div>

                    <!-- Account Type Field -->
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="accountType">Account Type: *</label>
                        <div class="col-sm-10">
                            <select v-model="firmAccount.accountType" class="form-control" id="accountType" required>
                                <option value="">Select an Account Type</option>
                                <option v-for="accountType in accountTypes" :key="accountType.id" :value="accountType.id">{{ accountType.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Institution Dropdown -->
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="institution">Institution: *</label>
                        <div class="col-sm-10">
                            <select v-model="firmAccount.institutionId" class="form-control" id="institution" required>
                                <option value="">Select an institution</option>
                                <option v-for="institution in institutions" :key="institution.id" :value="institution.id">{{ institution.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Branch Code Field -->
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="branchCode">Branch Code: *</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.branchCode" class="form-control" id="branchCode" placeholder="Enter the branch code" required>
                        </div>
                    </div>

                    <!-- Aggregate Payment References Section -->
                    <h5>Aggregate Payment References</h5>
                    <hr class="mt-0"/>
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="myReference">My Reference:</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.myReference" class="form-control" id="myReference" placeholder="My reference to use when making aggregated payments to this account">
                        </div>
                    </div>
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="recipientReference">Recipient Reference:</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.recipientReference" class="form-control" id="recipientReference" placeholder="Recipient's reference to use when making aggregated payments to this account">
                        </div>
                    </div>
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for=""></label>
                        <div class="col-sm-10">
                            <!-- Save Button -->
                            <button type="submit" class="btn btn-info">Save</button>
                            <button type="button" class="btn btn-secondary ml-1" @click="cancel">Cancel</button>
                        </div>
                    </div>

                    
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            firmAccount: {
                displayText: '',
                accountCategory: '',
                accountNumber: '',
                accountHolderType: 'Natural Person', // Default selection
                companyName: '',
                registrationNumber: '',
                accountType: '',
                institutionId: '',
                branchCode: '',
                myReference: '',
                recipientReference: '',
            },
            //accountCategories: ['Firm business account', 'Compliance Certificate Provider', 'Municipality / Government'], // Add more categories as needed
            categories: [],  // Will be populated with categories from the API
            accountTypes: [], //will be populated with account types from the API
            institutions: [] // This will hold the list of institutions fetched from the API
        };
    },
    mounted() {
        this.loadInstitutions();
        this.loadCategories();
        this.loadAccountTypes();
    },
    methods: {
        // Fetch the list of institutions from the backend API
        loadInstitutions() {
            axios.get('/api/institutions').then(response => {
                this.institutions = response.data;
            });
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
        // Save the firm account details
        saveFirmAccount() {
            axios.post('/api/firmAccount-accounts', this.firmAccount)
                .then(response => {
                    alert('FirmAccount account created successfully!');
                    this.resetForm();
                })
                .catch(error => {
                    console.error('Error creating firm account:', error);
                });
        },
        // Reset the form fields after saving or canceling
        resetForm() {
            this.firmAccount = {
                displayText: '',
                accountCategory: '',
                accountNumber: '',
                accountHolderType: 'Natural Person',
                companyName: '',
                registrationNumber: '',
                accountType: '',
                institutionId: '',
                branchCode: '',
                myReference: '',
                recipientReference: '',
            };
        },
        // Handle cancel button
        cancel() {
            this.$router.go(-1);  // Navigate back
        },
    }
};
</script>

<style scoped>
.section-title {
    font-weight: bold;
    margin-bottom: 20px;
}
.card-body {
    padding: 20px;
}
.form-group label {
    font-weight: bold;
}
</style>
