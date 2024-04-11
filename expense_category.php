<?php

declare(strict_types = 1);

use ExpenseTracker\ExpenseCategoryList;

require_once 'modals.php';

$expenseCategoryList = new ExpenseCategoryList();
$catList = $expenseCategoryList->getAllCategories();
?>


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
                                    <button type="button" class="btn btn-info btn-xs" data-bs-toggle="modal" data-bs-target="#modal_update<?php
                                    echo $row['expense_category_id'] ?>">
                                        <span class='fa fa-pencil'></span> Edit
                                    </button>
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
