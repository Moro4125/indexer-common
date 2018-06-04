<?php

namespace Moro\Indexer\Common\Transaction;

use Moro\Indexer\Common\Transaction\Exception\DuplicateDriverException;
use Moro\Indexer\Common\Transaction\Exception\UnknownFacadeException;

/**
 * Interface ManagerInterface
 * @package Moro\Indexer\Common\Transaction
 */
interface ManagerInterface
{
    /**
     * @param DriverInterface $driver
     * @return TransactionFacade
     *
     * @throws DuplicateDriverException
     */
    function register(DriverInterface $driver): TransactionFacade;

    /**
     * @param callable $callback
     * @return mixed
     */
    function execute(callable $callback);

    /**
     * @param TransactionFacade $facade
     * @return bool
     *
     * @throws UnknownFacadeException
     */
    function activate(TransactionFacade $facade): bool;
}