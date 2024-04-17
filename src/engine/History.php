<?php

declare(strict_types = 1);

namespace engine;

use database\PearDatabase;

/**
 *
 */
class History
{
    /**
     * @param  string  $entityType
     * @param  int     $entityId
     * @param          $action
     * @param          $who
     * @param          $changeData
     *
     * @return void
     */
    public static function logTrack(string $entityType, int $entityId, $action, $who, $changeData)
    {
        $adb = PearDatabase::getInstance();
        $tables = $adb->getTablesConfig();
        $query = "INSERT INTO `{$tables['history_table_name']}` 
                              (`entity_type`, `entity_id`, `action`, `who`, `change_data`)
                              VALUES (?, ?, ?, ?, ?)";
        $adb->pquery($query, [$entityType, $entityId, $action, $who, $changeData]);

    }

}
