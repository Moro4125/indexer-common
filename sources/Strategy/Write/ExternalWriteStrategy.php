<?php

namespace Moro\Indexer\Common\Strategy\Write;

use Moro\Indexer\Common\Strategy\WriteStrategyInterface;
use Moro\Indexer\Common\Bus\ManagerInterface as BusManager;
use Psr\Log\LoggerInterface;

/**
 * Class ExternalWriteStrategy
 * @package Moro\Indexer\Common\Strategy\Write
 */
class ExternalWriteStrategy implements WriteStrategyInterface
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
     * @param string $id
     */
    public function updateEntity(string $type, string $id)
    {
        $message = ['action' => 'update', 'type' => $type, 'id' => $id];

        if ($this->_logger) {
            $msg = 'Indexer client send message %1$s.';
            $this->_logger->notice(sprintf($msg, json_encode($message)));
        }

        $this->_busManager->send($message);
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function removeEntity(string $type, string $id)
    {
        $message = ['action' => 'remove', 'type' => $type, 'id' => $id];

        if ($this->_logger) {
            $msg = 'Indexer client send message %1$s.';
            $this->_logger->notice(sprintf($msg, json_encode($message)));
        }

        $this->_busManager->send($message);
    }
}