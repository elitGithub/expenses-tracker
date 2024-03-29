<?php

declare(strict_types = 1);

use database\PearDatabase;
use Permissions\Permissions;
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
    public function __construct(int $id = 0)
    {
        if ($id) {
            $this->id = $id;
        }
        $this->adb = PearDatabase::getInstance();
        $tables = $this->adb->getTablesConfig();
        $this->entityTable = $tables['users_table_name'];
        $this->roleTable = $tables['user_to_role_table_name'];
    }

    /**
     * @param $name
     * @param $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    public static function getActiveAdminUser(): User
    {
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
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

    public function login($userName, $password)
    {
        // TODO: add log!
        // TODO: redirect to logout page.
        $query = "SELECT * FROM `$this->entityTable` WHERE CAST(`user_name` AS BINARY) = ?";
        $result = $this->adb->requirePsSingleResult($query, [$userName]);

        if (!$result) {
            return false;
        }


        $row = $this->adb->fetchByAssoc($result);

        if ((bool) $row['active'] !== true) {
            /**
             * require_once('modules/Users/Logout.php');
             * die('Privileges not found.');
             */
            return false;
        }

        if (!password_verify($password, $row['password'])) {
            return false;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->id = $row['user_id'];
        $this->retrieveUserInfoFromFile();
        $_SESSION['user'] = $userName;
        return true;
    }

    /**
     * @return void
     */
    public function retrieveUserInfoFromFile()
    {
        try {
            $userData['user_data'] = PermissionsManager::getUserPrivileges($this->id);
            foreach ($userData as $propertyName => $propertyValue) {
                $this->$propertyName = $propertyValue;
            }

        } catch (Throwable $exception) {
            // TODO: add log
            /**
             *  require_once('modules/Users/Logout.php');
             *
             * /
             */
            die('Privileges not found.');
        }
    }

    /**
     * @param  string  $password
     *
     * @return string|null
     */
    public function encryptPassword(string $password): ?string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

}
