<?php

namespace Moro\Indexer\Common\Configuration\Regulation\Decorator;

use Moro\Indexer\Common\Configuration\ManagerInterface as ConfigurationManagerInterface;
use Moro\Indexer\Common\Regulation\FactoryInterface;
use Moro\Indexer\Common\Regulation\Manager\Decorator\AbstractDecorator;
use Moro\Indexer\Common\Regulation\ManagerInterface as RegulationManagerInterface;

/**
 * Class ManagerDecorator
 * @package Moro\Indexer\Common\Configuration\Regulation\Decorator
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

    public function getDecoratedInstance(): RegulationManagerInterface
    {
        if (empty($this->_instance)) {
            $this->_instance = $this->_factory->newManager();
            $this->_configuration->apply($this->_instance);
        }

        return $this->_instance;
    }
}