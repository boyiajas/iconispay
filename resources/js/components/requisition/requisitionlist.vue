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
                <h6>{{ statusTitle }}</h6>
            </div>
            <div class="card-body">
                <!-- Matters Table with DataTable -->
                <div class="table-responsive">
                    <table id="matters-status-table" class="table table-bordered display nowrap" style="width:100%">
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
import { userCan } from '../permission/userCan.js';

export default {
    props: ['status'],
    data() {
        return {
            filterStatus: '',
            filterText: '',
            statuses: [],
            mattersTable: null,
            statusTitle: this.status, // Dynamically set title based on status
        };
    },
    computed: {
        canAction() {
            return userCan(['admin']);
        }
    },
    methods: {
        loadStatuses() {
            axios.get('/api/statuses').then(response => {
                this.statuses = response.data;
                this.reloadStatusColumn();// Refresh status column in DataTable once statuses are loaded
            });
        },
        initializeDataTable() {
            console.log(this.status);
            this.mattersTable = $('#matters-status-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: `/api/requisitions/bystatus`, // Use dynamic URL based on the status prop
                    type: 'GET',
                    data: (d) => {
                        d.status = this.status;
                        d.filter_text = this.filterText; // Send any filter text for server-side filtering
                    }
                },
                columns: [
                    { data: 'user.name', name: 'user.name' },
                    { data: 'file_reference', name: 'file_reference' },
                    { data: 'reason', name: 'reason' },
                    { data: 'properties', name: 'properties' },
                    { data: 'parties', name: 'parties' },
                   /*  { data: 'status_id', name: 'status_id', render: (data) => this.getStatusName(data) }, */
                    { data: 'progress', name: 'progress' },
                     // Conditionally add the Action column if the user has the admin role
                     ...(this.canAction ? [{ data: 'id', name: 'id', orderable: false, searchable: false, render: (data) => this.actionButtons(data) }] : [])
                    //{ data: 'id', name: 'id', orderable: false, searchable: false, render: (data) => this.actionButtons(data) }
                ],
                responsive: true,
                destroy: true, // Reinitialize the table if needed
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
        getStatusName(statusId) {
            const status = this.statuses.find(s => s.id === statusId);
            return status ? status.name : '';
        },
        actionButtons(matterId) {
            return `
                <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); editMatter(${matterId})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteMatter(${matterId})">
                    <i class="fas fa-trash-alt"></i> Delete
                </button>
            `;
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
