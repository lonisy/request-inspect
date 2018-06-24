<?php
require_once __DIR__ . '/' . IM_ENV . '/params.php';
require_once __DIR__ . '/' . IM_ENV . '/redis.php';

defined('CONSOLE_ECHO') or define('CONSOLE_ECHO', false);
defined('WS_MAX_REQUEST') or define('WS_MAX_REQUEST', 100000);
defined('GATEWAY_SERVER_HOST') or define('GATEWAY_SERVER_HOST', '0.0.0.0');
defined('HEARTBEAT_IDLE_TIME') or define('HEARTBEAT_IDLE_TIME', 600); // https://wiki.swoole.com/wiki/page/283.html
defined('HEARTBEAT_CHECK_INTERVAL') or define('HEARTBEAT_CHECK_INTERVAL', 60); // https://wiki.swoole.com/wiki/page/283.html
defined('GATEWAY_SERVER_PORT') or define('GATEWAY_SERVER_PORT', 9511);
defined('GATEWAY_WORKER_NUM') or define('GATEWAY_WORKER_NUM', 20);
defined('GATEWAY_REACTOR_NUM') or define('GATEWAY_REACTOR_NUM', 2);
//defined('GATEWAY_DAEMONIZE') or define('GATEWAY_DAEMONIZE', true);
defined('GATEWAY_DAEMONIZE') or define('GATEWAY_DAEMONIZE', false);
defined('BACK_LOG') or define('BACK_LOG', 128);
defined('REDIS_CACHE_EXPIRE') or define('REDIS_CACHE_EXPIRE', 86400); // redis_cache_expire