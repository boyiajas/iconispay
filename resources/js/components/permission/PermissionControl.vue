<!-- components/PermissionControl.vue -->
<template>
    <span v-if="hasAccess">
        <slot></slot> <!-- Render content if the user has access -->
    </span>
</template>

<script>
export default {
    props: {
        roles: {
            type: Array,
            default: () => []
        },
        permissions: {
            type: Array,
            default: () => []
        }
    },
    computed: {
        hasAccess() {
            const user = window.Laravel.user;
            // If `user` is null, access should be denied
            if (!user) {
                return false;
            }
            // Safely access `roles` and `permissions`
            const userRoles = user.roles.map(role => role.name);
            const userPermissions = user.permissions.map(permission => permission.name);

            // Check access based only on roles or permissions as provided
            if (this.roles.length > 0 && this.permissions.length === 0) {
                // Only roles are provided; check role access only
                return this.roles.some(role => userRoles.includes(role));
            } 
            
            if (this.permissions.length > 0 && this.roles.length === 0) {
                // Only permissions are provided; check permission access only
                return this.permissions.some(permission => userPermissions.includes(permission));
            }

            // If both roles and permissions are provided, check for both
            const roleAccess = this.roles.some(role => userRoles.includes(role));
            const permissionAccess = this.permissions.some(permission => userPermissions.includes(permission));
            
            return roleAccess && permissionAccess;
        }
    }
};
</script>
