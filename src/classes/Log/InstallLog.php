<?php

declare(strict_types = 1);

namespace Log;
/**
 *
 */
class InstallLog extends AbstractLogger
{

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
    public function log($level, $message, array $context = [], array $trace = null)
    {
        if (!is_array($trace) || count($trace) < 1) {
            $trace = debug_backtrace();
        }
        $context = array_merge($context, $trace);
        $this->logger->log($level, $message, $context);
        var_dump($message);
        var_dump($context);
        error_log($message);
    }
}
