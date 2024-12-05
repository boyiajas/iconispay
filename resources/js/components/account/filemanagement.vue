<template>
    <div class="container mt-4 mb-5">
        <h2>File Management <span style="color:#999;font-weight: normal;font-size: 20px;">{{ fileDetails.fileReference }}</span>
            <span>
                <button class="btn btn-light btn-default-default btn-sm ml-1 pull-right" @click="printPage"><i class="fas fa-print"></i> Print</button>
            </span>
        </h2>
        <div class="mb-3">
            <div class="d-flex justify-content-between px-3 py-2 bg-faded rounded">
                <div><strong>Status:</strong> <span class="badge bg-info" style="border-radius: 3px;">{{ fileDetails.status }}</span></div>
                <div><strong>Number of Payments:</strong> {{ fileDetails.numberOfPayments }}</div>
                <div><strong>Total in File:</strong> R{{ formatNumberWithCommas(fileDetails.totalAmount) }}</div>
                <div><strong>Total Confirmed:</strong> R{{ formatNumberWithCommas(fileDetails.totalConfirmed) }}</div>
                <div><a class="btn btn-white btn-sm" :href="`/secure-download/${fileId}`"><i class="fa fa-download"></i> Download File</a></div>
            </div>
        </div>
        <div class="mb-3">
            <div class="px-3 py-2 bg-faded rounded">
                <div class="d-flex justify-content-end">
                    <span v-if="anyChecked">{{ selectedPaymentsCount }} selected payment(s) &nbsp; - &nbsp; {{ selectedPaymentsTotal }}</span>
                    <button class="btn btn-light btn-white btn-sm ml-4" @click="selectAll">Select All</button>
                    <button class="btn btn-light btn-white btn-sm ml-2" :class="{ disabled: !anyChecked }" @click="deselectAll">Deselect All</button>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <span class="text-fade">Mark selected as: </span>
                    <button class="btn btn-primary btn-sm text-white ml-3" :class="{ disabled: !anyChecked }" @click="markGenerated">Generated</button>
                    <button class="btn btn-success btn-sm text-white ml-1" :class="{ disabled: !anyChecked }" @click="markProcessed">Processed</button>
                    <button class="btn btn-warning btn-sm ml-1" :class="{ disabled: !anyChecked }" @click="markFailed">Failed</button>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Payment Details</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="15%">File Reference</th>
                                    <th width="20%">Recipient Account</th>
                                    <th width="20%">Recipient Reference</th>
                                    <th width="20%">My Reference</th>
                                    <th>Amount</th>
                                    <th width="12%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(payment, index) in fileDetails.payments" :key="index">
                                    <td><div>{{ payment.fileReference }} </br><span style="color:#999;font-size:14px;">Created By {{ payment.requisitionCreatedBy.name }}</span></div></td>
                                    <td>{{ payment.recipientDisplayText }}  -  {{ payment.payToAccountInstitution || 'N/A' }}
                                    <br/> ({{ payment.recipientBranchCode || 'N/A' }})
                                    - {{ payment.recipientAccount || 'N/A' }}
                                    </td>
                                    <td>{{ payment.recipientReference }}</td>
                                    <td>{{ payment.myReference }}</td>
                                    <td>R{{ payment.amount }}</td>
                                    <td>
                                        <span 
                                            :class="{
                                                'badge-info': payment.status === 'Generated',
                                                'badge-success': payment.status === 'processed',
                                                'badge-warning': payment.status === 'failed'
                                            }"
                                            class="badge "
                                            style="border-radius: 3px;"
                                        >
                                            {{ payment.status }}
                                        </span>
                                        <span class="pull-right"><input class="form-check-input" type="checkbox" id="gridCheck" v-model="payment.marked"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </li>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between mt-2 mb-3">
                            <div>No of confirmed payments: {{ fileDetails.numberOfProcessedPayments }}</div>
                            <div class="d-flex">
                                <div>Confirmed Amount: </div>
                                <div style="width:150px;border-top:solid 2px #999;margin-right:150px;margin-left:50px;">R{{ formatNumberWithCommas(fileDetails.totalConfirmed) }}</div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 mb-3">
                            <div>No of payments: {{ fileDetails.numberOfPayments }}</div>
                            <div class="d-flex">
                                <div>Total File Amount: </div>
                                <div style="width:150px;border-top:solid 2px #999;margin-right:150px;margin-left:50px;">
                                    R{{ formatNumberWithCommas(fileDetails.totalAmount) }}
                                    </div>
                            </div>
                        </div>
                    </li>
                </ul>

            </div>

        </div>
        <div class="mb-3">
            <div class="py-1 rounded">
                <div class="d-flex justify-content-between">
                    <button class="btn btn-default-default btn-sm pull-right" @click="cancelButton"> Discard</button>
                    <button 
                        class="btn btn-primary btn-sm pull-right" 
                        :class="{ disabled: fileDetails.numberOfProcessedPayments !== fileDetails.numberOfPayments }" 
                        @click="savePayments"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">History Log</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(log, index) in fileDetails.historyLog" :key="index">
                            <td>{{ log.user_name }}</td>
                            <td>{{ log.action }}</td>
                            <td>{{ log.details }}</td>
                            <td>{{ log.date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>

import { useToast } from 'vue-toastification';

export default {
    data() {
        return {
            fileId: this.$route.params.id,
            fileDetails: {
                fileReference: "",
                status: "",
                createdBy: "",
                numberOfPayments: 0,
                totalAmount: 0.00,
                totalConfirmed: 0.00,
                historyLog: [
                    {
                        user_name: "Brian@LegalAssoc",
                        action: "Downloaded Payaway File",
                        details: "Downloaded the pay away file FNB - 446578856",
                        date: "2018-12-19 07:53 AM"
                    }
                ],
                payments:[

                ],
            }
        };
    },
    computed: {
        anyChecked() {
            // Check if any payment is marked
            return this.fileDetails.payments.some(payment => payment.marked);
        },
        selectedPaymentsCount() {
            // Calculate the number of selected payments
            return this.fileDetails.payments.filter(payment => payment.marked).length;
        },
        selectedPaymentsTotal() {
            // Calculate the total amount of selected payments as numbers
            const total = this.fileDetails.payments
            .filter(payment => payment.marked)
            .reduce((sum, payment) => {
                // Remove commas and convert to number, with a fallback of 0
                const amount = parseFloat(payment.amount.toString().replace(/,/g, '')) || 0;
                return sum + amount;
            }, 0);
            // Return the total with the "R" prefix
            return `R${this.formatNumberWithCommas(total)}`;
        }
    },
    mounted() {
        this.fetchFileDetails();
    },
    setup() {
        // Initialize toast
        const toast = useToast();
        return { toast };
    },
    methods: {
        formatNumberWithCommas(value) {
            // Ensure the value is a valid number
            const number = parseFloat(value);
            if (isNaN(number)) {
                return "0.00"; // Default to "0.00" if the value is not a number
            }

            // Format the number with two decimal places and add commas
            return number.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        fetchFileDetails() { 
            axios.get(`/api/file-management/${this.fileId}`)
                .then(response => {
                    const data = response.data;
                    console.log("data fetched  ",data);
                    this.fileDetails.fileReference = data.fileReference;
                    this.fileDetails.status = data.status;
                    this.fileDetails.numberOfPayments = data.numberOfPayments;
                    this.fileDetails.totalAmount = data.totalAmount;
                    this.fileDetails.totalConfirmed = data.totalConfirmed;
                    this.fileDetails.historyLog = data.historyLog;
                    this.fileDetails.payments = data.payments;
                    this.fileDetails.numberOfProcessedPayments = data.numberOfProcessedPayments;
                    this.fileDetails.createdBy = data.createdBy;

                    this.updateConfirmedPayments();
                    
                })
                .catch(error => {
                    console.error("Error fetching file details:", error);
                });
        },
        selectAll() {
            // Mark all payments as selected
            this.fileDetails.payments.forEach(payment => {
                payment.marked = true;
            });
        },
        deselectAll() {
            // Unmark all payments
            this.fileDetails.payments.forEach(payment => {
                payment.marked = false;
            });
        },
        savePayments() {
            // Placeholder for save functionality
            // Collect the IDs of all requisitions related to the payments
            const requisitionIds = this.fileDetails.payments.map(payment => payment.requisition_id);

            console.log("this are the requisition ids ", requisitionIds);

            // Make a request to the backend to update the status of the requisitions
            axios.post('/api/requisitions/update-status', { requisitionIds })
                .then(response => {
                    //alert("Requisitions updated successfully.");
                    //this.fetchFileDetails(); // Refresh file details after updating
                    
                    this.toast.success('Payments marked as completed successfully.', {
                        title: 'Success'
                    });

                    this.$router.push({ name: 'accounts' });
                })
                .catch(error => {
                    console.error("Error updating requisitions:", error);
                });
        },
        markProcessed() {
            // Collect IDs of selected payments
            const selectedPaymentIds = this.fileDetails.payments
                .filter(payment => payment.marked)
                .map(payment => payment.id); console.log("payment ids ", selectedPaymentIds);

            // Make an Axios request to mark the payments as processed
            axios.post('/api/payments/mark-processed', { paymentIds: selectedPaymentIds })
                .then(response => {
                    this.fetchFileDetails(); // Refresh file details
                    this.toast.success('Payments marked as processed successfully.', {
                        title: 'Success'
                    });
                    //alert("Payments marked as processed successfully.");
                })
                .catch(error => {
                    console.error("Error marking payments as processed:", error);
                });
        },
        markFailed() {
            // Collect IDs of selected payments
            const selectedPaymentIds = this.fileDetails.payments
                .filter(payment => payment.marked)
                .map(payment => payment.id);

            // Make an Axios request to mark the payments as failed
            axios.post('/api/payments/mark-failed', { paymentIds: selectedPaymentIds })
                .then(response => {
                    this.fetchFileDetails(); // Refresh file details
                    alert("Payments marked as failed successfully.");
                })
                .catch(error => {
                    console.error("Error marking payments as failed:", error);
                });
        },
        markGenerated() {
            // Collect IDs of selected payments
            const selectedPaymentIds = this.fileDetails.payments
                .filter(payment => payment.marked)
                .map(payment => payment.id);

            // Make an Axios request to mark the payments as generated
            axios.post('/api/payments/mark-generated', { paymentIds: selectedPaymentIds })
                .then(response => {
                    this.fetchFileDetails(); // Refresh file details
                    
                    alert("Payments marked as generated successfully.");
                })
                .catch(error => {
                    console.error("Error marking payments as generated:", error);
                });
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'ZAR' }).format(value);
        },
        updateConfirmedPayments() {
            // Calculate the number of payments with status "processed"
            const confirmedPayments = this.fileDetails.payments.filter(payment => payment.status === 'processed');
            
            // Update number of confirmed payments
            this.fileDetails.numberOfConfirmedPayments = confirmedPayments.length;

            // Calculate the total confirmed amount
            const totalConfirmedAmount = confirmedPayments.reduce((sum, payment) => {
                const amount = parseFloat(payment.amount.toString().replace(/,/g, '')) || 0;
                return sum + amount;
            }, 0);

            // Update the total confirmed amount
            this.fileDetails.totalConfirmed = totalConfirmedAmount;
        },
        /* downloadFile() {
            // Logic to download the file
            
            alert("Download functionality will be implemented here.");
        }, */
        // Print the current page
        printPage() {
            console.log("this is our data", this.fileDetails);
            // Create the print-friendly content dynamically
            const printContent = `
                <div style="font-family: Arial, sans-serif; margin: 20px;">
                    <h2>File Management</h2>
                    <h3>${this.fileDetails.fileReference}</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; text-align: left;">
                        <tbody style="border: 1px solid #ddd;font-size:14px;">
                            <tr style="background: #f2f2f2; text-align: left;">
                                <th style="width:50%;padding:10px;">Status: <span style="border:solid 1px #999;font-size:10px;padding-left:5px; padding-right: 5px;">${this.fileDetails.status}</span></th>
                                <th style="width:50%;padding:10px;">Number of Payments: <span style="font-weight: normal;">${this.fileDetails.numberOfPayments}</span></th>
                            </tr>
                            <tr>
                                <th style="width:50%;padding:10px;">Total in File: <span style="font-weight: normal;">R${this.formatNumberWithCommas(this.fileDetails.totalAmount)}</span></th>
                                <th style="width:50%;padding:10px;">Total Confirmed: <span style="font-weight: normal;">R${this.formatNumberWithCommas(this.fileDetails.totalConfirmed)}</span></th>
                            </tr>
                        </tbody>
                    </table>
                   
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <tbody style="border: 1px solid #ddd;font-size:14px;padding: 10px;">
                            <tr style="background: #f2f2f2; text-align: left;">
                                <th style="width:15%;padding:10px;">File Reference</th>
                                <th style="width:15%;padding:10px;">Recipient Account</th>
                                <th style="width:15%;padding:10px;">Recipient Reference</th>
                                <th style="width:25%;padding:10px;">My Reference</th>
                                <th style="width:15%;padding:10px;">Amount</th>
                                <th style="width:10%;padding:10px;">Status</th>
                            </tr>
                        </tbody>
                    </table>
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <tbody style="border: 1px solid #ddd;font-size:14px;padding: 10px;">
                            ${this.fileDetails.payments
                                .map(
                                    payment => `
                                    <tr style="background: #f2f2f2; text-align: left;">
                                        <td style="width:15%;padding:10px;">${payment.fileReference} </br><span style="color:#aaa;font-size:10px;"><i>Created By ${payment.requisitionCreatedBy.name }</i></span></td>
                                        <td style="width:15%;padding:10px;">${payment.recipientDisplayText} - ${payment.payToAccountInstitution} (${payment.recipientBranchCode}) - ${payment.recipientAccount}</td>
                                        <td style="width:15%;padding:10px;">${payment.recipientReference}</td>
                                        <td style="width:25%;padding:10px;">${payment.myReference}</td>
                                        <td style="width:15%;padding:10px;">R${payment.amount}</td>
                                        <td style="width:10%;padding:10px;"><span style="border:solid 1px #999;font-size:10px;padding-left:5px; padding-right: 5px;">${payment.status}</span></td>
                                    </tr>
                                `
                                )
                                .join('')}
                        </tbody>
                    </table>
                    <table style="width: 100%; border-collapse: collapse;text-align: left;">
                        <tbody style="border: 1px solid #ddd;font-size:14px;">
                            <tr style="background: #f2f2f2; text-align: left;">
                                <td style="width:50%;padding:10px;"><i>No of Confirmed Payments: <span style="font-weight: normal;">${this.fileDetails.numberOfProcessedPayments}</span></i></td>
                                <td style="width:50%;padding:10px;">Confirmed Amount: <div style="font-weight: normal; border-top:solid 2px #666;width: 150px;float: right;">R${this.formatNumberWithCommas(this.fileDetails.totalConfirmed)}</div></td>
                            </tr>
                            <tr>
                                <td style="width:50%;padding:10px;"><i>Number of Payments: <span style="font-weight: normal;">${this.fileDetails.numberOfPayments}</span></i></td>
                                
                                <td style="width:50%;padding:10px;">Total File Amount: <div style="font-weight: normal; border-top:solid 2px #666;width: 150px;float: right;">R${this.formatNumberWithCommas(this.fileDetails.totalAmount)}</div></td>
                            </tr>
                        </tbody>
                    </table>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; text-align: left;">
                        <tbody style="border: 1px solid #ddd;font-size:14px;">
                            <tr style="background: #f2f2f2; text-align: left;">
                                <td style="width:50%;padding:10px;"><i>Generated by: <span style="font-weight: normal;">${this.fileDetails.createdBy}</span></i></td>
                                
                            </tr>
                            
                        </tbody>
                    </table>
                   
                    <h4>History Log</h4>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <thead>
                            <tr style="background: #f2f2f2;">
                                <th style="padding: 8px; border: 1px solid #ddd;">User Name</th>
                                <th style="padding: 8px; border: 1px solid #ddd;">Action</th>
                                <th style="padding: 8px; border: 1px solid #ddd;">Details</th>
                                <th style="padding: 8px; border: 1px solid #ddd;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${this.fileDetails.historyLog
                                .map(
                                    log => `
                                    <tr>
                                        <td style="padding: 8px; border: 1px solid #ddd;">${log.user_name}</td>
                                        <td style="padding: 8px; border: 1px solid #ddd;">${log.action}</td>
                                        <td style="padding: 8px; border: 1px solid #ddd;">${log.details}</td>
                                        <td style="padding: 8px; border: 1px solid #ddd;">${log.date}</td>
                                    </tr>
                                `
                                )
                                .join('')}
                        </tbody>
                    </table>
                </div>
            `;

            // Open a new window for printing
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Print</title></head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        },

        cancelButton() {
            this.$router.go(-1);
        }
    }
};
</script>

<style scoped>
.table {
    text-align: left;
}

.card-header {
    font-weight: bold;
}

.text-end {
    text-align: right;
}
</style>
