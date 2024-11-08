<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="section-title">Matters  
                <router-link to="/requisition/new" class="btn btn-primary btn-sm ml-4">+ New Requisition</router-link>
            </h4>
        </div>

        <!-- Filters and Options Section -->
        <div class="d-flex align-items-center">
            <div class="form-group me-3">
                <label for="status" class="me-2">Status:</label>
                <select class="form-control d-inline w-auto" id="status" v-model="filterStatus" @change="reloadTable">
                    <option value="">All</option>
                    <option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.name }}</option>
                </select>
            </div>

            <div class="form-group me-3">
                <label for="filter" class="me-2">Filter:</label>
                <input type="text" class="form-control d-inline w-auto" id="filter" v-model="filterText" placeholder="Search..." @input="reloadTable">
            </div>
        </div>
        <div class="card">
        <div class="card-header">
            <h6>All Matters</h6>
        </div>
        <div class="card-body">
        <!-- Matters Table with DataTable -->
        <div class="table-responsive">
            <table id="matters-table" class="table table-bordered display nowrap" style="width:100%">
                <thead>
                    <tr class="table-secondary">
                        <th>Created By</th>
                        <th>File Reference</th>
                        <th>Reason</th>
                        <th>Properties</th>
                        <th>Parties</th>
                        <!-- <th>Status</th> -->
                        <th>Progress</th>
                        <th v-if="canAction">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-bs5';
import { userCan } from '../permission/userCan';

export default {
    name: 'Matters',
    data() {
        return {
            filterStatus: '',
            filterText: '',
            statuses: [],
            mattersTable: null,
        };
    },
    computed: {
        canAction() {
            return userCan(['admin']); // Only users with the admin role can access actions
        }
    },
    methods: {
        loadStatuses() {
            axios.get('/api/statuses').then(response => {
                this.statuses = response.data;
                this.reloadStatusColumn(); // Refresh status column in DataTable once statuses are loaded
            });
        },
        
        editMatter(matterId) {
            this.$router.push({ name: 'editMatter', params: { id: matterId } });
        },
        deleteMatter(matterId) {
            if (confirm('Are you sure you want to delete this matter?')) {
                axios.delete(`/api/requisitions/${matterId}`).then(() => {
                    this.reloadTable();
                }).catch(error => {
                    console.error('Error deleting matter:', error);
                });
            }
        },
        initializeDataTable() {
            this.mattersTable = $('#matters-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/api/requisitions',
                    type: 'GET',
                    data: (d) => {
                        d.status_id = this.filterStatus;
                        d.filter_text = this.filterText;
                    }
                },
                columns: [
                    { data: 'user.name', name: 'user.name' },
                    { data: 'file_reference', name: 'file_reference' },
                    { data: 'reason', name: 'reason' },
                    { data: 'properties', name: 'properties' },
                    { data: 'parties', name: 'parties' },
                    /* { data: 'status_id', name: 'status_id', render: (data) => this.getStatusName(data) }, */
                    { data: 'progress', name: 'progress' },
                    // Conditionally add the Action column if the user has the admin role
                    ...(this.canAction ? [{ data: 'id', name: 'id', orderable: false, searchable: false, render: (data) => this.actionButtons(data) }] : [])
                    //{ data: 'id', name: 'id', orderable: false, searchable: false, render: (data) => this.actionButtons(data) }
                ],
                responsive: true,
                destroy: true, // Reinitializes the table if needed
                rowCallback: (row, data) => {
                    // Attach click event to each row that navigates to the details page
                    $(row).on('click', () => {
                        this.$router.push({ name: 'detailsrequisition', params: { requisitionId: data.id } });
                        //this.$router.push({ name: 'detailsrequisition', params: { requisitionId } });
                    });
                }
            });
        },
        reloadTable() {
            if (this.mattersTable) {
                this.mattersTable.ajax.reload();
            }
        },
        reloadStatusColumn() {
            if (this.mattersTable) {
                // Redraw only the Status column after statuses have been loaded
                this.mattersTable.columns().every((index) => {
                    if (this.mattersTable.column(index).dataSrc() === 'status_id') {
                        this.mattersTable.column(index).data((row, type, set) => {
                            if (type === 'display') {
                                return this.getStatusName(row.status_id);
                            }
                            return row.status_id;
                        }).draw(false);
                    }
                });
            }
        },
        actionButtons(matterId) {
            // Conditionally render action buttons only if the user has admin role
            if (!this.canAction) {
                return ''; // Return an empty string if the user is not an admin
            }
            return `
                <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); editMatter(${matterId})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteMatter(${matterId})">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            `;
        },
        getStatusName(statusId) {
            const status = this.statuses.find(s => s.id === statusId);
            return status ? status.name : '';
        },
    },
    mounted() {
        this.initializeDataTable();
        this.loadStatuses();
    },
};
</script>

<style scoped>
.section-title {
    font-size: 24px;
    font-weight: bold;
}

.table-responsive {
    /* border: 1px solid #ddd; */
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

.badge {
    font-size: 12px;
}

.btn-outline-primary {
    color: #007bff;
}

.btn-outline-danger {
    color: #dc3545;
}

.btn-outline-primary:hover, .btn-outline-danger:hover {
    color: #fff;
}
</style>
