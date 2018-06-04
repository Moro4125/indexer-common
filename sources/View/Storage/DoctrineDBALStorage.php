<?php

namespace Moro\Indexer\Common\View\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Moro\Indexer\Common\Integration\DoctrineDBALConst;
use Moro\Indexer\Common\Transaction\Facade\DoctrineDBALFacade;
use Moro\Indexer\Common\View\StorageInterface;

/**
 * Class DoctrineDBALStorage
 * @package Moro\Indexer\Common\View\Storage
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
     * @param string $type
     * @param string $id
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function find(string $type, string $id): array
    {
        $this->_normalizeString($type);
        $this->_normalizeString($id);

        $statement = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $select = $connection->createQueryBuilder()
                ->select(self::COL_VIEW_KIND_ID)
                ->from(self::TABLE_VIEW)
                ->where(self::COL_VIEW_TYPE_ID . '=?')
                ->andWhere(self::COL_VIEW_ENTITY_ID . '=?');

            return $connection->prepare($select->getSQL());
        });

        return $statement->execute([$type, $id]) ? $statement->fetchAll(FetchMode::COLUMN) : [];
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @param string $content
     * @throws \Doctrine\DBAL\DBALException
     */
    public function save(string $type, string $kind, string $id, string $content)
    {
        $this->_normalizeString($type);
        $this->_normalizeString($kind);
        $this->_normalizeString($id);

        $this->_facade->activate();
        $driver = $this->_facade;

        $update = $driver->statement(__METHOD__ . __LINE__, function (Connection $connection) {
            $update = $connection->createQueryBuilder()
                ->update(self::TABLE_VIEW)
                ->set(self::COL_VIEW_CONTENT, '?')
                ->set(self::COL_VIEW_UPDATED_AT, '?')
                ->where(self::COL_VIEW_TYPE_ID . '=?')
                ->andWhere(self::COL_VIEW_KIND_ID . '=?')
                ->andWhere(self::COL_VIEW_ENTITY_ID . '=?');

            return $connection->prepare($update->getSQL());
        });

        if (!$update->execute([$content, time(), $type, $kind, $id]) || !$update->rowCount()) {
            $record = [
                self::COL_VIEW_TYPE_ID    => $type,
                self::COL_VIEW_KIND_ID    => $kind,
                self::COL_VIEW_ENTITY_ID  => $id,
                self::COL_VIEW_CONTENT    => $content,
                self::COL_VIEW_UPDATED_AT => time(),
            ];

            $insert = $driver->statement(__METHOD__ . __LINE__, function (Connection $connection) use ($record) {
                $insert = $connection->createQueryBuilder()
                    ->insert(self::TABLE_VIEW)
                    ->values(array_fill_keys(array_keys($record), '?'));

                return $connection->prepare($insert->getSQL());
            });
            $insert->execute(array_values($record));
        }
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return null|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function load(string $type, string $kind, string $id): ?string
    {
        $this->_normalizeString($type);
        $this->_normalizeString($kind);
        $this->_normalizeString($id);

        $select = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $select = $connection->createQueryBuilder()
                ->select(self::COL_VIEW_CONTENT)
                ->from(self::TABLE_VIEW)
                ->where(self::COL_VIEW_TYPE_ID . '=?')
                ->andWhere(self::COL_VIEW_KIND_ID . '=?')
                ->andWhere(self::COL_VIEW_ENTITY_ID . '=?');

            return $connection->prepare($select->getSQL());
        });

        return $select->execute([$type, $kind, $id]) ? $select->fetchColumn() ?: null : null;
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function drop(string $type, string $kind, string $id): bool
    {
        $this->_normalizeString($type);
        $this->_normalizeString($kind);
        $this->_normalizeString($id);

        $this->_facade->activate();

        $delete = $this->_facade->statement(__METHOD__, function (Connection $connection) {
            $delete = $connection->createQueryBuilder()
                ->delete(self::TABLE_VIEW)
                ->where(self::COL_VIEW_TYPE_ID . '=?')
                ->andWhere(self::COL_VIEW_KIND_ID . '=?')
                ->andWhere(self::COL_VIEW_ENTITY_ID . '=?');

            return $connection->prepare($delete->getSQL());
        });

        return $delete->execute([$type, $kind, $id]) && $delete->rowCount() > 0;
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
}