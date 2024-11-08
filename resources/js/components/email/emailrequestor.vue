<template>
    <div class="container mt-4">
        <h4>Email Notification to Requestor 
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
    name: 'EmailRequestor',
    props: {
        requisitionId: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            emailForm: {
                recipient: '',
                subject: 'Matter requiring attention ( HG )',
                greeting: '',
                message: 'A matter with file reference: HG requires your attention.',
            },
            recipients: [],
            url: '',
            currentUser: window.Laravel.user || { name: 'Guest' },
            errors: {}
        };
    },
    mounted() {
        this.loadRecipients();
        this.url = this.generateUrl();
    },
    methods: {
        loadRecipients() {
            axios.get('/api/recipients')
                .then(response => {
                    this.recipients = response.data;
                })
                .catch(error => {
                    console.error('Error fetching recipients:', error);
                });
        },
        generateUrl() {
            return `https://app.lexispay.co.za/matters/305633/requisition/payments/to/review`;
        },
        sendEmail() {
            axios.post('/api/send-email/requestor-notification', this.emailForm)
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
</style>
