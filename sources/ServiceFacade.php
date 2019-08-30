<?php

namespace Moro\Indexer\Common;

use Moro\Indexer\Common\Exception\UnknownTypeInterface;
use Moro\Indexer\Common\Regulation\Exception\InstructionFailedException;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use Moro\Indexer\Common\Strategy\ReadStrategyInterface;
use Moro\Indexer\Common\Strategy\WriteStrategyInterface;

/**
 * Class ServiceFacade
 * @package Moro\Indexer\Common
 */
class ServiceFacade
{
    /** @var ReadStrategyInterface */
    protected $reader;
    /** @var WriteStrategyInterface */
    protected $writer;

    /**
     * @param ReadStrategyInterface $readStrategy
     * @param WriteStrategyInterface $writeStrategy
     */
    public function __construct(ReadStrategyInterface $readStrategy, WriteStrategyInterface $writeStrategy)
    {
        $this->setReadStrategy($readStrategy);
        $this->setWriteStrategy($writeStrategy);
    }

    /**
     * @param ReadStrategyInterface $readStrategy
     */
    public function setReadStrategy(ReadStrategyInterface $readStrategy)
    {
        $this->reader = $readStrategy;
    }

    /**
     * @param WriteStrategyInterface $writeStrategy
     */
    public function setWriteStrategy(WriteStrategyInterface $writeStrategy)
    {
        $this->writer = $writeStrategy;
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
        $this->writer->updateEntity($type, $id);
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function removeEntity(string $type, string $id)
    {
        $this->writer->removeEntity($type, $id);
    }

    /**
     * @param string $index
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function receiveIds(string $index, int $offset = null, int $limit = null): array
    {
        return $this->reader->receiveIds($index, $offset, $limit);
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
        return $this->reader->receiveViews($index, $kind, $offset, $limit);
    }

    /**
     * @param string $index
     * @param string $kind
     * @param string $id
     * @return string|null
     */
    public function receiveView(string $index, string $kind, string $id): ?string
    {
        return $this->reader->receiveView($index, $kind, $id);
    }
}