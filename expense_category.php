<?php

declare(strict_types=1);
require_once 'modals.php';
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

                        <th>Expense Name</th>
                        <th>Amount</th>
                        <th>Date Created</th>
                        <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>


                        <?php
                        $sql = mysqli_query($con, "SELECT * FROM expense_category_tbl order by expense_category_name ASC");
                        while ($row = mysqli_fetch_array($sql))
                        {
                        ?>
                        <tr>
                            <td><?php
                                echo $row['expense_category_name'] ?></td>
                            <td><?php
                                echo number_format((float) $row['amount'], 2, '.', '') ?></td>
                            <td><?php
                                echo $date = DATE_FORMAT(new DateTime($row['created_at']), 'd-M-Y') ?></td>
                            <td>
                                <button type="button" class="btn btn-info btn-xs" data-target="#modal_update<?php
                                echo $row['expense_category_id'] ?>" data-toggle='modal'><span class='glyphicon glyphicon-pencil'></span>
                                    Edit
                                </button>
                            </td>

                            <div class="modal fade" id="modal_update<?php
                            echo $row['expense_category_id'] ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3 class="modal-title">Update Expense</h3>
                                        </div>
                                        <form action="update_expense.php" method="POST" enctype="multipart/form-data">
                                            <div class="modal-body">

                                                <!-- <center><h4>Are you sure you want to delete this expense?</h4></center> -->
                                                <!-- hidden fields -->
                                                <input type="hidden" id="getID" name="getID" value="<?php
                                                echo $row['expense_category_id'] ?>">

                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label>Expense Name</label>
                                                        <input type="text" name="expense_name" id="expense_name" class="form-control"
                                                               value="<?php
                                                               echo $row['expense_category_name'] ?>" required="">
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <label>Expense Amount</label>
                                                        <input type="text" name="amount" id="amount" class="form-control" value="<?php
                                                        echo $row['amount'] ?>" required="">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- <div class="row" >
                                           <div class="form-group">  -->
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary" data-dismiss="modal">No</button>
                                                <input type="submit" id="submit" name="submit" value="Yes" class="btn btn-danger"/>
                                            </div>
                                            <!-- </div>
                                           </div> -->
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php
                            }
                            ob_flush();
                            ?>

                        </tbody>
                    </table>

                </div>

            </div>
        </div>
        <!--End Advanced Tables -->
    </div>
</div>
