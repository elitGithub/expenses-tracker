<?php

declare(strict_types = 1);


use engine\UsersList;
use Permissions\PermissionsManager;

if (!PermissionsManager::isPermittedAction('view_user_management', $current_user)) {
    header('Location: index.php');
}
// user_id, email, user_name, first_name, last_name, created_by, active, is_admin, created_at
$userList = new UsersList();
$collection = $userList->loadUserList($current_user);
?>

<!-- Modal trigger button with Bootstrap 5 data attributes -->
<button class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class="fa fa-plus-circle fa-2x"></i> Add User
</button>
<div class="row">
    <div class="col-md-12">
        <!-- Advanced Tables -->
        <div class="panel panel-default">
            <div class="h3">
                User Management
                <div class="h6 pull-right">Filter Report using the search</div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>User Name</th>
                            <th>Created By</th>
                            <th>Active</th>
                            <?php
                            if (PermissionsManager::isAdmin($current_user)): ?>
                                <th>Is Admin</th> <?php
                            endif; ?>
                            <th>Created At</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($collection as $user): ?>
                            <tr>
                                <td><?php
                                    echo $user->user_id; ?></td>
                                <td><?php
                                    echo $user->email; ?></td>
                                <td><?php
                                    echo $user->user_name; ?></td>
                                <td><?php
                                    echo $user->first_name . ' ' . $user->last_name; ?></td>
                                <td><?php
                                    echo $user->creator; ?></td>
                                <td>
                                    <?php
                                    if ($user->active === '1'): ?>
                                        <p class="bg-success-subtle">Active</p>
                                    <?php
                                    else: ?>
                                        <p>Inactive</p>
                                    <?php
                                    endif ?>
                                </td>
                                <?php
                                if (PermissionsManager::isAdmin($current_user)): ?>
                                    <td>
                                        <?php
                                        echo $user->is_admin; ?>
                                    </td>
                                <?php
                                endif; ?>
                                <td>
                                    <?php
                                    echo $user->created_at ?>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--End Advanced Tables -->
    </div>
</div>

<?php

require_once 'modals.php';
?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const toggleUserPassword = document.getElementById('toggleUserPassword');
    const toggleRetypePassword = document.getElementById('toggleRetypePassword');
    const showUserPassword = document.getElementById('showUserPassword');
    const showRetypePassword = document.getElementById('showRetypePassword');
    const adminPassword = document.getElementById('password');
    const passwordRetype = document.getElementById('password_retype');
    toggleUserPassword?.addEventListener('click', () => {
      if (adminPassword.type === 'password') {
        adminPassword.type = 'text';
        showAdminPassword.className = 'fa fa-eye-slash';
      } else {
        adminPassword.type = 'password';
        showAdminPassword.className = 'fa fa-eye';
      }
    });
    toggleRetypePassword?.addEventListener('click', () => {
      if (passwordRetype.type === 'password') {
        passwordRetype.type = 'text';
        showRetypePassword.className = 'fa fa-eye-slash';
      } else {
        passwordRetype.type = 'password';
        showRetypePassword.className = 'fa fa-eye';
      }
    });

    document.getElementById('user_photo').addEventListener('change', (ev) => {
      document.querySelector('#upload_user_photo').value = ev.target.files.length;
    });
  });
</script>

