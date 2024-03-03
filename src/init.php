<?php

function classesAutoloader($className)
{
    $path = 'classes' . DIRECTORY_SEPARATOR;
    $extension = '.php';

    // Replace the namespace separator with directory separator
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);

    // The full path to the class file
    $file = $path . $className . $extension;

    // Check if the file exists in the root of the classes directory
    if (file_exists($file)) {
        require_once $file;
    } else {
        // If not found, search in subdirectories
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                continue;
            }

            $filePath = $item->getRealPath();
            $fixedFilePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

            // Check if the file path ends with the class file path
            if (endsWith($fixedFilePath, $className . $extension)) {
                require_once $filePath;
                break;
            }
        }
    }
}

// Helper function to check if a string ends with a specific substring
function endsWith($haystack, $needle): bool
{
    $length = strlen($needle);
    if (!$length) {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}

// Register the autoloader
spl_autoload_register('classesAutoloader');
