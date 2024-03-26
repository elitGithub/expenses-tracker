<?php

declare(strict_types = 1);

use database\PearDatabase;

/**
 *
 */
class User
{

    public static function getActiveAdminUser()
    {
        $adb = PearDatabase::getInstance();
        $tables = PearDatabase::getTablesConfig();
        $query = "SELECT * FROM
             `{$tables['users_table_name']}`
                 LEFT JOIN `{$tables['user_to_role_table_name']}` ON
                     `{$tables['users_table_name']}`.user_id = `{$tables['user_to_role_table_name']}`.user_id
                LEFT JOIN `{$tables['roles_table_name']}` ON
                `{$tables['roles_table_name']}`.role_id = `{$tables['user_to_role_table_name']}`.role_id
                WHERE `{$tables['roles_table_name']}`.role_name = ? LIMIT 1";
        $exists = $adb->pquery($query, ['administrator']);
        $user = new User();
    }

}
