<?php

declare(strict_types = 1);


use ExpenseTracker\ExpenseCategory;

$expenseCategory = new ExpenseCategory();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] === 'Add' && password_verify($_POST['formToken'], $_SESSION['formToken']['add_new_category'])) {
    $name = Filter::filterInput(INPUT_POST, 'new_expense_category_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $amount = Filter::filterInput(INPUT_POST, 'new_expense_category_budget', FILTER_SANITIZE_NUMBER_FLOAT);
    $isDefault = Filter::filterInput(INPUT_POST, 'is_default', FILTER_VALIDATE_BOOLEAN, false);

    if (!$name || !$amount) {
        $_SESSION['errors'][] = 'Please enter a name and amount';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    $catId = $expenseCategory->addNew($name, (float) $amount, $isDefault);

    if ($expenseCategory->defaultChanged) {
        $_SESSION['success'][] = 'Default category changed to category ' . $name;
    }
    if ($catId) {
        $_SESSION['success'][] = 'Successfully added a new category with ID ' . $catId;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

    $_SESSION['errors'][] = 'Failed to add new category';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}
$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);


