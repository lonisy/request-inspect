<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/6/10
 * Time: 18:20
 * https://github.com/phpredis/phpredis
 */

namespace Easyim\Components;

defined('REDIS_HOST') or define('REDIS_HOST', 'redis32con');
defined('REDIS_PORT') or define('REDIS_PORT', 6379);
defined('REDIS_DATABASE') or define('REDIS_DATABASE', 0);

class Redis
{
    private static $_instance;

    public static function getInstance()
    {
        try {
            if (self::$_instance instanceof \Redis && strtoupper(self::$_instance->ping()) == 'PONG') {
                return self::$_instance;
            } else {
                self::$_instance = new \Redis();
                self::$_instance->connect(REDIS_HOST, REDIS_PORT);
                self::$_instance->setOption(\Redis::OPT_PREFIX, 'easyim:');//设置表前缀为hf_
                self::$_instance->select(1);
                return self::$_instance;
            }
        } catch (Exception $e) {
            echo 'Redis-Exception: ' . $e->getMessage();
        }
    }
}