<?php

declare(strict_types = 1);

use Core\UniqueIdsGenerator;
use Permissions\PermissionsManager;
use Permissions\Role;

if (!PermissionsManager::isPermittedAction('view_user_management', $current_user)) {
    header('Location: index.php');
    return;
}
$uniqueIdGenerator = new UniqueIdsGenerator();
$permissionsToken = $uniqueIdGenerator->generateTrueRandomString();
$_SESSION['formToken']['edit_permissions_token'] = password_hash($permissionsToken, PASSWORD_DEFAULT);
$roles = Role::getChildRoles($current_user, true);
if (isset($_POST['user_role'])) {
    $roleId = Filter::filterInput(INPUT_POST, 'user_role', FILTER_SANITIZE_NUMBER_INT);
    if ($roleId) {
        $rolePermissions = PermissionsManager::listAllPermissionsForRole((int) $roleId);
        $roleName = Role::getRoleById($roleId);
    }
}
?>

<div class="mb-4">
    <form action="index.php?action=manage_permissions" method="POST" id="selectRoleForm">
        <label for="user_role">Select Role</label>
        <select class="form-control" name="user_role" id="user_role" onchange="this.form.submit()">
            <option value="" selected disabled>Choose Role</option>
            <?php
            echo join('', Role::getChildRoles($current_user, true));
            ?>
        </select>
    </form>
</div>

<?php
if (isset($rolePermissions) && count($rolePermissions)): ?>
    <h3 class="mb-4">Editing permissions for <?php
        echo htmlspecialchars($roleName) ?></h3>
    <form action="index.php?action=update-permissions" method="POST" id="permissionsForm">
        <div class="row">
            <?php
            foreach ($rolePermissions as $permission): ?>
                <div class="col-md-4 mb-3">
                    <!-- Each checkbox and label pair is placed in a column that takes up 4 out of 12 possible columns on medium screens and above -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="<?php
                        echo $permission['action_id'] ?>"
                               id="perm_<?php
                               echo $permission['action_id'] ?>"
                            <?php
                            echo $permission['is_enabled'] ? 'checked' : '' ?>
                               data-action-id="<?php
                               echo $permission['action_id'] ?>">
                        <label class="form-check-label" for="perm_<?php
                        echo $permission['action_id'] ?>">
                            <?php
                            echo htmlspecialchars($permission['action_label']) ?>
                        </label>
                    </div>
                </div>
            <?php
            endforeach; ?>
        </div>
        <input type="hidden" name="formToken" value="<?php
        echo $permissionsToken ?>">
        <input type="hidden" name="role_id" value="<?php
        echo $roleId ?>">
        <?php
        if (PermissionsManager::isPermittedAction('edit_permissions', $current_user)): ?>
            <button class="btn btn-outline-primary btn-block mt-4 pull-right" type="submit">Submit Changes</button>
        <?php
        endif; ?>
    </form>
<?php
endif; ?>
