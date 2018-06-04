<?php

namespace Moro\Indexer\Common\View\Manager;

use Moro\Indexer\Common\View\Manager\Decorator\AbstractDecorator;
use Moro\Indexer\Common\View\ManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class LazyManager
 * @package Moro\Indexer\Common\View\Manager
 */
class LazyManager extends AbstractDecorator
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    protected $_manager;

    /**
     * @param ContainerInterface $container
     * @param string|null $manager
     */
    public function __construct(ContainerInterface $container, string $manager = null)
    {
        $this->_container = $container;
        $this->_manager = $manager;
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @return ManagerInterface
     */
    public function getDecoratedInstance(): ManagerInterface
    {
        $this->_manager = $this->_manager ?? ManagerInterface::class;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_instance = $this->_instance ?? $this->_container->get($this->_manager);

        return $this->_instance;
    }
}