<?php

declare(strict_types = 1);

namespace Setup;

require_once EXTR_ROOT_DIR . '/system/config.php';


/**
 *
 */
class Setup
{
    protected string $primaryConfigFile;
    private string   $rootDir;

    /**
     * Setup constructor.
     */
    public function __construct()
    {
        $this->primaryConfigFile = EXTR_ROOT_DIR . '/system/config.php';
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
     * @param  string[]  $dirs
     *
     * @return string[]
     */
    public function checkDirs(array $dirs): array
    {
        $failedDirs = [];

        foreach ($dirs as $dir) {
            if (false === is_dir($this->rootDir . $dir)) {
                // If the folder does not exist, try to create it
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
}
