<?php

namespace Moro\Indexer\Common\Configuration\Manager;

use Moro\Indexer\Common\Configuration\AdapterInterface;
use Moro\Indexer\Common\Configuration\Configuration\Configuration;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;
use Moro\Indexer\Common\Configuration\ManagerInterface;
use Moro\Indexer\Common\DecoratorInterface;
use ReflectionObject;

/**
 * Class ConfigurationManager
 * @package Moro\Indexer\Common\Configuration\Manager
 */
class ConfigurationManager implements ManagerInterface
{
    /** @var AdapterInterface */
    private $_adapter;
    /** @var ConfiguratorInterface[] */
    private $_configurators;
    /** @var ConfiguratorInterface */
    private $_configuration;

    /**
     * @param AdapterInterface $adapter
     * @return ManagerInterface
     */
    public function setAdapter(AdapterInterface $adapter): ManagerInterface
    {
        $this->_adapter = $adapter;
        $this->_configuration = null;

        return $this;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        assert($this->_adapter);

        return $this->_adapter;
    }

    /**
     * @param string $class
     * @param ConfiguratorInterface $configurator
     * @return ManagerInterface
     */
    public function addConfigurator(string $class, ConfiguratorInterface $configurator): ManagerInterface
    {
        $this->_configurators[$class] = $configurator;

        return $this;
    }

    /**
     * @param $object
     * @param null|array $context
     * @return bool
     */
    public function apply($object, array $context = null): bool
    {
        assert($this->_adapter);

        $this->_configuration || $this->_configuration = new Configuration($this->_adapter->load());
        $oldContext = $this->_configuration->getContext();

        $this->_configuration->setContext($context);
        $this->_configuration->setManager($this);

        $flag = false;
        $used = [];

        try {
            while ($object) {
                $reflection = new ReflectionObject($object);

                foreach ($reflection->getInterfaceNames() as $interface) {
                    if (empty($used[$interface]) && $configurator = $this->_configurators[$interface] ?? null) {
                        $used[$interface] = true;
                        $configurator->apply($this->_configuration, $object);
                        $flag = true;
                    }
                }

                while ($reflection) {
                    $class = $reflection->getName();

                    if (empty($used[$class]) && $configurator = $this->_configurators[$class] ?? null) {
                        $used[$class] = true;
                        $configurator->apply($this->_configuration, $object);
                        $flag = true;
                    }

                    $reflection = $reflection->getParentClass();
                }

                $object = ($object instanceof DecoratorInterface) ? $object->getDecoratedInstance() : null;
            }
        }
        finally {
            $this->_configuration->setContext($oldContext);
        }

        return $flag;
    }
}