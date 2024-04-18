<?php

declare(strict_types = 1);

namespace engine;

use database\PearDatabase;
use Permissions\CacheSystemManager;
use Permissions\PermissionsManager;
use Permissions\Role;
use Session\SessionWrapper;
use Throwable;

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
    protected ?PearDatabase $adb = null;
    protected array        $permissions = [];
    protected $tables = null;

    /**
     * @param  int  $id
     */
    public function __construct(int $id = 0)
    {
        if ($id) {
            $this->id = $id;
        }

        $this->session = new SessionWrapper();
    }

    /**
     * @return \database\PearDatabase|null
     */
    public function database(): ?PearDatabase
    {
        if (is_null($this->adb)) {
            $this->adb = PearDatabase::getInstance();
        }

        return $this->adb;
    }

    /**
     * @return void
     */
    public function tables()
    {
        $this->tables = $this->database()->getTablesConfig();
        $this->entityTable = $this->tables['users_table_name'];
        $this->roleTable = $this->tables['user_to_role_table_name'];
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
     * @throws \Throwable
     */
    public function login($userName, $password): bool
    {
        global $default_language;
        $this->tables();
        $query = "SELECT * FROM `$this->entityTable` WHERE CAST(`user_name` AS BINARY) = ?";
        $result = $this->database()->requirePsSingleResult($query, [$userName]);

        if (!$result) {
            return false;
        }

        $row = $this->database()->fetchByAssoc($result);

        if ((bool) $row['active'] !== true) {
            return false;
        }

        if (!password_verify($password, $row['password'])) {
            return false;
        }

        $this->initFromRow($row);

        $this->roleid = Role::getRoleByUserId($this->id);
        CacheSystemManager::refreshUserInCache($this);
        PermissionsManager::refreshPermissionsInCache();
        $this->retrieveUserInfoFromFile();

        $this->session->sessionAddKey('authenticated_user_language', $default_language);
        $this->session->sessionAddKey('username', $userName);
        $this->session->sessionAddKey('authenticated_user_name', $userName);
        $this->session->sessionAddKey('loggedin', true);
        $this->session->sessionAddKey('last_login', date('Y-m-d H:i:s'));
        $this->session->sessionAddKey('ua', $_SERVER['HTTP_USER_AGENT']);
        $this->session->sessionAddKey('is_logged_in', true);

        $this->refreshUserInSession();

        $this->updateLastLogin();
        return true;
    }

    /**
     * @return void
     */
    public function refreshUserInSession()
    {
        $this->session->sessionAddKey('authenticated_user_id', $this->id);
        $this->session->sessionAddKey('authenticated_user_data', [
            'userName'   => $this->user_name,
            'user_id'    => $this->id,
            'name'       => $this->first_name . ' ' . $this->last_name,
            'email'      => $this->email,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'active'     => $this->active,
            'role'       => $this->roleid,
            'is_admin'   => $this->is_admin ?? 'Off',
        ]);
        $this->session->sessionAddKey('username', $this->user_name);
        $this->session->sessionAddKey('password', $this->password);
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
                $this->session->sessionAddKey($propertyName, $propertyValue);
            }
            foreach ($userData['permissions'] as $actionId => $isEnabled) {
                $this->permissions[$actionId] = $isEnabled;
            }
        } catch (Throwable $exception) {
            if ($ajax) {
                die(json_encode(['success' => false, 'message' => $exception->getMessage()]));
            }
            var_dump($exception);
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
     * @param  int|null  $id
     *
     * @return \engine\User|null
     * @throws \Exception
     */
    public static function getUserById(?int $id = null): ?User
    {
        if (is_null($id)) {
            return null;
        }

        $instance = new self($id);
        $instance->tables();
        $query = "SELECT * FROM `$instance->entityTable` WHERE user_id = ?";
        $result = $instance->database()->pquery($query, [$id]);
        if (!$result || $instance->database()->num_rows($result) === 0) {
            return null;
        }

        $row = $instance->database()->fetchByAssoc($result);
        $row['roleid'] = Role::getRoleByUserId($id);
        $instance->initFromRow($row);
        return $instance;
    }

    /**
     * @param  string  $password
     * @param  string  $confirmPassword
     *
     * @return bool
     */
    public function changePassword(string $password, string $confirmPassword): bool
    {
        if ($password !== $confirmPassword) {
            $_SESSION['errors'][] = 'Please make sure you typed password and confirm password';
            return false;
        }
        $this->tables();
        $query = "UPDATE `$this->entityTable` SET `password` = ? WHERE `user_id` = ?";
        $result = $this->database()->pquery($query, [$this->encryptPassword($password), $this->id]);
        if (!$result) {
            return false;
        }

        return $this->database()->getAffectedRowCount($result) > 0;
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
        $this->database()->pquery("UPDATE `{$this->entityTable}` SET `last_login` = CONVERT_TZ(NOW(), 'SYSTEM','+00:00')  WHERE `user_id` = ?;", [$this->id]);
    }


}
