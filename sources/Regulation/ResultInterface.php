<?php

namespace Moro\Indexer\Common\Regulation;

/**
 * Interface CollectionInterface
 * @package Moro\Indexer\Common\Regulation
 */
interface ResultInterface
{
    /**
     * @param string $index
     * @param string $order
     * @return ResultInterface
     */
    function addToIndex(string $index, string $order): ResultInterface;

    /**
     * @param int $timestamp
     * @return ResultInterface
     */
    function addToScheduler(int $timestamp): ResultInterface;

    /**
     * @param string $kind
     * @return ResultInterface
     */
    function addUsedKind(string $kind): ResultInterface;

    /**
     * @return int
     */
    function getIndexListCount(): int;

    /**
     * @return \Iterator
     */
    function getIndexListIterator(): \Iterator;

    /**
     * @return int
     */
    function getEntryListCount(): int;

    /**
     * @return \Iterator
     */
    function getEntryListIterator(): \Iterator;

    /**
     * @return int
     */
    function getKindListCount(): int;

    /**
     * @return \Iterator
     */
    function getKindListIterator(): \Iterator;
}