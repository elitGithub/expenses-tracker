<?php

declare(strict_types = 1);


use ExpenseTracker\ExpenseCategory;
use ExpenseTracker\ExpenseCategoryList;

$expenseCatList = new ExpenseCategoryList();
$expenseCategory = new ExpenseCategory();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] === 'Edit' && password_verify($_POST['formToken'], $_SESSION['formToken']['delete_category'])) {

    $_SESSION['errors'][] = 'Could not delete expense';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);
