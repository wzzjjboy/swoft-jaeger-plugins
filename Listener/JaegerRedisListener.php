<?php


namespace Plugins\Listener;


use Plugins\Concern\ListenerTrait;
use Plugins\Concern\SpanStarterTrait;
use Plugins\Manager\TracerManager;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Event\Annotation\Mapping\Subscriber;
use Swoft\Event\EventInterface;
use Swoft\Event\EventSubscriberInterface;
use Swoft\Redis\RedisEvent;

/**
 * Class JaegerRedisListener
 * @package App\Plugins\Listener
 * @Subscriber()
 */
class JaegerRedisListener implements EventSubscriberInterface
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
            RedisEvent::BEFORE_COMMAND => 'before',
            RedisEvent::AFTER_COMMAND => 'after',
        ];
    }

    public function before(EventInterface $event){
        $this->_before($event, 'redis');
    }

    public function after(EventInterface $event){
        $this->_after($event);
    }
}
