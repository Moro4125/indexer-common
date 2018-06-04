<?php

namespace Moro\Indexer\Common\Bus\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use Moro\Indexer\Common\Bus\AdapterInterface;
use Moro\Indexer\Common\Bus\Exception\CallTimeOutException;
use Moro\Indexer\Common\Integration\DoctrineDBALConst;
use Moro\Indexer\Common\Transaction\Facade\DoctrineDBALFacade;

/**
 * Class DoctrineDBALAdapter
 * @package Moro\Indexer\Common\Bus\Adapter
 */
class DoctrineDBALAdapter implements AdapterInterface, DoctrineDBALConst
{
    /**
     * @var DoctrineDBALFacade
     */
    protected $_facade;

    /**
     * @var int
     */
    protected $_lockTimeout;

    /**
     * @var int
     */
    protected $_callTimeout;

    /**
     * @param DoctrineDBALFacade $facade
     */
    public function __construct(DoctrineDBALFacade $facade)
    {
        $this->_facade = $facade;
        $this->_lockTimeout = 16;
        $this->_callTimeout = 3;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setLockTimeout(int $timeout)
    {
        $this->_lockTimeout = $timeout;

        return $this;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setCallTimeout(int $timeout)
    {
        $this->_callTimeout = $timeout;

        return $this;
    }

    /**
     * @param string $from
     * @param string $identifier
     * @param string $target
     * @param array $message
     * @throws \Doctrine\DBAL\DBALException
     */
    public function send(string $from, string $identifier, string $target, array $message)
    {
        if ($this->_facade) {
            $this->_facade->activate();
        }

        $record = [
            self::COL_BUS_CREATED_AT => time(),
            self::COL_BUS_SENDER     => $from,
            self::COL_BUS_IDENTIFIER => $identifier,
            self::COL_BUS_TARGET     => $target,
            self::COL_BUS_MESSAGE    => json_encode($message),
        ];

        $insert = $this->_facade->statement(__METHOD__, function (Connection $connection) use ($record) {
            $insert = $connection->createQueryBuilder()
                ->insert(self::TABLE_BUS)
                ->values(array_fill_keys(array_keys($record), '?'));

            return $insert->getSQL();
        });

        $insert->execute(array_values($record));
    }

    /**
     * @param string $for
     * @param string|null $identifier
     * @param string|null $from
     * @return array|null
     */
    public function read(string $for, string $identifier = null, string $from = null): ?array
    {
        $message = null;
        $lockedBy = dechex(mt_rand(16, 255)) . dechex(mt_rand(16, 255)) . dechex(mt_rand(16, 255));

        if ($this->_readMessageExists($for, $identifier, $from, $lockedBy)) {
            if ($this->_readMessageLocked($for, $identifier, $from, $lockedBy)) {
                if ($record = $this->_readMessageReceive($lockedBy)) {
                    $message = json_decode($record[self::COL_BUS_MESSAGE], true);
                    $message['bus:locked'] = $lockedBy;

                    $this->_readMessageFree($lockedBy);
                }
            }
        }

        return $message;
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $for
     * @param string $identifier
     * @param string $from
     * @param string $lockedBy
     * @return bool
     */
    private function _readMessageExists($for, $identifier, $from, $lockedBy): bool
    {
        $parameters = $this->_readBuilderParameters($for, $identifier, $from, $lockedBy, false);
        $select = $this->_facade->statement(__METHOD__,
            function (Connection $connection) use ($identifier, $from) {
                return $this->_readCreateSelect($connection, $identifier, $from)
                    ->getSQL();
            });

        /** @noinspection PhpUnhandledExceptionInspection */
        if ($select->execute($parameters) && !$select->fetch()) {
            return false;
        }

        if ($this->_facade) {
            $this->_facade->activate();
        }

        return true;
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $for
     * @param string $identifier
     * @param string $from
     * @param string $lockedBy
     * @return bool
     */
    private function _readMessageLocked($for, $identifier, $from, $lockedBy): bool
    {
        $parameters = $this->_readBuilderParameters($for, $identifier, $from, $lockedBy, true);
        $update = $this->_facade->statement(__METHOD__,
            function (Connection $connection) use ($identifier, $from) {
                return $this->_readCreateUpdate($connection, $identifier, $from)
                    ->getSQL();
            });

        /** @noinspection PhpUnhandledExceptionInspection */
        return $update->execute($parameters) && $update->rowCount();
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $lockedBy
     * @return array|null
     */
    private function _readMessageReceive($lockedBy): ?array
    {
        $select = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $select = $connection->createQueryBuilder()
                ->select('*')
                ->from(self::TABLE_BUS)
                ->where(self::COL_BUS_LOCKED_BY . '=?');

            return $select->getSQL();
        });

        /** @noinspection PhpUnhandledExceptionInspection */
        return $select->execute([$lockedBy]) ? $select->fetch(FetchMode::ASSOCIATIVE) ?: null : null;
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $lockedBy
     */
    private function _readMessageFree($lockedBy)
    {
        $delete = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $delete = $connection->createQueryBuilder()
                ->delete(self::TABLE_BUS)
                ->where(self::COL_BUS_LOCKED_BY . '=?');

            return $delete->getSQL();
        });
        /** @noinspection PhpUnhandledExceptionInspection */
        $delete->execute([$lockedBy]);
    }

    /**
     * @param Connection $connection
     * @param $identifier
     * @param $from
     * @return QueryBuilder
     */
    private function _readCreateSelect(Connection $connection, $identifier, $from): QueryBuilder
    {
        $select = $connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_BUS)
            ->setMaxResults(1);

        $this->_readBuilderAddWhere($select, $identifier, $from);

        return $select;
    }

    /**
     * @param Connection $connection
     * @param $identifier
     * @param $from
     * @return QueryBuilder
     */
    private function _readCreateUpdate(Connection $connection, $identifier, $from): QueryBuilder
    {
        $update = $connection->createQueryBuilder()
            ->update(self::TABLE_BUS)
            ->set(self::COL_BUS_LOCKED_AT, '?')
            ->set(self::COL_BUS_LOCKED_BY, '?')
            ->setMaxResults(1);

        $this->_readBuilderAddWhere($update, $identifier, $from);

        return $update;
    }

    /**
     * @param QueryBuilder $builder
     * @param $identifier
     * @param $from
     */
    private function _readBuilderAddWhere(QueryBuilder $builder, $identifier, $from)
    {
        foreach ([true, false] as $isNull) {
            $conditions = [];
            $conditions[] = $isNull ? $builder->expr()
                ->isNull(self::COL_BUS_LOCKED_AT) : $builder->expr()
                ->isNotNull(self::COL_BUS_LOCKED_AT);

            $conditions[] = $builder->expr()
                ->eq(self::COL_BUS_TARGET, '?');

            if ($identifier) {
                $conditions[] = $builder->expr()
                    ->eq(self::COL_BUS_IDENTIFIER, '?');
            }

            if ($from) {
                $conditions[] = $builder->expr()
                    ->eq(self::COL_BUS_SENDER, '?');
            }

            if (empty($isNull)) {
                $conditions[] = $builder->expr()
                    ->lt(self::COL_BUS_LOCKED_AT, '?');
            }

            $expression = $builder->expr();
            $builder->orWhere(call_user_func_array([$expression, 'andX'], $conditions));
        }
    }

    /**
     * @param $for
     * @param $identifier
     * @param $from
     * @param $lockedBy
     * @param $isUpdate
     * @return array
     */
    private function _readBuilderParameters($for, $identifier, $from, $lockedBy, $isUpdate): array
    {
        $parameters = $isUpdate ? [time(), $lockedBy] : [];

        foreach ([true, false] as $isNull) {
            $parameters[] = $for;

            if ($identifier) {
                $parameters[] = $identifier;
            }

            if ($from) {
                $parameters[] = $from;
            }

            if (empty($isNull)) {
                $parameters[] = time() - $this->_lockTimeout;
            }
        }

        return $parameters;
    }

    /**
     * @param string $from
     * @param string $identifier
     * @param string $target
     * @param array $message
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function call(string $from, string $identifier, string $target, array $message): array
    {
        $transaction = $this->_facade;

        try {
            $start = microtime(true);
            $this->_facade = null;

            $this->send($from, $identifier, $target, $message);

            while (!$result = $this->read($from, $identifier, $target)) {
                if (microtime(true) - $start > $this->_callTimeout) {
                    throw new CallTimeOutException(sprintf(CallTimeOutException::MSG, json_encode($message)));
                }

                usleep(mt_rand(100000, 250000)); // 1000000 = 1 sec
            }
        }
        finally {
            $this->_facade = $transaction;
        }

        return $result;
    }
}