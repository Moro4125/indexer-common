<?php

namespace Moro\Indexer\Test\Container7;

use Moro\Container7\Definition;
use Moro\Container7\Parameters;
use Moro\Container7\Tags;
use Moro\Indexer\Common\Index\Storage\MemoryStorage as IndexMemoryStorage;
use Moro\Indexer\Common\Integration\Container7\CommonProvider;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Scheduler\Storage\MemoryStorage as SchedulerMemoryStorage;
use Moro\Indexer\Common\Source\Adapter\MemoryAdapter as SourceMemoryAdapter;
use Moro\Indexer\Common\Source\AdapterInterface as SourceAdapterInterface;
use Moro\Indexer\Common\View\KindInterface;
use Moro\Indexer\Common\View\Storage\MemoryStorage as ViewMemoryStorage;
use Moro\Indexer\Test\DummyAdapter;
use Moro\Indexer\Test\SimpleEntity;
use Moro\Indexer\Test\SimpleInstruction;
use Moro\Indexer\Test\SimpleKind;

/**
 * Class MemoryTestProvider
 * @package Moro\Indexer\Test\Container7
 */
class MemoryTestProvider
{
    const SIMPLE = 'simple';

    public function parameters(Parameters $parameters)
    {
        $parameters->append([
            CommonProvider::P_TYPES                   => [self::SIMPLE => SimpleEntity::class],
            CommonProvider::P_BUS_ADAPTER_CLASS       => DummyAdapter::class,
            CommonProvider::P_SOURCE_ADAPTER_CLASS    => SourceMemoryAdapter::class,
            CommonProvider::P_INDEX_STORAGE_CLASS     => IndexMemoryStorage::class,
            CommonProvider::P_VIEW_STORAGE_CLASS      => ViewMemoryStorage::class,
            CommonProvider::P_SCHEDULER_STORAGE_CLASS => SchedulerMemoryStorage::class,
        ]);
    }

    public function tags(Tags $tags)
    {
        $tags->add(self::SIMPLE, 'simpleInstruction1');
        $tags->add(self::SIMPLE, 'simpleInstruction2');
        $tags->add(self::SIMPLE, 'simpleInstruction3');
        $tags->add(self::SIMPLE, 'simpleKind1');
    }

    public function definition(Definition $d)
    {
        $d->addTuner(SourceAdapterInterface::class, null, null, SourceMemoryAdapter::class);
    }

    public function initSourceMemoryStorage(SourceMemoryAdapter $adapter)
    {
        $adapter->addEntityRecord('1', 10, ['id' => '1', 'name' => 'Alpha', 'updated_at' => '10']);
        $adapter->addEntityRecord('2', 30, ['id' => '2', 'name' => 'Echo', 'updated_at' => '30']);
        $adapter->addEntityRecord('3', 40, ['id' => '3', 'name' => 'Victor', 'updated_at' => '40']);
        $adapter->addEntityRecord('4', 50, ['id' => '4', 'name' => 'Sierra', 'updated_at' => '50']);
        $adapter->addEntityRecord('5', 20, ['id' => '5', 'name' => 'Whiskey', 'updated_at' => '20']);
        $adapter->addEntityRecord('6', 60, ['id' => '6', 'name' => 'November', 'updated_at' => '60']);
        $adapter->addEntityRecord('7', 11, ['id' => null]); // test wrong structure.
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
}