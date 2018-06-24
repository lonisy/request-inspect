<?php
/**
 * Created by lonisy@163.com
 * User: lilei
 * Date: 2017/10/23
 * Time: 14:33
 * http://www.importnew.com/23293.html
 * http://www.cnblogs.com/xiazh/archive/2012/11/14/2770297.html
 */

namespace Easyim;

use Easyim\Events\ImSocketEvent;
use Easyim\Events\ImApiEvent;

class WebSocketServer
{
    private $socketEvents;
    private $apiEvents;

    public function run()
    {
        $this->socketEvents = new ImSocketEvent();
        $this->apiEvents    = new ImApiEvent();
        $this->server       = new \swoole_websocket_server(GATEWAY_SERVER_HOST, GATEWAY_SERVER_PORT);
        $this->server->set(array(
            'enable_static_handler'    => true,
            'document_root'            => CONSOLE_ROOT . '/webroot/',
            'reactor_num'              => GATEWAY_WORKER_NUM,
            'worker_num'               => GATEWAY_WORKER_NUM,
            'daemonize'                => GATEWAY_DAEMONIZE,
            'max_request'              => WS_MAX_REQUEST,
            'backlog'                  => BACK_LOG,
            'log_file'                 => LOG_FILE,
            'heartbeat_idle_time'      => HEARTBEAT_IDLE_TIME,
            'heartbeat_check_interval' => HEARTBEAT_CHECK_INTERVAL,
        ));
        $this->server->on('start', [$this->socketEvents, 'onStart']);
        $this->server->on('workerstart', [$this->socketEvents, 'onWorkerStart']);
        $this->server->on('open', [$this->socketEvents, 'onOpen']);
        $this->server->on('message', [$this->socketEvents, 'onMessage']);

//        $this->server->on('message', function ($server, $frame) {
//            $this->socketEvents->onMessage($server, $frame, $this->server);
//        });

        $this->server->on('request', function ($request, $response) {
            $this->apiEvents->onRequest($request, $response, $this->server);
        });

        $this->server->on('close', function ($server, $fd, $reactorId) {
            $this->socketEvents->onClose($server, $fd, $reactorId, $this->server);
        });

//        $this->server->addProcess($this->createChatChannelProcess($this->server, $this->events));

        $this->server->start();
    }

    private function createChatChannelProcess($server, $events)
    {
        $process = new \swoole_process(function ($process) use ($server, $events) {
            $events->createChatChannelProcess($server, $events);
        });
        return $process;
    }
}
