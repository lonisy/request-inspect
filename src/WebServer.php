<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/6/14
 * Time: 07:56
 */

namespace Easyim;

use Easyim\Events\ImSocketEvent;
use Easyim\Events\ImApiEvent;
use \swoole_http_server as swoole_http_server;

class WebServer
{
    private $apiEvents;
    private $server;

    public function run()
    {
        $this->apiEvents = new ImApiEvent();
        $this->server = new swoole_http_server("0.0.0.0", 9502);
        $this->server->on('start', [$this->apiEvents, 'onStart']);
        $this->server->on('request', [$this->apiEvents, 'onRequest']);
        $this->server->start();
    }
}
