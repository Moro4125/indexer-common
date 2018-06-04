<?php

namespace Moro\Indexer\Common\Transaction;

/**
 * Interface DriverInterface
 * @package Moro\Indexer\Common\Transaction
 */
interface DriverInterface
{
    /**
     * @return void
     */
    function init();

    /**
     * @return void
     */
    function begin();

    /**
     * @return void
     */
    function commit();

    /**
     * @return void
     */
    function rollback();

    /**
     * @return void
     */
    function free();
}