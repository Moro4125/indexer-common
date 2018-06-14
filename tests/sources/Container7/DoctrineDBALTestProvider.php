<?php

namespace Moro\Indexer\Test\Container7;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Types\Type;
use Moro\Container7\Parameters;
use Moro\Container7\Tags;
use Moro\Indexer\Common\Integration\Container7\DoctrineDBALProvider;
use Moro\Indexer\Common\Integration\DoctrineDBALConst;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\View\KindInterface;
use Moro\Indexer\Test\SimpleInstruction;
use Moro\Indexer\Test\SimpleKind;

/**
 * Class DoctrineDBALTestProvider
 * @package Moro\Indexer\Test\Container7
 */
class DoctrineDBALTestProvider
{
    const SIMPLE = 'simple';

    public function tags(Tags $tags)
    {
        $tags->add(self::SIMPLE, 'simpleInstruction1');
        $tags->add(self::SIMPLE, 'simpleInstruction2');
        $tags->add(self::SIMPLE, 'simpleInstruction3');
        $tags->add(self::SIMPLE, 'simpleKind1');
    }

    public function parameters(Parameters $parameters)
    {
        $parameters->set(DoctrineDBALProvider::P_SOURCE_TYPE_2_TABLE, [
            self::SIMPLE => 'simple',
        ]);
    }

    public function connection(EventManager $eventManager): Connection
    {
        $params = [
            'driver' => 'pdo_sqlite',
            'memory' => true,
            'path'   => ':memory:',
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        return DriverManager::getConnection($params, null, $eventManager);
    }

    public function events(): EventManager
    {
        return new EventManager();
    }

    public function listeners(EventManager $manager)
    {
        $manager->addEventListener(Events::postConnect, $this);
    }

    public function simpleInstruction1(): InstructionInterface
    {
        $required = ['Alpha'];
        $target = ['first'];
        $kind = ['json'];

        return new SimpleInstruction($required, $target, $kind);
    }

    public function simpleInstruction2(): InstructionInterface
    {
        $required = ['Alpha', 'Echo'];
        $target = ['second'];
        $kind = ['json'];

        return new SimpleInstruction($required, $target, $kind);
    }

    public function simpleInstruction3(): InstructionInterface
    {
        $required = ['Echo', 'Sierra', 'Whiskey'];
        $target = ['third'];
        $kind = ['json'];

        return new SimpleInstruction($required, $target, $kind);
    }

    public function simpleKind1(): KindInterface
    {
        return new SimpleKind('json');
    }

    public function postConnect(ConnectionEventArgs $args)
    {
        $connection = $args->getConnection();
        /** @noinspection PhpUnhandledExceptionInspection */
        $platform = $connection->getDatabasePlatform();
        $schema = $connection->getSchemaManager()
            ->createSchema();

        $table = $schema->createTable(DoctrineDBALConst::TABLE_BUS);
        $table->addColumn(DoctrineDBALConst::COL_BUS_SENDER, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_BUS_IDENTIFIER, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_BUS_TARGET, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_BUS_MESSAGE, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_BUS_LOCKED_BY, Type::STRING)->setNotnull(false);
        $table->addColumn(DoctrineDBALConst::COL_BUS_LOCKED_AT, Type::INTEGER)->setNotnull(false);
        $table->addColumn(DoctrineDBALConst::COL_BUS_CREATED_AT, Type::INTEGER);

        $table = $schema->createTable(DoctrineDBALConst::TABLE_INDEX_TYPE);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_TYPE_ID, Type::INTEGER)
            ->setAutoincrement(true);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_TYPE_NAME, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_TYPE_UPDATED_AT, Type::INTEGER);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_TYPE_LOCKED_BY, Type::INTEGER)->setNotnull(false);
        $table->setPrimaryKey([DoctrineDBALConst::COL_INDEX_TYPE_ID]);
        $table->addUniqueIndex([DoctrineDBALConst::COL_INDEX_TYPE_NAME]);

        $table = $schema->createTable(DoctrineDBALConst::TABLE_INDEX_LIST);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_LIST_ID, Type::INTEGER)
            ->setAutoincrement(true);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_LIST_NAME, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_LIST_TYPE_ID, Type::INTEGER);
        $table->setPrimaryKey([DoctrineDBALConst::COL_INDEX_LIST_ID]);

        $table = $schema->createTable(DoctrineDBALConst::TABLE_INDEX_DATA);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_DATA_INDEX_ID, Type::INTEGER);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_DATA_ENTITY_ID, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_DATA_ORDER, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_DATA_UPDATED_AT, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_INDEX_DATA_VERSION, Type::INTEGER);

        $table = $schema->createTable(DoctrineDBALConst::TABLE_SCHEDULER);
        $table->addColumn(DoctrineDBALConst::COL_SCHEDULER_TYPE_ID, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_SCHEDULER_ENTITY_ID, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_SCHEDULER_ACTION, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_SCHEDULER_ORDER, Type::INTEGER);

        $table = $schema->createTable(DoctrineDBALConst::TABLE_VIEW);
        $table->addColumn(DoctrineDBALConst::COL_VIEW_TYPE_ID, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_VIEW_ENTITY_ID, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_VIEW_KIND_ID, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_VIEW_CONTENT, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_VIEW_UPDATED_AT, Type::STRING);
        $table->addColumn(DoctrineDBALConst::COL_VIEW_VERSION, Type::INTEGER);

        $table = $schema->createTable(self::SIMPLE);
        $table->addColumn('id', Type::INTEGER)
            ->setAutoincrement(true);
        $table->addColumn('name', Type::STRING);
        $table->addColumn('updated_at', Type::INTEGER);

        foreach ($schema->toSql($platform) as $sql) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $connection->executeUpdate($sql);
        }

        $data = [
            self::SIMPLE => [
                ['id' => 1, 'name' => 'Alpha', 'updated_at' => 10],
                ['id' => 2, 'name' => 'Echo', 'updated_at' => 30],
                ['id' => 3, 'name' => 'Victor', 'updated_at' => 40],
                ['id' => 4, 'name' => 'Sierra', 'updated_at' => 50],
                ['id' => 5, 'name' => 'Whiskey', 'updated_at' => 20],
                ['id' => 6, 'name' => 'November', 'updated_at' => 60],
                ['id' => 7, 'name' => '', 'updated_at' => 0],
            ],
        ];

        foreach ($data as $table => $records) {
            foreach ($records as $record) {
                $insert = $connection->createQueryBuilder()
                    ->insert($table)
                    ->values(array_fill_keys(array_keys($record), '?'));
                /** @noinspection PhpUnhandledExceptionInspection */
                $connection->executeUpdate($insert->getSQL(), array_values($record));
            }
        }
    }
}