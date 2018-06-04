<?php

namespace Moro\Indexer\Common\Configuration;

/**
 * Interface AdapterInterface
 * @package Moro\Indexer\Common\Configuration
 */
interface AdapterInterface
{
    /**
     * @return array
     */
    function load(): array;

    /**
     * @param array $configuration
     * @return bool
     */
    function save(array $configuration): bool;
}