<?php

declare(strict_types = 1);

$rootPath = realpath(dirname(__FILE__, 3)); // Adjust the path as needed
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', $rootPath);
}

define('EXTR_ROOT_DIR', dirname(__FILE__, 3));

const EXTR_SRC_DIR = EXTR_ROOT_DIR . '/src';

require_once EXTR_ROOT_DIR . '/system/config.php';

require_once EXTR_SRC_DIR . '/engine/functions.php';

const USER_AVATARS_UPLOAD_DIR = EXTR_ROOT_DIR . '/assets/public/userImages';
const SITE_IMAGES_UPLOAD_DIR = EXTR_ROOT_DIR . '/assets/public/images/';
const USER_AVATARS_FILE_URL = 'assets/public/userImages/';

const ENVIRONMENT = 'production';

const ALLOWED_MIME_TYPES = [
    'jpg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
];

// Debug mode:
// - false debug mode disabled
// - true  debug mode enabled
const DEBUG = false;
if (DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(-1);
} else {
    error_reporting(0);
}

// Fix the PHP include path if the system is running under a "strange" PHP configuration
//
$foundCurrPath = false;
$includePaths = explode(PATH_SEPARATOR, ini_get('include_path'));
$i = 0;
while ((!$foundCurrPath) && ($i < count($includePaths))) {
    if ('.' == $includePaths[$i]) {
        $foundCurrPath = true;
    }
    ++$i;
}
if (!$foundCurrPath) {
    ini_set('include_path', '.' . PATH_SEPARATOR . ini_get('include_path'));
}


// Tweak some PHP configuration values
// Warning: be sure the server has enough memory and stack for PHP
ini_set('pcre.backtrack_limit', '100000000');
ini_set('pcre.recursion_limit', '100000000');


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

/**
 * @param $dir
 *
 * @return array
 */
function scanDirectoryForClassesUsingIterator($dir): array
{
    $directory = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);
    $classesMap = [];

    foreach ($iterator as $file) {
        if ($file->isFile() && preg_match('/\.(php|class\.php)$/', $file->getFilename())) {
            $path = $file->getRealPath();
            $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            if (isset($classesMap[$className])) {
                $classPath = explode(DIRECTORY_SEPARATOR, $path);
                $lastKey = array_key_last($classPath);
                $className = $classPath[$lastKey];
                if ($lastKey > 0) {
                    $keyBeforeLast = array_key_last($classPath) - 1;
                    $className = join(DIRECTORY_SEPARATOR, [$classPath[$keyBeforeLast], $className]);
                    $className = str_replace('.php', '', $className);
                }
            }
            $classesMap[$className] = $path;
        }
    }

    return $classesMap;
}

/**
 * @param $className
 * @param $classesMap
 *
 * @return mixed|null
 */
function getClassPathUsingIterator($className, $classesMap)
{
    $simpleClassName = null;
    $classPath = explode('\\', $className);

    $lastKey = array_key_last($classPath);
    $nameSpacedClassName = $classPath[$lastKey];
    if ($lastKey > 0) {
        $keyBeforeLast = array_key_last($classPath) - 1;
        $simpleClassName = join(DIRECTORY_SEPARATOR, [$classPath[$keyBeforeLast], $nameSpacedClassName]);
    }

    if (isset($classesMap[$simpleClassName])) {
        return $classesMap[$simpleClassName];
    }
    // Split class name into parts to handle namespace if needed
    $classNameParts = explode('\\', $className);
    $simpleClassName = end($classNameParts);


    if (isset($classesMap[$simpleClassName])) {
        return $classesMap[$simpleClassName];
    }
    return null;
}

