<?php

namespace Moro\Indexer\Common\Integration\Symfony4\DependencyInjection;

use Doctrine\DBAL\Connection;
use Moro\Indexer\Common\Bus\Adapter\DoctrineDBALAdapter as BusAdapter;
use Moro\Indexer\Common\Bus\AdapterInterface as BusAdapterInterface;
use Moro\Indexer\Common\Event\Manager\LazyManager as EventLazyManager;
use Moro\Indexer\Common\Index\Storage\DoctrineDBALStorage as IndexStorage;
use Moro\Indexer\Common\Index\StorageInterface as IndexStorageInterface;
use Moro\Indexer\Common\Scheduler\Storage\Decorator\DoctrineRepeatDecorator as SchedulerRepeatDecorator;
use Moro\Indexer\Common\Scheduler\Storage\DoctrineDBALStorage as SchedulerStorage;
use Moro\Indexer\Common\Scheduler\StorageInterface as SchedulerStorageInterface;
use Moro\Indexer\Common\Source\Adapter\DoctrineDBALAdapter as SourceAdapter;
use Moro\Indexer\Common\Source\AdapterInterface as SourceAdapterInterface;
use Moro\Indexer\Common\Strategy\CheckEntity\Decorator\DoctrineIgnoreDecorator as CheckIgnoreDecorator;
use Moro\Indexer\Common\Strategy\CheckEntityInterface;
use Moro\Indexer\Common\Strategy\RemoveEntity\Decorator\DoctrineRepeatDecorator as RemoveRepeatDecorator;
use Moro\Indexer\Common\Strategy\RemoveEntityInterface;
use Moro\Indexer\Common\Strategy\UpdateEntity\Decorator\DoctrineRepeatDecorator as UpdateRepeatDecorator;
use Moro\Indexer\Common\Strategy\UpdateEntityInterface;
use Moro\Indexer\Common\Transaction\Driver\DoctrineDBALDriver;
use Moro\Indexer\Common\Transaction\Facade\DoctrineDBALFacade;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManagerInterface;
use Moro\Indexer\Common\Transaction\TransactionFacade;
use Moro\Indexer\Common\View\Storage\DoctrineDBALStorage as ViewStorage;
use Moro\Indexer\Common\View\StorageInterface as ViewStorageInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class IndexerDoctrineDBALExtension
 * @package Moro\Indexer\Common\Integration\Symfony4\DependencyInjection
 */
class IndexerDoctrineDBALExtension extends Extension implements CompilerPassInterface, PrependExtensionInterface
{
    const A_TRANSACTION = 'indexer_transaction_doctrine_dbal_facade';

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('indexer_common', [
            IndexerCommonConfiguration::P_BUS_ADAPTER_CLASS       => BusAdapter::class,
            IndexerCommonConfiguration::P_SOURCE_ADAPTER_CLASS    => SourceAdapter::class,
            IndexerCommonConfiguration::P_INDEX_STORAGE_CLASS     => IndexStorage::class,
            IndexerCommonConfiguration::P_SCHEDULER_STORAGE_CLASS => SchedulerStorage::class,
            IndexerCommonConfiguration::P_VIEW_STORAGE_CLASS      => ViewStorage::class,
        ]);
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Transaction manager.

        $container->register(DoctrineDBALDriver::class)
            ->setShared(true)
            ->addArgument(new Reference(Connection::class));

        $container->register(self::A_TRANSACTION, TransactionFacade::class)
            ->setShared(true)
            ->setFactory([new Reference(TransactionManagerInterface::class), 'register'])
            ->addArgument(new Reference(DoctrineDBALDriver::class));

        $container->register(DoctrineDBALFacade::class, DoctrineDBALFacade::class)
            ->setShared(true)
            ->addArgument(new Reference(DoctrineDBALDriver::class))
            ->addArgument(new Reference(self::A_TRANSACTION));

        // Strategy decorators.

        $container->register(UpdateRepeatDecorator::class)
            ->setDecoratedService(UpdateEntityInterface::class, UpdateRepeatDecorator::class . '.inner', 13)
            ->addArgument(new Reference(UpdateRepeatDecorator::class . '.inner'))
            ->addArgument(new Reference(EventLazyManager::class));

        $container->register(RemoveRepeatDecorator::class)
            ->setDecoratedService(RemoveEntityInterface::class, RemoveRepeatDecorator::class . '.inner')
            ->addArgument(new Reference(RemoveRepeatDecorator::class . '.inner'))
            ->addArgument(new Reference(EventLazyManager::class));

        $container->register(CheckIgnoreDecorator::class)
            ->setDecoratedService(CheckEntityInterface::class, CheckIgnoreDecorator::class . '.inner')
            ->addArgument(new Reference(CheckIgnoreDecorator::class . '.inner'))
            ->addArgument(new Reference(EventLazyManager::class));

        // Storage decorators.

        $container->register(SchedulerRepeatDecorator::class)
            ->setDecoratedService(SchedulerStorageInterface::class, SchedulerRepeatDecorator::class . '.inner')
            ->addArgument(new Reference(SchedulerRepeatDecorator::class . '.inner'));
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $facade = new Reference(DoctrineDBALFacade::class);

        // Bus manager.

        $definition = $container->getDefinition(BusAdapterInterface::class);

        if ($definition->getClass() === BusAdapter::class) {
            $definition->setArguments([$facade]);
        }

        // Source manager.

        foreach ($container->findTaggedServiceIds(SourceAdapterInterface::class) as $id => $attributes) {
            if ($definition->getClass() === SourceAdapter::class) {
                $definition->setArguments([$facade]);
            }
        }

        // Index manager.

        $definition = $container->getDefinition(IndexStorageInterface::class);

        if ($definition->getClass() === IndexStorage::class) {
            $definition->setArguments([$facade]);
        }

        // Scheduler manager.

        $definition = $container->getDefinition(SchedulerStorageInterface::class);

        if ($definition->getClass() === SchedulerStorage::class) {
            $definition->setArguments([$facade]);
        }

        // View manager.

        $definition = $container->getDefinition(ViewStorageInterface::class);

        if ($definition->getClass() === ViewStorage::class) {
            $definition->setArguments([$facade]);
        }
    }
}