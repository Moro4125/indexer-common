<?php

namespace Moro\Indexer\Common\Configuration;

/**
 * Interface ConfiguratorInterface
 * @package Moro\Indexer\Common\Configuration
 */
interface ConfiguratorInterface
{
    /**
     * @param ConfigurationInterface $configuration
     * @param mixed $object
     */
    function apply(ConfigurationInterface $configuration, $object);
}