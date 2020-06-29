<?php


namespace App\Plugins\Listener;


use App\Plugins\Concern\ListenerTrait;
use App\Plugins\Concern\SpanStarterTrait;
use App\Plugins\Manager\TracerManager;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\DbEvent;
use Swoft\Event\Annotation\Mapping\Subscriber;
use Swoft\Event\EventInterface;
use Swoft\Event\EventSubscriberInterface;

/**
 * Class JaegerRedisListener
 * @package App\Plugins\Listener
 * @Subscriber()
 */
class JaegerMysqlListener implements EventSubscriberInterface
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
            DbEvent::SELECTING => 'before',
            DbEvent::SQL_RAN => 'after',
        ];
    }

    public function before(EventInterface $event){
        $this->_before($event, 'mysql');
    }

    public function after(EventInterface $event){
        $this->_after($event);
    }
}
