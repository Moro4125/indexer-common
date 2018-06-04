<?php

use Moro\Indexer\Common\Event\Manager\EventManager;
use Moro\Indexer\Common\Event\Manager\LazyManager;
use Moro\Indexer\Common\Event\ManagerInterface;
use Moro\Indexer\Common\Event\Event\AbstractEvent;
use Moro\Indexer\Common\Event\Event\SchedulerDeriveEvent;
use Moro\Indexer\Common\Event\Event\SchedulerDeferEvent;
use Moro\Indexer\Common\Event\Event\MessageIsDerivedEvent;
use Moro\Indexer\Common\Event\Event\ExceptionRepairedEvent;
use Moro\Indexer\Common\Event\Event\IndexUpdateEvent;
use Moro\Indexer\Common\Event\Event\ViewDropEvent;
use Moro\Indexer\Common\Event\Event\ViewSaveEvent;
use Moro\Indexer\Common\Scheduler\Entry\SchedulerEntry;
use Moro\Indexer\Test\SimpleContainer;
use Moro\Indexer\Test\DummyEvent;

/**
 * Class EventTest
 */
class EventTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    public function testEventManager(ManagerInterface $manager = null)
    {
        /** @var EventManager $manager */
        $manager = $manager ?? new EventManager();

        $this->specify('Test empty manager', function() use ($manager) {
            verify($manager->trigger(new AbstractEvent()))->same($manager);
            $manager->fire();
        });

        $this->specify('Trigger and fire event', function() use ($manager) {
            $called = 0;
            $listener = function($e) use (&$called) {
                $called++;
                verify($e)->isInstanceOf(AbstractEvent::class);
            };

            verify($manager->attach(AbstractEvent::class, $listener))->same($manager);
            $manager->trigger(new AbstractEvent())->fire();
            verify($called)->same(1);
            verify($manager->detach(AbstractEvent::class, $listener))->same($manager);

            $manager->trigger(new AbstractEvent())->fire();
            verify($called)->same(1);
        });

        $this->specify('Trigger and fire event with priority', function() use ($manager) {
            $called = [];
            $listener1 = function($e) use (&$called) {
                $called[] = 1;
                verify($e)->isInstanceOf(AbstractEvent::class);
            };
            $listener2 = function(AbstractEvent $e) use (&$called) {
                $called[] = 2;
                verify($e)->isInstanceOf(AbstractEvent::class);
                $e->stopPropagation();
            };
            $listener3 = function($e) use (&$called) {
                $called[] = 3;
                verify($e)->isInstanceOf(AbstractEvent::class);
            };

            $listener4 = function() use (&$called) {
                $called[] = 4;
            };

            verify($manager->attach(AbstractEvent::class, $listener1, ManagerInterface::MIDDLE))->same($manager);
            verify($manager->attach(AbstractEvent::class, $listener2, ManagerInterface::AFTER))->same($manager);
            verify($manager->attach(AbstractEvent::class, $listener3, ManagerInterface::BEFORE))->same($manager);
            verify($manager->attach(AbstractEvent::class, $listener4, 0))->same($manager);
            $manager->trigger(new AbstractEvent())->fire();
            $manager->trigger(new DummyEvent())->fire();
            verify($called)->same([3,1,2]);
        });
    }

    public function testLazyManager()
    {
        $container = new SimpleContainer();
        $container->set('manager', new EventManager());

        $lazy = new LazyManager($container, 'manager');
        $this->testEventManager($lazy);
    }

    public function testConcreteEvents()
    {
        $entry = new SchedulerEntry();
        $timestamp = time();
        $message = ['msg' => 'text'];
        $exception = new RuntimeException();

        $event1 = new SchedulerDeriveEvent($entry, $timestamp);
        $event2 = new MessageIsDerivedEvent($message);
        $event3 = new ExceptionRepairedEvent($exception, self::class);
        $event4 = new IndexUpdateEvent('simple');
        $event5 = new ViewDropEvent('simple', 'json', '1');
        $event6 = new ViewSaveEvent('simple', 'json', '1');
        $event7 = new SchedulerDeferEvent('update', 'simple', '1', $timestamp);

        $this->specify('Test SchedulerDeriveEvent', function () use ($event1, $entry, $timestamp) {
            verify($event1->getName())->same(SchedulerDeriveEvent::class);
            verify($event1->getEntry())->same($entry);
            verify($event1->getTimestamp())->same($timestamp);
        });

        $this->specify('Test MessageIsDerivedEvent', function () use ($event2, $message) {
            verify($event2->getName())->same(MessageIsDerivedEvent::class);
            verify($event2->getMessage())->same($message);
        });

        $this->specify('Test ExceptionRepairedEvent', function () use ($event3, $exception) {
            verify($event3->getName())->same(ExceptionRepairedEvent::class);
            verify($event3->getException())->same($exception);
            verify($event3->getRepairedBy())->same(self::class);
        });

        $this->specify('Test IndexUpdateEvent', function () use ($event4) {
            verify($event4->getName())->same(IndexUpdateEvent::class);
            verify($event4->getAlias())->same('simple');
        });

        $this->specify('Test ViewDropEvent', function () use ($event5) {
            verify($event5->getName())->same(ViewDropEvent::class);
            verify($event5->getType())->same('simple');
            verify($event5->getKind())->same('json');
            verify($event5->getId())->same('1');
        });

        $this->specify('Test ViewSaveEvent', function () use ($event6) {
            verify($event6->getName())->same(ViewSaveEvent::class);
            verify($event6->getType())->same('simple');
            verify($event6->getKind())->same('json');
            verify($event6->getId())->same('1');
        });

        $this->specify('Test SchedulerDeferEvent', function () use ($event7, $timestamp) {
            verify($event7->getName())->same(SchedulerDeferEvent::class);
            verify($event7->getAction())->same('update');
            verify($event7->getType())->same('simple');
            verify($event7->getId())->same('1');
            verify($event7->getTimestamp())->same($timestamp);
        });
    }
}