<?php

declare(strict_types = 1);

return $systemSettings = [
    'with_names'    => [
        'expense_category_table_name' => 'expense_category',
        'expenses_table_name'         => 'expenses',
        'users_table_name'            => 'users',
        'history_table_name'          => 'history',
        'actions_table_name'          => 'actions',
        'roles_table_name'            => 'roles',
        'user_to_role_table_name'     => 'user_to_role',
        'role_permissions_table_name' => 'role_permissions',
    ],
    'without_names' => [
        'expense_category_table_name' => '',
        'expenses_table_name'         => '',
        'users_table_name'            => '',
        'history_table_name'          => '',
        'actions_table_name'          => '',
        'roles_table_name'            => '',
        'role_permissions_table_name' => '',
        'user_to_role_table_name'     => '',
    ],
];

