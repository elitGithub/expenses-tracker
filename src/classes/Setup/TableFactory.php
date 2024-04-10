<?php

declare(strict_types = 1);

namespace Setup;

/**
 *
 */
class TableFactory
{
    private array $queries        = [];
    private array $systemSettings = [];

    /**
     * @var string
     */
    private string $sqlTemplate = '';

    /**
     * @param  array   $systemSettings  Reference to the system settings array
     * @param  string  $prefix  Prefix for table names
     */
    public function __construct(array &$systemSettings, string $prefix = '')
    {
        $this->sqlTemplate = file_get_contents(EXTR_ROOT_DIR . '/db_script/table_creation_script.sql');
        $this->systemSettings =& $systemSettings;
        $this->generateQueries($prefix);
    }

    /**
     * @param $prefix
     *
     * @return void
     */
    private function generateQueries($prefix)
    {
        $sqlFormatted = sprintf($this->sqlTemplate, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix, $prefix,
                                $prefix, $prefix, $prefix);
        $sqlNormalized = str_replace("\r\n", "\n", $sqlFormatted); // Normalize newline characters

        $this->queries = explode(";\n", $sqlNormalized);
        $this->queries = array_filter($this->queries, fn($value) => !empty($value));

        $this->extractTableNames($prefix); // Extract and store table names and update settings
    }

    /**
     * @param  string  $prefix
     *
     * @return void
     */
    private function extractTableNames(string $prefix): void
    {
        global $systemSettings;
        require_once EXTR_ROOT_DIR . '/db_script/tableSettings.php';
        $withNames = $systemSettings['with_names'];
        foreach ($this->queries as $query) {
            if (preg_match('/CREATE TABLE IF NOT EXISTS `(' . $prefix . '[a-zA-Z_]+)`/', $query, $matches)) {
                // Determine the corresponding setting key based on the table name
                $tableNameWithoutPrefix = str_replace($prefix, '', $matches[1]);
                $settingKey = array_search($tableNameWithoutPrefix, $withNames, true);

                if ($settingKey !== false) {
                    // Update the system settings with the full table name
                    $this->systemSettings[$settingKey] = $matches[1];
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

}
