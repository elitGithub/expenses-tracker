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
                                        <button type="button" class="btn btn-info btn-xs"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editExpenseModal-<?php
                                                echo $row['expense_id']; ?>">
                                            <span class='fa fa-pencil'></span> Edit
                                        </button>

                                    <?php
                                    endif; ?>
                                    <?php
                                    if (PermissionsManager::isPermittedAction('delete_expense', $user)): ?>
                                        <button type="button" class="btn btn-danger btn-xs"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteExpenseModal-<?php
                                                echo $row['expense_id']; ?>"><span
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
