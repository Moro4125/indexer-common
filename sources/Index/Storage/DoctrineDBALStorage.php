<?php

namespace Moro\Indexer\Common\Index\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Moro\Indexer\Common\Index\StorageInterface;
use Moro\Indexer\Common\Integration\DoctrineDBALConst;
use Moro\Indexer\Common\Transaction\Facade\DoctrineDBALFacade;

/**
 * Class DoctrineDBALStorage
 * @package Moro\Indexer\Common\Index\Storage
 */
class DoctrineDBALStorage implements StorageInterface, DoctrineDBALConst
{
    /**
     * @var DoctrineDBALFacade
     */
    protected $_facade;

    /**
     * @param DoctrineDBALFacade $facade
     */
    public function __construct(DoctrineDBALFacade $facade)
    {
        $this->_facade = $facade;
    }

    /**
     * @param int $index
     * @return null|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function hasAlias(int $index): ?string
    {
        $select = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $select = $connection->createQueryBuilder()
                ->select(self::COL_INDEX_LIST_NAME)
                ->from(self::TABLE_INDEX_LIST)
                ->where(self::COL_INDEX_LIST_ID . '=?');

            return $connection->prepare($select->getSQL());
        });

        return $select->execute([$index]) ? $select->fetchColumn() ?: null : null;
    }

    /**
     * @param string $alias
     * @return int|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function hasIndex(string $alias): ?int
    {
        $this->_normalizeString($alias);

        $select = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $select = $connection->createQueryBuilder()
                ->select(self::COL_INDEX_LIST_ID)
                ->from(self::TABLE_INDEX_LIST)
                ->where(self::COL_INDEX_LIST_NAME . '=?');

            return $connection->prepare($select->getSQL());
        });

        return $select->execute([$alias]) ? $select->fetchColumn() ?: null : null;
    }

    /**
     * @param string $alias
     * @param string $type
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addIndex(string $alias, string $type): int
    {
        $this->_normalizeString($alias);
        $this->_normalizeString($type);
        $this->_facade->activate();

        $record = [
            self::COL_INDEX_LIST_NAME    => $alias,
            self::COL_INDEX_LIST_TYPE_ID => $type,
        ];

        $insert = $this->_facade->statement(__METHOD__, function (Connection $connection) use ($record) {
            $insert = $connection->createQueryBuilder()
                ->insert(self::TABLE_INDEX_LIST)
                ->values(array_fill_keys(array_keys($record), '?'));

            return $connection->prepare($insert->getSQL());
        });

        return $insert->execute(array_values($record)) ? (int)$this->_facade->getLastInsertId() : 0;
    }

    /**
     * @param string $type
     * @param string|null $id
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findIndexes(string $type, string $id = null): array
    {
        $this->_normalizeString($type);
        $this->_normalizeString($id);

        $parameters = [];
        $statementId = __METHOD__ . (isset($id) ? 'A' : 'B');

        $select = $this->_facade->statement($statementId, function (Connection $connection) use ($id) {
            $select = $connection->createQueryBuilder()
                ->select('t1.' . self::COL_INDEX_LIST_NAME)
                ->from(self::TABLE_INDEX_LIST, 't1');

            $select->where('t1.' . self::COL_INDEX_LIST_TYPE_ID . '=?');

            if (isset($id)) {
                $condition = 't1.' . self::COL_INDEX_LIST_ID . ' = ' . 't2.' . self::COL_INDEX_DATA_INDEX_ID;
                $select->innerJoin('t1', self::TABLE_INDEX_DATA, 't2', $condition);
                $select->andWhere('t2.' . self::COL_INDEX_DATA_ENTITY_ID . '=?');
            }

            return $connection->prepare($select->getSQL());
        });

        $parameters[] = $type;

        if (isset($id)) {
            $parameters[] = $id;
        }

        return $select->execute($parameters) ? $select->fetchAll(FetchMode::COLUMN) ?: [] : [];
    }

    /**
     * @param int $index
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function dropIndex(int $index): bool
    {
        $this->_facade->activate();

        $delete = $this->_facade->statement(__METHOD__ . __LINE__, function (Connection $connection) {
            $delete = $connection->createQueryBuilder()
                ->delete(self::TABLE_INDEX_DATA)
                ->where(self::COL_INDEX_DATA_INDEX_ID . '=?');

            return $connection->prepare($delete->getSQL());
        });

        $count = $delete->execute([$index]) ? $delete->rowCount() : 0;

        $delete = $this->_facade->statement(__METHOD__ . __LINE__, function (Connection $connection) {
            $delete = $connection->createQueryBuilder()
                ->delete(self::TABLE_INDEX_LIST)
                ->where(self::COL_INDEX_LIST_ID . '=?');

            return $connection->prepare($delete->getSQL());
        });

        $count += $delete->execute([$index]) ? $delete->rowCount() : 0;

        return !empty($count);
    }

    /**
     * @param string $alias
     * @return null|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTypeByIndex(string $alias): ?string
    {
        $this->_normalizeString($alias);

        $select = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $select = $connection->createQueryBuilder()
                ->select(self::COL_INDEX_LIST_TYPE_ID)
                ->from(self::TABLE_INDEX_LIST)
                ->where(self::COL_INDEX_LIST_NAME . '=?');

            return $connection->prepare($select->getSQL());
        });

        return $select->execute([$alias]) ? $select->fetchColumn() ?: null : null;
    }

    /**
     * @param int $index
     * @param string $id
     * @param string|null $order
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insert(int $index, string $id, string $order = null)
    {
        $this->_normalizeString($id);
        $this->_normalizeOrder($order);

        $this->_facade->activate();
        $driver = $this->_facade;

        $update = $driver->statement(__METHOD__ . __LINE__, function (Connection $connection) {
            $update = $connection->createQueryBuilder()
                ->update(self::TABLE_INDEX_DATA)
                ->set(self::COL_INDEX_DATA_ORDER, '?')
                ->set(self::COL_INDEX_DATA_UPDATED_AT, '?')
                ->where(self::COL_INDEX_DATA_INDEX_ID . '=?')
                ->andWhere(self::COL_INDEX_DATA_ENTITY_ID . '=?');

            return $connection->prepare($update->getSQL());
        });

        if (!$update->execute([$order, time(), $index, $id]) || !$update->rowCount()) {
            $record = [
                self::COL_INDEX_DATA_INDEX_ID   => $index,
                self::COL_INDEX_DATA_ENTITY_ID  => $id,
                self::COL_INDEX_DATA_ORDER      => $order,
                self::COL_INDEX_DATA_UPDATED_AT => time(),
            ];

            $insert = $driver->statement(__METHOD__ . __LINE__, function (Connection $connection) use ($record) {
                $insert = $connection->createQueryBuilder()
                    ->insert(self::TABLE_INDEX_DATA)
                    ->values(array_fill_keys(array_keys($record), '?'));

                return $connection->prepare($insert->getSQL());
            });

            $insert->execute(array_values($record));
        }
    }

    /**
     * @param int $index
     * @param int|null $from
     * @param int|null $limit
     * @param bool|null $withOrder
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function select(int $index, int $from = null, int $limit = null, bool $withOrder = null): array
    {
        $statementId = __METHOD__ . ':' . $from . ':' . $limit;
        $fetchMode = $withOrder ? FetchMode::NUMERIC : FetchMode::COLUMN;

        $select = $this->_facade->statement($statementId, function (Connection $connection) use ($from, $limit) {
            $select = $connection->createQueryBuilder()
                ->select(self::COL_INDEX_DATA_ENTITY_ID, self::COL_INDEX_DATA_ORDER)
                ->from(self::TABLE_INDEX_DATA)
                ->where(self::COL_INDEX_DATA_INDEX_ID . '=?')
                ->orderBy(self::COL_INDEX_DATA_ORDER);

            if ($from !== null) {
                $select->setFirstResult($from);
            }

            if ($limit !== null) {
                $select->setMaxResults($limit);
            }

            return $connection->prepare($select->getSQL());
        });

        $result = $select->execute([$index]) ? $select->fetchAll($fetchMode) : [];

        return $withOrder ? array_column($result, 1, 0) : $result;
    }

    /**
     * @param int $index
     * @param string $id
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function remove(int $index, string $id): bool
    {
        $this->_normalizeString($id);
        $this->_facade->activate();

        $delete = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $delete = $connection->createQueryBuilder()
                ->delete(self::TABLE_INDEX_DATA)
                ->where(self::COL_INDEX_DATA_INDEX_ID . '=?')
                ->andWhere(self::COL_INDEX_DATA_ENTITY_ID . '=?');

            return $connection->prepare($delete->getSQL());
        });

        return $delete->execute([$index, $id]) ? $delete->rowCount() > 0 : false;
    }

    /**
     * @param string $string
     */
    protected function _normalizeString(string &$string)
    {
        if (preg_match('/(?>^.{41,})/', $string)) {
            $string = sha1($string);
        }
    }

    /**
     * @param string $string
     */
    protected function _normalizeOrder(string &$string)
    {
        if (preg_match('/((?>^.{40}))./', $string, $match)) {
            $string = $match[1];
        }
    }
}