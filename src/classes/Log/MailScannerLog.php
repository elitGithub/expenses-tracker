<?php

declare(strict_types = 1);

namespace Log;

class MailScannerLog extends AbstractLogger
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