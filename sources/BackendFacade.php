<?php

namespace Moro\Indexer\Common;

use Moro\Indexer\Common\Bus\ManagerInterface as BusManager;
use Moro\Indexer\Common\Dispatcher\Event\ExceptionIgnoreEvent;
use Moro\Indexer\Common\Dispatcher\Event\ExceptionRepairedEvent;
use Moro\Indexer\Common\Dispatcher\Event\MessageIsDerivedEvent;
use Moro\Indexer\Common\Dispatcher\Event\SchedulerDeriveEvent;
use Moro\Indexer\Common\Dispatcher\Event\WaitRandomTickEvent;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManager;
use Moro\Indexer\Common\Exception\UnknownTypeInterface;
use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Strategy\CheckEntityInterface;
use Moro\Indexer\Common\Strategy\ReceiveIdsInterface;
use Moro\Indexer\Common\Strategy\ReceiveViewInterface;
use Moro\Indexer\Common\Strategy\ReceiveViewsInterface;
use Moro\Indexer\Common\Strategy\RemoveEntityInterface;
use Moro\Indexer\Common\Strategy\UpdateEntityInterface;
use Moro\Indexer\Common\Strategy\WaitingForActionInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BackendFacade
 * @package Moro\Indexer\Common
 */
class BackendFacade
{
    /** @var UpdateEntityInterface */
    protected $_updateEntityStrategy;
    /** @var RemoveEntityInterface */
    protected $_removeEntityStrategy;
    /** @var ReceiveIdsInterface */
    protected $_receiveIdsStrategy;
    /** @var ReceiveViewInterface */
    protected $_receiveViewStrategy;
    /** @var ReceiveViewsInterface */
    protected $_receiveViewsStrategy;
    /** @var WaitingForActionInterface */
    protected $_waitingStrategy;
    /** @var CheckEntityInterface */
    protected $_checkEntityStrategy;
    /** @var EventManager */
    protected $_eventManager;
    /** @var BusManager */
    protected $_busManager;
    /** @var null|LoggerInterface */
    protected $_logger;

    /**
     * @param UpdateEntityInterface $updateStrategy
     * @param RemoveEntityInterface $removeStrategy
     * @param ReceiveIdsInterface $receiveIdsStrategy
     * @param ReceiveViewInterface $receiveViewStrategy
     * @param ReceiveViewsInterface $receiveViewsStrategy
     * @param WaitingForActionInterface $waitingStrategy
     * @param CheckEntityInterface $checkStrategy
     * @param EventManager $events
     * @param BusManager|null $bus
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        UpdateEntityInterface $updateStrategy,
        RemoveEntityInterface $removeStrategy,
        ReceiveIdsInterface $receiveIdsStrategy,
        ReceiveViewInterface $receiveViewStrategy,
        ReceiveViewsInterface $receiveViewsStrategy,
        WaitingForActionInterface $waitingStrategy,
        CheckEntityInterface $checkStrategy,
        EventManager $events,
        BusManager $bus = null,
        LoggerInterface $logger = null
    ) {
        $this->_updateEntityStrategy = $updateStrategy;
        $this->_removeEntityStrategy = $removeStrategy;
        $this->_receiveIdsStrategy = $receiveIdsStrategy;
        $this->_receiveViewStrategy = $receiveViewStrategy;
        $this->_receiveViewsStrategy = $receiveViewsStrategy;
        $this->_waitingStrategy = $waitingStrategy;
        $this->_checkEntityStrategy = $checkStrategy;
        $this->_eventManager = $events;
        $this->_busManager = $bus;
        $this->_logger = $logger;

        if ($bus) {
            $bus->setOwner('backend');
        }
    }

    /**
     * @param int|null $limit
     * @throws UnknownTypeInterface
     */
    public function wait(int $limit = null)
    {
        if ($this->_logger) {
            $message = 'Indexer start background work with steps limit %1$s.';
            $this->_logger->notice(sprintf($message, (int)$limit));
        }

        $listener1 = function (MessageIsDerivedEvent $event) {
            $message = $event->getMessage();

            if ($this->_logger) {
                $msg = 'Indexer receive message %1$s from bus.';
                $this->_logger->notice(sprintf($msg, json_encode($message)));
            }

            $this->{'_busAction' . ucfirst($message['action'])}($message);
        };

        $listener2 = function (SchedulerDeriveEvent $event) {
            $entry = $event->getEntry();

            if ($this->_logger) {
                $msg = 'Indexer receive entry %1$s from scheduler.';
                $record = ['action' => $entry->getAction(), 'type' => $entry->getType(), 'id' => $entry->getId()];
                $this->_logger->notice(sprintf($msg, json_encode($record)));
            }

            $this->{'_schedulerAction' . ucfirst($entry->getAction())}($entry);
        };

        $listener3 = function (WaitRandomTickEvent $event) {
            $this->_checkEntity($event->getType());
        };

        $listener4 = function (ExceptionRepairedEvent $event) {
            if ($this->_logger) {
                $msg = 'Repair %1$s by %2$s.';
                $exception = $event->getException();
                $description = get_class($exception);
                $this->_logger->debug(sprintf($msg, $description, $event->getRepairedBy()));
            }
        };

        $listener5 = function (ExceptionIgnoreEvent $event) {
            if ($this->_logger) {
                $msg = 'Ignore %1$s by %2$s.';
                $exception = $event->getException();
                $description = get_class($exception);
                $this->_logger->debug(sprintf($msg, $description, $event->getIgnoredBy()));
            }
        };

        $this->_eventManager->attach(MessageIsDerivedEvent::class, $listener1, EventManager::TOP);
        $this->_eventManager->attach(SchedulerDeriveEvent::class, $listener2, EventManager::TOP);
        $this->_eventManager->attach(WaitRandomTickEvent::class, $listener3, EventManager::TOP);
        $this->_eventManager->attach(ExceptionRepairedEvent::class, $listener4, EventManager::TOP);
        $this->_eventManager->attach(ExceptionIgnoreEvent::class, $listener5, EventManager::TOP);

        try {
            $this->_waitingStrategy->wait($limit);
        }
        finally {
            $this->_eventManager->detach(ExceptionIgnoreEvent::class, $listener5);
            $this->_eventManager->detach(ExceptionRepairedEvent::class, $listener4);
            $this->_eventManager->detach(WaitRandomTickEvent::class, $listener3);
            $this->_eventManager->detach(SchedulerDeriveEvent::class, $listener2);
            $this->_eventManager->detach(MessageIsDerivedEvent::class, $listener1);
        }
    }

    /**
     * @param array $message
     */
    protected function _busActionSelect(array $message)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $message['offset'] = $message['offset'] ?? null;
        $message['limit'] = $message['limit'] ?? null;
        $index = $message['index'];

        $ids = $this->_receiveIdsStrategy->receiveIds($index, $message['offset'], $message['limit']);

        $msg = ['action' => 'receive', 'ids' => $ids];
        $this->_busManager->send($msg, implode(':', $message['sender']));
    }

    /**
     * @param array $message
     */
    protected function _busActionQuery(array $message)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $message['offset'] = $message['offset'] ?? null;
        $message['limit'] = $message['limit'] ?? null;
        $index = $message['index'];
        $kind = $message['kind'];

        $list = $this->_receiveViewsStrategy->receive($index, $kind, $message['offset'], $message['limit']);

        $msg = ['action' => 'receive', 'list' => $list];
        $this->_busManager->send($msg, implode(':', $message['sender']));
    }

    /**
     * @param array $message
     */
    protected function _busActionGet(array $message)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $index = $message['index'];
        $kind = $message['kind'];
        $id = $message['id'];

        $entity = $this->_receiveViewStrategy->receive($index, $kind, $id);

        $msg = ['action' => 'receive', 'entity' => $entity];
        $this->_busManager->send($msg, implode(':', $message['sender']));
    }

    /**
     * @param array $message
     * @throws UnknownTypeInterface
     */
    protected function _busActionUpdate(array $message)
    {
        $this->_updateEntityStrategy->update($message['type'], $message['id']);
    }

    /**
     * @param array $message
     */
    protected function _busActionRemove(array $message)
    {
        $this->_removeEntityStrategy->remove($message['type'], $message['id']);
    }

    /**
     * @param EntryInterface $entry
     * @throws UnknownTypeInterface
     */
    protected function _schedulerActionUpdate(EntryInterface $entry)
    {
        $this->_updateEntityStrategy->update($entry->getType(), $entry->getId());
    }

    /**
     * @param EntryInterface $entry
     * @throws UnknownTypeInterface
     */
    protected function _schedulerActionRemove(EntryInterface $entry)
    {
        $this->_removeEntityStrategy->remove($entry->getType(), $entry->getId());
    }

    /**
     * @param string $type
     */
    protected function _checkEntity(string $type)
    {
        $this->_checkEntityStrategy->check($type);
    }
}