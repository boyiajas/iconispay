<template>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
            <h6>Documents</h6>
            <button class="btn btn-white btn-sm" @click="openUploadModal">+ New Document</button>
        </div>
        <div class="card-body">
            <!-- Documents DataTable -->
            <div class="table-responsive">
                <table id="documents-table" class="table table-bordered table-striped display nowrap" style="width:100%">
                    <thead>
                        <tr class="table-secondary">
                            <th>User</th>
                            <th>Description</th>
                            <th>File Name</th>
                            <th>Date Uploaded</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- Upload Document Modal -->
        <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            The maximum file size is 10 MB. Only PDFs and images may be uploaded.
                        </div>
                        <form @submit.prevent="uploadDocument">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description: *</label>
                                <input type="text" v-model="documentForm.description" class="form-control" id="description" required>
                            </div>
                            <div class="mb-3">
                                <label for="file" class="form-label">Select Document: *</label>
                                <input type="file" @change="handleFileUpload" class="form-control" id="file" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        requisitionId: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            documentsTable: null,  // Reference to the DataTable instance for documents
            documentForm: {
                description: '',
                file: null
            },
            documentModalInstance: null,
        };
    },
    mounted() {
        this.loadDocumentsData();
    },
    methods: {
        // Initialize and load Documents DataTable when Documents tab is clicked
        loadDocumentsData() {
            if (this.documentsTable) {
                this.documentsTable.ajax.reload();
                return;
            }

            this.documentsTable = $('#documents-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: `/api/requisitions/${this.requisitionId}/documents`,  // Your API endpoint to fetch documents
                    type: 'GET',
                    data: (json) => json // Return full JSON response for DataTables
                },
                columns: [
                    { data: 'user_name', name: 'user_name' },  // Assuming the API returns `user_name`
                    { data: 'description', name: 'description' },
                    { data: 'file_name', name: 'file_name' },
                    { data: 'uploaded_at', name: 'uploaded_at', render: (data) => new Date(data).toLocaleString() }  // Format date
                ],
                responsive: false,
                destroy: true,  // Reinitialize the table if needed
            });
        },

        // Open the upload document modal
        openUploadModal() {
            this.documentModalInstance = new bootstrap.Modal(document.getElementById('uploadDocumentModal'));
            this.documentModalInstance.show();
        },

        // Handle file selection
        handleFileUpload(event) {
            this.documentForm.file = event.target.files[0];
        },

        closeModal() {
            //const newUserModal = bootstrap.Modal.getInstance(document.getElementById('newUserModal'));
            if (this.documentModalInstance) {
                this.documentModalInstance.hide();
            }
        },

        // Submit the document form to the backend via Axios
        uploadDocument() {
            const formData = new FormData();
            formData.append('description', this.documentForm.description);
            formData.append('file', this.documentForm.file);
            formData.append('requisition_id', this.requisitionId);

            axios.post('/api/documents', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                console.log('Document uploaded successfully:', response.data);
                // Close the modal and reset form
                this.closeModal();
                this.documentForm.description = '';
                this.documentForm.file = null;

                // Reload the documents table to reflect the new document
                if (this.documentsTable) {
                    this.documentsTable.ajax.reload();
                }
            })
            .catch(error => {
                console.error('Error uploading document:', error);
            });
        }
    }
};
</script>
