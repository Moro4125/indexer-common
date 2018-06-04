<?php

namespace Moro\Indexer\Common\Configuration\Source\Decorator;

use Moro\Indexer\Common\Configuration\ManagerInterface as ConfigurationManagerInterface;
use Moro\Indexer\Common\Source\FactoryInterface;
use Moro\Indexer\Common\Source\Manager\Decorator\AbstractDecorator;
use Moro\Indexer\Common\Source\ManagerInterface as SourceManagerInterface;

/**
 * Class ManagerDecorator
 * @package Moro\Indexer\Common\Configuration\Source\Decorator
 */
class ManagerDecorator extends AbstractDecorator
{
    protected $_configuration;
    protected $_factory;

    public function __construct(ConfigurationManagerInterface $configuration, FactoryInterface $factory)
    {
        $configuration->apply($factory);

        $this->_configuration = $configuration;
        $this->_factory = $factory;
    }

    public function getDecoratedInstance(): SourceManagerInterface
    {
        if (empty($this->_instance)) {
            $this->_instance = $this->_factory->newManager();
            $this->_configuration->apply($this->_instance);
        }

        return $this->_instance;
    }
}