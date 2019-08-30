<?php

namespace Moro\Indexer\Common\Strategy;

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
use Moro\Indexer\Common\Bus\ManagerInterface as BusManager;
use Moro\Indexer\Common\Dispatcher\Event\ExceptionIgnoreEvent;
use Moro\Indexer\Common\Dispatcher\Event\ExceptionRepairedEvent;
use Moro\Indexer\Common\Dispatcher\Event\MessageIsDerivedEvent;
use Moro\Indexer\Common\Dispatcher\Event\SchedulerDeriveEvent;
use Moro\Indexer\Common\Dispatcher\Event\WaitRandomTickEvent;
use Moro\Indexer\Common\Scheduler\EntryInterface;


/**
 * Interface ReadStrategyInterface
 * @package Moro\Indexer\Common\Strategy
 */
interface ReadStrategyInterface
{
    /**
     * @param string $index
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    function receiveIds(string $index, int $offset = null, int $limit = null);

    /**
     * @param string $index
     * @param string $kind
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    function receiveViews(string $index, string $kind, int $offset = null, int $limit = null): array;

    /**
     * @param string $index
     * @param string $kind
     * @param string $id
     * @return string|null
     */
    function receiveView(string $index, string $kind, string $id): ?string;
}