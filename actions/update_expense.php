<?php

declare(strict_types = 1);

use ExpenseTracker\Expense;
use Permissions\PermissionsManager;


$expense = new Expense();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && PermissionsManager::isPermittedAction('edit_expense', $user) && password_verify($_POST['formToken'], $_SESSION['formToken']['edit_expense'])) {
    $expenseId = Filter::filterInput(INPUT_POST, 'expense_id', FILTER_SANITIZE_NUMBER_INT);
    if (empty($expenseId)) {
        $_SESSION['errors'][] = 'Missing expense id.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    $description = Filter::filterInput(INPUT_POST, 'expense_description', FILTER_SANITIZE_SPECIAL_CHARS, '');
    $amount = Filter::filterInput(INPUT_POST, 'amount_spent', FILTER_SANITIZE_NUMBER_FLOAT, 0.00);
    $categoryId = Filter::filterInput(INPUT_POST, 'expense_category_id', FILTER_SANITIZE_NUMBER_INT);
    $date = Filter::filterInput(INPUT_POST, 'expense_date', FILTER_SANITIZE_SPECIAL_CHARS);
    $expense = $expense->getById((int)$expenseId);

    $expense->expense_description = $description;
    $expense->amount_spent = $amount;
    $expense->expense_category_id = $categoryId;
    $expense->expense_date = $date;
    $result = $expense->update();

    if ($result > 0) {
        $_SESSION['success'][] = 'Successfully updated an expense with ID ' . $expenseId;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    $_SESSION['errors'][] = 'Expense not updated.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);
