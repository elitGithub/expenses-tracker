<?php

declare(strict_types = 1);


use ExpenseTracker\ExpenseCategory;
use ExpenseTracker\ExpenseCategoryList;
use Permissions\PermissionsManager;

$expenseCatList = new ExpenseCategoryList();
$expenseCategory = new ExpenseCategory();

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    PermissionsManager::isPermittedAction('delete_expense_category', $current_user) &&
    password_verify($_POST['formToken'], $_SESSION['formToken']['delete_category'])) {
    $category = new ExpenseCategory();
    $categoryId = Filter::filterInput(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    if (empty($categoryId)) {
        $_SESSION['errors'][] = 'Missing category id.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    $category->getById((int)$categoryId);

    try {
        $result = $category->delete();
    } catch (Exception $e) {
        $_SESSION['errors'][] = $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }



    if ($result) {
        $_SESSION['success'][] = 'Category deleted.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    $_SESSION['errors'][] = 'Could not delete category';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    return;
}

$_SESSION['errors'][] = 'Wrong request format.';
header('Location: ' . $_SERVER['HTTP_REFERER']);
