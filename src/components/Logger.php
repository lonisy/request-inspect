<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/6/10
 * Time: 17:50
 */

namespace Easyim\Components;

class Logger
{
    private static $_instance;
    
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function info($msg = '')
    {
        echo 'info: ' . is_string($msg) ? $msg : (string)$msg, "\n";
    }

    public static function error($msg = '')
    {
        echo 'error: ' . is_string($msg) ? $msg : (string)$msg, "\n";
    }

    public static function debug($msg = 'error', $data = [])
    {
        echo 'debug: ' . is_string($msg) ? $msg : (string)$msg, "\n";
        var_dump($data);
    }
}