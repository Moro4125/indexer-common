<?php

namespace Moro\Indexer\Common\Dispatcher\Manager;

use Moro\Indexer\Common\Dispatcher\EventInterface;
use Moro\Indexer\Common\Dispatcher\ManagerInterface;
use Moro\Indexer\Common\Dispatcher\MiddlewareInterface;
use Psr\Container\ContainerInterface;

/**
 * Class LazyManager
 * @package Moro\Indexer\Common\Dispatcher\Manager
 */
class LazyManager implements ManagerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    protected $_manager;
    protected $_instance;

    /**
     * @param ContainerInterface $container
     * @param string|null $manager
     */
    public function __construct(ContainerInterface $container, string $manager = null)
    {
        $this->_container = $container;
        $this->_manager = $manager;
    }

    /**
     * @param MiddlewareInterface $middleware
     * @param int|null $priority
     * @return ManagerInterface
     */
    public function wrap(MiddlewareInterface $middleware, int $priority = null): ManagerInterface
    {
        $this->_getManager()
            ->wrap($middleware, $priority);

        return $this;
    }

    /**
     * @param MiddlewareInterface $middleware
     * @return ManagerInterface
     */
    public function unwrap(MiddlewareInterface $middleware): ManagerInterface
    {
        $this->_getManager()
            ->unwrap($middleware);

        return $this;
    }

    /**
     * @param string $event
     * @param callable $listener
     * @param int|null $priority
     * @return $this
     */
    public function attach(string $event, callable $listener, int $priority = null): ManagerInterface
    {
        $this->_getManager()
            ->attach($event, $listener, $priority);

        return $this;
    }

    /**
     * @param string $event
     * @param callable $listener
     * @return $this
     */
    public function detach(string $event, callable $listener): ManagerInterface
    {
        $this->_getManager()
            ->detach($event, $listener);

        return $this;
    }

    /**
     * @return $this
     */
    public function init(): ManagerInterface
    {
        $this->_getManager()
            ->init();

        return $this;
    }

    /**
     * @param EventInterface $event
     * @return ManagerInterface
     */
    public function trigger(EventInterface $event): ManagerInterface
    {
        $this->_getManager()
            ->trigger($event);

        return $this;
    }

    /**
     * @return void
     */
    public function fire()
    {
        $this->_getManager()
            ->fire();
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @return ManagerInterface
     */
    protected function _getManager(): ManagerInterface
    {
        $this->_manager = $this->_manager ?? ManagerInterface::class;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_instance = $this->_instance ?? $this->_container->get($this->_manager);

        return $this->_instance;
    }
}