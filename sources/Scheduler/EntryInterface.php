<?php

namespace Moro\Indexer\Common\Scheduler;

/**
 * Interface EntryInterface
 * @package Moro\Indexer\Common\Scheduler
 */
interface EntryInterface
{
    function setType(string $type): EntryInterface;

    function getType(): string;

    function setId(string $id): EntryInterface;

    function getId(): string;

    function setAction(string $action): EntryInterface;

    function getAction(): string;
}