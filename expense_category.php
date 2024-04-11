<?php

declare(strict_types = 1);

use ExpenseTracker\ExpenseCategoryList;
use Permissions\PermissionsManager;

$expenseCategoryList = new ExpenseCategoryList();
$catList = $expenseCategoryList->getAllCategories();
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
                            <th>Category Name</th>
                            <th>Category Budget</th>
                            <th>Date Created</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($catList as $row): ?>
                            <tr>
                                <td><?php
                                    echo $row['expense_category_name'] ?></td>
                                <td><?php
                                    echo number_format((float) $row['amount'], 2, '.', '') ?></td>
                                <td><?php
                                    echo $date = DATE_FORMAT(new DateTime($row['created_at']), 'd-M-Y') ?></td>
                                <td>
                                    <?php if(PermissionsManager::isPermittedAction('edit_expense_category', $user)): ?>
                                    <button type="button" class="btn btn-info btn-xs"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCategoryModal-<?php
                                    echo $row['expense_category_id'] ?>">
                                        <span class='fa fa-pencil'></span> Edit
                                    </button>
                                    <?php endif; ?>
                                    <?php if (PermissionsManager::isPermittedAction('delete_expense_category', $user)): ?>
                                        <button type="button" class="btn btn-danger btn-xs" data-bs-toggle="modal_delete_category"><span
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
