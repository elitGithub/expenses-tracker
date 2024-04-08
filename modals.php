<?php

declare(strict_types = 1);

use ExpenseTracker\ExpenseCategoryList;

$expenseCategoryList = new ExpenseCategoryList();

?>

<div class="panel panel-default d-none" id="add_new_expense_modal">
    <div class="panel-body">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="myModalLabel"><i class="fa fa-plus-circle fa-1x"></i> Add Expenses</h4>
                    </div>
                    <div class="modal-body">
                        <div class="header">
                        </div>
                        <div class="content">
                            <div>
                                <form action="index.php" method="POST" enctype="multipart/form-data">

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="expense_category_id">Expense Name:</label>
                                            <select class="form-control" name="expense_category_id" id="expense_category_id" required="">
                                                <option value="" selected="">Choose Expense Category</option>
                                                <?php
                                                echo join('', $expenseCategoryList->getAllCategories(true))
                                                ?>
                                            </select>
                                        </div>


                                        <div class="form-group col-md-6">
                                            <label for="amount_spent"> Amount Spent: </label>
                                            <input type="text" name="amount_spent" id="amount_spent" class="form-control"
                                                   placeholder="Please Enter Expense Amount :" onBlur="this.value=trim(this.value);"
                                                   required>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="expense_description"> Expense Description: </label>
                                            <input type="text" name="expense_description" id="expense_description" class="form-control"
                                                   placeholder="Please Enter Expense Description :" onBlur="this.value=trim(this.value);"
                                                   required>
                                        </div>


                                        <div class="form-group col-md-6">
                                            <label for="expense_date"> Date: </label>
                                            <input type="date" name="expense_date" id="expense_date" class="form-control"
                                                   placeholder="Please Enter Expense Date :" onBlur="this.value=trim(this.value);"
                                                   required>

                                        </div>
                                    </div>


                                    <?php
                                    if (isset($message)) {
                                        echo "<font color='FF0000'><h5>$message</font></h5>";
                                    } ?>
                                    <div class="row">
                                        <div class="form-group">
                                            <div class="modal-footer">
                                                <input type="submit" id="submit" name="submit" value="Add" class="btn btn-primary"
                                                       style=""/>
                                                <input type="reset" id="rest" value="Cancel / Reset" class="btn btn-danger" style=""/>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade d-none" id="modal_delete<?php echo $row['expense_id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">System</h3>
            </div>
            <div class="modal-body">
                <center><h4>Are you sure you want to delete this expense?</h4></center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">No</button>
                <a type="button" class="btn btn-danger" href="delete.php?expense_id=<?php
                echo $row['expense_id'] ?>">Yes</a>
            </div>
        </div>
    </div>
</div>
