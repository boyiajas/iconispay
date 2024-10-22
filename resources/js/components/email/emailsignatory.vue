<template>
    <div class="container mt-4">
        <h4>Email Notification to Signatory 
            <span class="pull-right">
                <button class="btn btn-light btn-sm ml-1" @click="printPage"><i class="fas fa-print"></i> Print</button>
            </span>
        </h4>

        <!-- Form for email notification -->
        <form @submit.prevent="sendEmail">
            <div class="mb-3">
                <label for="recipient" class="form-label">Recipient:</label>
                <select v-model="emailForm.recipient" class="form-select" required>
                    <option value="" disabled>--Please Select a Recipient--</option>
                    <option v-for="recipient in recipients" :key="recipient.id" :value="recipient.id">
                        {{ recipient.name }}
                    </option>
                </select>
                <div v-if="errors.recipient" class="text-danger">Required (Please select a Recipient).</div>
            </div>

            <div class="mb-3">
                <label for="subject" class="form-label">Email Subject:</label>
                <input type="text" v-model="emailForm.subject" class="form-control" id="subject" required>
            </div>

            <div class="mb-3">
                <label for="greeting" class="form-label">Greeting:</label>
                <input type="text" v-model="emailForm.greeting" class="form-control" id="greeting" required>
            </div>

            <div class="mb-3">
                <label for="message" class="form-label">Message:</label>
                <textarea v-model="emailForm.message" class="form-control" id="message" rows="5" required></textarea>
            </div>

            <p>Please follow this URL: <a :href="url" target="_blank">{{ url }}</a></p>

            <p>Kind Regards,<br>{{ currentUser.name }}</p>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary" @click="cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Send</button>
            </div>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'EmailSignatory',
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
        this.loadRequisitionDetails();
        this.loadRecipients();  // Fetch recipients when component is mounted
        this.url = this.generateUrl();  // Generate URL dynamically based on file reference
    },
    methods: {

        loadRequisitionDetails() {
            axios.get(`/api/requisitions/${this.requisitionId}`)
                .then(response => {
                    this.requisition = response.data || {};  // Set requisition data or empty object
                    // Set subject and message after requisition is loaded
                    this.emailForm.subject = `Matter ready for authorisation (${this.requisition.file_reference})`;
                    this.emailForm.message = `A matter with file reference: ${this.requisition.file_reference} is ready for authorisation.`;
                })
                .catch(error => {
                    console.error('Error loading requisition details:', error);
                });
        },

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

        // Generate URL for authorisation
        generateUrl() {
            const matterId = 300316;  // Example matter ID, replace with dynamic value
            const requisitionId = 3;  // Example requisition ID, replace with dynamic value
            return `https://app.lexispay.co.za/matters/${matterId}/requisition/payments/${requisitionId}/authorise`;
        },

        // Send email via Axios
        sendEmail() {
            axios.post('/api/send-email', this.emailForm)
                .then(response => {
                    alert('Email sent successfully!');
                })
                .catch(error => {
                    if (error.response && error.response.data.errors) {
                        this.errors = error.response.data.errors;
                    } else {
                        console.error('Error sending email:', error);
                    }
                });
        },

        // Print the current page
        printPage() {
            window.print();
        },

        // Handle cancel button
        cancel() {
            this.$router.go(-1);  // Navigate back
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
</style>
