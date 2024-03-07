<?php

declare(strict_types = 1);

namespace Setup;

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
     * Creates the file /config/database.php.
     *
     * @param  int[]|string[] $data   Array with database credentials
     * @param  string         $folder Folder
     */
    public function createDatabaseFile(array $data, string $folder = '/config')
    {
        return file_put_contents(
            $this->rootDir . $folder . '/database.php',
            "<?php\n" .
            "\$DB['server'] = '" . $data['dbServer'] . "';\n" .
            "\$DB['port'] = '" . $data['dbPort'] . "';\n" .
            "\$DB['user'] = '" . $data['dbUser'] . "';\n" .
            "\$DB['password'] = '" . $data['dbPassword'] . "';\n" .
            "\$DB['db'] = '" . $data['dbDatabaseName'] . "';\n" .
            "\$DB['prefix'] = '" . $data['dbPrefix'] . "';\n" .
            "\$DB['type'] = '" . $data['dbType'] . "';",
            LOCK_EX
        );
    }

}
