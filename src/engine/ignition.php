<?php

declare(strict_types = 1);


require_once './config/config.php';
spl_autoload_register(function ($className) {
    $baseDir = EXTR_SRC_DIR; // Adjust the base directory as needed

    // Normalize class name for namespace and directory separator
    $className = ltrim($className, '\\');

    // Generate the classes map using RecursiveDirectoryIterator
    $classesMap = scanDirectoryForClassesUsingIterator($baseDir);

    if ($path = getClassPathUsingIterator($className, $classesMap)) {
        require_once $path;
    }
});

function scanDirectoryForClassesUsingIterator($dir): array
{
    $directory = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);
    $classesMap = [];

    foreach ($iterator as $file) {
        if ($file->isFile() && preg_match('/\.(php|class\.php)$/', $file->getFilename())) {
            $path = $file->getRealPath();
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $classesMap[$className] = $path;
        }
    }

    return $classesMap;
}

function getClassPathUsingIterator($className, $classesMap) {
    // Split class name into parts to handle namespace if needed
    $classNameParts = explode('\\', $className);
    $simpleClassName = end($classNameParts);

    if (isset($classesMap[$simpleClassName])) {
        return $classesMap[$simpleClassName];
    }

    return null;
}

