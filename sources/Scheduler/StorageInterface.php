<?php

namespace Moro\Indexer\Common\Scheduler;

/**
 * Interface StorageInterface
 * @package Moro\Indexer\Common\Scheduler
 */
interface StorageInterface
{
    /**
     * @param int $timestamp
     * @param EntryInterface $entry
     */
    function defer(int $timestamp, EntryInterface $entry);

    /**
     * @param EntryInterface $entry
     * @return bool
     */
    function derive(EntryInterface $entry): bool;
}