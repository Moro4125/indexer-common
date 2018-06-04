<?php

namespace Moro\Indexer\Common\Configuration;

/**
 * Interface ConfigurationInterface
 * @package Moro\Indexer\Common\Configuration
 */
interface ConfigurationInterface
{
    /**
     * @param array|null $context
     * @return ConfigurationInterface
     */
    function setContext(?array $context): ConfigurationInterface;

    /**
     * @return array|null
     */
    function getContext(): ?array;

    /**
     * @param string $key
     * @return bool
     */
    function has(string $key): bool;

    /**
     * @param string $key
     * @return mixed
     */
    function get(string $key);

    /**
     * @param $object
     * @param array|null $context
     * @return bool
     */
    function apply($object, array $context = null): bool;
}