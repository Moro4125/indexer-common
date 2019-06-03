<?php

namespace Moro\Indexer\Common\Configuration;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\Configuration
 */
interface ManagerInterface
{
    /**
     * @param AdapterInterface $adapter
     * @return ManagerInterface
     */
    function setAdapter(AdapterInterface $adapter): ManagerInterface;

    /**
     * @return AdapterInterface
     */
    function getAdapter(): AdapterInterface;

    /**
     * @param string $class
     * @param ConfiguratorInterface $configurator
     * @return ManagerInterface
     */
    function addConfigurator(string $class, ConfiguratorInterface $configurator): ManagerInterface;

    /**
     * @param $object
     * @param null|array $context
     * @return int
     */
    function apply($object, array $context = null): int;
}