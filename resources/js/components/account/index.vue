<template>
    <div class="container mt-4">
        <!-- Accounts Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Accounts <span class="pull-right"><button class="btn btn-white btn-sm" @click="refreshData">Refresh</button></span></h5>
            </div>
            <div class="card-body">
                
                <div class="table-responsive">
                    <table id="accounts-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                        <thead>
                            <tr class="table-secondary">
                                <th>Method</th>
                                <th width="20%">Account</th>
                                <th>Institution</th>
                                <th>Account Number</th>
                                <th>Ready for Payment</th>
                                <th width="27%">Pending Confirmation Files</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Confirmation Files Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Pending Confirmation Files</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="pending-confirmation-files-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                        <thead>
                            <tr class="table-secondary">
                                <th>Account</th>
                                <th>File</th>
                                <th>Payments</th>
                                <th>Date Generated</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recently Closed Files Table -->
        <div class="card">
            <div class="card-header">
                <h5>Recently Closed Files</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <label for="fromDate" class="me-2">From:</label>
                    <input type="date" v-model="fromDate" class="form-control me-3" id="fromDate">
                    <label for="toDate" class="me-2">To:</label>
                    <input type="date" v-model="toDate" class="form-control me-3" id="toDate">
                    <button class="btn btn-primary" @click="filterClosedFiles">Go</button>
                </div>
                <div class="table-responsive">
                    <table id="recently-closed-files-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                        <thead>
                            <tr class="table-secondary">
                                <th width="20%">Account</th>
                                <th>File</th>
                                <th>Payments</th>
                                <th>Date Completed</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

       
        <!-- File Details Modal -->
        <div class="modal fade" id="fileDetailsModal" tabindex="-1" aria-labelledby="fileDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fileDetailsModalLabel">File Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- File Status -->
                        <div v-if="fileDetails.statusMessage" class="alert alert-info px-2 py-2" role="alert">
                            {{ fileDetails.statusMessage }}
                        </div>

                        <!-- Source Account Details -->
                        <h5 class="mb-2 mt-4">Source Account: <span class="text-fade" style="font-size: 16px;">{{ fileDetails.accountHolder }} - {{ fileDetails.accountName }}</span></h5>
                        <div class="row px-3 py-2 mx-0 bg-faded rounded">
                            <div class="col-sm-6">{{ fileDetails.institution }} - {{ fileDetails.accountNumber }}</div>
                            <div class="col-sm-6"><strong>Account Holder:</strong> {{ fileDetails.accountHolder }}</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <div><strong>Time Generated:</strong> {{ fileDetails.timeGenerated }}</div>
                                <div><strong>Created By:</strong> {{ fileDetails.createdBy }}</div>
                            </div>
                            <div>
                                <a :href="`/secure-download/${fileDetails.fileId}`" class="btn btn-primary btn-sm"><i class="fa fa-download"></i> Download File</a>
                            </div>
                        </div>
                       
                        

                        <!-- File Details Table -->
                        <table class="table table-bordered mt-3 table-striped display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>File Reference</th>
                                    <th>Recipient Account</th>
                                    <th>Recipient Reference</th>
                                    <th>My Reference</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Check if files are available before looping -->
                                <!-- Check if files are available before looping -->
                                <template v-if="fileDetails && fileDetails.payments">
                                    <tr v-for="(payment, index) in fileDetails.payments" :key="index">
                                        <td>{{ payment.fileReference }}</td>
                                        <td>
                                            <div class="bg-faded rounded">
                                                <b>{{ payment.beneficiaryAccount.display_text }}</b> - 
                                                {{ payment.beneficiaryAccount.institution.short_name }}
                                                <br/>
                                                ({{ payment.beneficiaryAccount.branch_code }}) - {{ payment.beneficiaryAccount.account_number }}
                                            </div>
                                            
                                        </td>
                                        <td>{{ payment.recipientReference }}</td>
                                        <td>{{ payment.myReference }}</td>
                                        <td>R{{ payment.amount }}</td>
                                        
                                    </tr>
                                </template>
                                <!-- Show a message if there are no files -->
                                <tr v-else>
                                    <td colspan="5" class="text-center">No file details available</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Total Amount and Number of Entries -->
                        <p class="text-end fw-bold">Total Amount: R{{ fileDetails.totalAmount }}</p>
                        <p class="text-end txt-xs">Number of payment entries: {{ fileDetails.numberOfPayments }}</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        
                        <button type="button" class="btn btn-danger btn-sm" @click="discardFile">Discard</button>
                        <div>
                            <a :href="`/secure-download/${fileDetails.fileId}`" class="btn btn-primary btn-sm mr-1"><i class="fa fa-download"></i> Download File</a>
                            <!-- <button type="button" class="btn btn-primary btn-sm mr-1" @click="downloadFile"><i class="fa fa-download"></i> Download File</button> -->
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" @click="closeModal">Close</button>
                        </div>
                            
                        
                        
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';
import $ from 'jquery';
/* import 'datatables.net';
import 'datatables.net-bs5'; */
import moment from 'moment';
import { useToast } from 'vue-toastification';

export default {
    name: 'Account',
    data() {
        return {
            fromDate: '',
            toDate: '',
            accountsTable: null,
            pendingConfirmationFilesTable: null,
            recentlyClosedFilesTable: null,
            fileDetialsModalInstance: null,
            formatDate(dateString) {
                //return moment(dateString).format('DD MMM YYYY');
                return moment(dateString).format('DD-MM-YYYY');
            },
            // Format the currency
            formatCurrency(value) {
                return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
            },
            sourceAccount: {
                name: "Legal Assoc FNB Trust account",
                details: "FNB - 446578856 (250655)",
                holder: "Legal Assoc"
            },
            fileDetails: {
                generatedTime: "19 Dec 2018 07:53",
                createdBy: "Brian Ndevu"
            },
            payments: [
                {
                    fileReference: "Payment Requisition - Training requirements",
                    description: "First payment",
                    recipientName: "Smith Golden Attorneys - Smith Golden Attorneys",
                    accountDetails: "FNB (250655) - 65468464688",
                    recipientReference: "Legal Assoc",
                    myReference: "Payment Requisition",
                    amount: 26000.00
                }
            ],
            totalAmount: 26000.00
        };
    },
    mounted() {
        this.initializeAccountsTable();
        this.initializePendingConfirmationFilesTable();
        this.initializeRecentlyClosedFilesTable();
    },
    setup() {
        // Initialize toast
        const toast = useToast();
        return { toast };
    },
    methods: {
        // Initialize the Accounts Table
        initializeAccountsTable() {
            if ($.fn.dataTable.isDataTable('#accounts-table')) {
                $('#accounts-table').DataTable().destroy(); // Destroy existing instance if already initialized
            }

            this.accountsTable = $('#accounts-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/accounts',
                    type: 'GET',
                    error: (xhr, error, thrown) => {
                        console.error('Error fetching data:', error, thrown);
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { 
                        data: 'ready_for_payment',
                        render: (data) => {
                            return `<span class="badge bg-default pr-3 pl-3">${data.generated_file_count > 0 ? 'Manual' : 'File Upload'}</span>`;
                        }
                    },
                    { data: 'display_text' },
                    { data: 'institution_name' },
                    { data: 'account_number' },
                    { 
                        data: 'ready_for_payment',
                        render: (data) => {
                            
                            return `
                                ${data.new_requisition_count} Requisition(s) 
                                <button class="btn ${(data.generated_file_count > 0 && data.new_requisition_count <= 0) ? 'bg-info text-white' : (data.new_requisition_count > 0 ? 'bg-info text-white generate-file-btn' : 'bg-default-default')} btn-sm ml-2 px-1 py-0"
                                    data-id="${data.account_id}" ${data.requisition_count > 0 ? '' : 'disabled'}>
                                    Generate
                                     ${data.new_requisition_count > 0 ? `<span class="badge text-info bg-white rounded-pill pr-1 pl-1">
                                        ${data.new_requisition_count}
                                    </span>` : `${data.new_requisition_count}`}
                                </button>
                            `;
                        }
                    },
                    { 
                        data: 'account',
                        render: (data, row) => {
                            //console.log("now we are here");
                            //console.log(data.pending_confirmation_files);
                            if (data.pending_confirmation_files && data.pending_confirmation_files.length > 0) {
                                return data.pending_confirmation_files.map(file => `
                                  
                                    <a href="#" class="btn btn-link p-0 file-management-btn" data-file-id="${file.file_id}">
                                        ${data.account_number} (${this.formatDate(file.generated_at)})
                                    </a>
                                    
                                `).join('') + `<span class="pull-right btn btn-sm btn-default-default py-0 px-1 edit-account-btn" data-firmaccount-id="${data.account_id}"><i class="fas fa-edit"></i></span>`;
                            } else {
                                return `No open files <span class="pull-right btn btn-sm btn-default-default py-0 px-1 edit-account-btn" data-firmaccount-id="${data.account_id}"><i class="fas fa-edit"></i></span>`;
                            }
                        }
                    }, 
                   
                ],
                createdRow: (row, data, dataIndex) => {
                    // Attach click event to the edit button to navigate to the AccountFileEdit component
                    $(row).find('.edit-account-btn').on('click', (event) => {
                        const firmAccountId = $(event.currentTarget).data('firmaccount-id');
                        if (firmAccountId) {
                            this.openAccountFileEdit(firmAccountId);
                        }
                    });

                    $(row).find('.file-management-btn').on('click', (event) => {
                        const fileId = $(event.currentTarget).data('file-id');
                        if (fileId) {
                            this.navigateToFileManagement(fileId);
                        }
                    });

                    $('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');
                }, 
                /* createdRow: function(row, data, dataIndex) {
                    // Apply style to all <td> elements
                    $('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');
                }, */
                responsive: true,
                paging: true,
                pageLength: 10,
                lengthMenu: [ [10, 25, 50, 100], [10, 25, 50, 100] ],
                searching: true,
                autoWidth: true,
               
                drawCallback: () => {
                    

                    $('#accounts-table').off('click', '.generate-file-btn').on('click', '.generate-file-btn', (event) => {
                        const sourceAccountId = $(event.currentTarget).data('id');
                       
                        if (sourceAccountId) {
                            this.generateFile(sourceAccountId);
                        } else {
                            console.error("Source Account ID is undefined");
                        }
                    });
                }
            });
        },
        // Method to navigate to AccountFileEdit.vue with the correct firm account ID
        openAccountFileEdit(firmAccountId) {
            this.$router.push({ name: 'accountfileedit', params: { id: firmAccountId } });
        },
        navigateToFileManagement(fileId) {
            this.$router.push({ name: 'filemanagement', params: { id: fileId } });
        },
        // Method to show the File Details Modal
         showFileDetailsModal(newFileDetails) {

            if(newFileDetails){
                this.fileDetails = newFileDetails

                this.fileDetialsModalInstance = new bootstrap.Modal(document.getElementById('fileDetailsModal'));
                this.fileDetialsModalInstance.show();

            }else{
                console.error("Error fetching file details:", error);
            }
        }, 

        generateFile(sourceAccountId) {
            axios.post(`/api/firm-accounts/${sourceAccountId}/generate-file`)
                .then(response => {
                    this.toast.success(response.data.message, {
                        title: 'Success'
                    });
                    this.accountsTable.ajax.reload();
                    //console.log(response.data.file);
                    this.pendingConfirmationFilesTable.ajax.reload();
                    this.showFileDetailsModal(response.data.file);
                })
                .catch(error => {
                    console.error("Error generating file:", error);
                });
        },
        // Initialize the Pending Confirmation Files Table
        initializePendingConfirmationFilesTable() {
    
            this.pendingConfirmationFilesTable = $('#pending-confirmation-files-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/pending-confirmation-files',
                    type: 'GET',
                    error: (xhr, error, thrown) => {
                        console.error('Error fetching data:', error, thrown);
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'display' },
                    { data: 'default_file_name' }, // This will display "Default - {account_number}"
                    { data: 'payments' },
                    { data: 'date_generated'},
                    { data: 'total_amount', render: $.fn.dataTable.render.number(',', '.', 2, 'R') },
                    { data: 'status' },
                    
                ],
                createdRow: (row, data, dataIndex) => {
                   
                    $(row).find('.file-management-btn').on('click', (event) => {
                        const fileId = $(event.currentTarget).data('file-id');
                        if (fileId) {
                            this.navigateToFileManagement(fileId);
                        }
                    });

                    $('td', row).css('word-wrap', 'break-word').css('white-space', 'normal');
                },
                responsive: true,
                destroy: true,
            });
        },

        // Initialize the Recently Closed Files Table
        initializeRecentlyClosedFilesTable() {
            this.recentlyClosedFilesTable = $('#recently-closed-files-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/recently-closed-files',
                    type: 'GET',
                    data: (d) => {
                        d.from_date = this.fromDate;
                        d.to_date = this.toDate;
                    },
                    error: (xhr, error, thrown) => {
                        console.error('Error fetching data:', error, thrown);
                        //alert('An error occurred while fetching the data. Please try again later.');
                    }
                },
                columns: [
                    { data: 'display_text' },
                    { data: 'file_name' },
                    { data: 'payments' },
                    { data: 'date_completed' },
                    { data: 'total_amount', render: $.fn.dataTable.render.number(',', '.', 2, 'R') },
                    { data: 'status' }
                ],
                responsive: true,
                destroy: true,
            });
        },
        closeModal() {
            //const newUserModal = bootstrap.Modal.getInstance(document.getElementById('newUserModal'));
            if (this.fileDetialsModalInstance) {
                this.fileDetialsModalInstance.hide();
            }
            
        },
        /* forceFileDownload(response){
            const url = window.URL.createObjectURL(new Blob([response.data]))
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', 'file.txt') //or any other extension
            document.body.appendChild(link)
            link.click()
        }, */
        /* downloadFile() {
            const fileId = this.fileDetails.fileId;
            if (!fileId) {
                this.toast.error('File ID is missing.', { title: 'Error' });
                return;
            }

            // Define the file URL
            const fileUrl = `/secure-download/${fileId}`;

            axios({
                method: 'get',
                url: fileUrl,
                responseType: 'arraybuffer'
            })
            .then(response => {
                
                this.forceFileDownload(response)
                
            })
            .catch(() => console.log('error occured'))

        }, */

        // Refresh all data
        refreshData() {
            this.accountsTable.ajax.reload();
            this.pendingConfirmationFilesTable.ajax.reload();
            this.recentlyClosedFilesTable.ajax.reload();
        },

        // Filter Recently Closed Files based on date range
        filterClosedFiles() {
            this.recentlyClosedFilesTable.ajax.reload();
        }
    }
};
</script>

<style scoped>
.section-title {
    font-weight: bold;
    margin-bottom: 20px;
}

.table-responsive {
    border-radius: 5px;
    overflow: hidden;
}

.table thead th {
    font-weight: bold;
    text-align: center;
}

.table tbody td {
    text-align: center;
}

.table-secondary {
    background-color: #f2f2f2;
}

.form-control {
    max-width: 150px;
}

.modal-content {
    border-radius: 5px;
}

.alert {
    font-size: 14px;
}

.source-account h6 {
    font-weight: bold;
}

.text-muted {
    font-size: 12px;
}

.table th, .table td {
    vertical-align: middle;
    font-size: 14px;
}

.modal-footer .btn {
    min-width: 100px;
}
</style>
