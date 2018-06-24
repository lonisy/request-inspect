#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/6/10
 * Time: 19:07
 */
defined('IM_DEBUG') or define('IM_DEBUG', true);
defined('IM_ENV') or define('IM_ENV', 'prod');
defined('CONSOLE_ROOT') or define('CONSOLE_ROOT', __DIR__);
require(CONSOLE_ROOT . "/src/config/console.php");
require(CONSOLE_ROOT . '/vendor/autoload.php');

// http://www.php.cn/php-weizijiaocheng-389345.html
// https://www.jianshu.com/p/68b7114a1d70
// http://www.redis.cn/documentation.html
// http://www.redis.net.cn/

$redis = Easyim\Components\Redis::getInstance();


$token = md5(time());
// 禁言
$ttl = 20;

//$token = 'shutup:1:1';
$token = 'denylogin:1';
$redis->set($token, 1, $ttl);
var_dump($redis->ttl($token));
var_dump($redis->get($token));
sleep(1);
var_dump($redis->ttl($token));
var_dump($redis->get($token));
sleep(1);
var_dump($redis->ttl($token));
var_dump($redis->get($token));
exit();


// hash 覆盖
$redis->hMset($token, array('name' => 'lonisy1', 'avatar' => 'empty'));
var_dump($redis->hGetAll($token)) . PHP_EOL;

$redis->hMset($token, array('name' => 'lonisy2', 'avatar' => 'empty'));
var_dump($redis->hGetAll($token)) . PHP_EOL;

$redis->expire($token, $ttl);

exit();

// 禁止访客 1禁止，0允许
$redis->set('refused_visitor', 0);


// 无需集合
$setKey = 'room:fd';
$redis->sAdd($setKey, 'a');
$redis->sAdd($setKey, [1, 2, 3, 4]);
$redis->sAdd($setKey, 'a');
$redis->sAdd($setKey, 'c');

$redis->sRem($setKey, 'a');// 删除1个


var_dump($redis->sMembers($setKey));
$redis->delete($setKey);
var_dump($redis->sMembers($setKey));


//while ($keys = $redis->scan($iterator))
//{
//    foreach ($keys as $key)
//    {
//        echo $key . PHP_EOL;
//    }
//}
exit();


$token = md5(time());
$ttl   = 2;
$redis->setex($token, $ttl, $token);
echo $redis->get($token) . PHP_EOL;
sleep($ttl);
echo $redis->get($token) . PHP_EOL;


echo ' --------- hash set array' . PHP_EOL;

$redis->hMset($token, array('name' => 'lonisy', 'avatar' => 'empty'));
$redis->expire($token, $ttl);

echo $redis->exists($token);
echo $redis->hGet($token, 'name') . PHP_EOL;
var_dump($redis->hGetAll($token)) . PHP_EOL;

sleep($ttl);

echo $redis->exists($token);
echo $redis->hGet($token, 'name') . PHP_EOL;
var_dump($redis->hGetAll($token)) . PHP_EOL;