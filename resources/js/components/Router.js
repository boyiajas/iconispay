import { createRouter, createWebHistory } from 'vue-router';
import Home from './Home.vue';
import Matter from './matter/index.vue';
import Report from './report/index.vue';
import Account from './account/index.vue';
import NewRequisition from './requisition/create.vue';
import DetailsRequisition from './requisition/details.vue';
import NewBeneficiary from './beneficiary/create.vue';
import NewFirmAccount from './firmaccount/create.vue';
import Setup from './setup/index.vue';
import RequisitionList from './requisition/requisitionlist.vue';
import EmailSignatory from './email/emailsignatory.vue';
import EmailRequestor from './email/emailrequestor.vue';
import AccountFileEdit from './account/accountfileedit.vue';
import FileManagement from './account/filemanagement.vue';
import AllTransactionsForAFile from './account/alltransactionsforafile.vue';
import ReadyForPayment from './report/readyForPayment.vue';
import PaidByDate from './report/paidByDate.vue';
import MatterFiltered from './matter/matterfiltered.vue';

// Define Vue Router routes, excluding the /contact route
const routes = [
    { path: '/home', name: 'home', component: Home },
    { path: '/matters', name: 'matters', component: Matter },
    { path: '/accounts', name: 'accounts', component: Account },
    { path: '/reports', name: 'reports', component: Report },
    { path: '/reports/ready-for-payment', name: 'ready-for-payment', component: ReadyForPayment },
    { path: '/reports/paid-by-date', name: 'paid-by-date', component: PaidByDate },
    { path: '/requisition/new', name: 'newrequisition', component: NewRequisition },
    
    // Updated route for requisition details with dynamic parameters
    { 
        path: '/matters/requisitions/:requisitionId/details',
        name: 'detailsrequisition',
        component: DetailsRequisition,
        props: true // Enables passing route parameters as props to the component
    },
    {   path: "/filtered-matters", 
        name: "filteredmatters", 
        component: MatterFiltered, 
        //props: true 
    },
    {
        path: '/accounts/:id/edit',
        name: 'accountfileedit',
        component: AccountFileEdit,
        props: true
    },
    {
        path: '/file-management/:id',
        name: 'filemanagement',
        component: FileManagement
    },
    {
        path: '/reports/alltransactionsforfilereference/:id',
        name: 'alltransactionsforafile',
        component: AllTransactionsForAFile
    },
    {
        path: '/requisitions/status/:status',
        name: 'requisitionStatus',
        component: RequisitionList,
        props: true
    },
    { 
        path: '/matter/emailsignatory/:requisitionId', 
        name: 'emailsignatory', 
        component: EmailSignatory, 
        props: true // Pass route params as props
    },
    { 
        path: '/matter/emailrequestor/:requisitionId', 
        name: 'emailrequestor', 
        component: EmailRequestor, 
        props: true // Pass route params as props
    },

    { path: '/firmaccount/new', name: 'newfirmaccount', component: NewFirmAccount },
    { path: '/beneficiary/new', name: 'newbeneficiary', component: NewBeneficiary },
    { path: '/setup', name: 'setup', component: Setup},

    // Catch-all route to redirect non-existing routes to the home page
    { path: '/:pathMatch(.*)*', redirect: '/home' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;