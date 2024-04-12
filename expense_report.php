<?php

declare(strict_types = 1);

use ExpenseTracker\ExpenseList;
use Permissions\PermissionsManager;

$expensesList = new ExpenseList();
$rows = $expensesList->getExpenses();

?>
<!-- Modal trigger button with Bootstrap 5 data attributes -->
<button class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
    <i class="fa fa-plus-circle fa-2x"></i> Enter Expenses
</button>
<div class="row">
    <div class="col-md-12">
        <!-- Advanced Tables -->
        <div class="panel panel-default">
            <div class="panel-heading">
                Expense Categories
                <div class="pull-right">Filter Report using the search</div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                        <tr>
                            <th>Expense Category Name</th>
                            <th>Amount Spent</th>
                            <th>Expense Description</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($rows as $row): ?>
                            <tr>
                                <td><?php
                                    echo $row['expense_category_name']; ?></td>
                                <td><?php
                                    echo $row['amount_spent']; ?></td>
                                <td><?php
                                    echo $row['expense_description']; ?></td>
                                <td><?php
                                    echo $row['expense_date']; ?></td>
                                <td>
                                    <?php
                                    if (PermissionsManager::isPermittedAction('edit_expense', $user)): ?>
                                        <button type="button" class="btn btn-info btn-xs editButton"
                                                data-id="<?php echo $row['expense_id']; ?>"
                                                data-expense-category-id="<?php echo $row['expense_category_id']; ?>"
                                                data-description="<?php echo htmlspecialchars($row['expense_description']); ?>"
                                                data-amount="<?php echo htmlspecialchars($row['amount_spent']); ?>"
                                                data-date="<?php echo htmlspecialchars($row['created_at']); ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editExpenseModal">
                                            <span class='fa fa-pencil'></span> Edit
                                        </button>
                                    <?php
                                    endif; ?>
                                    <?php
                                    if (PermissionsManager::isPermittedAction('delete_expense', $user)): ?>
                                        <button type="button" class="btn btn-danger btn-xs deleteButton"
                                                data-id="<?php echo $row['expense_id']; ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteExpenseModal"><span
                                                class='fa fa-trash'></span> Delete
                                        </button>
                                    <?php
                                    endif; ?>
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
        const editExpenseModal = document.getElementById('editExpenseModal');
        const deleteModal = document.getElementById('deleteExpenseModal');

        deleteButtons.forEach((button) => {
            button.addEventListener('click', function () {
                deleteModal.querySelector('#expense_id').value = this.getAttribute('data-id');
            });
        });

        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const expenseId = this.getAttribute('data-id');
                const expenseCategoryId = this.getAttribute('data-expense-category-id');
                const description = this.getAttribute('data-description');
                const amount = this.getAttribute('data-amount');
                const date = this.getAttribute('data-date');

                editExpenseModal.querySelector('#expense_id').value = expenseId;
                editExpenseModal.querySelector('#expense_category_id').value = expenseCategoryId;
                editExpenseModal.querySelector('#amount_spent').value = amount;
                editExpenseModal.querySelector('#expense_description').value = description;
                editExpenseModal.querySelector('#expense_date').value = date;
            });
        });
    });
</script>

