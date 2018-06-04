<?php

use Moro\Indexer\Common\Scheduler\Entry\SchedulerEntry;
use Moro\Indexer\Common\Scheduler\Manager\LazyManager;
use Moro\Indexer\Common\Scheduler\Manager\SchedulerManager;
use Moro\Indexer\Common\Scheduler\ManagerInterface;
use Moro\Indexer\Common\Scheduler\Storage\MemoryStorage;
use Moro\Indexer\Common\Scheduler\Factory\ContainerFactory;
use Moro\Indexer\Common\Scheduler\Factory\ClassFactory;
use Moro\Indexer\Common\Scheduler\FactoryInterface;
use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Test\SimpleContainer;

/**
 * Class SchedulerTest
 */
class SchedulerTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    const SIMPLE = 'simple';

    public function testSchedulerEntry()
    {
        $entry = new SchedulerEntry();

        verify($entry->setType(self::SIMPLE))->same($entry);
        verify($entry->getType())->same(self::SIMPLE);

        verify($entry->setId('1'))->same($entry);
        verify($entry->getId())->same('1');

        verify($entry->setAction('update'))->same($entry);
        verify($entry->getAction())->same('update');
    }

    public function testSchedulerManager(ManagerInterface $manager = null, FactoryInterface $factory = null)
    {
        /** @var ManagerInterface $manager */
        $manager = $manager ?? new SchedulerManager();

        $this->specify('Work without factory.', function () use ($manager) {
            $this->assertThrows(AssertionError::class, function() use ($manager) {
                $manager->newEntry();
            });
        });

        $this->specify('Work without storage.', function () use ($manager) {
            $this->assertThrows(AssertionError::class, function() use ($manager) {
                $manager->defer(time(), new SchedulerEntry(self::SIMPLE, '0', 'unknown'));
            });
            $this->assertThrows(AssertionError::class, function() use ($manager) {
                $manager->derive();
            });
        });

        $this->specify('Add factory.', function() use ($manager, $factory) {
            $factory = $factory ?? new ClassFactory(SchedulerEntry::class);
            verify($manager->setFactory($factory))->same($manager);
            verify($manager->newEntry())->isInstanceOf(EntryInterface::class);
        });

        $this->specify('Add storage.', function() use ($manager) {
            $storage = new MemoryStorage();
            verify($manager->setStorage($storage))->same($manager);
        });

        $this->specify('Defer and derive single entry', function () use ($manager) {
            $manager->defer(time(), new SchedulerEntry(self::SIMPLE, '1', 'update'));

            $entry = $manager->derive();
            verify($entry)->isInstanceOf(SchedulerEntry::class);
            verify($entry->getType())->same(self::SIMPLE);
            verify($entry->getId())->same('1');
            verify($entry->getAction())->same('update');

            verify($manager->derive())->null();
        });
    }

    public function testLazyManager()
    {
        $container = new SimpleContainer();
        $container->set('manager', new SchedulerManager());
        $container->set('entry', new SchedulerEntry());
        $lazy = new LazyManager($container, 'manager');

        $factory = new ContainerFactory($container, 'entry');

        $this->testSchedulerManager($lazy, $factory);
    }
}