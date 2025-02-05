<template>
    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">Filtered Matters  
                <router-link to="/requisition/new" class="btn btn-primary btn-sm ml-4">+ New Requisition</router-link>
            </h2>
        </div>
        <div class="card">
            <div class="card-header">
                <h6>All Filtered Matters 
                    <span class="btn btn-sm btn-white btn-default-default mr-1 pull-right" @click="cancel"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</span> 
                </h6>
            </div>
            <div class="card-body">
            <!-- Matters Table with DataTable -->
                <div class="">
        
                    <table id="filtered-matters-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Created By</th>
                                <th>File Reference</th>
                                <th>Reason</th>
                                <th>Parties</th>
                                <th>Progress</th>
                                <th v-if="canAction">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import $ from "jquery";
import 'datatables.net';
import 'datatables.net-bs5';
import { useRequisitionStore } from '../store/datastore';
import { userCan } from '../permission/userCan.js';

export default {
    name: 'FilteredMatter',
    //components: { VueMultiselect },
    props: {
        user: {
            type: Object,
            required: true
        },
        
    },
    data() {
        return {
            requisitions: [],
        };
    },
    created() {
        //this.requisitions = this.$route.params.requisitions || [];
        const store = useRequisitionStore();
        this.requisitions = store.requisitions || [];
    },
    mounted() {
        this.initializeDataTable();
    },
    computed: {
        canAction() {
            return userCan(['admin']);
        }
    },
    methods: {
        initializeDataTable() {

            const isAdmin = this.user.roles.some(role => role.name.toLowerCase().includes('admin'));

            $("#filtered-matters-table").DataTable({
                destroy: true, // Ensures reinitialization doesn't cause issues
                data: this.requisitions,
                columns: [
                    { data: "user.name", defaultContent: "N/A" }, // Assuming `user` relationship exists
                    { data: "file_reference" },
                    { data: "reason", defaultContent: "N/A" },
                    { data: "parties", defaultContent: "N/A" },
                    {
                        data: "progress",
                        render: function (data, type, row) {
                            let progress = "";

                            if (row.authorization_status) {
                                progress += '<span class="badge bg-success me-1">Authorized</span>';
                            }
                            if (row.status_id === 3 && !row.authorization_status) {
                                progress += '<span class="badge bg-default me-1">Authorized</span>';
                            }
                            if (row.status_id === 3 && !row.authorization_status && !row.funding_status) {
                                progress += '<span class="badge bg-default me-1">Funded</span>';
                            }
                            if (row.status_id === 4) {
                                progress += '<span class="badge bg-default me-1">Funded</span>';
                            }
                            if (row.status_id === 4 && !row.authorization_status) {
                                progress += '<span class="badge bg-default me-1">Authorized</span>';
                            }
                            if (row.funding_status) {
                                progress += '<span class="badge bg-success">Funded</span>';
                            }

                            // If no progress status is set, return a default value
                            return progress || '<span class="badge bg-default">No Progress</span>';
                        },
                    },
                    {
                        data: null,
                        render: function (data, type, row) {                         

                            //const isAdmin = this.user.roles.some(role => role.name.toLowerCase().includes('admin'));
                            return `
                                                                
                                 ${isAdmin ? `<button class="btn btn-sm btn-outline-secondary edit-matter-btn" data-toggle="tooltip" title="Edit this Matter" data-id="${data.id}"><i class="fas fa-edit"></i></button>` : ''}
                                  ${isAdmin ? `<button class="btn btn-sm btn-outline-danger edit-matter-btn" data-toggle="tooltip" title="Delete this Matter" data-id="${data.id}"><i class="fas fa-trash-alt"></i></button>` : ''}
                                
                            `;
                        }
                    },
                ],
                responsive: true,
                paging: true,
                pageLength: 10,
                searching: true,
                autoWidth: false,
                rowCallback: (row, data) => {
                    // Attach click event to each row that navigates to the details page
                    $(row).on('click', () => {
                        this.$router.push({ name: 'detailsrequisition', params: { requisitionId: data.id } });
                        //this.$router.push({ name: 'detailsrequisition', params: { requisitionId } });
                    });
                }
            });
        },
        // Handle cancel button
        cancel() {
            this.$router.go(-1);  // Navigate back
        },
    },
};
</script>

<style scoped>
.table th, .table td {
    text-align: center;
}
</style>
