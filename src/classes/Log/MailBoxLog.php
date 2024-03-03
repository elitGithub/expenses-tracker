<?php

declare(strict_types = 1);

namespace Log;

/**
 * Wrapper for MailBox logger. Add your own system-specific Logger.
 */
class MailBoxLog extends AbstractLogger
{

    /**
     *
     * @param $level
     * @param $message
     * @param  array  $context
     * @param  null  $trace  *
     *
* @return void
     */
    protected function log($level, $message, array $context = [], $trace = null)
    {
        var_dump($message);
        var_dump($context);
        error_log($message);
    }
}