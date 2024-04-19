<?php

declare(strict_types = 1);

use ExpenseTracker\ExpenseCategory;
use Permissions\PermissionsManager;


if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    PermissionsManager::isPermittedAction('edit_expense_category', $current_user) &&
    password_verify($_POST['formToken'], $_SESSION['formToken']['edit_category'])) {
    $category = new ExpenseCategory();
    $categoryId = Filter::filterInput(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    settype($categoryId, 'integer');
    if (empty($categoryId)) {
        $_SESSION['errors'][] = 'Missing category id.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    $category->getById((int)$categoryId);
    $name = Filter::filterInput(INPUT_POST, 'expense_category_name', FILTER_SANITIZE_SPECIAL_CHARS, '');
    $budget = Filter::filterInput(INPUT_POST, 'expense_category_budget', FILTER_SANITIZE_NUMBER_FLOAT, 0.00);
    $isDefault = Filter::filterInput(INPUT_POST, 'is_default', FILTER_VALIDATE_BOOLEAN, false);

    $category->expense_category_name = trim($name);
    $category->amount = (float)$budget;
    $category->is_default = $isDefault;
    $result = $category->update();

    if ($category->defaultChanged) {
        $_SESSION['success'][] = 'Default category changed to category ' . $name;
    }
    if ($result > 0) {
        $_SESSION['success'][] = 'Successfully updated the category with ID ' . $categoryId;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    $_SESSION['errors'][] = 'Category not updated.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);

