<?php

declare(strict_types = 1);


if (isset($_POST['submit']) && !empty($_POST['amount_spent']) && !empty($_POST['expense_category_id']) && !empty($_POST['expense_date'])
    && !empty($_POST['expense_description'])) {
    // echo print_r($_POST);
    // exit;
    $amount_spent = mysqli_real_escape_string($con, $_POST['amount_spent']);
    @$expense_date = mysqli_real_escape_string($con, $_POST['expense_date']);
    $expense_description = mysqli_real_escape_string($con, $_POST['expense_description']);
    $expense_category_id = mysqli_real_escape_string($con, $_POST['expense_category_id']);
    $created_at = date('Y-m-d');
    $zero = '0';

    //get a particular row amount and expense_name
    $getAll = mysqli_query(
        $con,
        "SELECT expense_category_name, amount FROM expense_category_tbl WHERE expense_category_id ='" . $expense_category_id . " ' "
    );
    while ($row = mysqli_fetch_assoc($getAll)) {
        $_amount = $row['amount']; //SET AMOUNT
        $_expense_category_name = $row['expense_category_name'];
        // exit;
    }

    $getBal = mysqli_query(
        $con,
        "SELECT expense_category_id, SUM(amount_spent) AS amount_spent FROM expense_tbl WHERE expense_category_id ='" . $expense_category_id . " ' GROUP BY  expense_category_id "
    );
    $sum = 0;
    while ($row = mysqli_fetch_assoc($getBal)) {
        $_amount_spent = $row['amount_spent'];
        $sum += $_amount_spent;
    }

    $sum;
//exit;

    $balance = $_amount - $sum;
    // exit;

    if ($balance < 0) {
        echo '
    <script type="text/javascript">
    confirm("You are spending too much on ' . ucfirst($_expense_category_name) . ' ");
    </script>';
        // exit;
    }

    $data = mysqli_query(
        $con,
        "INSERT INTO expense_tbl(expense_category_id,expense_description,expense_date,created_at,deleted,amount_spent)
      VALUES('" . $expense_category_id . "','" . $expense_description . "','" . $expense_date . "','" . $created_at . "','" . $zero . "','" . $amount_spent . "')"
    );
}


//if data inserted successfully
if (@$data === true) {
    echo '
         <script type="text/javascript">
          alert("Success!");
         </script>';
} else {
    $message = 'All fields are required';
}
