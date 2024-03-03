<?php

namespace database;

use Exception;

if (!defined('ADODB_ERROR_HANDLER_TYPE')) {
    define('ADODB_ERROR_HANDLER_TYPE', E_USER_ERROR);
}


class ADODB_EXCEPTION extends Exception
{
    public $dbms;
    public $fn;
    public $sql      = '';
    public $params   = '';
    public $host     = '';
    public $database = '';

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @link https://php.net/manual/en/exception.construct.php
     *
     * @param $dbms
     * @param $fn
     * @param $errno
     * @param $errmsg
     * @param $p1
     * @param $p2
     * @param $thisConnection
     */
    public function __construct($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection)
    {
        switch ($fn) {
            case 'EXECUTE':
                $this->sql = is_array($p1) ? $p1[0] : $p1;
                $this->params = $p2;
                $s = "$dbms error: [$errno: $errmsg] in $fn(\"$this->sql\")";
                break;

            case 'PCONNECT':
            case 'CONNECT':
                $user = $thisConnection->user;
                $s = "$dbms error: [$errno: $errmsg] in $fn($p1, '$user', '****', $p2)";
                break;
            default:
                $s = "$dbms error: [$errno: $errmsg] in $fn($p1, $p2)";
                break;
        }

        $this->dbms = $dbms;
        if ($thisConnection) {
            $this->host = $thisConnection->host;
            $this->database = $thisConnection->database;
        }
        $this->fn = $fn;
        $this->msg = $errmsg;

        if (!is_numeric($errno)) {
            $errno = - 1;
        }
        parent::__construct($s, $errno);
    }
}

/**
 * Default Error Handler. This will be called with the following params
 *
 * @param  mixed  $dbms    the RDBMS you are connecting to
 * @param  mixed  $fn      the name of the calling function (in uppercase)
 * @param  mixed  $errno   the native error number from the database
 * @param  mixed  $errmsg  the native error msg from the database
 * @param  mixed  $p1      $fn specific parameter - see below
 * @param         $p2
 * @param         $thisConnection
 *
 * @throws ADODB_Exception
 */

function adodb_throw($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection)
{
    global $ADODB_EXCEPTION;

    if (error_reporting() == 0) {
        return;
    } // obey @ protocol
    if (is_string($ADODB_EXCEPTION)) {
        $errfn = $ADODB_EXCEPTION;
    } else {
        $errfn = 'ADODB_Exception';
    }
    throw new $errfn($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection);
}
