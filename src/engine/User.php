<?php

declare(strict_types = 1);

use database\PearDatabase;
use Permissions\PermissionsManager;
use Permissions\Role;

/**
 *
 */
class User
{
    public int $id;
    /**
     * @var mixed
     */
    protected $entityTable;
    /**
     * @var mixed
     */
    protected $roleTable;
    /**
     * @var \database\PearDatabase
     */
    protected PearDatabase $adb;

    /**
     * @param  int  $id
     */
    public function __construct(int $id = 0) {
        if ($id) {
            $this->id = $id;
            $tables = PearDatabase::getTablesConfig();
            $this->entityTable = $tables['users_table_name'];
            $this->roleTable = $tables['user_to_role_table_name'];
            $this->adb = PearDatabase::getInstance();
        }
    }

    public static function getActiveAdminUser(): User
    {
        $adb = PearDatabase::getInstance();
        $tables = PearDatabase::getTablesConfig();
        $query = "SELECT * FROM
             `{$tables['users_table_name']}`
                 LEFT JOIN `{$tables['user_to_role_table_name']}` ON
                     `{$tables['users_table_name']}`.user_id = `{$tables['user_to_role_table_name']}`.user_id
                LEFT JOIN `{$tables['roles_table_name']}` ON
                `{$tables['roles_table_name']}`.role_id = `{$tables['user_to_role_table_name']}`.role_id
                WHERE `{$tables['roles_table_name']}`.role_id = ? LIMIT 1";
        $exists = $adb->pquery($query, [Role::getRoleIdByName('administrator')]);

        if (!$exists || !$adb->num_rows($exists)) {
            throw new Exception('No active admin user');
        }
        $user = new User($adb->query_result($exists, 0, 'user_id'));
        $user->retrieveUserInfoFromFile();
        return $user;
    }

    public function retrieveUserInfoFromFile()
    {
        $data = PermissionsManager::getUserPrivileges($this->id);
        var_dump($data);
    }

    /**
     * @param  string  $password
     * @return string|null
     */
    public function encryptPassword (string $password): ?string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param $password
     *
     * @return bool
     */
    public function verifyPassword ($password): bool
    {
        $query = "SELECT user_name, password FROM $this->entityTable WHERE id=?";
        $result = $this->adb->pquery($query, [$this->id]);
        $row = $this->adb->fetchByAssoc($result);
        return password_verify($password, $row['password']);
    }

}
