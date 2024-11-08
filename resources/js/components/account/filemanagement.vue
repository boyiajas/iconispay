<template>
    <div class="container mt-4">
        <h4>File Management <span style="color:#999;font-weight: normal;font-size: 20px;">{{ fileDetails.fileReference }}</span>
            <span>
                <button class="btn btn-light btn-default-default btn-sm ml-1 pull-right" @click="printPage"><i class="fas fa-print"></i> Print</button>
            </span>
        </h4>
        <div class="mb-3">
            <div class="d-flex justify-content-between px-3 py-2 bg-faded rounded">
                <div><strong>Status:</strong> <span class="badge bg-info" style="border-radius: 3px;">{{ fileDetails.status }}</span></div>
                <div><strong>Number of Payments:</strong> {{ fileDetails.numberOfPayments }}</div>
                <div><strong>Total in File:</strong> {{ formatCurrency(fileDetails.totalAmount) }}</div>
                <div><strong>Total Confirmed:</strong> {{ formatCurrency(fileDetails.totalConfirmed) }}</div>
                <div><a class="btn btn-white btn-sm" :href="`/secure-download/${fileId}`"><i class="fa fa-download"></i> Download File</a></div>
            </div>
        </div>
        <div class="mb-3">
            <div class="px-3 py-2 bg-faded rounded">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-light btn-white btn-sm pull-right" @click="printPage"> Select All</button>
                    <button class="btn btn-light btn-white btn-sm pull-right disabled" @click="printPage"> Deselect All</button>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <span class="text-fade">Mark selected as: </span>
                    <button class="btn btn-primary btn-sm text-white pull-right ml-1" @click="printPage">Generated</button>
                    <button class="btn btn-success btn-sm text-white pull-right ml-1" @click="printPage">Processed</button>
                    <button class="btn btn-warning btn-sm pull-right disabled ml-1" @click="printPage">Failed</button>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Payment Details</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>File Reference</th>
                            <th>Recipient Account</th>
                            <th>Recipient Reference</th>
                            <th>My Reference</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(payment, index) in fileDetails.payments" :key="index">
                            <td>{{ payment.fileReference }}</td>
                            <td>{{ payment.recipientAccount }}</td>
                            <td>{{ payment.recipientReference }}</td>
                            <td>{{ payment.myReference }}</td>
                            <td>R{{ payment.amount }}</td>
                            <td>
                                <span class="badge bg-info" style="border-radius: 3px;">{{ payment.status }}</span>
                                <span class="pull-right"><input class="form-check-input" type="checkbox" id="gridCheck" v-model="payment.marked"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-3">
            <div class="py-1 rounded">
                <div class="d-flex justify-content-between">
                    <button class="btn btn-default-default btn-sm pull-right" @click="cancelButton"> Discard</button>
                    <button class="btn btn-primary btn-sm pull-right" @click="printPage"> Save</button>
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
                            <td>{{ log.userName }}</td>
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
export default {
    data() {
        return {
            fileId: this.$route.params.id,
            fileDetails: {
                fileReference: "",
                status: "",
                numberOfPayments: 0,
                totalAmount: 0.00,
                totalConfirmed: 0.00,
                historyLog: [
                    {
                        userName: "Brian@LegalAssoc",
                        action: "Downloaded Payaway File",
                        details: "Downloaded the pay away file FNB - 446578856",
                        date: "2018-12-19 07:53 AM"
                    },
                    {
                        userName: "Brian@LegalAssoc",
                        action: "Created Hash",
                        details: "Added a hash validation to the file",
                        date: "2018-12-19 07:53 AM"
                    }
                ],
                payments:[

                ],
            }
        };
    },
    mounted() {
        this.fetchFileDetails();
    },
    methods: {
        fetchFileDetails() {
            axios.get(`/api/file-management/${this.fileId}`)
                .then(response => {
                    const data = response.data;
                    console.log(data);
                    this.fileDetails.fileReference = data.fileReference;
                    this.fileDetails.status = data.status;
                    this.fileDetails.numberOfPayments = data.numberOfPayments;
                    this.fileDetails.totalAmount = data.totalAmount;
                    this.fileDetails.totalConfirmed = data.totalConfirmed;
                    this.fileDetails.historyLog = data.historyLog;
                    this.fileDetails.payments = data.payments;
                    
                })
                .catch(error => {
                    console.error("Error fetching file details:", error);
                });
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'ZAR' }).format(value);
        },
        /* downloadFile() {
            // Logic to download the file
            
            alert("Download functionality will be implemented here.");
        }, */
        // Print the current page
        printPage() {
            window.print();
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
