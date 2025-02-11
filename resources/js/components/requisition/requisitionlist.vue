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
                <div class="">
                    <table id="matters-status-table" class="table table-bordered display nowrap" style="width:100%">
                        <thead>
                            <tr class="table-secondary">
                                <th>Created By</th>
                                <th>File Reference</th>
                                <th>Reason</th>
                                <th>Properties</th>
                                <th>Parties</th>
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
            statuses: [],
            mattersTable: null
        };
    },
    computed: {
        statusTitle() {
            return this.status;
        },
        canAction() {
            return userCan(['admin']);
        }
    },
    methods: {
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
                ajax: {
                    url: `/api/requisitions/bystatus`,
                    type: 'GET',
                    data: (d) => {
                        d.status = this.status;
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
