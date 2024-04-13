<?php

declare(strict_types = 1);

use ExpenseTracker\ExpenseCategoryList;
use Permissions\PermissionsManager;

$expenseCategoryList = new ExpenseCategoryList();
$catList = $expenseCategoryList->categoryReport();
if (!PermissionsManager::isPermittedAction('expense_category', $user)) {
    header('Location: index.php');
}
?>
<!-- Trigger Button -->
<button class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
    <i class="fa fa-plus-circle fa-2x"></i> Add Category
</button>

<div class="row">
    <div class="col-md-12">
        <!-- Advanced Tables -->
        <div class="panel panel-default">
            <div class="panel-heading">
                Category List
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                        <tr>
                            <th>Category Id</th>
                            <th>Category Name</th>
                            <th>Category Budget</th>
                            <th>Total Expenses in category</th>
                            <th>Date Created</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($catList as $row): ?>
                            <tr <?php if ((float)$row['cat_expenses'] >= (float) $row['amount']) echo 'class="bg-color-red"'?>>
                                <td><?php
                                    echo $row['expense_category_id'] ?>
                                </td>
                                <td><?php
                                    echo $row['expense_category_name'] ?>
                                </td>
                                <td><?php
                                    echo number_format((float) $row['amount'], 2, '.', '') ?>
                                </td>
                                <td><?php
                                    echo number_format((float) $row['cat_expenses'], 2, '.', '') ?>
                                </td>
                                <td><?php
                                    echo $date = date_format(new DateTime($row['created_at']), 'd-M-Y') ?>
                                </td>
                                <td>
                                    <?php if(PermissionsManager::isPermittedAction('edit_expense_category', $user)): ?>
                                        <button type="button" class="btn btn-info btn-xs editButton"
                                                data-id="<?php echo $row['expense_category_id'] ?>"
                                                data-name="<?php echo htmlspecialchars($row['expense_category_name']); ?>"
                                                data-amount="<?php echo htmlspecialchars($row['amount']); ?>"
                                                data-default="<?php echo htmlspecialchars($row['is_default']); ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCategoryModal">
                                            <span class='fa fa-pencil'></span> Edit
                                        </button>

                                    <?php endif; ?>
                                    <?php if (PermissionsManager::isPermittedAction('delete_expense_category', $user) && !$row['is_default']): ?>
                                        <button type="button" class="btn btn-danger btn-xs deleteButton"
                                                data-id="<?php echo $row['expense_category_id'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteCategoryModal"><span
                                                class='fa fa-trash'></span> Delete
                                        </button>
                                    <?php endif;?>
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
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.editButton');
        const deleteButtons = document.querySelectorAll('.deleteButton');
        const editExpenseModal = document.getElementById('editCategoryModal');
        const deleteModal = document.getElementById('deleteCategoryModal');

        deleteButtons.forEach((button) => {
            button.addEventListener('click', function () {
                deleteModal.querySelector('#category_id').value = this.getAttribute('data-id');
            });
        });

        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const expenseCategoryId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const amount = this.getAttribute('data-amount');
                const isDefault = this.getAttribute('data-default');

                editExpenseModal.querySelector('#category_id').value = expenseCategoryId;
                editExpenseModal.querySelector('#expense_category_name').value = name;
                editExpenseModal.querySelector('#expense_category_budget').value = amount;
                editExpenseModal.querySelector('#is_default').checked = Number(isDefault) === 1;
            });
        });
    });
</script>

