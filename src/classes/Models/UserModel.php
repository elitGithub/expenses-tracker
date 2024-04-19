<?php

declare(strict_types = 1);

namespace Models;

use database\PearDatabase;
use Permissions\CacheSystemManager;
use Permissions\PermissionsManager;
use engine\User;

/**
 * User Model for storage
 */
class UserModel
{
    protected string       $entityTable;
    protected string       $userToRoleTable;
    protected ?PearDatabase $adb = null;


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
     * @param  string  $password
     * @param  string  $firstName
     * @param  string  $lastName
     * @param          $createdBy
     * @param  int     $roleId
     * @param  string  $isAdmin
     *
     * @return bool|int
     * @throws \Throwable
     */
    public function createNew(string $email, string $userName, string $password, string $firstName, string $lastName, $createdBy, int $roleId, string $isAdmin)
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
                     `is_admin`,
                     `last_update_at`,
                     `created_at`)
                                 VALUES (?, ?, ?, ?, ?, ?, 1, ?, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());";
            $this->adb->pquery($query, [$email, $userName, $firstName, $lastName, $user->encryptPassword($password), $createdBy, ucfirst($isAdmin)]);
            $id = $this->adb->getLastInsertID();
            $this->adb->pquery("INSERT INTO `$this->userToRoleTable` (`user_id`, `role_id`) VALUES (?, ?)", [$id, $roleId]);
            if ($id) {
                CacheSystemManager::writeUser($id,
                                              ['userName' => $userName,
                                               'name' => $firstName . ' ' . $lastName,
                                               'active' => 1,
                                               'role' => $roleId,
                                               'is_admin' => $isAdmin,
                                              ]);
            }
            return $id;
        }

        $_SESSION['errors'][] = 'User already exists.';
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

    public function deleteUser(int $userId)
    {
        $query = "UPDATE
                      `$this->entityTable`
                    SET
                        `active` = '0',
                        `deleted_at` = CURRENT_TIMESTAMP(),
                        `email` = CONCAT(`email`, '_', `user_id`, 'DELETED_USER'),
                        `user_name` = CONCAT(`user_name`, '_', `user_id`, 'DELETED_USER')
                    WHERE `user_id` = ?;";
        $result = $this->adb->preparedQuery($query, [$userId]);
        if ($result && $this->adb->getAffectedRowCount($result)) {
            $this->adb->pquery("DELETE FROM `$this->userToRoleTable` WHERE `user_id` = ?", [$userId]);
            return true;
        }

        return false;
    }

    /**
     * @param  \engine\User  $user
     * @param  int           $roleId
     * @param  string        $email
     * @param  string        $firstName
     * @param  string        $lastName
     * @param                $active
     * @param  string        $isAdmin
     *
     * @return bool
     * @throws \Throwable
     */
    public function updateUser(User $user, int $roleId, string $email, string $firstName, string $lastName, $active, string $isAdmin): bool
    {
        $query = "UPDATE
                      `$this->entityTable`
                  SET
                      `email` = ?,
                      `first_name` = ?,
                      `last_name` = ?,
                      `active` = ?,
                      `is_admin` = ?,
                      `last_update_at` = CURRENT_TIMESTAMP()
                  WHERE `user_id` = ?; ";

        $result = $this->adb->pquery($query, [$email, $firstName, $lastName, $active, $isAdmin, $user->id]);
        $userChanges = $this->adb->getAffectedRowCount($result);
        if (!$result) {
            return false;
        }

        $roleQuery = "UPDATE `$this->userToRoleTable` SET `role_id` = ? WHERE `user_id` = ?;";
        $result = $this->adb->pquery($roleQuery, [$roleId, $user->id]);
        if (!$result) {
            return false;
        }

        CacheSystemManager::refreshUserInCache($user);
        PermissionsManager::refreshPermissionsInCache();
        $user->refreshUserInSession();

        return $userChanges > 0;
    }

}
