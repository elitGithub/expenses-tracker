<?php

declare(strict_types = 1);

namespace Session;

/**
 * In case we want to use a different session manager like Redis or Memcached and so on.
 */
class SessionWrapper
{

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('expenses-tracker');
            session_start();
        }
    }

    /**
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function sessionAddKey($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function sessionReadKey($key)
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function sessionHasKey($key): bool
    {
        return isset($_SESSION[$key]);
    }



}
