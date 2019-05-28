<?php

namespace Moro\Indexer\Common;

use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManager;
use Moro\Indexer\Common\Exception\UnknownTypeInterface;
use Moro\Indexer\Common\Regulation\Exception\InstructionFailedException;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use Moro\Indexer\Common\Action\CheckEntityInterface;
use Moro\Indexer\Common\Action\ReceiveIdsInterface;
use Moro\Indexer\Common\Action\ReceiveViewInterface;
use Moro\Indexer\Common\Action\ReceiveViewsInterface;
use Moro\Indexer\Common\Action\RemoveEntityInterface;
use Moro\Indexer\Common\Action\UpdateEntityInterface;
use Moro\Indexer\Common\Action\WaitingForActionInterface;
use Psr\Log\LoggerInterface;

/**
 * Class MonolithFacade
 * @package Moro\Indexer\Common
 */
class MonolithFacade extends BackendFacade
{
    /**
     * @param UpdateEntityInterface $updateAction
     * @param RemoveEntityInterface $removeAction
     * @param ReceiveIdsInterface $receiveIdsAction
     * @param ReceiveViewInterface $receiveViewAction
     * @param ReceiveViewsInterface $receiveViewsAction
     * @param WaitingForActionInterface $waitingAction
     * @param CheckEntityInterface $checkAction
     * @param EventManager $events
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
        LoggerInterface $logger = null
    ) {
        parent::__construct($updateAction, $removeAction, $receiveIdsAction, $receiveViewAction, $receiveViewsAction,
            $waitingAction, $checkAction, $events, null, $logger);
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @throws UnknownTypeInterface
     * @throws NotFoundException
     * @throws WrongStructureException
     * @throws AdapterFailedException
     * @throws InstructionFailedException
     */
    public function updateEntity(string $type, string $id)
    {
        if ($this->_logger) {
            $message = 'Indexer update entity "%1$s" with ID "%2$s".';
            $this->_logger->notice(sprintf($message, $type, $id));
        }

        $this->_updateEntityAction->update($type, $id);
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function removeEntity(string $type, string $id)
    {
        if ($this->_logger) {
            $message = 'Indexer remove entity "%1$s" with ID "%2$s".';
            $this->_logger->notice(sprintf($message, $type, $id));
        }

        $this->_removeEntityAction->remove($type, $id);
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $index
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function receiveIds(string $index, int $offset = null, int $limit = null): array
    {
        if ($this->_logger) {
            $message = 'Indexer select IDs from index "%1$s" (%2$s, %3$s).';
            $this->_logger->notice(sprintf($message, $index, (int)$offset, (int)$limit));
        }

        return $this->_receiveIdsAction->receiveIds($index, $offset, $limit);
    }

    /**
     * @param string $index
     * @param string $kind
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function receiveViews(string $index, string $kind, int $offset = null, int $limit = null): array
    {
        if ($this->_logger) {
            $message = 'Indexer receive entities from index "%1$s" with view "%2$s" (%3$s, %4$s).';
            $this->_logger->notice(sprintf($message, $index, $kind, (int)$offset, (int)$limit));
        }

        return $this->_receiveViewsAction->receive($index, $kind, $offset, $limit);
    }

    /**
     * @param string $index
     * @param string $kind
     * @param string $id
     * @return string|null
     */
    public function receiveView(string $index, string $kind, string $id): ?string
    {
        if ($this->_logger) {
            $message = 'Indexer receive entity with ID "%3$s" from index "%1$s" with view "%2$s".';
            $this->_logger->notice(sprintf($message, $index, $kind, $id));
        }

        return $this->_receiveViewAction->receive($index, $kind, $id);
    }
}