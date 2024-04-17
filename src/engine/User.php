<?php

declare(strict_types = 1);

use database\PearDatabase;
use Permissions\CacheSystemManager;
use Permissions\PermissionsManager;
use Permissions\Role;
use Session\SessionWrapper;

/**
 *
 */
class User
{
    public ?int           $id = null;
    public SessionWrapper $session;
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
    protected array        $permissions = [];

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
        $this->session = new SessionWrapper();
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

    /**
     * @param $row
     *
     * @return void
     */
    public function initFromRow($row)
    {
        $this->id = (int) $row['user_id'];
        foreach ($row as $key => $userInfo) {
            $this->$key = $userInfo;
        }
    }

    /**
     * @param $userName
     * @param $password
     *
     * @return bool
     */
    public function login($userName, $password): bool
    {
        global $default_language;
        $query = "SELECT * FROM `$this->entityTable` WHERE CAST(`user_name` AS BINARY) = ?";
        $result = $this->adb->requirePsSingleResult($query, [$userName]);

        if (!$result) {
            return false;
        }

        $row = $this->adb->fetchByAssoc($result);

        if ((bool) $row['active'] !== true) {
            return false;
        }

        if (!password_verify($password, $row['password'])) {
            return false;
        }

        $this->initFromRow($row);

        $this->roleid = Role::getRoleByUserId($this->id);
        CacheSystemManager::refreshUserInCache($this);
        $this->retrieveUserInfoFromFile();

        $this->session->sessionAddKey('authenticated_user_language', $default_language);
        $this->session->sessionAddKey('authenticated_user_id', $this->id);
        $this->session->sessionAddKey('username', $userName);
        $this->session->sessionAddKey('authenticated_user_name', $userName);
        $this->session->sessionAddKey('loggedin', true);
        $this->session->sessionAddKey('ua', $_SERVER['HTTP_USER_AGENT']);
        $this->session->sessionAddKey('is_logged_in', true);
        $this->session->sessionAddKey('username', $this->user_name);
        $this->session->sessionAddKey('password', $this->password);
        $this->updateLastLogin();
        return true;
    }

    /**
     * @return false|mixed
     */
    public function isLoggedIn()
    {
        if ($this->session->sessionHasKey('loggedin') && $this->session->sessionReadKey('loggedin') &&
            $this->session->sessionHasKey('ua') && $this->session->sessionReadKey('ua') === $_SERVER['HTTP_USER_AGENT']
        ) {
            return $this->session->sessionReadKey('username');
        }

        return false;
    }


    /**
     * @return void
     */
    public function retrieveUserInfoFromFile($ajax = false)
    {
        if (!$this->id && $this->session->sessionHasKey('authenticated_user_id')) {
            $this->id = $this->session->sessionReadKey('authenticated_user_id');
        }
        try {
            $userData = PermissionsManager::getUserPrivileges($this->id);
            foreach ($userData['user_data'] as $propertyName => $propertyValue) {
                $this->$propertyName = $propertyValue;
            }
            foreach ($userData['permissions'] as $actionId => $isEnabled) {
                $this->permissions[$actionId] = $isEnabled;
            }
        } catch (Throwable $exception) {
            if ($ajax) {
                die(json_encode(['success' => false, 'message' => $exception->getMessage()]));
            }
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


    /**
     * @param  int  $userId
     *
     * @return array - Data related to last_login
     */
    public static function getLastLoginData(int $userId): array
    {
        $adb = PearDatabase::getInstance();
        if (!$userId || !is_numeric($userId)) {
            return [];
        }
        $tables = $adb->getTablesConfig();
        $sql = "SELECT `last_login`, `user_name`, `email` FROM `{$tables['users_table_name']}` WHERE `user_id` = ?";
        $res = $adb->pquery($sql, [$userId]);
        return $adb->num_rows($res) ? $adb->fetchByAssoc($res) : [];
    }

    /**
     * @return void
     */
    private function updateLastLogin()
    {
        $this->adb->pquery("UPDATE `{$this->entityTable}` SET `last_login` = CONVERT_TZ(NOW(), 'SYSTEM','+00:00')  WHERE `user_id` = ?;", [$this->id]);
    }


}
