<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="section-title">{{ statusTitle }}  
                <router-link to="/requisition/new" class="btn btn-primary btn-sm ml-4">+ New Requisition</router-link>
            </h4>
        </div>

        <!-- Filters and Options Section -->
        <div class="d-flex align-items-center">
            <div class="form-group me-3">
                <label for="status" class="me-2">Status:</label>
                <select class="form-control d-inline w-auto" id="status" v-model="filterStatus" @change="onStatusChange">
                    <option value="0">All</option>
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
                <h6>{{ statusTitle }}</h6>
            </div>
            <div class="card-body">
                <!-- Matters Table with DataTable -->
                <div class="">
                    <table id="matters-status-table" class="table table-bordered display nowrap" style="width:100%">
                        <thead>
                            <tr class="table-secondary">
                                <th>Created By</th>
                                <th>File Reference</th>
                                <th>Reason</th>
                                <th>Properties</th>
                                <th>Dates</th>
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
import { userCan } from '../permission/userCan.js';

export default {
    props: {
        user: {
            type: Object,
            required: true
        },
        status: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            filterStatus: '',
            filterText: '',
            selectedStatus: this.status,
            statuses: [],
            mattersTable: null
        };
    },
    computed: {
        statusTitle() {
            return this.selectedStatus;
        },
        canAction() {
            return userCan(['admin','superadmin']);
        }
    },
    watch: {
        statuses(newStatuses) {
            if (newStatuses.length > 0) {
                console.log("All statuses loaded:", newStatuses);
                console.log("Current status is:", this.status);

                const foundStatus = newStatuses.find(s => s.name === this.status);
                if (foundStatus) {
                    this.filterStatus = foundStatus.id.toString(); // Ensure the correct value
                    console.log("Filter status set to:", this.filterStatus);
                } else {
                    this.filterStatus = ""; // Default to "All"
                    console.log("Filter status set to default (All)");
                }
            }
        }
    },
    methods: {
        onStatusChange() {

            console.log("Status changed to ID:", this.filterStatus);
            // Find the status name based on selected ID
            // Find the status name based on the selected ID
            const foundStatus = this.statuses.find(s => s.id === this.filterStatus);

            if (foundStatus) {
                this.selectedStatus = foundStatus.name; // ✅ Update local variable, not `this.status`
                console.log("Updated selectedStatus to:", this.selectedStatus);
            } else {
                this.selectedStatus = "all"; // Default to "All"
                console.log("Updated selectedStatus to default (All)");
            }

            this.initializeDataTable(); // ✅ Reload table with new status
            this.reloadTable();
        },
        loadStatuses() {
            axios.get('/api/statuses').then(response => {
                this.statuses = response.data;

                this.reloadStatusColumn();
            });
        },
        initializeDataTable() {
            const self = this; // Store reference to Vue instance

            this.mattersTable = $('#matters-status-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: `/api/requisitions/bystatus`,
                    type: 'GET',
                    data: (d) => {
                        d.status = this.selectedStatus;
                        d.filter_text = this.filterText;
                    },
                    error: (xhr, error, thrown) => {
                        console.error('Error fetching data:', error, thrown);
                    }
                },
                columns: [
                    { data: 'user.name', name: 'user.name' },
                    { data: 'file_reference', name: 'file_reference' },
                    { data: 'reason', name: 'reason' },
                    { data: 'properties', name: 'properties' },
                    { data: 'parties', name: 'parties' },
                    { data: 'progress', name: 'progress' },
                    {
                        data: null,
                        render: (data) => {
                            const isAdmin = self.user.roles.some(role => role.name.toLowerCase().includes('admin'));

                            return `
                                ${isAdmin ? `<button class="btn btn-sm btn-outline-secondary edit-matter-btn" title="Edit" data-id="${data.id}"><i class="fas fa-edit"></i></button>` : ''}
                                ${isAdmin ? `<button class="btn btn-sm btn-outline-danger delete-matter-btn" title="Delete" data-id="${data.id}"><i class="fas fa-trash-alt"></i></button>` : ''}
                            `;
                        }
                    }
                ],
                responsive: true,
                destroy: true,
                rowCallback: (row, data) => {
                    $(row).on('click', () => {
                        self.$router.push({ name: 'detailsrequisition', params: { requisitionId: data.id } });
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
        getStatusName(statusId) {
            const status = this.statuses.find(s => s.id === statusId);
            return status ? status.name : '';
        }
    },
    mounted() {
        this.initializeDataTable();
        this.loadStatuses();
    }
};
</script>

<style scoped>
/* Add your styles here */
</style>
