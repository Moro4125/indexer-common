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
use Moro\Indexer\Common\Action\CheckEntityInterface;
use Moro\Indexer\Common\Action\ReceiveIdsInterface;
use Moro\Indexer\Common\Action\ReceiveViewInterface;
use Moro\Indexer\Common\Action\ReceiveViewsInterface;
use Moro\Indexer\Common\Action\RemoveEntityInterface;
use Moro\Indexer\Common\Action\UpdateEntityInterface;
use Moro\Indexer\Common\Action\WaitingForActionInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BackendFacade
 * @package Moro\Indexer\Common
 */
class BackendFacade
{
    /** @var UpdateEntityInterface */
    protected $_updateEntityAction;
    /** @var RemoveEntityInterface */
    protected $_removeEntityAction;
    /** @var ReceiveIdsInterface */
    protected $_receiveIdsAction;
    /** @var ReceiveViewInterface */
    protected $_receiveViewAction;
    /** @var ReceiveViewsInterface */
    protected $_receiveViewsAction;
    /** @var WaitingForActionInterface */
    protected $_waitingAction;
    /** @var CheckEntityInterface */
    protected $_checkEntityAction;
    /** @var EventManager */
    protected $_eventManager;
    /** @var BusManager */
    protected $_busManager;
    /** @var null|LoggerInterface */
    protected $_logger;

    /**
     * @param UpdateEntityInterface $updateAction
     * @param RemoveEntityInterface $removeAction
     * @param ReceiveIdsInterface $receiveIdsAction
     * @param ReceiveViewInterface $receiveViewAction
     * @param ReceiveViewsInterface $receiveViewsAction
     * @param WaitingForActionInterface $waitingAction
     * @param CheckEntityInterface $checkAction
     * @param EventManager $events
     * @param BusManager|null $bus
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        UpdateEntityInterface $updateAction,
        RemoveEntityInterface $removeAction,
        ReceiveIdsInterface $receiveIdsAction,
        ReceiveViewInterface $receiveViewAction,
        ReceiveViewsInterface $receiveViewsAction,
        WaitingForActionInterface $waitingAction,
        CheckEntityInterface $checkAction,
        EventManager $events,
        BusManager $bus = null,
        LoggerInterface $logger = null
    ) {
        $this->_updateEntityAction = $updateAction;
        $this->_removeEntityAction = $removeAction;
        $this->_receiveIdsAction = $receiveIdsAction;
        $this->_receiveViewAction = $receiveViewAction;
        $this->_receiveViewsAction = $receiveViewsAction;
        $this->_waitingAction = $waitingAction;
        $this->_checkEntityAction = $checkAction;
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
            $this->_waitingAction->wait($limit);
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

        $ids = $this->_receiveIdsAction->receiveIds($index, $message['offset'], $message['limit']);

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

        $list = $this->_receiveViewsAction->receive($index, $kind, $message['offset'], $message['limit']);

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

        $entity = $this->_receiveViewAction->receive($index, $kind, $id);

        $msg = ['action' => 'receive', 'entity' => $entity];
        $this->_busManager->send($msg, implode(':', $message['sender']));
    }

    /**
     * @param array $message
     * @throws UnknownTypeInterface
     */
    protected function _busActionUpdate(array $message)
    {
        $this->_updateEntityAction->update($message['type'], $message['id']);
    }

    /**
     * @param array $message
     */
    protected function _busActionRemove(array $message)
    {
        $this->_removeEntityAction->remove($message['type'], $message['id']);
    }

    /**
     * @param EntryInterface $entry
     * @throws UnknownTypeInterface
     */
    protected function _schedulerActionUpdate(EntryInterface $entry)
    {
        $this->_updateEntityAction->update($entry->getType(), $entry->getId());
    }

    /**
     * @param EntryInterface $entry
     * @throws UnknownTypeInterface
     */
    protected function _schedulerActionRemove(EntryInterface $entry)
    {
        $this->_removeEntityAction->remove($entry->getType(), $entry->getId());
    }

    /**
     * @param string $type
     */
    protected function _checkEntity(string $type)
    {
        $this->_checkEntityAction->check($type);
    }
}