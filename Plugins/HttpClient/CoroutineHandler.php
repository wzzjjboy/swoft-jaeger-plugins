<?php


namespace App\Plugins\HttpClient;

use Swoft;
use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Swoole\Coroutine\Http\Client;

class CoroutineHandler extends \Hyperf\Guzzle\CoroutineHandler
{
    /**
     * @param RequestInterface $request
     * @param array $options
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options)
    {
        $uri = $request->getUri();
        $host = $uri->getHost();
        $port = $uri->getPort();
        $ssl = $uri->getScheme() === 'https';
        $path = $uri->getPath();
        $query = $uri->getQuery();

        if (empty($port)) {
            $port = $ssl ? 443 : 80;
        }
        if (empty($path)) {
            $path = '/';
        }
        if ($query !== '') {
            $path .= '?' . $query;
        }

        $client = new Client($host, $port, $ssl);
        $client->setMethod($request->getMethod());
        $client->setData((string) $request->getBody());

        // 初始化Headers
        $this->initHeaders($client, $request, $options);
        // 初始化配置
        $settings = $this->getSettings($request, $options);
        // 设置客户端参数
        if (! empty($settings)) {
            $client->set($settings);
        }

        $ms = microtime(true);

        // Before event
        Swoft::trigger(HttpEvent::EVENT_REQUEST_BEFORE, $this, $request->getMethod(), $request->getBody(), $request->getHeaders(), $request->getUri());

        $this->execute($client, $path);

        $ex = $this->checkStatusCode($client, $request);
        if ($ex !== true) {
            return \GuzzleHttp\Promise\rejection_for($ex);
        }

        $response = $this->getResponse($client, $request, $options, microtime(true) - $ms);

        Swoft::trigger(HttpEvent::EVENT_REQUEST_AFTER, $this, $request->getMethod(), $request->getBody(), $request->getHeaders(), $request->getUri(), $response);

        return new FulfilledPromise($response);
    }
}
