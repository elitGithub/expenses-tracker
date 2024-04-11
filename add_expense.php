<?php

declare(strict_types = 1);

use ExpenseTracker\Expense;


$expense = new Expense();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] === 'Add' && password_verify($_POST['formToken'], $_SESSION['formToken']['add_new_expense'])) {
    $description = Filter::filterInput(INPUT_POST, 'expense_description', FILTER_SANITIZE_SPECIAL_CHARS, '');
    $amount = Filter::filterInput(INPUT_POST, 'amount_spent', FILTER_SANITIZE_NUMBER_FLOAT, 0.00);
    $categoryId = Filter::filterInput(INPUT_POST, 'expense_category_id', FILTER_SANITIZE_NUMBER_INT);
    $date = Filter::filterInput(INPUT_POST, 'expense_date', FILTER_SANITIZE_SPECIAL_CHARS);
    $expenseId = $expense->add($description, $date, (float)$amount, (int)$categoryId);

    if ($expenseId > 0) {
        $_SESSION['success'][] = 'Successfully created a new expense with ID ' . $expenseId;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    $_SESSION['errors'][] = 'Could not create a new expense.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);
