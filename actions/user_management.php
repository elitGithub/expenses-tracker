<?php

declare(strict_types=1);


use engine\UsersList;
use Permissions\PermissionsManager;

if (!PermissionsManager::isPermittedAction('view_user_management', $current_user)) {
    header('Location: index.php');
}
$userList = new UsersList();
$collection = $userList->loadUserList($current_user);
?>

<!-- Modal trigger button with Bootstrap 5 data attributes -->
<button class="btn btn-primary btn-sm search-prepend me-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class="fa fa-plus-circle"></i> Add User
</button>
<div class="row">
    <div class="col-md-12">
        <!-- Advanced Tables -->
        <div class="panel panel-default">
            <div class="h3">
                User Management
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
                                <th>User Role</th>
                                <th>Created By</th>
                                <th>Active</th>
                                <?php
                                if (PermissionsManager::isAdmin($current_user)) : ?>
                                    <th>Is Admin</th> <?php
                                                    endif; ?>
                                <th>Created At</th>
                                <?php
                                if (PermissionsManager::isPermittedAction('edit_user', $current_user)) : ?>
                                    <th>Actions</th>
                                <?php
                                endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($collection as $user) : ?>
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
                                        echo $user->role_name;
                                        ?>
                                    </td>
                                    <td><?php
                                        echo $user->creator; ?></td>
                                    <td <?php if ((int) $user->active === 1) : ?> class="bg-success-subtle" <?php else : ?> class="bg-danger-subtle" <?php endif ?>>
                                        <?php
                                        if ((int) $user->active === 1) : ?>
                                            <p>Active</p>
                                        <?php else : ?>
                                            <p>Inactive</p>
                                        <?php
                                        endif ?>
                                    </td>
                                    <?php
                                    if (PermissionsManager::isAdmin($current_user)) : ?>
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

                                    <?php
                                    if (PermissionsManager::isPermittedAction('edit_user', $current_user)) : ?>
                                        <td>
                                            <button type="button" class="btn btn-info btn-xs editButton" data-id="<?php
                                                                                                                    echo $user->user_id ?>" data-role_id="<?php
                                                                                                                                                            echo $user->role_id ?>" data-first_name="<?php
                                                                                                                                                                                                        echo htmlspecialchars($user->first_name) ?>" data-last_name="<?php
                                                                                                                                                                                                                                                                        echo htmlspecialchars($user->last_name) ?>" data-email="<?php
                                                                                                                                                                                                                                                                                                                                echo $user->email ?>" data-userName="<?php
                                                                                                                                                                                                                                                                                                                                                                        echo $user->user_name ?>" data-active="<?php
                                                                                                                                                                                                                                                                                                                                                                                                                echo $user->active ?>" data-is_admin="<?php
                                                                                                                                                                                                                                                                                                                                                                                                                                                        echo $user->is_admin ?>" data-bs-toggle="modal" data-bs-target="#editUserModal">
                                                <span class='fa fa-pencil'></span> Edit
                                            </button>
                                            <?php
                                            if (
                                                PermissionsManager::isPermittedAction('delete_user', $current_user) &&
                                                !PermissionsManager::isAdmin($user)
                                            ) : ?>
                                                <button type="button" class="btn btn-danger btn-xs deleteButton" data-id="<?php
                                                                                                                            echo $user->user_id ?>" data-userName="<?php
                                                                                                                                                                    echo $user->user_name ?>" data-bs-toggle="modal" data-bs-target="#deleteUserModal"><span class='fa fa-trash'></span> Delete
                                                </button>
                                            <?php
                                            endif; ?>
                                        </td>

                                    <?php
                                    endif; ?>
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
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.editButton');
        const deleteButtons = document.querySelectorAll('.deleteButton');
        const editUserModal = document.getElementById('editUserModal');
        const deleteUserModal = document.getElementById('deleteUserModal');

        deleteButtons.forEach((button) => {
            button.addEventListener('click', function() {
                deleteUserModal.querySelector('#del_user_id').value = this.getAttribute('data-id');
                deleteUserModal.querySelector('#del_user_message').innerText = `Are you sure you want to delete the user ${ this.getAttribute('data-userName') }?`;
            });
        });

        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const roleId = this.getAttribute('data-role_id');
                const userName = this.getAttribute('data-userName');
                const userEmail = this.getAttribute('data-email');
                const firstName = this.getAttribute('data-first_name');
                const lastName = this.getAttribute('data-last_name');
                const active = this.getAttribute('data-active');
                const isAdmin = this.getAttribute('data-is_admin');

                editUserModal.querySelector('#edit_user_id').value = userId;
                editUserModal.querySelector('#edit_user_name').value = userName;
                editUserModal.querySelector('#edit_user_email').value = userEmail;
                editUserModal.querySelector('#edit_user_role').value = roleId;
                editUserModal.querySelector('#edit_first_name').value = firstName;
                editUserModal.querySelector('#edit_last_name').value = lastName;
                editUserModal.querySelector('#edit_is_active').checked = active === '1';
                editUserModal.querySelector('#edit_is_admin').checked = isAdmin === 'On';
            });
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const passwordTogglers = document.querySelectorAll('.password-toggler');

        passwordTogglers.forEach((toggler) => {
            toggler.addEventListener('click', () => {
                const togglerIDom = toggler.querySelector('i');
                const input = toggler.previousElementSibling || toggler.nextElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    togglerIDom.classList.remove('fa-eye');
                    togglerIDom.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    togglerIDom.classList.remove('fa-eye-slash');
                    togglerIDom.classList.add('fa-eye');
                }
            });
        });

        document.getElementById('user_photo').addEventListener('change', (ev) => {
            document.querySelector('#upload_user_photo').value = ev.target.files.length;
        });

        document.getElementById('edit_user_photo').addEventListener('change', (ev) => {
            document.querySelector('#edit_user_upload_user_photo').value = ev.target.files.length;
        });

        setTimeout(() => {
            // Add a div next to the search input
            const searchInput = document.querySelector('.dt-search');
            const searchPrepend = document.querySelector('.search-prepend');
            searchInput.insertAdjacentElement('afterbegin', searchPrepend);
        }, 0);
    });
</script>