<?php

namespace Moro\Indexer\Common\Transaction\Manager;

use Moro\Indexer\Common\Transaction\DriverInterface;
use Moro\Indexer\Common\Transaction\ManagerInterface;
use Moro\Indexer\Common\Transaction\TransactionFacade;
use Psr\Container\ContainerInterface;

/**
 * Class LazyManager
 * @package Moro\Indexer\Common\Transaction\Manager
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
     * @param DriverInterface $driver
     * @return \Moro\Indexer\Common\Transaction\TransactionFacade
     */
    public function register(DriverInterface $driver): TransactionFacade
    {
        return $this->_getManager()
            ->register($driver);
    }

    /**
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    public function execute(callable $callback)
    {
        return $this->_getManager()
            ->execute($callback);
    }

    /**
     * @param \Moro\Indexer\Common\Transaction\TransactionFacade $facade
     * @return bool
     *
     * @see \Moro\Indexer\Common\Transaction\TransactionFacade::activate()
     */
    public function activate(TransactionFacade $facade): bool
    {
        return $this->_getManager()
            ->activate($facade);
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