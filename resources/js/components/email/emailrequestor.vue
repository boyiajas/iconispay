<template>
    <div class="container mt-4">
        <h4 class="mb-5">Email Notification to Requestor 
            <span class="pull-right">
                <button class="btn btn-light btn-sm ml-1" @click="printPage"><i class="fas fa-print"></i> Print</button>
            </span>
        </h4>

        <!-- Form for email notification -->
        <form @submit.prevent="sendEmail">
            <div class="mb-3 row">
                
                <label for="recipient" class="form-label col-2">Recipient:</label>
                <div class="col-10">{{ emailForm.recipient }}</div>
                <!-- <select v-model="emailForm.recipient" class="form-select" required>
                    <option value="" disabled>--Please Select a Recipient--</option>
                    <option v-for="recipient in recipients" :key="recipient.id" :value="recipient.id">
                        {{ recipient.name }}
                    </option>
                </select>
                <div v-if="errors.recipient" class="text-danger">Required (Please select a Recipient).</div> -->
            </div>

            <div class="mb-3 row">
                <label for="subject" class="form-label col-2">Email Subject:</label>
                <div class="col-10">{{ emailForm.subject }}</div>
                <!-- <input type="text" v-model="emailForm.subject" class="form-control" id="subject" required> -->
            </div>

            <div class="mb-3 row">
                <label for="greeting" class="form-label col-2">Greeting:</label>
                <div class="col-10">
                    <input type="text" v-model="emailForm.greeting" class="form-control" id="greeting" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="message" class="form-label col-2">Message:</label>
                <div class="col-10">
                    <textarea v-model="emailForm.message" class="form-control" id="message" rows="5" required></textarea>
                </div>
            </div>

            <p>Please follow this URL: <a :href="url" target="_blank">{{ url }}</a></p>

            <p>Kind Regards,<br/><br/>{{ currentUser.name }}</p>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary" @click="cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"> <span id="buttonSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Send</button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';
import { useToast } from 'vue-toastification';

export default {
    name: 'EmailRequestor',
    props: {
        requisitionId: {
            type: Number,
            required: true
        }
    },
    data() {
        
        return {
            requisition: {},  // Initialize as an empty object
            emailForm: {
                recipientId: '',
                recipient: '',
                subject: '',
                greeting: 'Dear',
                message: '',
            },
            recipients: [],  // This will be populated from the API
            url: '',  // This will be the URL to follow for authorisation
            currentUser: window.Laravel.user || { name: 'Guest' }, // Default to 'Guest' if user is not available
            errors: {},
           
        };
    },
    mounted() {
        this.loadRecipients();
        this.loadRequisitionDetails();
        this.url = this.generateUrl();
    },
    setup() {
        // Initialize toast
        const toast = useToast();
        return { toast };
    },
    methods: {
         // Fetch recipients from the API
         loadRecipients() {
            axios.get('/api/recipients')
                .then(response => {
                    this.recipients = response.data;
                })
                .catch(error => {
                    console.error('Error fetching recipients:', error);
                });
        },
        loadRequisitionDetails() {
            axios.get(`/api/requisitions/${this.requisitionId}`)
                .then(response => {
                    this.requisition = response.data || {};  // Set requisition data or empty object
                    console.log(this.requisition);
                    this.emailForm.recipientId = this.requisition?.user?.id;
                    this.emailForm.greeting = 'Dear '+this.requisition?.user?.name;
                    this.emailForm.recipient = this.requisition?.user?.name;
                    // Set subject and message after requisition is loaded
                    this.emailForm.subject = `Matter requiring attention (${this.requisition.file_reference})`;
                    this.emailForm.message = `A matter with file reference: ${this.requisition.file_reference} requires your attention.`;
                })
                .catch(error => {
                    console.error('Error loading requisition details:', error);
                });
        },
        generateUrl() {
            const matterId = 300316;  // Example matter ID, replace with dynamic value
            const requisitionId = 3;  // Example requisition ID, replace with dynamic value
            /*testing env*///return `http://127.0.0.1:8000/matters/requisitions/${requisitionId}/details`;
            /*live env*/return `https://pay.iconis.co.za/matters/requisitions/${requisitionId}/details`;
        },

        sendEmail() {

            const buttonSpinner = document.getElementById('buttonSpinner');
            buttonSpinner.classList.remove('d-none');

            console.log(this.emailForm);
            
            axios.post('/api/send-email/requestor-notification', this.emailForm)
                .then(response => {
                    buttonSpinner.classList.add('d-none');
                    //alert('Email sent successfully!');
                    this.toast.success(response.data.message, {
                        title: 'Success'
                    });
                })
                .catch(error => {
                    if (error.response && error.response.data.errors) {
                        buttonSpinner.classList.add('d-none');
                        //this.errors = error.response.data.errors;
                        this.toast.error(error.response ? error.response.data : 'No response data', {
                            title: 'Error'
                        });
                    } else {
                        console.error('Error sending email:', error);
                        buttonSpinner.classList.add('d-none');
                    }
                });
        },
        printPage() {
            window.print();
        },
        cancel() {
            this.$router.go(-1);
        }
    }
};
</script>

<style scoped>
h4 {
    margin-bottom: 20px;
}

.btn-light {
    color: #000;
    background-color: #f8f9fa;
}

.btn-primary {
    color: #fff;
}

textarea {
    resize: none;
}

a{
    color: #0097b2bf !important;
}
</style>
