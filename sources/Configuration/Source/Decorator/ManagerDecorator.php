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
	protected $_manager;
    protected $_factory;

	public function __construct(ConfigurationManagerInterface $manager, FactoryInterface $factory)
    {
		$manager->apply($factory);

		$this->_manager = $manager;
        $this->_factory = $factory;
    }

    public function getDecoratedInstance(): SourceManagerInterface
    {
        if (empty($this->_instance)) {
            $this->_instance = $this->_factory->newManager();
			$this->_manager->apply($this->_instance);
        }

        return $this->_instance;
    }
}