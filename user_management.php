<?php

declare(strict_types = 1);


use Permissions\PermissionsManager;

if (!PermissionsManager::isPermittedAction('view_user_management', $user)) {
    header('Location: index.php');
}

\Permissions\Role::getChildRoles($user);
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
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
<!--                        --><?php
//                        foreach ($rows as $row): ?>
<!--                            <tr>-->
<!--                                <td>--><?php
//                                    echo $row['expense_category_name']; ?><!--</td>-->
<!--                                <td>--><?php
//                                    echo $row['amount_spent']; ?><!--</td>-->
<!--                                <td>--><?php
//                                    echo $row['expense_description']; ?><!--</td>-->
<!--                                <td>--><?php
//                                    echo $row['expense_date']; ?><!--</td>-->
<!--                                <td>-->
<!--                                    --><?php
//                                    if (PermissionsManager::isPermittedAction('edit_expense', $user)): ?>
<!--                                        <button type="button" class="btn btn-info btn-xs editButton"-->
<!--                                                data-id="--><?php //echo $row['expense_id']; ?><!--"-->
<!--                                                data-expense-category-id="--><?php //echo $row['expense_category_id']; ?><!--"-->
<!--                                                data-description="--><?php //echo htmlspecialchars($row['expense_description']); ?><!--"-->
<!--                                                data-amount="--><?php //echo htmlspecialchars($row['amount_spent']); ?><!--"-->
<!--                                                data-date="--><?php //echo htmlspecialchars($row['created_at']); ?><!--"-->
<!--                                                data-bs-toggle="modal"-->
<!--                                                data-bs-target="#editExpenseModal">-->
<!--                                            <span class='fa fa-pencil'></span> Edit-->
<!--                                        </button>-->
<!--                                    --><?php
//                                    endif; ?>
<!--                                    --><?php
//                                    if (PermissionsManager::isPermittedAction('delete_expense', $user)): ?>
<!--                                        <button type="button" class="btn btn-danger btn-xs deleteButton"-->
<!--                                                data-id="--><?php //echo $row['expense_id']; ?><!--"-->
<!--                                                data-bs-toggle="modal"-->
<!--                                                data-bs-target="#deleteExpenseModal"><span-->
<!--                                                class='fa fa-trash'></span> Delete-->
<!--                                        </button>-->
<!--                                    --><?php
//                                    endif; ?>
<!--                                </td>-->
<!--                            </tr>-->
<!--                        --><?php
//                        endforeach;
//                        ?>
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
