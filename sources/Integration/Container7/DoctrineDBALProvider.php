<?php

namespace Moro\Indexer\Common\Integration\Container7;

use Doctrine\DBAL\Connection;
use Moro\Container7\Aliases;
use Moro\Container7\Container;
use Moro\Container7\Parameters;
use Moro\Container7\Tags;
use Moro\Indexer\Common\Bus\Adapter\DoctrineDBALAdapter as BusAdapter;
use Moro\Indexer\Common\Dispatcher\Manager\LazyManager as DispatcherLazyManager;
use Moro\Indexer\Common\Index\Storage\DoctrineDBALStorage as IndexStorage;
use Moro\Indexer\Common\Scheduler\Storage\Decorator\DoctrineRepeatDecorator as SchedulerRepeatDecorator;
use Moro\Indexer\Common\Scheduler\Storage\DoctrineDBALStorage as SchedulerStorage;
use Moro\Indexer\Common\Scheduler\StorageInterface as SchedulerStorageInterface;
use Moro\Indexer\Common\Source\Adapter\DoctrineDBALAdapter as SourceAdapter;
use Moro\Indexer\Common\Source\AdapterInterface as SourceAdapterInterface;
use Moro\Indexer\Common\Action\CheckEntity\Decorator\DoctrineIgnoreDecorator as CheckIgnoreDecorator;
use Moro\Indexer\Common\Action\CheckEntityInterface;
use Moro\Indexer\Common\Action\RemoveEntity\Decorator\DoctrineRepeatDecorator as RemoveRepeatDecorator;
use Moro\Indexer\Common\Action\RemoveEntityInterface;
use Moro\Indexer\Common\Action\UpdateEntity\Decorator\DoctrineRepeatDecorator as UpdateRepeatDecorator;
use Moro\Indexer\Common\Action\UpdateEntityInterface;
use Moro\Indexer\Common\Transaction\Driver\DoctrineDBALDriver;
use Moro\Indexer\Common\Transaction\Facade\DoctrineDBALFacade;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManagerInterface;
use Moro\Indexer\Common\Transaction\TransactionFacade;
use Moro\Indexer\Common\View\Storage\DoctrineDBALStorage as ViewStorage;

/**
 * Class DoctrineDBALProvider
 * @package Moro\Indexer\Common\Integration\Container7
 */
class DoctrineDBALProvider
{
    const A_TRANSACTION = 'indexer.transaction.doctrine-dbal.facade';

    const P_SOURCE_TYPE_2_TABLE   = 'indexer/source/type2table';
    const P_SOURCE_ALIAS_2_COLUMN = 'indexer/source/alias2column';

    public function parameters(Parameters $parameters)
    {
        $parameters->append([
            CommonProvider::P_BUS_ADAPTER_CLASS       => BusAdapter::class,
            CommonProvider::P_SOURCE_ADAPTER_CLASS    => SourceAdapter::class,
            CommonProvider::P_INDEX_STORAGE_CLASS     => IndexStorage::class,
            CommonProvider::P_SCHEDULER_STORAGE_CLASS => SchedulerStorage::class,
            CommonProvider::P_VIEW_STORAGE_CLASS      => ViewStorage::class,
            self::P_SOURCE_TYPE_2_TABLE               => [],
            self::P_SOURCE_ALIAS_2_COLUMN             => [
                '_default' => ['id' => 'id', 'timestamp' => 'updated_at'],
            ],
        ]);
    }

    public function aliases(Aliases $aliases)
    {
        $aliases->add(self::A_TRANSACTION, TransactionFacade::class);
    }

    public function tags(Tags $tags)
    {
        $tags->add(SourceAdapterInterface::class, SourceAdapter::class);
    }

    public function driver(Connection $connection): DoctrineDBALDriver
    {
        return new DoctrineDBALDriver($connection);
    }

    public function transaction(TransactionManagerInterface $manager, DoctrineDBALDriver $driver): TransactionFacade
    {
        return $manager->register($driver);
    }

    public function facade(Container $container, DoctrineDBALDriver $driver): DoctrineDBALFacade
    {
        /** @var TransactionFacade $facade */
        $facade = $container->get(self::A_TRANSACTION);

        return new DoctrineDBALFacade($driver, $facade);
    }

    public function busAdapter(DoctrineDBALFacade $facade): BusAdapter
    {
        return new BusAdapter($facade);
    }

    public function sourceAdapter(DoctrineDBALFacade $facade, Parameters $parameters, ...$arguments): SourceAdapter
    {
        $type = array_shift($arguments);
        $type2table = $parameters->get(self::P_SOURCE_TYPE_2_TABLE);
        $alias2column = $parameters->get(self::P_SOURCE_ALIAS_2_COLUMN);
        $table = $type2table[$type] ?? $type;
        $aliases = $alias2column[$type] ?? $alias2column['_default'];

        return new SourceAdapter($facade, $table, $aliases);
    }

    public function indexStorage(DoctrineDBALFacade $facade): IndexStorage
    {
        return new IndexStorage($facade);
    }

    public function schedulerStorage(DoctrineDBALFacade $facade): SchedulerStorage
    {
        return new SchedulerStorage($facade);
    }

    public function viewStorage(DoctrineDBALFacade $facade): ViewStorage
    {
        return new ViewStorage($facade);
    }

    public function updateEntityActionDecorator(
        UpdateEntityInterface $strategy,
        DispatcherLazyManager $events
    ): ?UpdateEntityInterface {
        return new UpdateRepeatDecorator($strategy, $events);
    }

    public function removeEntityActionDecorator(
        RemoveEntityInterface $strategy,
        DispatcherLazyManager $events
    ): ?RemoveEntityInterface {
        return new RemoveRepeatDecorator($strategy, $events);
    }

    public function checkEntityActionDecorator(
        CheckEntityInterface $strategy,
        DispatcherLazyManager $events
    ): ?CheckEntityInterface {
        return new CheckIgnoreDecorator($strategy, $events);
    }

    public function schedulerStorageDecorator(SchedulerStorageInterface $storage): ?SchedulerStorageInterface
    {
        return new SchedulerRepeatDecorator($storage);
    }
}