<?php

declare(strict_types = 1);

namespace Setup;

/**
 *
 */
class Setup
{
    private string $rootDir;

    /**
     * Setup constructor.
     */
    public function __construct()
    {
        $this->setRootDir(EXTR_ROOT_DIR);
    }

    /**
     * Sets the root directory of the Expense Tracker instance.
     */
    public function setRootDir(string $rootDir): void
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Checks basic folders and creates them if necessary.
     *
     * @param  string[] $dirs
     * @return string[]
     */
    public function checkDirs(array $dirs): array
    {
        $failedDirs = [];

        foreach ($dirs as $dir) {
            if (false === is_dir($this->rootDir . $dir)) {
                // If the folder does not exist try to create it
                if (false === mkdir($this->rootDir . $dir)) {
                    // If the folder creation fails
                    $failedDirs[] = 'Folder [' . $this->rootDir . $dir . '] could not be created.';
                } elseif (false === chmod($this->rootDir . $dir, 0775)) {
                    $failedDirs[] = 'Folder [' . $this->rootDir . $dir . '] could not be given correct permissions (775).';
                }
                // The folder exists, check permissions
            } elseif (false === is_writable($this->rootDir . $dir)) {
                // If the folder exists but is not writeable
                $failedDirs[] = 'Folder [' . $this->rootDir . $dir . '] exists but is not writable.';
            }

            if (0 === count($failedDirs)) {
                // if no failed dirs exist
                copy(
                    $this->rootDir . '/install/index.html',
                    $this->rootDir . $dir . '/index.html'
                );
            }
        }

        return $failedDirs;
    }

    /**
     * @param  array  $dbConfig
     * @param  array  $permissionsConfig
     * @param  array  $redisConfig
     * @param  array  $memcachedConfig
     *
     * @return void
     */
    public function createConfigFiles(array $dbConfig = [], array $permissionsConfig = [], array $redisConfig = [], array $memcachedConfig = [])
    {
        $primaryConfigFile = EXTR_ROOT_DIR . '/config/config.php';
        require_once $primaryConfigFile;

        $dbConfigFile = EXTR_ROOT_DIR . '/config/database.php';
        $userManagementFile = EXTR_ROOT_DIR . '/config/cache_config.php';
        $dbConfigData = '<?php 
                              ' . var_export($dbConfig, true) . ';';

        file_put_contents($dbConfigFile, $dbConfigData);
        file_put_contents($primaryConfigFile, "require_once('$dbConfigFile');\n", FILE_APPEND);

        if (count($redisConfig)) {
            $redisConfigData = '<?php 
                                      ' . var_export($redisConfig, true) . ';';
            file_put_contents($userManagementFile, $redisConfigData);
        }

        if (count($memcachedConfig)) {
            $memcachedConfigData = '<?php ' . var_export($memcachedConfig, true) . ';';
            file_put_contents($userManagementFile, $memcachedConfigData);
        }

        file_put_contents($primaryConfigFile, "require_once('$userManagementFile');\n", FILE_APPEND);
    }

    public function createPermissionsFile(array $permissions)
    {

    }
}
