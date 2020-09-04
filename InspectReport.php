<?php
/**
 * Created by PhpStorm.
 * User: lilei
 * Date: 2018/6/19
 * Time: 23:19
 */

namespace app\components;

use Yii;

use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class InspectReport
{
    /**
     * Created by Lilei <lonisy@163.com>. lilei
     * 发送 API 请求数据到调试工具上
     * @throws \yii\base\InvalidConfigException
     */
    public static function send()
    {
        if (YII_ENV == 'prod') {
            return;
        }
        $inspect_host = 'devtv.github.com';
        if (YII_ENV == 'qa') {
            $inspect_host = 'qatv.github.com';
        } else if (YII_ENV == 'prv') {
            $inspect_host = 'prvtv.github.com';
        } else if (YII_ENV == 'prod-us') {
            $inspect_host = 'tv-us.github.com';
        }
        $request        = Yii::$app->request;
        $response       = Yii::$app->response;
        $url            = 'http://inspect.github.com/report?inspect=' . $inspect_host;
        $requestHeaders = [];
        foreach ($request->getHeaders() as $key => $val) {
            $requestHeaders[strtoupper($key)] = $val[0] ?? '';
        }
        $params = [
            'info'     => [
                'Url'          => $request->getUrl(),
                'BaseUrl'      => $request->getBaseUrl(),
                'PathInfo'     => $request->getPathInfo(),
                'Port'         => $request->getPort(),
                'Status'       => $response->getStatusCode(),
                'Method'       => $request->getMethod(),
                'UserIP'       => $request->getUserIP(),
                'ContentType'  => $request->getContentType(),
                'Host'         => parse_url($request->getHostInfo(), PHP_URL_HOST),
                'UserAgent'    => $request->getUserAgent(),
                'RequestTime'  => date('H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
                'RequestTime2' => $_SERVER['REQUEST_TIME'] ?? '',
                'RequestTime3' => time(),
                'RunTime'      => self::getRunTime(),
                'Len'         => round((mb_strlen($response->content) / 1024), 2) . '0kb',
            ],
            'request'  => [
                'POST'    => $request->post(),
                'GET'     => $request->get(),
                'HEADERS' => $requestHeaders,
                'COOKIES' => $request->getCookies(),
            ],
            'response' => [
                'BODY'    => json_decode($response->content, true) ?? $response->content,
                'HEADERS' => $response->getHeaders(),
                'COOKIES' => $response->getCookies()
            ]
        ];
        self::postAsync($url, $params);
    }

    /**
     * Created by Lilei <lonisy@163.com>. lilei
     * 上报数据到 调试服务器上
     * @param $params
     */
    public static function report($params)
    {
        if (YII_ENV == 'prod') {
            return;
        }
        $inspect_host = 'devtv.github.com';
        if (YII_ENV == 'qa') {
            $inspect_host = 'qatv.github.com';
        } else if (YII_ENV == 'prv') {
            $inspect_host = 'prvtv.github.com';
        } else if (YII_ENV == 'prod-us') {
            $inspect_host = 'tv-us.github.com';
        }
        $url  = 'http://inspect.github.com/report?inspect=' . $inspect_host;
        $data = [
            'info'     => [
                'Url'         => $params['url'] ?? '-',
                'PathInfo'    => isset($params['url']) ? parse_url($params['url'], PHP_URL_PATH) : '',
                'Host'        => isset($params['url']) ? parse_url($params['url'], PHP_URL_HOST) : '',
                'Method'      => $params['method'] ?? '-',
                'Status'      => $params['http_status'] ?? '-',
                'RequestTime' => date('H:i:s', time()),
                'RunTime'     => self::getRunTime(),
                'Port'        => isset($params['url']) ? parse_url($params['url'], PHP_URL_PORT) : '80',
                'Len'         => round((mb_strlen($params['response']) / 1024), 2) . '0kb',
            ],
            'request'  => [
                'POST'    => $params['post'] ?? [],
                'GET'     => $params['get'] ?? [],
                'HEADERS' => $params['headers'] ?? [],
                'COOKIES' => $params['cookies'] ?? [],
            ],
            'response' => [
                'BODY' => $params['response'] ?? [],
            ]
        ];
        self::postAsync($url, $data);
    }

    public static function getRunTime()
    {
        $t1 = ($_SERVER['REQUEST_TIME'] ?? time());
        $t2 = microtime(true);
        return round($t2 - $t1, 3) . 's';
    }

    public static function postAsync($url, $params = [])
    {
        try {
            $client  = new \GuzzleHttp\Client();
            $promise = $client->postAsync($url, [
                'form_params' => $params
            ]);
            $promise->then(
                function (ResponseInterface $res) {
                    // echo $res->getStatusCode() . "\n"; // 不记录日志
                },
                function (RequestException $e) {
                    // echo $e->getMessage() . "\n"; // 不记录日志
                    // echo $e->getRequest()->getMethod(); // 不记录日志
                }
            );
            $promise->wait();
        } catch (RequestException $e) {
            Yii::error(Psr7\str($e->getRequest()), 'postAsync');
            if ($e->hasResponse()) {
                Yii::error(Psr7\str($e->getResponse()), 'postAsync');
            }
        }
    }
}
