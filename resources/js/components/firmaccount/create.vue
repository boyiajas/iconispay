<template>
    <div class="container mt-4 mb-5">
        <h2 class="section-title">Source Account <small>Create an account</small></h2>

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
                            <select v-model="firmAccount.accountCategory" class="form-control" id="category" required>
                                <option value="">Select a category</option>
                                <option v-for="category in categories" :key="category" :value="category.id">{{ category.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="displayText">Number of Authorisations:</label>
                        <div class="col-sm-10">
                            <input type="number" v-model="firmAccount.numberOfAuthorizer" class="form-control" id="displayText" placeholder="Specify the number of authorisations required for this account or leave blank to use global firm number">
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
                                <input class="form-check-input" type="radio" v-model="firmAccount.accountHolderType" id="natural" value="natural">
                                <label class="form-check-label" for="natural">Natural Person</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" v-model="firmAccount.accountHolderType" id="juristic" value="juristic">
                                <label class="form-check-label" for="juristic">Juristic Person</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0 row form-group">
                        <label for="account_holder" class="form-label col-sm-2" v-if="this.firmAccount.accountHolderType == 'natural'">Account Holder: *</label>
                        <div class="col-sm-10 row pr-0 mb-1" v-if="this.firmAccount.accountHolderType == 'natural'">
                            <div class="col-sm-2 pr-0" id="initials">
                                <input type="text" v-model="firmAccount.initials" class="form-control" maxlength="10" placeholder="Initials" required>
                            </div>
                            <div class="col-sm-10 pr-0 mb-1">
                                <input type="text" v-model="firmAccount.surname" class="form-control" placeholder="Surname" required>
                            </div>
                        </div>
                        <label for="account_holder" class="form-label col-sm-2" v-if="this.firmAccount.accountHolderType == 'juristic'">Company Name: *</label>
                        <div class="col-sm-10 mb-2" v-if="this.firmAccount.accountHolderType == 'juristic'">
                            <input type="text" v-model="firmAccount.companyName" class="form-control" id="companyName" placeholder="Enter the name of the Company">
                        </div>
                        
                    </div>
                    
                    <div class="mb-2 row form-group" v-if="this.firmAccount.accountHolderType == 'natural'">
                        <label for="id_number" class="form-label col-sm-2">ID No. / Passport No.:</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.idNumber" class="form-control" placeholder="Enter ID Number or leave blank if not known">
                        </div>
                    </div>
                    <div class="mb-2 row form-group" v-if="this.firmAccount.accountHolderType == 'juristic'">
                        <label for="id_number" class="form-label col-sm-2">Registration #.:</label>
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
                            <select v-model="firmAccount.institution.id" class="form-control" id="institution" required @change="onInstitutionChange">
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

                    <PermissionControl :roles="['superadmin']">
                        <!-- Organisation Dropdown -->
                        <div class="form-group mb-3 row">
                            <label  class="form-label col-sm-2" for="organisation">Organisation: </label>
                            <div class="col-sm-10">
                                <select v-model="firmAccount.organisation_id" class="form-control">
                                    <option value="" disabled>Select an organisation</option>
                                    <option v-for="org in organisations" :key="org.id" :value="org.id">
                                        {{ org.name }} -- {{ org.location }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </PermissionControl>

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
                        <label for="branch_code" class="form-label col-sm-2">Verification:</label>
                        <div class="col-sm-10">
                            <div class="pl-2" style="background-color:#eee;border-radius: 5px;">
                                <div class="form-check pt-2 pb-2">
                                    <span v-if="firmAccount.verified === 1">
                                        <input  class="form-check-input" type="checkbox" id="gridCheck"  :checked="firmAccount.verified === 1" :disabled="firmAccount.verified === 1">
                                        <label class="form-check-label" for="gridCheck">
                                            Completed for account holder and account details
                                        </label>
                                    </span>
                                    <span v-else>
                                        <input class="form-check-input" type="checkbox" id="gridCheck" v-model="firmAccount.verified">
                                        <label class="form-check-label" for="gridCheck">
                                            Verify account holder and account details
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="emailAddress">Email Address:</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.emailAddress" class="form-control" id="emailAddress" placeholder="Enter the email address">
                        </div>
                    </div>
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for="phoneNumber">Phone Number:</label>
                        <div class="col-sm-10">
                            <input type="text" v-model="firmAccount.phoneNumber" class="form-control" id="phoneNumber" placeholder="Enter the phone number e.g +27767676787">
                        </div>
                    </div>
                    <div class="form-group mb-3 row">
                        <label class="form-label col-sm-2" for=""></label>
                        <div class="col-sm-10">
                            <!-- Save Button -->
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary ml-1" @click="cancel">Cancel</button>
                        </div>
                    </div>

                    
                </form>
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
                            <p class="form-label mt-3 mb-4">The entry was successfully saved</p>
                            <div class="alert alert-success p-2 mb-4" role="alert" v-if="avsResult && avsResult.account_found">
                                <h6>The account holder matched the account details</h6>
                            </div>
                            <div class="alert alert-success p-2 mb-4" role="alert" v-else>
                                <h6>The account holder fields did not match</h6>
                            </div>
                            <h5>Account Results 
                                <span class="pull-right">
                                    <span  class="text-success mr-4"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i>Found</span>
                                    <span  class="text-success"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i>Open (for 3+ months)</span>
                                </span>
                            </h5>
                            <hr class="mt-0 mb-2">
                            <div class="row mb-0">
                                    <div class="col-sm-3"><p class="pull-right"><strong>Number:</strong></p></div>
                                    <div class="col-sm-5">{{ avsResult && avsResult.account_number }}</div>
                                    <div class="col-sm-4"></div>
                            </div>
                            <div class="row mb-0">
                                    <div class="col-sm-3"><p class="pull-right"><strong>Branch Code:</strong></p></div>
                                    <div class="col-sm-5">{{ avsResult && avsResult.branch_code }}</div>
                                    <div class="col-sm-4"></div>
                            </div>
                            <div class="row mb-0">
                                    <div class="col-sm-3"><p class="pull-right"><strong>Account Type:</strong></p></div>
                                    <div class="col-sm-5">{{ avsResult && avsResult.branch_code }}</div>
                                    <div class="col-sm-4"> <span class="text-success" v-if="avsResult && avsResult.account_found"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span></div>
                            </div>
                            <br/><br/>
                            <h5>Account Holder Results</h5>
                            <hr class="mt-0 mb-2">
                            <div v-if="avsResult && avsResult.account_holder_type == 'natural'">
                                <div class="row mb-0">
                                    <div class="col-sm-3"><p class="pull-right"><strong>Initials:</strong></p></div>
                                    <div class="col-sm-5">{{ avsResult.initials }}</div>
                                    <div class="col-sm-4"> <span class="text-success" v-if="avsResult && avsResult.holder_matched"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span></div>
                                </div>
                                <div class="row mb-0">
                                    <div class="col-sm-3"><p class="pull-right"><strong>Name:</strong></p></div>
                                    <div class="col-sm-5">{{ avsResult.surname }}</div>
                                    <div class="col-sm-4"> <span class="text-success" v-if="avsResult && avsResult.holder_matched"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span></div>
                                </div>
                                <div class="row mb-0">
                                    <div class="col-sm-3"><p class="pull-right"><strong>Id Number:</strong></p></div>
                                    <div class="col-sm-5">{{ avsResult.id_number }}</div>
                                    <div class="col-sm-4"> <span class="text-fade"><i class="far fa-square mr-1 disabled" aria-hidden="true"></i> Not Supplied</span></div>
                                </div>
                            </div>
                            <div v-else>
                                <div class="row mb-0">
                                    <div class="col-sm-3"><p class="pull-right"><strong>Name:</strong></p></div>
                                    <div class="col-sm-5">{{ avsResult && avsResult.holder_name }}</div>
                                    <div class="col-sm-4"> <span class="text-success" v-if="avsResult && avsResult.holder_matched"><i class="far fa-check-square bg-green mr-1" aria-hidden="true"></i> Matched</span></div>
                                </div>
                                <div class="row mb-0">
                                    <div class="col-sm-3"><p class="pull-right"><strong>Registration No.:</strong></p></div>
                                    <div class="col-sm-5"></div>
                                    <div class="col-sm-4"> <span class="text-fade"><i class="far fa-square mr-1 disabled" aria-hidden="true"></i> Not Supplied</span></div>
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
         <!-- AVS Result Modal Ends here -->
    </div>
    
</template>

<script>
import axios from 'axios';
import { useToast } from 'vue-toastification';
import PermissionControl from '../permission/PermissionControl.vue';

export default {
    data() {
        return {
            organisation: { id: null, name: '', description: '' },
            organisations: [],
            firmAccount: {
                displayText: '',
                accountCategory: '',
                numberOfAuthorizer: '',
                accountNumber: '',
                accountHolderType: 'natural', // Default selection
                initials: '',
                surname: '',
                companyName: '',
                idNumber: '',
                registrationNumber: '',
                accountType: '',
                institution: { id: null},
                branchCode: '',
                myReference: '',
                recipientReference: '',
                verified: false,
                emailAddress: '',
                phoneNumber: '',
            },
            //accountCategories: ['Firm business account', 'Compliance Certificate Provider', 'Municipality / Government'], // Add more categories as needed
            categories: [],  // Will be populated with categories from the API
            accountTypes: [], //will be populated with account types from the API
            institutions: [], // This will hold the list of institutions fetched from the API
            avsResult: {},  // Store the AVS result data

            showAvsModelInstance: null,
        };
    },
    mounted() {
        this.loadInstitutions();
        this.loadCategories();
        this.loadAccountTypes();
        this.getAllOrganisations();
    },
    setup() {
        // Initialize toast
        const toast = useToast();
        return { toast };
    },
    methods: {
        getAllOrganisations() {
            axios.get('/api/organisation/all-organisations')
                .then(response => {
                    this.organisations = response.data;
                })
                .catch(error => {
                    this.toast.error('Error fetching organisations.');
                });
        },
        // Perform AVS Verification using Axios
        performAvsVerification(accountData) { 
            axios.post('/api/avs/verify', {
                    account_number: accountData.accountNumber,
                    branch_code: accountData.branchCode,
                    account_holder: accountData.displayText,
                    account_holder_type: accountData.accountHolderType,
                    registration_number: accountData?.registrationNumber,
                    id_number: accountData?.idNumber,
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
                })
                .catch(error => {
                    console.error('AVS Verification failed:', error);
                    //alert('AVS Verification failed. Please try again.');
                    if (error.response && error.response.data.errors) {
                        this.errors = error.response.data.errors;  // Show validation errors
                    }
                });
        },
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
        onInstitutionChange() {
           
            const selectedInstitution = this.institutions.find(institution => institution.id === this.firmAccount.institution.id);
            
            if (selectedInstitution) {
                this.firmAccount.branchCode = selectedInstitution.branch_code;
            } else {
                this.firmAccount.branchCode = ''; // Clear branch code if no institution is selected
            }
        },
        // Save the firm account details
        saveFirmAccount() {
            //alert('FirmAccount account created successfully!');
            const verifiedStatus = this.firmAccount.verified;
            this.firmAccount.verified = 0;

            axios.post('/api/firm-accounts', this.firmAccount)
                .then(response => {
                    // Show success toast
                    this.toast.success('Firm account created successfully!', {
                        title: 'Success'
                    });

                    if(verifiedStatus){
                        // Show the AVS Result Modal after verification
                        this.showAvsModelInstance = new bootstrap.Modal(document.getElementById('avsResultModal'));
                        this.showAvsModelInstance.show();
                        
                        this.performAvsVerification(this.firmAccount);

                        // Reset the form after submission
                        this.resetForm();

                    }else{
                        this.resetForm();
                    }

                })
                .catch(error => {
                    //console.error('Error creating firm account:', error);
                    this.toast.error(error.response ? error.response?.data?.message : 'No response data', {
                        title: 'Error'
                    });
                });
        },
        // Reset the form fields after saving or canceling
        resetForm() {
            this.firmAccount = {
                displayText: '',
                accountCategory: '',
                numberOfAuthorizer: '',
                accountNumber: '',
                accountHolderType: 'natural', // Default selection
                initials: '',
                surname: '',
                companyName: '',
                idNumber: '',
                registrationNumber: '',
                accountType: '',
                institution: { id: null},
                branchCode: '',
                myReference: '',
                recipientReference: '',
                verified: false,
                emailAddress: '',
                phoneNumber: '',
            };
        },
        closeModal() {
            //const newUserModal = bootstrap.Modal.getInstance(document.getElementById('newUserModal'));
            if(this.showAvsModelInstance){
                this.showAvsModelInstance.hide();
            }
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
