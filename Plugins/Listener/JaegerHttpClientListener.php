<?php


namespace App\Plugins\Listener;

use App\Plugins\Concern\SpanStarterTrait;
use App\Plugins\HttpClient\HttpEvent;
use GuzzleHttp\Psr7\Response;
use App\Plugins\Concern\ListenerTrait;
use App\Plugins\Manager\TracerManager;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Event\Annotation\Mapping\Subscriber;
use Swoft\Event\EventSubscriberInterface;
use Swoft\Event\EventInterface;

/**
 * Class JaegerRedisListener
 * @package App\Plugins\Listener
 * @Subscriber()
 */
class JaegerHttpClientListener implements EventSubscriberInterface
{
    use ListenerTrait, SpanStarterTrait;

    /**
     * @Inject()
     * @var TracerManager
     */
    private $tracerManager;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return  [
            HttpEvent::EVENT_REQUEST_BEFORE => 'before',
            HttpEvent::EVENT_REQUEST_AFTER => 'after',
        ];
    }

    public function before(EventInterface $event){
        $this->_before($event, 'http');
    }

    public function after(EventInterface $event){
        $this->_after($event, function() use ($event){
            /** @var Response $response */
            if (($response = $event->getParam(4)) instanceof Response){
                $this->span->log(['statusCode' => $response->getStatusCode(), 'body' => $response->getBody()->getContents()]);
            }
        });
    }
}
