<?php

namespace Moro\Indexer\Common\Regulation\Result;

use Moro\Indexer\Common\Regulation\ResultInterface;

/**
 * Class RegulationCollection
 * @package Moro\Indexer\Common\Regulation\Collection
 */
class RegulationResult implements ResultInterface
{
    protected $_toIndex;
    protected $_toScheduler;
    protected $_usedKinds;

    /**
     * @param string $index
     * @param string $order
     * @return ResultInterface
     */
    public function addToIndex(string $index, string $order): ResultInterface
    {
        $this->_toIndex[$index] = $order;
        return $this;
    }

    /**
     * @param int $timestamp
     * @return ResultInterface
     */
    public function addToScheduler(int $timestamp): ResultInterface
    {
        $this->_toScheduler[$timestamp] = $timestamp;
        return $this;
    }

    public function addUsedKind(string $kind): ResultInterface
    {
        $this->_usedKinds[$kind] = $kind;
        return $this;
    }

    /**
     * @return int
     */
    public function getIndexListCount(): int
    {
        return $this->_toIndex ? count($this->_toIndex) : 0;
    }

    /**
     * @return \Iterator
     */
    public function getIndexListIterator(): \Iterator
    {
        return new \ArrayIterator($this->_toIndex ?? []);
    }

    /**
     * @return int
     */
    public function getEntryListCount(): int
    {
        return $this->_toScheduler ? count($this->_toScheduler) : 0;
    }

    /**
     * @return \Iterator
     */
    public function getEntryListIterator(): \Iterator
    {
        return new \ArrayIterator($this->_toScheduler ?? []);
    }

    /**
     * @return int
     */
    public function getKindListCount(): int
    {
        return $this->_usedKinds ? count($this->_usedKinds) : 0;
    }

    /**
     * @return \Iterator
     */
    public function getKindListIterator(): \Iterator
    {
        return new \ArrayIterator($this->_usedKinds ?? []);
    }
}