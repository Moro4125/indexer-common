<?php

namespace Moro\Indexer\Common\Strategy\Write;

use Moro\Indexer\Common\Action\RemoveEntityInterface;
use Moro\Indexer\Common\Action\UpdateEntityInterface;
use Moro\Indexer\Common\Exception\UnknownTypeInterface;
use Moro\Indexer\Common\Regulation\Exception\InstructionFailedException;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use Moro\Indexer\Common\Strategy\WriteStrategyInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InternalWriteStrategy
 * @package Moro\Indexer\Common\Strategy\Write
 */
class InternalWriteStrategy implements WriteStrategyInterface
{
    /** @var UpdateEntityInterface */
    protected $_updateEntityAction;
    /** @var RemoveEntityInterface */
    protected $_removeEntityAction;
    /** @var null|LoggerInterface */
    protected $_logger;

    /**
     * @param UpdateEntityInterface $updateAction
     * @param RemoveEntityInterface $removeAction
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        UpdateEntityInterface $updateAction,
        RemoveEntityInterface $removeAction,
        LoggerInterface $logger = null
    ) {
        $this->_updateEntityAction = $updateAction;
        $this->_removeEntityAction = $removeAction;
        $this->_logger = $logger;
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
}