<?php

namespace Moro\Indexer\Common\Transaction\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Moro\Indexer\Common\Transaction\DriverInterface;

/**
 * Class DoctrineDBALDriver
 * @package Moro\Indexer\Common\Transaction\Driver
 */
class DoctrineDBALDriver implements DriverInterface
{
    /**
     * @var Connection
     */
    protected $_connection;

    /**
     * @var Statement[]
     */
    protected $_statements;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $id
     * @param callable $callback
     * @return Statement
     */
    public function statement(string $id, callable $callback): Statement
    {
        if (empty($this->_statements[$id])) {
            $statementOrSql = $callback($this->_connection);
            /** @noinspection PhpUnhandledExceptionInspection */
            $statement = is_string($statementOrSql) ? $this->_connection->prepare($statementOrSql) : $statementOrSql;

            assert($statement instanceof Statement);

            if (is_array($this->_statements)) {
                $this->_statements[$id] = $statement;
            }
        } else {
            $statement = $this->_statements[$id];
        }

        return $statement;
    }

    /**
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->_connection->lastInsertId();
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->_statements = [];
    }

    /**
     * @internal
     * @return void
     */
    public function begin()
    {
        $this->_connection->beginTransaction();
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function commit()
    {
        if ($this->_connection->isRollbackOnly()) {
            $this->_connection->rollBack();
        } else {
            $this->_connection->commit();
        }
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function rollback()
    {
        $this->_connection->rollBack();
    }

    /**
     * @return void
     */
    public function free()
    {
        $this->_statements = null;
    }
}