<?php

declare(strict_types = 1);

use ExpenseTracker\Expense;
use Permissions\PermissionsManager;


$expense = new Expense();

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    PermissionsManager::isPermittedAction('delete_expense', $current_user) &&
    password_verify($_POST['formToken'], $_SESSION['formToken']['delete_expense'])) {
    $expenseId = Filter::filterInput(INPUT_POST, 'expense_id', FILTER_SANITIZE_NUMBER_INT);
    if (empty($expenseId)) {
        $_SESSION['errors'][] = 'Missing expense id.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    $result = $expense->delete((int)$expenseId);

    if ($result > 0) {
        $_SESSION['success'][] = 'Successfully deleted an expense with ID ' . $expenseId;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    $_SESSION['errors'][] = 'Could not delete expense';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);
