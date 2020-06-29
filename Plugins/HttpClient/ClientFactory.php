<?php


namespace App\Plugins\HttpClient;



use Swoole\Coroutine;
use GuzzleHttp\Client;
use Swoft\Bean\Container;
use GuzzleHttp\HandlerStack;
use Psr\Container\ContainerInterface;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * @Bean()
 * Class ClientFactory
 * @package App\Plugins\HttpClient
 */
class ClientFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function create(array $options = []): Client
    {
        $this->container = Container::getInstance();

        $stack = null;

        if (Coroutine::getCid() > 0) {
            $stack = HandlerStack::create(new CoroutineHandler());
        }

        $config = array_replace(['handler' => $stack], $options);

        if (method_exists($this->container, 'make')) {
            // Create by DI for AOP.
            return $this->container->make(Client::class, ['config' => $config]);
        }
        return new Client($config);
    }
}
