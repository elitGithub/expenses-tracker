<?php

declare(strict_types = 1);

use Core\UniqueIdsGenerator;
use ExpenseTracker\ExpenseCategoryList;
use Permissions\PermissionsManager;
use Permissions\Role;

$uniqueIdGenerator = new UniqueIdsGenerator();

$addNewCatToken = $uniqueIdGenerator->generateTrueRandomString();
$editCatToken = $uniqueIdGenerator->generateTrueRandomString();
$deleteCatToken = $uniqueIdGenerator->generateTrueRandomString();
$addNewExpenseToken = $uniqueIdGenerator->generateTrueRandomString();
$editExpenseToken = $uniqueIdGenerator->generateTrueRandomString();
$deleteExpenseToken = $uniqueIdGenerator->generateTrueRandomString();
$addUserToken = $uniqueIdGenerator->generateTrueRandomString();
$editUserToken = $uniqueIdGenerator->generateTrueRandomString();


$_SESSION['formToken']['add_new_category'] = password_hash($addNewCatToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['edit_category'] = password_hash($editCatToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['delete_category'] = password_hash($deleteCatToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['add_new_expense'] = password_hash($addNewExpenseToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['edit_expense'] = password_hash($editExpenseToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['delete_expense'] = password_hash($deleteExpenseToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['add_user_token'] = password_hash($addUserToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['edit_user_token'] = password_hash($editUserToken, PASSWORD_DEFAULT);

$expenseCategoryList = new ExpenseCategoryList();
$expenseCategories = $expenseCategoryList->getAllCategories();
?>


<!--  Modals-->
<div class="d-flex d-row justify-content-between">
    <!-- MANAGE EXPENSE MODALS   -->
    <?php if (PermissionsManager::isPermittedAction('add_expense', $current_user) && count($expenseCategories) > 0): ?>
        <div class="panel panel-default" id="add_new_expense_modal">
            <div class="panel-body">
                <!-- Modal -->
                <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header d-flex justify-content-between">
                                <h4 class="modal-title" id="addExpenseModalLabel">
                                    <i class="fa fa-plus-circle fa-1x"></i> Add Expenses
                                </h4>
                                <!-- 'data-bs-dismiss' attribute for Bootstrap 5 -->
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="index.php?action=add_expense" method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="add_expense_category_id">Expense Name:</label>
                                            <select class="form-control" name="expense_category_id" id="add_expense_category_id" required>
                                                <option value="" selected>Choose Expense Category</option>
                                                <?php
                                                echo join('', $expenseCategoryList->getAllCategories(true)) ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="add_expense_amount_spent">Amount Spent:</label>
                                            <input type="number" class="form-control" name="amount_spent" id="add_expense_amount_spent"
                                                   placeholder="Please Enter Expense Amount :" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="add_expense_description">Expense Description:</label>
                                            <input type="text" class="form-control" name="expense_description" id="add_expense_description"
                                                   placeholder="Please Enter Expense Description :" required>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="add_expense_date">Date:</label>
                                            <input type="date" class="form-control" name="expense_date" id="add_expense_date" required>
                                        </div>
                                    </div>

                                    <input type="hidden" name="formToken" value="<?php
                                    echo htmlspecialchars($addNewExpenseToken); ?>">

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel / Reset</button>
                                        <input type="submit" id="submit" name="submit" value="Add" class="btn btn-primary">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endif; ?>
    <?php if (PermissionsManager::isPermittedAction('edit_expense', $current_user)): ?>
        <div class="panel-body">
            <!-- Modal -->
            <div class="modal fade" id="editExpenseModal" aria-hidden="true" aria-labelledby="myModalLabel">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <!-- 'data-bs-dismiss' attribute for Bootstrap 5 -->
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            <h4 class="modal-title" id="myModalLabel">
                                <i class="fa fa-plus-circle fa-1x"></i> Edit Expense
                            </h4>
                        </div>
                        <div class="modal-body">
                            <form action="index.php?action=update_expense" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="expense_id">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="expense_category_id">Expense Category:</label>
                                        <select class="form-control" name="expense_category_id" id="expense_category_id" required>
                                            <option disabled value="" selected>Choose Expense Category</option>
                                            <?php
                                            echo join('', $expenseCategoryList->getAllCategories(true)) ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="amount_spent">Amount Spent:</label>
                                        <input type="text" class="form-control" name="amount_spent" id="amount_spent"
                                               placeholder="Please Enter Expense Amount :" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="expense_description">Expense Description:</label>
                                        <input type="text" class="form-control" name="expense_description" id="expense_description"
                                               placeholder="Please Enter Expense Description :" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="expense_date">Date:</label>
                                        <input type="date" class="form-control" name="expense_date" id="expense_date" required>
                                    </div>
                                </div>

                                <input type="hidden" name="formToken" value="<?php
                                echo htmlspecialchars($editExpenseToken); ?>">
                                <input type="hidden" id="expense_id" name="expense_id">

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel / Reset</button>
                                    <input type="submit" id="update_expense_submit" name="submit" value="Edit" class="btn btn-primary">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endif; ?>
    <?php if (PermissionsManager::isPermittedAction('delete_expense', $current_user)): ?>
        <div class="panel panel-default" id="modal_delete_expense">
            <div class="modal fade" id="deleteExpenseModal" tabindex="-1" aria-labelledby="deleteExpenseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="deleteExpenseModalLabel">System</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="index.php?action=delete_expense">
                            <div class="modal-body text-center">
                                <h4>Are you sure you want to delete this expense?</h4>
                                <!-- Hidden input for CSRF protection -->
                                <input type="hidden" id="del_expense_id" name="expense_id">
                                <input type="hidden" name="formToken" value="<?php
                                echo htmlspecialchars($deleteExpenseToken); ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                <button type="submit" class="btn btn-danger">Yes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php
    endif; ?>
    <!-- /MANAGE EXPENSE MODALS   -->

    <!-- MANAGE CATEGORY MODALS   -->
    <?php if (PermissionsManager::isPermittedAction('add_expense_category', $current_user)): ?>
        <div class="panel panel-default" id="add_new_category_modal">
            <!-- Modal Structure -->
            <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="Add category modal" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header d-flex justify-content-between">
                            <h4 class="modal-title" id="addCategoryModalLabel">
                                <i class="fa fa-plus-circle fa-1x"></i> Add Expense Category
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="index.php?action=add_expense_category" method="POST">
                                <div class="form-group">
                                    <label for="new_expense_category_name">Category Name:</label>
                                    <input type="text" class="form-control" name="new_expense_category_name" id="new_expense_category_name"
                                           placeholder="Enter Category Name" required>
                                </div>

                                <div class="form-group">
                                    <label for="new_expense_category_budget">Category Budget:</label>
                                    <input type="number" class="form-control" name="new_expense_category_budget" id="new_expense_category_budget"
                                           placeholder="Enter Category Budget" required>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" <?php
                                    if (count($expenseCategories) < 1) echo 'checked=true' ?> class="form-check-input" name="is_default"
                                           id="new_expense_category_is_default">
                                    <label for="new_expense_category_is_default">Set this category as default</label>
                                </div>
                                <input type="hidden" name="formToken" value="<?php
                                echo htmlspecialchars($addNewCatToken); ?>">
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <input type="submit" name="submit" value="Add" class="btn btn-primary">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endif; ?>
    <?php if (PermissionsManager::isPermittedAction('edit_expense_category', $current_user)): ?>
        <div class="modal fade" id="editCategoryModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header d-flex justify-content-between">
                        <h4 class="modal-title" id="addCategoryModalLabel">
                            <i class="fa fa-plus-circle fa-1x"></i> Update Category
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="index.php?action=update_expense_category" method="POST">
                            <div class="form-group">
                                <label for="expense_category_name">Category Name:</label>
                                <input type="text" class="form-control" name="expense_category_name" id="expense_category_name" required>
                            </div>

                            <div class="form-group">
                                <label for="expense_category_budget">Category Budget:</label>
                                <input type="number" class="form-control" name="expense_category_budget" id="expense_category_budget" required>
                            </div>
                            <div class="form-group">
                                <input type="checkbox" class="form-check-input" name="is_default" id="is_default">
                                <label for="is_default">Set this category as default</label>
                            </div>
                            <input type="hidden" name="formToken" value="<?php
                            echo htmlspecialchars($editCatToken); ?>">
                            <input type="hidden" name="category_id" id="category_id">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <input type="submit" name="submit" value="Save" class="btn btn-primary">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endif; ?>
    <?php if (PermissionsManager::isPermittedAction('delete_expense_category', $current_user)): ?>
        <div class="panel panel-default" id="modal_delete_expense">
            <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="deleteCategoryModalLabel">System</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="index.php?action=delete_category">
                            <div class="modal-body text-center">
                                <h4>Are you sure you want to delete this category All expenses will be moved to the category marked as default.</h4>
                                <!-- Hidden input for CSRF protection -->
                                <input type="hidden" id="del_category_id" name="category_id">
                                <input type="hidden" name="formToken" value="<?php
                                echo htmlspecialchars($deleteCatToken); ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                <button type="submit" class="btn btn-danger">Yes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php
    endif; ?>
    <!-- /MANAGE CATEGORY MODALS   -->

    <!-- MANAGE Users MODALS   -->
    <?php if (PermissionsManager::isPermittedAction('add_user', $current_user)): ?>
        <div class="panel panel-default" id="add_new_user_modal">
            <div class="panel-body">
                <!-- Modal -->
                <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header d-flex justify-content-between">
                                <h4 class="modal-title" id="addUserModalLabel">
                                    <i class="fa fa-plus-circle fa-1x"></i> Add User
                                </h4>
                                <!-- 'data-bs-dismiss' attribute for Bootstrap 5 -->
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="index.php?action=add_user" method="POST"  enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="mb-3">
                                            <label for="user_photo" class="form-label">Add user profile picture</label>
                                            <input class="form-control" name="user_photo" type="file" id="user_photo">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="user_name">Username:</label>
                                            <input type="text" class="form-control" name="user_name" id="user_name"
                                                   placeholder="Please Enter the username" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="email">User email:</label>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="Please Enter user email"
                                                   required>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label for="user_role">User role:</label>
                                            <select class="form-control" name="user_role" id="user_role" required>
                                                <option value="" selected disabled>Choose Role</option>
                                                <?php
                                                echo join('', Role::getChildRoles($current_user, true)) ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="first_name">User first name:</label>
                                            <input type="text" class="form-control" name="first_name" id="first_name"
                                                   placeholder="Please Enter first name" required>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="last_name">User last name:</label>
                                            <input type="text" class="form-control" name="last_name" id="last_name"
                                                   placeholder="Please Enter last name" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="password">User password:</label>
                                            <div class="input-group" id="show_user_password">
                                                <input name="password" type="password" minlength="8" autocomplete="off" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" id="password" class="form-control">
                                                <span class="input-group-text cursor-pointer" id="toggleUserPassword"><i class="fa fa-eye" id="showUserPassword"></i></span>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="password_retype">Retype password:</label>
                                            <div class="input-group" id="show_retype_password">
                                                <input type="password" autocomplete="off" name="password_retype" id="password_retype" minlength="8"
                                                       class="form-control" required>
                                                <span class="input-group-text cursor-pointer" id="toggleRetypePassword"><i class="fa fa-eye"
                                                                                                                           id="showRetypePassword"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    if (PermissionsManager::isAdmin($current_user)): ?>
                                        <div class="row">
                                            <div class="form-group">
                                                <input type="checkbox" class="form-check-input" name="is_admin" id="add_user_is_admin">
                                                <label for="add_user_is_admin">Make user admin</label>
                                            </div>
                                        </div>
                                    <?php
                                    endif; ?>
                                    <input type="hidden" name="formToken" value="<?php
                                    echo htmlspecialchars($addUserToken); ?>">
                                    <input type="hidden" id="upload_user_photo" name="upload_user_photo" value="">

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel / Reset</button>
                                        <input type="submit" id="add_user_submit" name="submit" value="Add" class="btn btn-primary">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endif ?>
    <?php if (PermissionsManager::isPermittedAction('edit_user', $current_user)): ?>
        <div class="panel panel-default" id="edit_user_modal">
            <div class="panel-body">
                <!-- Modal -->
                <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header d-flex justify-content-between">
                                <h4 class="modal-title" id="editUserModalLabel">
                                    <i class="fa fa-edit fa-1x"></i> Edit User
                                </h4>
                                <!-- 'data-bs-dismiss' attribute for Bootstrap 5 -->
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="index.php?action=edit_user" method="POST"  enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="mb-3">
                                            <label for="edit_user_photo" class="form-label">Change user profile picture</label>
                                            <input class="form-control" name="user_photo" type="file" id="edit_user_photo">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="edit_user_name">Username:</label>
                                            <input type="text" class="form-control" name="user_name" id="edit_user_name"
                                                   placeholder="Please Enter the username" disabled="disabled">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="edit_user_email">User email:</label>
                                            <input type="email" class="form-control" name="email" id="edit_user_email" placeholder="Please Enter user email"
                                                   required>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label for="edit_user_role">User role:</label>
                                            <select class="form-control" name="user_role" id="edit_user_role" required>
                                                <option value="" selected disabled>Choose Role</option>
                                                <?php
                                                echo join('', Role::getChildRoles($current_user, true)) ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="edit_first_name">User first name:</label>
                                            <input type="text" class="form-control" name="first_name" id="edit_first_name"
                                                   placeholder="Please Enter first name" required>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="edit_last_name">User last name:</label>
                                            <input type="text" class="form-control" name="last_name" id="edit_last_name"
                                                   placeholder="Please Enter last name" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="change_password">User password:</label>
                                            <div class="input-group" id="show_edit_user_password">
                                                <input name="password" type="password" minlength="8" autocomplete="off" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" id="change_password" class="form-control">
                                                <span class="input-group-text cursor-pointer" id="toggleChangeUserPassword"><i class="fa fa-eye" id="showChangeUserPassword"></i></span>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="retype_change_password">Retype password:</label>
                                            <div class="input-group" id="show_retype_change_password">
                                                <input type="password" autocomplete="off" name="password_retype" id="retype_change_password" minlength="8"
                                                       class="form-control">
                                                <span class="input-group-text cursor-pointer" id="toggleRetypeChangePassword"><i class="fa fa-eye"
                                                                                                                           id="showChangeRetypePassword"></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    if (PermissionsManager::isAdmin($current_user)): ?>
                                        <div class="row">
                                            <div class="form-group">
                                                <input type="checkbox" class="form-check-input" name="is_admin" id="edit_is_admin">
                                                <label for="edit_is_admin">Make user admin</label>
                                            </div>
                                        </div>
                                    <?php
                                    endif; ?>
                                    <div class="row">
                                        <div class="form-group">
                                            <input type="checkbox" class="form-check-input" name="active" id="edit_is_active">
                                            <label for="edit_is_active">Active</label>
                                        </div>
                                    </div>
                                    <input type="hidden" name="formToken" value="<?php
                                    echo htmlspecialchars($editUserToken); ?>">
                                    <input type="hidden" name="userId" id="edit_user_id">
                                    <input type="hidden" id="edit_user_upload_user_photo" name="upload_user_photo" value="">

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel / Reset</button>
                                        <input type="submit" id="edit_user_submit" name="submit" value="Save" class="btn btn-primary">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endif ?>
</div>
<!-- End Modals-->
