<?php

declare(strict_types = 1);

use ExpenseTracker\ExpenseCategory;




$category = new ExpenseCategory();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && password_verify($_POST['formToken'], $_SESSION['formToken']['edit_category'])) {
    $categoryId = Filter::filterInput(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    if (empty($categoryId)) {
        $_SESSION['errors'][] = 'Missing category id.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    $category->getById($categoryId);
    $name = Filter::filterInput(INPUT_POST, 'expense_category_name', FILTER_SANITIZE_SPECIAL_CHARS, '');
    $budget = Filter::filterInput(INPUT_POST, 'expense_category_budget', FILTER_SANITIZE_NUMBER_FLOAT, 0.00);
    $isDefault = Filter::filterInput(INPUT_POST, 'is_default', FILTER_VALIDATE_BOOLEAN, false);

    $category->expense_category_name = $name;
    $category->amount = $budget;
    $category->is_default = $isDefault;
    $result = $category->update();

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

