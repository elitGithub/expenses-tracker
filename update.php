<?php

session_start();
require_once('./src/db_config.php');
if (isset($_REQUEST['expense_category_id'])) {
    $expense_category_id = $_REQUEST['expense_category_id'];
    $_SESSION['expense_category_id'] = $expense_category_id;
    $sql = mysqli_query($con, "SELECT * FROM `expense_category_tbl` WHERE `expense_category_id`='" . $expense_category_id . "' ");
    while ($update_data = mysqli_fetch_array($sql)) {
        // echo $_SESSION['expense_category_name'] = $update_data['expense_category_name'];
        // echo $update_data['amount'];
        ?>
        <div class="content">
            <div>
                <form action="a.php" method="POST" enctype="multipart/form-data">
                    <div style=" margin-left:100px;">


                        <br>


                        <div class="form-group">
                            <label> Amount: </label>
                            <input type="text" name="amount" id="amount" style="margin-left:-50px" class="form-control"
                                   onBlur="this.value=trim(this.value);" value="<?php
                            echo $update_data['amount']; ?>" required>

                        </div>

                        <br>

                        <div class="form-group">
                            <label> Expense Name: </label>
                            <input type="text" name="expense_name" id="expense_name" style="margin-left:-50px" class="form-control"
                                   onBlur="this.value=trim(this.value);" value="<?php
                            echo $update_data['expense_category_name']; ?>" required>

                        </div>
                        <br>
                    </div>
            </div>
            <div class="modal-footer">

                <input type="submit" id="submit" name="submit" value="Add" class="btn btn-primary" style=""/>
                <input type="reset" id="rest" value="Cancel / Reset" class="btn btn-danger" style=""/>
            </div>
        </div>
        </form>
        <?php
    }
}
