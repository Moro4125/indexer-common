<?php

namespace Moro\Indexer\Common\Strategy\Read;

use Moro\Indexer\Common\Action\ReceiveIdsInterface;
use Moro\Indexer\Common\Action\ReceiveViewInterface;
use Moro\Indexer\Common\Action\ReceiveViewsInterface;
use Moro\Indexer\Common\Strategy\ReadStrategyInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InternalReadStrategy
 * @package Moro\Indexer\Common\Strategy\Read
 */
class InternalReadStrategy implements ReadStrategyInterface
{
    /** @var ReceiveIdsInterface */
    protected $_receiveIdsAction;
    /** @var ReceiveViewInterface */
    protected $_receiveViewAction;
    /** @var ReceiveViewsInterface */
    protected $_receiveViewsAction;
    /** @var null|LoggerInterface */
    protected $_logger;

    /**
     * @param ReceiveIdsInterface $receiveIdsAction
     * @param ReceiveViewInterface $receiveViewAction
     * @param ReceiveViewsInterface $receiveViewsAction
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ReceiveIdsInterface $receiveIdsAction,
        ReceiveViewInterface $receiveViewAction,
        ReceiveViewsInterface $receiveViewsAction,
        LoggerInterface $logger = null
    ) {
        $this->_receiveIdsAction = $receiveIdsAction;
        $this->_receiveViewAction = $receiveViewAction;
        $this->_receiveViewsAction = $receiveViewsAction;
        $this->_logger = $logger;
    }

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