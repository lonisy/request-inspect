<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/6/10
 * Time: 22:25
 */

namespace Easyim\Events;

use \swoole_http_request as swoole_http_request;
use \swoole_http_response as swoole_http_response;
use \swoole_async_readfile as swoole_async_readfile;
use Easyim\Components\Logger;
use Easyim\Components\Redis;

class ImApiEvent
{
    private $request_data = [];

    public function onStart($server)
    {
        Logger::info('Web Server Start...');
    }

    public function onRequest(swoole_http_request $request, swoole_http_response $response, $server)
    {
        $response->header('Content-Type', 'application/javascript; charset=utf8');
        $response->header('Server', 'Easyim by lonisy@163.com');
        $response->status(404);
        $response->gzip(4);
        $request_uri           = $request->server['request_uri'] ?? '/';
        $response_data['code'] = 0;

        try {
            if (is_array($request->get) && !empty($request->get)) {
                $this->request_data = array_merge($this->request_data, $request->get);
            }
            if (is_array($request->post) && !empty($request->post)) {
                $this->request_data = array_merge($this->request_data, $request->post);
            }

            switch ($request_uri) {
                case '/':
                    return $response->end();
                case '/sign': // 注册接口
                    if (!empty($this->request_data)) {
                        $this->verfiySign($this->request_data);
                    }
                    $response->status(200);
                    if (strtoupper($request->server['request_method']) == 'POST') {
                        $response_data['code'] = 1;
                        $response_data['data'] = $this->sign();
                    } else {
                        $response_data['msg'] = 'Bad request method!';
                    }
                    break;
                case '/message':
                    // 根据用户 ID 给房间发送消息，发送者&接收者
                    // 根据用户 ID 给用户发消息，私聊
                    break;
                case '/shutup':
                    // 禁言，给用户设置禁言时间
                    if (!empty($this->request_data)) {
                        $this->verfiySign($this->request_data);
                    }
                    $response->status(200);
                    if (strtoupper($request->server['request_method']) == 'POST') {
                        $response_data['code'] = 1;
                        $response_data['data'] = $this->shutup();
                    } else {
                        $response_data['msg'] = 'Bad request method!';
                    }
                    break;
                case '/offline':
                    //+ 踢下线，设置黑名单，给用户设置黑名单，永久性禁止用户访问
                    if (!empty($this->request_data)) {
                        $this->verfiySign($this->request_data);
                    }
                    $response->status(200);
                    if (strtoupper($request->server['request_method']) == 'POST') {
                        $response_data['code'] = 1;
                        $response_data['data'] = $this->offline();
                    } else {
                        $response_data['msg'] = 'Bad request method!';
                    }
                    break;
                case '/room/users':
                    //+ 根据房间 ID 获取用户在线列表，分页
                    break;
                case '/room':
                    break;
                case '/index2.html':
                    swoole_async_readfile(CONSOLE_ROOT . "/webroot/index.html", function ($filename, $content) {
                        echo "$filename: $content";
                    });
                    break;
                case '/report':
                    $response->status(200);
                    if (empty($request->post)) {
                        return $response->end('data error');
                    }
                    $room_id = $request->get['inspect'] ?? '';
                    if (empty($room_id)) {
                        return $response->end('inspect error');
                    }

                    Redis::getInstance()->incr('inspect_report_num');

                    // 根据 URI 作为房间号，获取房间内连接，循环发消息
                    $message['type']                       = 'report';
                    $message['data']                       = $request->post;
                    $message['info']['inspect_user_num']   = Redis::getInstance()->get('inspect_user_num');
                    $message['info']['inspect_report_num'] = Redis::getInstance()->get('inspect_report_num');

                    $room_fd_key = "room:$room_id:fds";
                    $room_fds    = Redis::getInstance()->sMembers($room_fd_key);
                    if (is_array($room_fds) && !empty($room_fds)) {
                        foreach ($room_fds as $room_fd) {
//                            var_dump($room_fd);
                            if ($server->exist($room_fd) == true) {
                                $server->push($room_fd, json_encode($message));
                            } else {
                                Logger::info('$room_fd:' . $room_fd . " not exist!");
                            }
                        }
                    }
                    return $response->end('ok');
                    break;
                default:
                    echo 'default' . PHP_EOL;
                    return $this->parserPHP($request, $response);
                    break;
            }
        } catch (\Exception $e) {
            $response_data['msg'] = $e->getMessage();
        }
        return $response->end(json_encode($response_data));
    }

    /**
     * Created by lonisy@163.com
     * 解析 PHP
     * @param $request
     * @param $response
     */
    private function parserPHP($request, $response)
    {
        $pathinfo = $request->server['path_info'];
        $filename = CONSOLE_ROOT . '/webroot/view' . $pathinfo;
        $response->header('Content-Type', 'text/html');
        if (is_file($filename)) {
            $response->status('200');
            $ext = pathinfo($request->server['path_info'], PATHINFO_EXTENSION);
            if ($ext == 'php') {
                ob_start();
                include $filename;
                $content = ob_get_contents();
                ob_end_clean();
                $response->end($content);
            } else {
                $content = file_get_contents($filename);
                $response->end($content);
            }
        } else {
            $response->status('404');
            $response->end('404 not found');
        }
    }

    public function verfiySign(array $parameter = [])
    {
        if (isset($parameter['dev'])) {
            unset($parameter['dev']);
            return true;
        }
        if (!isset($parameter['sign'])) {
            throw new \Exception('缺少验签字段!');
        }
        $sign = $this->genVerify($parameter);
        if ($sign != $parameter['sign']) {
            Logger::debug('验签失败!', $parameter);
            throw new \Exception('验签失败!');
        }
        return true;
    }

    public function genVerify(array $parameter = [], string $signKey = 'LXck86aYuxVpYjxG')
    {
        if (isset($parameter['sign'])) {
            unset($parameter['sign']);
        }
        if (empty($parameter)) {
            throw new \Exception('验签的数据格式错误!');
        }
        ksort($parameter);
        $signString = http_build_query($parameter, '&');
        $signString = $signString . $signKey;
        $signString = md5($signString);
        return $signString;
    }

    /**
     * Created by lonisy@163.com
     * 注册接口
     * @return array
     * @throws \Exception
     */
    private function sign()
    {
        if (!isset($this->request_data['user_id'])) {
            throw new \Exception('参数 user_id 缺失!');
        }
        if (!isset($this->request_data['name'])) {
            throw new \Exception('参数 name 缺失!');
        }
        $user_token_key = 'user_token:' . $this->request_data['user_id'];
        $token          = Redis::getInstance()->get($user_token_key);
        if (!$token) {
            $token = md5(uniqid(mt_rand(), true));
        }
        Redis::getInstance()->hMset($token, $this->request_data);
        Redis::getInstance()->expire($token, REDIS_CACHE_EXPIRE);
        Redis::getInstance()->set($user_token_key, $token, REDIS_CACHE_EXPIRE);
        return [
            'token' => $token,
        ];
    }

    /**
     * Created by lonisy@163.com
     * 禁言操作
     * @return \stdClass
     * @throws \Exception
     */
    private function shutup()
    {
        if (!isset($this->request_data['user_id'])) {
            throw new \Exception('参数 user_id 缺失!');
        }
        if (!isset($this->request_data['shutup_time'])) {
            throw new \Exception('参数 shutup_time 缺失!');
        }
        if (!isset($this->request_data['room_id'])) {
            throw new \Exception('参数 room_id 缺失!');
        }

        // 根据 user_id 取 fd
        $user_fd_key = 'user_fd:' . $this->request_data['user_id'];
        $fd          = Redis::getInstance()->get($user_fd_key);
        if ($fd) {
            $shutup_key  = "shutup:{$this->request_data['room_id']}:$fd";
            $shutup_time = $this->request_data['shutup_time'] + 0;
            Redis::getInstance()->set($shutup_key, 1, $shutup_time);
        }
        return new \stdClass();
    }

    /**
     * Created by lonisy@163.com
     * 黑名单，下线操作
     * @return \stdClass
     * @throws \Exception
     */
    private function offline()
    {
        if (!isset($this->request_data['user_id'])) {
            throw new \Exception('参数 user_id 缺失!');
        }
        if (!isset($this->request_data['denylogin_time'])) {
            throw new \Exception('参数 denylogin_time 缺失!');
        }

        // 根据 user_id 取 fd
        $user_fd_key = 'user_fd:' . $this->request_data['user_id'];
        $fd          = Redis::getInstance()->get($user_fd_key);
        if ($fd) {
            $denylogin_time = $this->request_data['denylogin_time'] + 0;
            if ($denylogin_time > 0 && $denylogin_time <= 60 * 60) {
                throw new \Exception('设为黑名单的时长需要大于一个小时。');
            }
            $denylogin_key = "denylogin:$fd";
            Redis::getInstance()->set($denylogin_key, 1, $denylogin_time);
        }
        return new \stdClass();
    }


}