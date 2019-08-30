<?php

namespace Moro\Indexer\Common\Strategy\Read;

use Moro\Indexer\Common\Strategy\ReadStrategyInterface;
use Moro\Indexer\Common\Bus\ManagerInterface as BusManager;
use Psr\Log\LoggerInterface;

/**
 * Class ExternalReadStrategy
 * @package Moro\Indexer\Common\Strategy\Read
 */
class ExternalReadStrategy implements ReadStrategyInterface
{
    /** @var BusManager */
    protected $_busManager;
    /** @var null|LoggerInterface */
    protected $_logger;

    /**
     * @param BusManager $bus
     * @param LoggerInterface|null $logger
     */
    public function __construct(BusManager $bus, LoggerInterface $logger = null)
    {
        $this->_busManager = $bus;
        $this->_logger = $logger;

        $bus->setOwner('client');
        $bus->setTarget('backend');
    }

    /**
     * @param string $type
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function receiveIds(string $type, int $offset = null, int $limit = null): array
    {
        $message = ['action' => 'select', 'type' => $type, 'offset' => $offset, 'limit' => $limit];

        if ($this->_logger) {
            $msg = 'Indexer client send message %1$s.';
            $this->_logger->notice(sprintf($msg, json_encode($message)));
        }

        $result = $this->_busManager->call($message);

        return $result['ids'] ?? [];
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
        $message = ['action' => 'query', 'index' => $index, 'kind' => $kind, 'offset' => $offset, 'limit' => $limit];

        if ($this->_logger) {
            $msg = 'Indexer client send message %1$s.';
            $this->_logger->notice(sprintf($msg, json_encode($message)));
        }

        $result = $this->_busManager->call($message);

        return $result['ids'] ?? [];
    }

    /**
     * @param string $index
     * @param string $kind
     * @param string $id
     * @return string|null
     */
    public function receiveView(string $index, string $kind, string $id): ?string
    {
        $message = ['action' => 'query', 'index' => $index, 'kind' => $kind, 'id' => $id];

        if ($this->_logger) {
            $msg = 'Indexer client send message %1$s.';
            $this->_logger->notice(sprintf($msg, json_encode($message)));
        }

        $result = $this->_busManager->call($message);

        return $result['view'] ?? null;
    }
}