<?php

declare(strict_types = 1);

namespace Loggers;



use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * File logger system for StreamHandler
 */
class FileLogger extends StreamHandler
{
    /**
     * Constructor.
     *
     * @param string $logDirectory The directory where log files will be stored.
     * @param int|string $logLevel The minimum logging level at which this handler will be triggered.
     */
    public function __construct(string $logDirectory, string $logName, $logLevel = Logger::DEBUG)
    {
        $logFilePath = $logDirectory . DIRECTORY_SEPARATOR . date('Y-m-d') . '_' . $logName . '.log';
        parent::__construct($logFilePath, $logLevel);
    }
}
