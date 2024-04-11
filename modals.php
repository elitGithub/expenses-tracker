<?php

declare(strict_types = 1);

use ExpenseTracker\ExpenseCategoryList;
use Permissions\PermissionsManager;

$uniqueIdGenerator = new \Core\UniqueIdsGenerator();

$addNewCatToken = $uniqueIdGenerator->generateTrueRandomString();
$addNewExpenseToken = $uniqueIdGenerator->generateTrueRandomString();
$editExpenseToken = $uniqueIdGenerator->generateTrueRandomString();
$deleteExpenseToken = $uniqueIdGenerator->generateTrueRandomString();
$_SESSION['formToken']['add_new_category'] = password_hash($addNewCatToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['add_new_expense'] = password_hash($addNewExpenseToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['edit_expense'] = password_hash($editExpenseToken, PASSWORD_DEFAULT);
$_SESSION['formToken']['delete_expense'] = password_hash($deleteExpenseToken, PASSWORD_DEFAULT);
$expenseCategoryList = new ExpenseCategoryList();
$expenseCategories = $expenseCategoryList->getAllCategories();
?>


<!--  Modals-->
<div class="d-flex d-row justify-content-between">
    <?php if (PermissionsManager::isPermittedAction('add_expense', $user) && count($expenseCategories) > 0): ?>
    <div class="panel panel-default" id="add_new_expense_modal">
        <!-- Modal trigger button with Bootstrap 5 data attributes -->
        <button class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="fa fa-plus-circle fa-2x"></i> Enter Expenses
        </button>

        <div class="panel-body">
            <!-- Modal -->
            <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <!-- 'data-bs-dismiss' attribute for Bootstrap 5 -->
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                &times;
                            </button>
                            <h4 class="modal-title" id="myModalLabel">
                                <i class="fa fa-plus-circle fa-1x"></i> Add Expenses
                            </h4>
                        </div>
                        <div class="modal-body">
                            <form action="index.php?action=add_expense" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="expense_category_id">Expense Name:</label>
                                        <select class="form-control" name="expense_category_id" id="expense_category_id" required>
                                            <option value="" selected>Choose Expense Category</option>
                                            <?php echo join('', $expenseCategoryList->getAllCategories(true)) ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="amount_spent">Amount Spent:</label>
                                        <input type="text" class="form-control" name="amount_spent" id="amount_spent" placeholder="Please Enter Expense Amount :" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="expense_description">Expense Description:</label>
                                        <input type="text" class="form-control" name="expense_description" id="expense_description" placeholder="Please Enter Expense Description :" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="expense_date">Date:</label>
                                        <input type="date" class="form-control" name="expense_date" id="expense_date" required>
                                    </div>
                                </div>

                                <input type="hidden" name="formToken" value="<?php echo htmlspecialchars($addNewExpenseToken); ?>">

                                <div class="modal-footer">
                                    <input type="submit" id="submit" name="submit" value="Add" class="btn btn-primary">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel / Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (PermissionsManager::isPermittedAction('add_expense_category', $user)): ?>
    <div class="panel panel-default" id="add_new_category_modal">
        <!-- Trigger Button -->
        <button class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fa fa-plus-circle fa-2x"></i> Add Category
        </button>

        <!-- Modal Structure -->
        <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="Add category modal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                        <h4 class="modal-title" id="addCategoryModalLabel">
                            <i class="fa fa-plus-circle fa-1x"></i> Add Expense Category
                        </h4>
                    </div>
                    <div class="modal-body">
                        <form action="index.php?action=add_expense_category" method="POST">
                            <!-- Expense Category Name Input -->
                            <div class="form-group">
                                <label for="new_expense_category_name">Category Name:</label>
                                <input type="text" class="form-control" name="new_expense_category_name" id="new_expense_category_name" placeholder="Enter Category Name" required>
                            </div>

                            <!-- Category Description Input -->
                            <div class="form-group">
                                <label for="new_expense_category_budget">Category Description:</label>
                                <input type="number" class="form-control" name="new_expense_category_budget" id="new_expense_category_budget" placeholder="Enter Category Budget" required>
                            </div>
                            <input type="hidden" name="formToken" value="<?php echo htmlspecialchars($addNewCatToken); ?>">
                            <div class="modal-footer">
                                <input type="submit" name="submit" value="Add" class="btn btn-primary">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (PermissionsManager::isPermittedAction('edit_expense', $user)): ?>
        <div class="panel-body">
            <!-- Modal -->
            <div class="modal fade" id="editExpenseModal-<?php echo $row['expense_id']; ?>" aria-hidden="true" aria-labelledby="myModalLabel" >
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <!-- 'data-bs-dismiss' attribute for Bootstrap 5 -->
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                &times;
                            </button>
                            <h4 class="modal-title" id="myModalLabel">
                                <i class="fa fa-plus-circle fa-1x"></i> Edit Expenses
                            </h4>
                        </div>
                        <div class="modal-body">
                            <form action="index.php?action=update_expense" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="expense_id" value="<?php echo $row['expense_id']; ?>">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="expense_category_id">Expense Name:</label>
                                        <select class="form-control" name="expense_category_id" id="expense_category_id" required>
                                            <option disabled value="" selected>Choose Expense Category</option>
                                            <?php
                                            echo join('', $expenseCategoryList->getAllCategories(true, $row['expense_category_id'])) ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="amount_spent">Amount Spent:</label>
                                        <input type="text" class="form-control" name="amount_spent" id="amount_spent" value="<?php echo $row['amount_spent']?>" placeholder="Please Enter Expense Amount :" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="expense_description">Expense Description:</label>
                                        <input type="text" class="form-control" name="expense_description" id="expense_description" value="<?php echo $row['expense_description']?>" placeholder="Please Enter Expense Description :" required>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="expense_date">Date:</label>
                                        <input type="date" class="form-control" value="<?php echo $row['expense_date']?>" name="expense_date" id="expense_date" required>
                                    </div>
                                </div>

                                <input type="hidden" name="formToken" value="<?php echo htmlspecialchars($editExpenseToken); ?>">

                                <div class="modal-footer">
                                    <input type="submit" id="submit" name="submit" value="Edit" class="btn btn-primary">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel / Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (PermissionsManager::isPermittedAction('delete_expense', $user)): ?>
        <div class="panel panel-default" id="modal_delete_expense">
            <div class="modal fade" id="deleteExpenseModal-<?php echo $row['expense_id']; ?>" tabindex="-1" aria-labelledby="deleteExpenseModalLabel-<?php echo $row['expense_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="deleteExpenseModalLabel-<?php echo $row['expense_id']; ?>">System</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="index.php?action=delete_expense">
                            <div class="modal-body text-center">
                                <h4>Are you sure you want to delete this expense?</h4>
                                <!-- Hidden input for CSRF protection -->
                                <input type="hidden" name="expense_id" value="<?php echo $row['expense_id']; ?>">
                                <input type="hidden" name="formToken" value="<?php echo htmlspecialchars($deleteExpenseToken); ?>">
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

    <?php endif; ?>
    <div class="modal fade" id="modal_update<?php echo $row['expense_category_id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Update Expense</h3>
                </div>
                <form action="index.php?action=update_expense" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="getID" name="getID" value="<?php
                        echo $row['expense_category_id'] ?>">

                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="expense_name">Expense Name</label>
                                <input type="text" name="expense_name" id="expense_name" class="form-control"
                                       value="<?php
                                       echo $row['expense_category_name'] ?>" required="">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="amount">Expense Amount</label>
                                <input type="text" name="amount" id="amount" class="form-control" value="<?php
                                echo $row['amount'] ?>" required="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">No</button>
                        <input type="submit" id="submit" name="submit" value="Yes" class="btn btn-danger"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Modals-->
