<?php

namespace Moro\Indexer\Common\Configuration\Source;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;
use Moro\Indexer\Common\Configuration\Source\Decorator\TypeDecorator;
use Moro\Indexer\Common\Source\FactoryInterface;

/**
 * Class ManagerConfigurator
 * @package Moro\Indexer\Common\Configuration\Source
 */
class ManagerConfigurator implements ConfiguratorInterface
{
    private $_factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param \Moro\Indexer\Common\Source\ManagerInterface $manager
     */
    public function apply(ConfigurationInterface $configuration, $manager)
    {
        foreach ($configuration->get('types|keys') ?? [] as $code) {
            $type = new TypeDecorator($configuration, $this->_factory, $code);
            $manager->addType($type);
        }
    }
}