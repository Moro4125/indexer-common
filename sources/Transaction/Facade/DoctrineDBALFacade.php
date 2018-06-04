<?php

namespace Moro\Indexer\Common\Transaction\Facade;

use Doctrine\DBAL\Statement;
use Moro\Indexer\Common\Transaction\Driver\DoctrineDBALDriver;
use Moro\Indexer\Common\Transaction\TransactionFacade;

/**
 * Class DoctrineDBALFacade
 * @package Moro\Indexer\Common\Transaction\Facade
 */
class DoctrineDBALFacade
{
    private $_driver;
    private $_facade;

    /**
     * @param DoctrineDBALDriver $driver
     * @param TransactionFacade $facade
     */
    public function __construct(DoctrineDBALDriver $driver, TransactionFacade $facade)
    {
        $this->_driver = $driver;
        $this->_facade = $facade;
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $id
     * @param callable $callback
     * @return Statement
     */
    public function statement(string $id, callable $callback): Statement
    {
        return $this->_driver->statement($id, $callback);
    }

    /**
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->_driver->getLastInsertId();
    }

    /**
     * @return bool
     */
    public function activate(): bool
    {
        return $this->_facade->activate();
    }
}