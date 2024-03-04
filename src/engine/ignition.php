<?php

declare(strict_types = 1);



require_once '../config.php';
spl_autoload_register(function ($className) {
    $baseDir = EXTR_SRC_DIR . '/classes/'; // Adjust the base directory as needed

    // Normalize class name for namespace and directory separator
    $className = ltrim($className, '\\');
    $fileName = '';
    $namespace = '';

    // Handle both namespaced and non-namespaced classes
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    // Convert PEAR-like class names to directory paths and handle both .php and .class.php extensions
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className);

    // Attempt to load the file with standard .php extension
    $finalPath = $baseDir . $fileName . '.php';
    if (file_exists($finalPath)) {
        require_once $finalPath;
        return;
    }

    // Attempt to load the file with .class.php extension
    $finalPathClass = $baseDir . $fileName . '.class.php';
    if (file_exists($finalPathClass)) {
        require_once $finalPathClass;
    }
});

// Helper function to check if a string ends with a specific substring
function endsWith($haystack, $needle): bool
{
    $length = strlen($needle);
    if (!$length) {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}