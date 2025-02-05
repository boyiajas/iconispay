// stores/datastore.js
import { defineStore } from 'pinia';

export const useRequisitionStore = defineStore('requisition', {
    state: () => ({
        requisition: null,
        requisitions: [], // Store multiple requisitions
    }),
    actions: {
        setRequisition(data) {
            this.requisition = data;
        },
        clearRequisition() {
            this.requisition = null;
        },
        setRequisitions(data) {
            this.requisitions = data;
        },
        clearRequisitions() {
            this.requisitions = [];
        },
    },
    persist: {
        enabled: true, // Enable persistence
        strategies: [
            /* {
                key: 'requisition', // Key in local storage
                storage: localStorage, // Use localStorage (you can switch to sessionStorage if needed)
            }, */
            {
                key: "requisition_store", // Key in localStorage
                storage: localStorage, // Use localStorage
            },
        ],
    },
});
