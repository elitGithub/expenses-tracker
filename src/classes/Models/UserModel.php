<?php

declare(strict_types = 1);

namespace Models;

use database\PearDatabase;
use Permissions\CacheSystemManager;
use User;

/**
 * User Model for storage
 */
class UserModel
{
    protected string       $entityTable;
    protected string       $userToRoleTable;
    protected PearDatabase $adb;

    public function __construct()
    {
        $this->adb = PearDatabase::getInstance();
        $tables = $this->adb->getTablesConfig();
        $this->entityTable = $tables['users_table_name'];
        $this->userToRoleTable = $tables['user_to_role_table_name'];
    }

    /**
     * @param  string  $email
     * @param  string  $userName
     *
     * @return bool
     * @throws \Exception
     */
    public function existsByEmailOrUserName(string $email, string $userName): bool
    {
        return !$this->checkUniqueEmail($email) || !$this->checkUniqueUserName($userName);
    }

    /**
     * @param  string  $email
     * @param  string  $userName
     * @param  string  $password
     * @param  string  $firstName
     * @param  string  $lastName
     * @param          $createdBy
     * @param  int     $roleId
     *
     * @return bool|int
     * @throws \Throwable
     */
    public function createNew(string $email, string $userName, string $password, string $firstName, string $lastName, $createdBy, int $roleId)
    {
        if ($this->checkUniqueEmail($email) && $this->checkUniqueUserName($userName)) {
            $user = new User();
            $query = "INSERT INTO `$this->entityTable` (
                     `email`, 
                     `user_name`, 
                     `first_name`, 
                     `last_name`, 
                     `password`, 
                     `created_by`,
                     `active`,
                     `last_update_at`,
                     `created_at`)
                                 VALUES (?, ?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());";
            $this->adb->pquery($query, [$email, $userName, $firstName, $lastName, $user->encryptPassword($password), $createdBy]);
            $id = $this->adb->getLastInsertID();
            $this->adb->pquery("INSERT INTO `$this->userToRoleTable` (`user_id`, `role_id`) VALUES (?, ?)", [$id, $roleId]);
            if ($id) {
                CacheSystemManager::writeUser($id,
                                              ['userName' => $userName,
                                               'name' => $firstName . ' ' . $lastName,
                                               'active' => 1,
                                               'role' => $roleId
                                              ]);
            }
            return $id;
        }

        return false;
    }

    /**
     * @param  string  $email
     * @param  string  $userName
     *
     * @return array|null
     */
    public function getByEmailAndUserName(string $email, string $userName): ?array
    {
        $query = "SELECT *
                     FROM `$this->entityTable`  
                     WHERE `email` = ? AND `user_name` = ? AND `active` = 1;";
        $res = $this->adb->preparedQuery($query, [$email, $userName]);
        return $this->adb->fetchByAssoc($res);
    }

    /**
     * @param  string  $email
     *
     * @return bool
     * @throws \Exception
     */
    public function checkUniqueEmail(string $email): bool
    {
        $result = $this->adb->pquery("SELECT COUNT(*) AS `total` FROM `$this->entityTable` WHERE 'email' = CAST(? AS BINARY) AND `deleted_at` IS NULL;",
                                     [$email]);
        return ($this->adb->query_result($result, 0, 'total') < 1);
    }

    /**
     * @param  string  $userName
     *
     * @return bool
     * @throws \Exception
     */
    public function checkUniqueUserName(string $userName): bool
    {
        $result = $this->adb->pquery("SELECT COUNT(*) AS `total` FROM `$this->entityTable` WHERE `user_name` = CAST(? AS BINARY) AND `deleted_at` IS NULL;",
                                     [$userName]);
        return ($this->adb->query_result($result, 0, 'total') < 1);
    }

}
