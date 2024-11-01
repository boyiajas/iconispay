// Permission/userCan.js

export function userCan(requiredRoles = [], requiredPermissions = []) {
    const user = window.Laravel.user;

    // If `user` is null, access should be denied
    if (!user) {
        return false;
    }

    // Get the user's roles and permissions
    const userRoles = user.roles ? user.roles.map(role => role.name) : [];
    const userPermissions = user.permissions ? user.permissions.map(permission => permission.name) : [];

    // Check role-based access
    if (requiredRoles.length > 0 && requiredPermissions.length === 0) {
        return requiredRoles.some(role => userRoles.includes(role));
    }

    // Check permission-based access
    if (requiredPermissions.length > 0 && requiredRoles.length === 0) {
        return requiredPermissions.some(permission => userPermissions.includes(permission));
    }

    // Check if the user has both the required roles and permissions
    const roleAccess = requiredRoles.some(role => userRoles.includes(role));
    const permissionAccess = requiredPermissions.some(permission => userPermissions.includes(permission));

    return roleAccess && permissionAccess;
}
