<?php

declare(strict_types = 1);

use Permissions\PermissionsManager;
use Permissions\Role;

if (!PermissionsManager::isPermittedAction('view_user_management', $current_user)) {
    header('Location: index.php');
    return;
}


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
    <form action="index.php?action=manage_permissions" method="POST">
        <label for="user_role">Select Role</label>
        <select class="form-control" name="user_role" id="user_role" onchange="this.form.submit()">
            <option value="" selected disabled>Choose Role</option>
            <?php
            echo join('', Role::getChildRoles($current_user, true))
            ?>
        </select>
    </form>
</div>

<?php if (isset($rolePermissions) && count($rolePermissions)): ?>
    <h3>Editing permissions for <?= htmlspecialchars($roleName) ?></h3>
    <form id="permissionsForm">
        <div class="row">
            <?php foreach ($rolePermissions as $permission): ?>
                <div class="col-md-4 mb-3"> <!-- Each checkbox and label pair is placed in a column that takes up 4 out of 12 possible columns on medium screens and above -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="perm_<?= $permission['action_id'] ?>"
                            <?= $permission['is_enabled'] ? 'checked' : '' ?>
                               data-action-id="<?= $permission['action_id'] ?>">
                        <label class="form-check-label" for="perm_<?= $permission['action_id'] ?>">
                            <?= htmlspecialchars($permission['action_label']) ?>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-outline-primary btn-block mt-4 pull-right" type="submit">Submit Changes</button>
    </form>
<?php endif; ?>



<script>
  $(document).ready(function () {
    const checkboxes = $('input[type="checkbox"][data-action-id]');

    // Initialize state from existing checkboxes
    const state = {};
    checkboxes.each(function () {
      let actionId = $(this).data('action-id');
      state[actionId] = { is_enabled: $(this).is(':checked') ? 1 : 0 };
    });

    // Update state on checkbox change
    checkboxes.change(function () {
      let actionId = $(this).data('action-id');
      state[actionId] = { is_enabled: $(this).is(':checked') ? 1 : 0 };
    });

    // Handle form submission
    $('#permissionsForm').submit(function (e) {
      e.preventDefault(); // Prevent the form from submitting normally

      // Convert the state object into FormData
      let formData = new FormData();
      for (let actionId in state) {
        formData.append(`permissions[${actionId}]`, state[actionId].is_enabled);
      }

      // AJAX request with FormData
      $.ajax({
        url: 'index.php?action=update-permissions',
        type: 'POST',
        data: formData,
        processData: false, // prevent jQuery from automatically transforming the data into a query string
        contentType: false, // prevent jQuery from setting the Content-Type header (let the browser set it)
        success: function (data) {
          console.log("Server response:", data);
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error('Error:', textStatus, errorThrown);
        }
      });
    });
  });


</script>
