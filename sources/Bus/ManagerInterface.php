<?php

namespace Moro\Indexer\Common\Bus;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\Bus
 */
interface ManagerInterface
{
    /**
     * @param AdapterInterface $adapter
     * @return $this
     */
    function setAdapter(AdapterInterface $adapter): ManagerInterface;

    /**
     * @param string $owner
     * @return ManagerInterface
     */
    function setOwner(string $owner): ManagerInterface;

    /**
     * @param string $target
     * @return ManagerInterface
     */
    function setTarget(string $target): ManagerInterface;

    /**
     * @param string $id
     * @return ManagerInterface
     */
    function setIdentifier(string $id): ManagerInterface;

    /**
     * @param array $message
     * @param string|null $target
     * @return array
     */
    function call(array $message, string $target = null): array;

    /**
     * @param array $message
     * @param string|null $target
     */
    function send(array $message, string $target = null);

    /**
     * @param string|null $for
     * @param string|null $from
     * @return array|null
     */
    function read(string $for = null, string $from = null): ?array;
}