<?php

namespace Moro\Indexer\Common\Scheduler\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Moro\Indexer\Common\Integration\DoctrineDBALConst;
use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\StorageInterface;
use Moro\Indexer\Common\Transaction\Facade\DoctrineDBALFacade;

/**
 * Class DoctrineDBALStorage
 * @package Moro\Indexer\Common\Scheduler\Storage
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
     * @param int $timestamp
     * @param EntryInterface $entry
     * @throws \Doctrine\DBAL\DBALException
     */
    public function defer(int $timestamp, EntryInterface $entry)
    {
        $this->_facade->activate();

        $record = [
            self::COL_SCHEDULER_ORDER     => $timestamp,
            self::COL_SCHEDULER_TYPE_ID   => $entry->getType(),
            self::COL_SCHEDULER_ENTITY_ID => $entry->getId(),
            self::COL_SCHEDULER_ACTION    => $entry->getAction(),
        ];

        if (!$this->_deferRecordExists($record)) {
            $this->_deferInsertRecord($record);
        }
    }

    /**
     * @param array $record
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function _deferRecordExists(array $record): bool
    {
        $select = $this->_facade->statement(__METHOD__, function (Connection $connection) use ($record) {
            $select = $connection->createQueryBuilder()
                ->select('*')
                ->from(self::TABLE_SCHEDULER);

            foreach (array_keys($record) as $index => $column) {
                if ($index) {
                    $select->andWhere($column . '=?');
                } else {
                    $select->where($column . '=?');
                }
            }

            return $select->getSQL();
        });

        return $select->execute(array_values($record)) && $select->fetch();
    }

    /**
     * @param array $record
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function _deferInsertRecord(array $record)
    {
        $insert = $this->_facade->statement(__METHOD__, function (Connection $connection) use ($record) {
            $insert = $connection->createQueryBuilder()
                ->insert(self::TABLE_SCHEDULER)
                ->values(array_fill_keys(array_keys($record), '?'));

            return $insert->getSQL();
        });

        $insert->execute(array_values($record));
    }

    /**
     * @param EntryInterface $entry
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function derive(EntryInterface $entry): bool
    {
        if (($record = $this->_derivedEntryExists()) && $record[self::COL_SCHEDULER_ORDER] <= time()) {
            $this->_facade->activate();

            if ($this->_derivedEntryLocked($record)) {
                $entry->setAction($record[self::COL_SCHEDULER_ACTION]);
                $entry->setType($record[self::COL_SCHEDULER_TYPE_ID]);
                $entry->setId($record[self::COL_SCHEDULER_ENTITY_ID]);

                return true;
            }
        }

        return false;
    }

    /**
     * @return array|null
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function _derivedEntryExists(): ?array
    {
        $result = null;
        $parameters = $this->_deriveBuilderSelectParameters();
        $select = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            return $this->_deriveCreateSelect($connection)
                ->getSQL();
        });

        if ($select->execute($parameters) && !$result = $select->fetch()) {
            return null;
        }

        return $result;
    }

    /**
     * @param Connection $connection
     * @return QueryBuilder
     */
    protected function _deriveCreateSelect(Connection $connection): QueryBuilder
    {
        $select = $connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_SCHEDULER)
            ->orderBy(self::COL_SCHEDULER_ORDER, 'ASC')
            ->setMaxResults(1);

        return $select;
    }

    /**
     * @return array
     */
    protected function _deriveBuilderSelectParameters(): array
    {
        $parameters = [];

        return $parameters;
    }

    /**
     * @param array $record
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function _derivedEntryLocked(array $record): bool
    {
        $parameters = $this->_deriveBuilderDeleteParameters($record);
        $update = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            return $this->_deriveCreateDelete($connection)
                ->getSQL();
        });

        return $update->execute($parameters) && $update->rowCount();
    }

    /**
     * @param Connection $connection
     * @return QueryBuilder
     */
    protected function _deriveCreateDelete(Connection $connection): QueryBuilder
    {
        $update = $connection->createQueryBuilder()
            ->delete(self::TABLE_SCHEDULER);

        $update->andWhere(self::COL_SCHEDULER_ACTION . '=?');
        $update->andWhere(self::COL_SCHEDULER_TYPE_ID . '=?');
        $update->andWhere(self::COL_SCHEDULER_ENTITY_ID . '=?');
        $update->andWhere(self::COL_SCHEDULER_ORDER . '=?');

        return $update;
    }

    /**
     * @param $record
     * @return array
     */
    protected function _deriveBuilderDeleteParameters(array $record = null): array
    {
        $parameters = [];

        $parameters[] = $record[self::COL_SCHEDULER_ACTION];
        $parameters[] = $record[self::COL_SCHEDULER_TYPE_ID];
        $parameters[] = $record[self::COL_SCHEDULER_ENTITY_ID];
        $parameters[] = $record[self::COL_SCHEDULER_ORDER];

        return $parameters;
    }
}