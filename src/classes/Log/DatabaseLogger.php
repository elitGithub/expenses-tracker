<?php

declare(strict_types = 1);

namespace Log;

/**
 * Logger for the Database classes
 */
class DatabaseLogger extends AbstractLogger
{

    /**
     *
     * @param              $level
     * @param              $message
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
//        echo($message);
//        echo '<pre>';
//        echo(json_encode($context, JSON_PRETTY_PRINT));
//        echo '</pre>';
        error_log($message);
    }


}
