<?php

declare(strict_types = 1);

namespace Log;

abstract class AbstractLogger
{
    protected string $logName;

    /**
     * @param  string  $logName
     */
    public function __construct(string $logName)
    {
        $this->logName = $logName;
    }

    abstract protected function log($level, $message, array $context = [], $trace = null);

    public function debug($message, array $context = [])
    {
        $this->log('debug', $message, $context, debug_backtrace());
    }

    public function info($message, array $context = [])
    {
        $this->log('info', $message, $context, debug_backtrace());
    }

    public function warning($message, array $context = [])
    {
        $this->log('warning', $message, $context, debug_backtrace());
    }

    public function error($message, array $context = [])
    {
        $this->log('error', $message, $context, debug_backtrace());
    }

    public function critical($message, array $context = [])
    {
        $this->log('critical', $message, $context, debug_backtrace());
    }

    public function fatal($message, array $context = [])
    {
        $this->log('fatal', $message, $context, debug_backtrace());
    }
}
