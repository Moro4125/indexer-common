<?php

namespace Moro\Indexer\Common\Bus;

/**
 * Interface AdapterInterface
 * @package Moro\Indexer\Common\Bus
 */
interface AdapterInterface
{
    /**
     * @param string $from
     * @param string $identifier
     * @param string $target
     * @param array $message
     * @return array
     */
    function call(string $from, string $identifier, string $target, array $message): array;

    /**
     * @param string $from
     * @param string $identifier
     * @param string $target
     * @param array $message
     */
    function send(string $from, string $identifier, string $target, array $message);

    /**
     * @param string $for
     * @param string $identifier
     * @param string|null $from
     * @return array|null
     */
    function read(string $for, string $identifier = null, string $from = null): ?array;
}