<?php

declare(strict_types = 1);

namespace Models;

use database\PearDatabase;

class UserModel
{
    protected string $entityTable;
    protected string $roleTable;
    protected PearDatabase $adb;

    public function __construct() {
        global $dbConfig;
        $this->entityTable = $dbConfig['tables']['users_table_name'];
        $this->roleTable = $dbConfig['tables']['user_to_role_table_name'];
        $this->adb = PearDatabase::getInstance();
    }

    /**
     * @param  string  $email
     * @param  string  $userName
     * @param  string  $firstName
     * @param  string  $lastName
     * @param          $createdBy
     * @param  int     $roleId
     *
     * @return bool
     * @throws \Exception
     */
    public function createNew(string $email, string $userName, string $firstName, string $lastName, $createdBy, int $roleId): bool
    {
        if ($this->checkUniqueEmail($email) && $this->checkUniqueUserName($userName)) {
            $query = "INSERT INTO $this->entityTable (`email`, `user_name`, `first_name`, `last_name`, `created_by`, `active`, `last_update_at`, `created_at`)
                                 VALUES (?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());";
            $this->adb->pquery($query, [$email, $userName, $firstName, $lastName, $createdBy]);
            $id = $this->adb->getLastInsertID();
            $this->adb->pquery("INSERT INTO $this->roleTable (`user_id`, `role_id`) VALUES (?, ?)", [$id, $roleId]);
            return true;
        }

        return false;
    }

    /**
     * @param  string  $email
     *
     * @return bool
     * @throws \Exception
     */
    public function checkUniqueEmail(string $email): bool
    {
        $result = $this->adb->pquery("SELECT COUNT(*) AS total FROM $this->entityTable WHERE 'email' = CAST(? AS BINARY) AND `deleted_at` IS NULL;", [$email]);
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
        $result = $this->adb->pquery("SELECT COUNT(*) AS total FROM $this->entityTable WHERE user_name = CAST(? AS BINARY) AND `deleted_at` IS NULL;", [$userName]);
        return ($this->adb->query_result($result, 0, 'total') < 1);
    }

}
