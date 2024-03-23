<?php

declare(strict_types = 1);

namespace Log;


use Loggers\FileLogger;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 *
 */
abstract class AbstractLogger implements LoggerInterface
{
    protected Logger $logger;

    /**
     * @param  string  $logName
     */
    public function __construct(string $logName, $logLevel = Logger::DEBUG)
    {
        $this->logger = new Logger($logName, [new FileLogger(EXTR_ROOT_DIR . '/config/logs/', $logLevel)]);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param  mixed       $level
     * @param  string      $message
     * @param  array       $context
     * @param  array|null  $trace
     *
     * @return void
     */
    abstract public function log($level, $message, array $context = [], array $trace = null);

    /**
     * @param         $message
     * @param  array  $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context, debug_backtrace());
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param  string  $message
     * @param  array   $context
     *
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context, debug_backtrace());
    }

    /**
     * Normal but significant events.
     *
     * @param  string  $message
     * @param  array   $context
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context, debug_backtrace());
    }

    /**
     * @param         $message
     * @param  array  $context
     *
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context, debug_backtrace());
    }

    /**
     * @param         $message
     * @param  array  $context
     *
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context, debug_backtrace());
    }

    /**
     * @param         $message
     * @param  array  $context
     *
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context, debug_backtrace());
    }

    /**
     * @param         $message
     * @param  array  $context
     *
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context, debug_backtrace());
    }

    /**
     * @param         $message
     * @param  array  $context
     *
     * @return void
     */
    public function fatal($message, array $context = [])
    {
        $this->emergency($message, $context);
        $this->log(LogLevel::EMERGENCY, $message, $context, debug_backtrace());
    }

    /**
     * @param         $message
     * @param  array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context, debug_backtrace());
    }
}
