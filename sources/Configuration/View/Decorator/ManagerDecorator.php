<?php

namespace Moro\Indexer\Common\Configuration\View\Decorator;

use Moro\Indexer\Common\Configuration\ManagerInterface as ConfigurationManagerInterface;
use Moro\Indexer\Common\View\FactoryInterface;
use Moro\Indexer\Common\View\Manager\Decorator\AbstractDecorator;
use Moro\Indexer\Common\View\ManagerInterface as ViewManagerInterface;

/**
 * Class ManagerDecorator
 * @package Moro\Indexer\Common\Configuration\View\Decorator
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

    public function getDecoratedInstance(): ViewManagerInterface
    {
        if (empty($this->_instance)) {
            $this->_instance = $this->_factory->newManager();
            $this->_configuration->apply($this->_instance);
        }

        return $this->_instance;
    }
}