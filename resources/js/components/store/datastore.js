// stores/datastore.js
import { defineStore } from 'pinia';

export const useRequisitionStore = defineStore('requisition', {
    state: () => ({
        requisition: null,
    }),
    actions: {
        setRequisition(data) {
            this.requisition = data;
        },
        clearRequisition() {
            this.requisition = null;
        },
    },
    persist: {
        enabled: true, // Enable persistence
        strategies: [
            {
                key: 'requisition', // Key in local storage
                storage: localStorage, // Use localStorage (you can switch to sessionStorage if needed)
            },
        ],
    },
});
