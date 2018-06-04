<?php

namespace Moro\Indexer\Common\Transaction\Manager;

use Moro\Indexer\Common\Transaction\DriverInterface;
use Moro\Indexer\Common\Transaction\Exception\DuplicateDriverException;
use Moro\Indexer\Common\Transaction\Exception\UnknownFacadeException;
use Moro\Indexer\Common\Transaction\ManagerInterface;
use Moro\Indexer\Common\Transaction\TransactionFacade;

/**
 * Class TransactionManager
 * @package Moro\Indexer\Common\Transaction\Manager
 */
class TransactionManager implements ManagerInterface
{
    protected $_map;
    protected $_used;
    protected $_stack;
    protected $_drivers;
    protected $_executed;

    /**
     * TransactionManager constructor.
     */
    public function __construct()
    {
        $this->_map = new \SplObjectStorage();
        $this->_used = new \SplObjectStorage();
        $this->_stack = [];
        $this->_drivers = [];
    }

    /**
     * @param DriverInterface $driver
     * @return \Moro\Indexer\Common\Transaction\TransactionFacade
     */
    public function register(DriverInterface $driver): TransactionFacade
    {
        if ($this->_map->contains($driver)) {
            throw new DuplicateDriverException(DuplicateDriverException::MSG);
        }

        $facade = new TransactionFacade($this);
        $this->_map->attach($facade, $driver);
        $this->_map->attach($driver);
        $this->_drivers[] = $driver;

        return $facade;
    }

    /**
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
     */
    public function execute(callable $callback)
    {
        if ($this->_executed) {
            return $callback();
        }

        /** @var DriverInterface $driver */
        foreach ($this->_drivers as $driver) {
            $driver->init();
        }

        $result = null;
        $this->_executed = true;

        try {
            $result = $callback();

            while ($facade = array_pop($this->_stack)) {
                /** @var DriverInterface $driver */
                $driver = $this->_map->offsetGet($facade);
                $this->_used->detach($facade);
                $driver->commit();
            }
        } catch (\Throwable $exception) {
            while ($facade = array_pop($this->_stack)) {
                /** @var DriverInterface $driver */
                $driver = $this->_map->offsetGet($facade);
                $this->_used->detach($facade);
                $driver->rollback();
            }

            throw $exception;
        }
        finally {
            $this->_executed = null;

            /** @var DriverInterface $driver */
            foreach ($this->_drivers as $driver) {
                $driver->free();
            }
        }

        return $result;
    }

    /**
     * @param \Moro\Indexer\Common\Transaction\TransactionFacade $facade
     * @return bool
     *
     * @see \Moro\Indexer\Common\Transaction\TransactionFacade::activate()
     */
    public function activate(TransactionFacade $facade): bool
    {
        if (!$this->_map->contains($facade)) {
            throw new UnknownFacadeException(UnknownFacadeException::MSG);
        }

        if ($this->_executed && !$this->_used->contains($facade)) {
            /** @var DriverInterface $driver */
            $driver = $this->_map->offsetGet($facade);
            $driver->begin();
            $this->_used->attach($facade);
            array_push($this->_stack, $facade);
        }

        return (bool)$this->_executed;
    }
}