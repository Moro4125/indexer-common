<?php

namespace Moro\Indexer\Test\Container7;

use Moro\Container7\Definition;
use Moro\Container7\Parameters;
use Moro\Indexer\Common\Index\Storage\MemoryStorage as IndexMemoryStorage;
use Moro\Indexer\Common\Integration\Container7\CommonProvider;
use Moro\Indexer\Common\Integration\Container7\ConfigurationProvider;
use Moro\Indexer\Common\Scheduler\Storage\MemoryStorage as SchedulerMemoryStorage;
use Moro\Indexer\Common\Source\Adapter\MemoryAdapter as SourceMemoryAdapter;
use Moro\Indexer\Common\Source\AdapterInterface as SourceAdapterInterface;
use Moro\Indexer\Common\View\Storage\MemoryStorage as ViewMemoryStorage;
use Moro\Indexer\Test\DummyAdapter;

/**
 * Class ConfigurationTestProvider
 * @package Moro\Indexer\Test\Container7
 */
class ConfigurationTestProvider
{
    const SIMPLE = 'simple';

    public function parameters(Parameters $parameters)
    {
        $parameters->append([
            CommonProvider::P_BUS_ADAPTER_CLASS       => DummyAdapter::class,
            CommonProvider::P_SOURCE_ADAPTER_CLASS    => SourceMemoryAdapter::class,
            CommonProvider::P_INDEX_STORAGE_CLASS     => IndexMemoryStorage::class,
            CommonProvider::P_VIEW_STORAGE_CLASS      => ViewMemoryStorage::class,
            CommonProvider::P_SCHEDULER_STORAGE_CLASS => SchedulerMemoryStorage::class,
        ]);

        $parameters->append([
            ConfigurationProvider::P_CONFIGURATION => [
                'types' => [
                    'simple' => [
                        'instructions' => [
                            [
                                'conditions' => [
                                    '[name="Alpha"]',
                                ],
                                'variables' => [
                                    'updated_at' => 'updated_at'
                                ],
                                'indexes' => [
                                    'first' => '{updated_at}',
                                ],
                                'kinds' => ['json'],
                            ],
                            [
                                'conditions' => [
                                    '[name="Alpha"]|merge([name="Echo"])',
                                ],
                                'variables' => [
                                    'updated_at' => 'updated_at'
                                ],
                                'indexes' => [
                                    'second' => '{updated_at}',
                                ],
                                'kinds' => ['json'],
                            ],
                            [
                                'conditions' => [
                                    '[name="Echo"]|merge([name="Sierra"])|merge([name="Whiskey"])',
                                ],
                                'variables' => [
                                    'updated_at' => 'updated_at'
                                ],
                                'indexes' => [
                                    'third' => '{updated_at}',
                                ],
                                'kinds' => ['json'],
                            ],
                        ],
                        'kinds' => [
                            'json' => [
                                'parameters' => [
                                    'id' => 'id',
                                    'name' => 'name',
                                    'updated_at' => 'updated_at',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
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
}