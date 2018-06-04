<?php

namespace Moro\Indexer\Common\Scheduler\Storage;

use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\StorageInterface;

/**
 * Class MemoryStorage
 * @package Moro\Indexer\Common\Scheduler\Storage
 */
class MemoryStorage implements StorageInterface
{
    protected $_schedule = [];
    /** @var EntryInterface[] */
    protected $_entries = [];
    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param int $timestamp
     * @param EntryInterface $entry
     */
    public function defer(int $timestamp, EntryInterface $entry)
    {
        $this->_entries[] = $entry;
        $index = array_search($entry, $this->_entries);
        $this->_schedule[$index] = $timestamp;

        asort($this->_schedule, SORT_ASC);
    }

    /**
     * @param EntryInterface $entry
     * @return bool
     */
    public function derive(EntryInterface $entry): bool
    {
        $timestamp = reset($this->_schedule);

        if (time() >= $timestamp && null !== $index = key($this->_schedule)) {
            $entry->setType($this->_entries[$index]->getType());
            $entry->setId($this->_entries[$index]->getId());
            $entry->setAction($this->_entries[$index]->getAction());

            unset($this->_schedule[$index]);
            unset($this->_entries[$index]);

            return true;
        }

        return false;
    }
}