<?php

declare(strict_types = 1);

require_once 'header.php';
require_once 'sidenav.php';
?>

<div class="row">
    <div class="col-md-12">
        <!-- Advanced Tables -->
        <div class="panel panel-default">
            <div class="panel-heading">
                Expense Report
                <div class="pull-right">Filter Report using the search</div>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                        <thead>
                        <th>Expense Name</th>
                        <th>Amount Spent</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>


                        <?php
                        $sql = mysqli_query($con, "SELECT *FROM expense_category_tbl i LEFT JOIN expense_tbl s ON i.expense_category_id= s.expense_category_id
  WHERE (s.expense_category_id>0 )");
                        while ($row = mysqli_fetch_array($sql))
                        {
                        ?>
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
                                <button type="button" class="btn btn-danger btn-xs" data-target="#modal_delete<?php
                                echo $row['expense_id'] ?>" data-toggle='modal'><span class='glyphicon glyphicon-trash'></span> Delete
                                </button>
                            </td>


                            <?php
                            } ?>

                            <?php


                            ob_flush();
                            mysqli_close($con);
                            ?>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
        <!--End Advanced Tables -->
    </div>
</div>
