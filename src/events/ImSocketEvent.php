<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/6/10
 * Time: 17:29
 */

namespace Easyim\Events;

use Easyim\Components\Logger;
use Easyim\Components\Redis;
use \swoole_websocket_server as swoole_websocket_server;
use \swoole_server as swoole_server;
use \swoole_websocket_frame as swoole_websocket_frame;
use \swoole_http_request as swoole_http_request;
use \swoole_http_response as swoole_http_response;


class ImSocketEvent
{
    public function onStart()
    {
        Logger::info('onStart');
    }

    public function onWorkerStart()
    {
        Logger::info('onWorkerStart');
    }

    public function onOpen(swoole_websocket_server $server, $req)
    {
        Logger::info("connection open: {$req->fd}");
    }

    public function onMessage(swoole_websocket_server $server, swoole_websocket_frame $frame)
    {
        Logger::info("onMessage: $frame->data");
        $messageData = json_decode($frame->data, true);

        $responseData = ['type' => 'error'];
        if (empty($messageData)) {
            $responseData['msg'] = "Wrong message data format !";
            Logger::info($responseData);
            $server->push($frame->fd, json_encode($responseData));
            return;
        }

        // 黑名单业务
        $denylogin = Redis::getInstance()->ttl("denylogin:$frame->fd");
        if ($denylogin > 0) {
            $responseData['type'] = 'denylogin';
            $server->push($frame->fd, json_encode($responseData));
            $server->close($frame->fd);
            return;
        }

        if (isset($messageData['type'])) {
            switch ($messageData['type']) {
                case 'ping':
                    $server->push($frame->fd, 'pong');
                    return;
                case 'login':
                    Redis::getInstance()->incr('inspect_user_num');

                    $token = $messageData['token'] ?? '';
                    if (empty($token)) {
                        $responseData['msg'] = 'Token cannot be empty!';
                        $server->push($frame->fd, json_encode($responseData));
                        $server->close($frame->fd);
                        return;
                    }

                    // 拒绝访客
                    $refused_visitors = Redis::getInstance()->get('refused_visitor');
                    $user_info        = Redis::getInstance()->hGetAll($token);
                    if ($refused_visitors == 1 && empty($user_info)) {
                        $responseData['msg'] = 'Refused Visitor!';
                        $server->push($frame->fd, json_encode($responseData));
                        $server->close($frame->fd);
                        return;
                    } else {
                        if (empty($user_info)) {
                            $user_id   = md5(uniqid(mt_rand(), true));
                            $user_info = [
                                'user_id' => $user_id,
                                'name'    => 'Guest_' . strtoupper($user_id),
                                'avatar'  => '',
                            ];
                            Redis::getInstance()->hMset($token, $user_info);
                            Redis::getInstance()->expire($token, REDIS_CACHE_EXPIRE);
                        }
                    }

                    // 根据 User_id 保存 fd
                    $user_fd_key = 'user_fd:' . $user_id;
                    Redis::getInstance()->set($user_fd_key, $frame->fd, REDIS_CACHE_EXPIRE);

                    // 如果客户端自定义用户信息是要兼容业务
                    if (isset($messageData['user_info']) && is_array($messageData['user_info'])) {
                        Logger::debug("login has user_info", $messageData['user_info']);
                        $user_info = $messageData['user_info'];
                        Redis::getInstance()->hMset($token, $user_info);
                        Redis::getInstance()->expire($token, REDIS_CACHE_EXPIRE);
                    }

                    // 响应数据包
                    $responseData['type']             = $messageData['type'];
                    $responseData['client_id']        = $frame->fd;
                    $responseData['client_info']      = $user_info;
                    $responseData['time']             = date('Y-m-d H:i:s');
                    $responseData['inspect_user_num'] = Redis::getInstance()->get('inspect_user_num');
                    $responseData['inspect_report_num'] = Redis::getInstance()->get('inspect_report_num');

                    $room_id = $messageData['room_id'] ?? 0;
                    Logger::info("room_id:" . $room_id);

                    // 存储连接 和 token 的关系
                    Redis::getInstance()->set("online:$frame->fd", $token, REDIS_CACHE_EXPIRE);
                    Redis::getInstance()->set("where:room:$frame->fd", $room_id, REDIS_CACHE_EXPIRE);
                    if (!empty($room_id)) {
                        Logger::info("存在房间的概念");
                        // 存在房间的概念
                        // 无需集合
                        $room_fd_key = "room:$room_id:fds";
                        $room_fds    = Redis::getInstance()->sMembers($room_fd_key);
                        Logger::debug("room_fds", $room_fds);
                        Redis::getInstance()->sAdd($room_fd_key, $frame->fd);


                        // TODO 可以使用 $server->exist(); 直接操作房间内的 fd ，不必在循环 connections

                        $online_fds = [];
                        foreach ($server->connections as $fd) {
                            if (in_array($fd, $room_fds)) {
                                $online_fds[] = $fd;
                            }
                        }
                        // 删除离线的连接
                        if (!empty($online_fds)) {
                            $offline_fds = array_diff($room_fds, $online_fds);
                            if (!empty($offline_fds)) {
                                foreach ($offline_fds as $offline_fd) {
                                    Redis::getInstance()->sRem($room_fd_key, $offline_fd);
                                    unset($offline_fd);
                                }
                                unset($offline_fds);
                            }
                        }

                        // 当前在线人数
                        $responseData['online_num'] = count($online_fds) + 1;
                        $client_list                = [];

                        // 进入房间时， 可以通知所有人， 我进入了房间
                        foreach ($online_fds as $fd) {
                            Logger::debug("connections:", $fd);
                            if ($fd == $frame->fd) {
                                // 排除当前连接
                                continue;
                            }

                            // 告诉所有人我来了
                            $client_token = Redis::getInstance()->get("online:$fd");
                            if ($client_token) {
                                $client_info = Redis::getInstance()->hGetAll($client_token);
                                if (!empty($client_info)) {
                                    $client_list[] = array_merge(['client_id' => $fd], $client_info);
                                    unset($client_token, $client_info);
                                    Logger::info('告诉所有人我来了..');
                                    $server->push($fd, json_encode($responseData));
                                }
                            }
                            unset($fd);
                        }
                        $client_list[]               = array_merge(['client_id' => $frame->fd], $user_info);
                        $responseData['client_list'] = $client_list;
                        $responseData['online_num']  = count($client_list);
                    } else {
                        // 无房间概念
                    }
                    $welcome_msg             = Redis::getInstance()->get("room:welcome_msg:$room_id");
                    $responseData['content'] = !empty($welcome_msg) ? $welcome_msg : 'welcome';
                    $server->push($frame->fd, json_encode($responseData));
                    return;
                case 'say':

                    $to_client_fd = $messageData['to_client_id'] ?? false;
                    if ($to_client_fd == false) {
                        $responseData['msg'] = "参数 to_client_id 缺失！";
                        $server->push($frame->fd, json_encode($responseData));
                        return;
                    }
                    if (!isset($messageData['content'])) {
                        $responseData['msg'] = "参数 content 缺失！";
                        $server->push($frame->fd, json_encode($responseData));
                        return;
                    }
                    if (empty($messageData['content'])) {
                        $responseData['msg'] = "参数 content 不能为空！";
                        $server->push($frame->fd, json_encode($responseData));
                        return;
                    }

                    // 获取发言人的信息
                    $client_token = Redis::getInstance()->get("online:$frame->fd");
                    if ($client_token) {
                        $client_info = Redis::getInstance()->hGetAll($client_token);
                        if (!empty($client_info)) {
                            $responseData['client_info'] = $client_info;
                        }
                    }
                    // 组装消息体
                    $responseData['type'] = 'say';
                    $responseData['time'] = date('Y-m-d H:i:s');

                    // 群发
                    if ($to_client_fd == 'all') {

                        // 获取房间所有的 fd
                        $room_id = Redis::getInstance()->get("where:room:$frame->fd");
                        if (!empty($room_id)) {

                            // 禁言业务
                            $shutup_time = Redis::getInstance()->ttl("shutup:$room_id:$frame->fd");
                            if ($shutup_time > 0) {
                                $responseData['type']        = 'shutup';
                                $responseData['shutup_time'] = $shutup_time;
                                $server->push($frame->fd, json_encode($responseData));
                                return;
                            }

                            // 组装消息体 正常发送房间信息
                            $responseData['to_client_id']   = $to_client_fd;
                            $responseData['from_client_id'] = $frame->fd;
                            $responseData['content']        = $messageData['content'];

                            // {"type":"say","from_client_id":"7f00000108fd00000223","from_client_name":"123123","to_client_id":"all","content":"123123","time":"2018-06-13 00:01:39"}
                            $room_fd_key = "room:$room_id:fds";
                            $room_fds    = Redis::getInstance()->sMembers($room_fd_key);
                            foreach ($room_fds as $room_fd) {
                                // 只给在线的发消息
                                if ($server->exist($room_fd) == true) {
                                    $server->push($room_fd, json_encode($responseData));
                                }
                            }

                        }
                        return;
                    }

                    // 一对一聊天
                    if ($server->exist($to_client_fd) == true) {
                        $server->push($frame->fd, json_encode($responseData));
                        // 自己发给自己时
                        if ($frame->fd != $to_client_fd) {
                            $server->push($to_client_fd, json_encode($responseData));
                        }
                        return;
                    }
            }
        }
    }

    public function onClose(swoole_server $server, $fd, $reactorId, swoole_websocket_server $web_socket_server)
    {
        // $server => swoole_websocket_server $web_socket_server
        // 相当于当前的 => websocket_server
        // 下文可用来直接发送 websocket

        $room_id = Redis::getInstance()->get("where:room:$fd");
        if ($room_id) {
            $room_fd_key = "room:$room_id:fds";
            Redis::getInstance()->sRem($room_fd_key, $fd);
            $room_fds                   = Redis::getInstance()->sMembers($room_fd_key);
            $responseData['type']       = 'logout';
            $responseData['client_id']  = $fd;
            $responseData['time']       = date('Y-m-d H:i:s');
            $responseData['online_num'] = count($room_fds);

            foreach ($server->connections as $innerfd) {
                Logger::debug("connections:", $innerfd);
                if ($innerfd == $fd) {
                    // 排除当前连接
                    continue;
                }
                // 获取在线列表
                if (in_array($innerfd, $room_fds)) {
                    $client_token = Redis::getInstance()->get("online:$innerfd");
                    if ($client_token) {
//                        $client_info = Redis::getInstance()->hGetAll($client_token);
//                        if (!empty($client_info)) {
//                            $responseData['client_info'] = $client_info;
//                            Logger::info('告诉所有人离开了..');
//                            // 告诉所有人我来了
//                            $server->push($innerfd, json_encode($responseData));
//                        }
                    } else {
                        // 如果已断线，从房间内删除。
                        Redis::getInstance()->sRem($room_fd_key, $innerfd);
                    }
                }
                Logger::info('onClose');
            }
        }

//        Redis::getInstance()->set("online:$frame->fd", $token, REDIS_CACHE_EXPIRE);
        //    根据 User_id 保存 fd
        //                    $user_fd_key = 'user_fd:' . $user_id;
        //                    Redis::getInstance()->set($user_fd_key, $frame->fd, REDIS_CACHE_EXPIRE);
    }
}