import { createRouter, createWebHistory } from 'vue-router';
import Home from './Home.vue';
import Matter from './matter/index.vue';
import NewRequisition from './requisition/create.vue';
import DetailsRequisition from './requisition/details.vue';
import NewBeneficiary from './beneficiary/create.vue';
import NewFirmAccount from './firmaccount/create.vue';
import Setup from './setup/index.vue';
import RequisitionList from './requisition/requisitionlist.vue';
import EmailSignatory from './email/emailsignatory.vue';

// Define Vue Router routes, excluding the /contact route
const routes = [
    { path: '/home', name: 'home', component: Home },
    { path: '/matters', name: 'matters', component: Matter },
    { path: '/requisition/new', name: 'newrequisition', component: NewRequisition },
    
    // Updated route for requisition details with dynamic parameters
    { 
        path: '/matters/requisitions/:requisitionId/details',
        name: 'detailsrequisition',
        component: DetailsRequisition,
        props: true // Enables passing route parameters as props to the component
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

    { path: '/firmaccount/new', name: 'newfirmaccount', component: NewFirmAccount },
    { path: '/beneficiary/new', name: 'newbeneficiary', component: NewBeneficiary },
    { path: '/setup', name: 'setup', component: Setup},
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;