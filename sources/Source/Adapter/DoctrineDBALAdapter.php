<?php

namespace Moro\Indexer\Common\Source\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Moro\Indexer\Common\Integration\DoctrineDBALConst;
use Moro\Indexer\Common\Source\AdapterInterface;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Transaction\Driver\DoctrineDBALDriver;
use Moro\Indexer\Common\Transaction\Facade\DoctrineDBALFacade;

/**
 * Class DoctrineDBALAdapter
 * @package Moro\Indexer\Common\Source\Adapter
 */
class DoctrineDBALAdapter implements AdapterInterface, DoctrineDBALConst
{
    /**
     * @var DoctrineDBALFacade
     */
    protected $_facade;

    /**
     * @var string
     */
    protected $_table;

    /**
     * @var array
     */
    protected $_alias2column;

    /**
     * @param DoctrineDBALDriver $facade
     * @param string $table
     * @param array $alias2column
     */
    public function __construct(DoctrineDBALFacade $facade, string $table = null, array $alias2column = null)
    {
        $this->_facade = $facade;
        $this->_table = $table;
        $this->_alias2column = $alias2column ?? ['id' => 'id', 'timestamp' => 'updated_at'];
    }

    /**
     * @param array $table
     * @return $this
     */
    public function setTable(array $table)
    {
        $this->_table = $table;

        return $this;
    }

    /**
     * @param array $alias2column
     * @return $this
     */
    public function setAlias2Column(array $alias2column)
    {
        $this->_alias2column = $alias2column;

        return $this;
    }

    /**
     * @param int $from
     * @param int $limit
     * @return array ID => TIMESTAMP
     * @throws DBALException
     */
    public function receiveIdList(int $from, int $limit): array
    {
        $select = $this->_facade->statement(__METHOD__, function (Connection $connection) use ($from, $limit) {
            $select = $connection->createQueryBuilder()
                ->select($this->_alias2column['id'], $this->_alias2column['timestamp'])
                ->from($this->_table)
                ->setFirstResult($from)
                ->setMaxResults($limit);

            return $select->getSQL();
        });

        $result = $select->execute() ? $select->fetchAll(FetchMode::NUMERIC) : [];

        return array_column($result, 1, 0);
    }

    /**
     * @param string $id
     * @return array
     *
     * @throws DBALException
     * @throws NotFoundException
     */
    public function receiveEntityById(string $id): array
    {
        $select = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $select = $connection->createQueryBuilder()
                ->select('*')
                ->from($this->_table)
                ->where($this->_alias2column['id'] . '=?')
                ->setMaxResults(1);

            return $select->getSQL();
        });

        if ($select->execute([$id]) && $record = $select->fetch(FetchMode::ASSOCIATIVE)) {
            return $record;
        }

        throw new NotFoundException(sprintf(NotFoundException::MSG, $id, $this->_table));
    }
}