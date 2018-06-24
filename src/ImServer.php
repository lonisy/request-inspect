<?php
$server = new swoole_websocket_server("0.0.0.0", 9501);
$server->set([
    'log_file'   => LOG_FILE,
]);
//+ 群聊
//+ 一对一聊天，私聊
//+ 支持图片，支持 UBB 表情代码
//+ 支持礼物效果
//+ 禁言，禁言有时间限制
//+ 踢下线，黑名单

$server->on('open', function ($server, $req) {
    Logger::info("创建进程!" . __METHOD__);
    echo "connection open: {$req->fd}\n";
});

$server->on('message', function ($server, $frame) {
    echo "received message: {$frame->data}\n";

    //room_id
    $server->push($frame->fd, json_encode(["hello", "world"]));
});

$server->on('close', function ($server, $fd) {
    echo "connection close: {$fd}\n";
});

$server->start();