<?php

namespace Moro\Indexer\Common\Scheduler;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\Scheduler
 */
interface ManagerInterface
{
    /**
     * @param FactoryInterface $factory
     * @return ManagerInterface
     */
    function setFactory(FactoryInterface $factory): ManagerInterface;

    /**
     * @param StorageInterface $storage
     * @return $this
     */
    function setStorage(StorageInterface $storage): ManagerInterface;

    /**
     * @return EntryInterface
     */
    function newEntry(): EntryInterface;

    /**
     * @param int $timestamp
     * @param EntryInterface $entry
     */
    function defer(int $timestamp, EntryInterface $entry);

    /**
     * @return EntryInterface|null
     */
    function derive(): ?EntryInterface;
}