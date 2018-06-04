<?php

namespace Moro\Indexer\Common\Configuration\View;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;
use Moro\Indexer\Common\Configuration\View\Decorator\TypeDecorator;
use Moro\Indexer\Common\View\FactoryInterface;

/**
 * Class ManagerConfigurator
 * @package Moro\Indexer\Common\Configuration\View
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
     * @param \Moro\Indexer\Common\View\ManagerInterface $manager
     */
    public function apply(ConfigurationInterface $configuration, $manager)
    {
        foreach ($configuration->get('types|keys') as $code) {
            $type = new TypeDecorator($configuration, $this->_factory, $code);
            $manager->addType($type);
        }
    }
}