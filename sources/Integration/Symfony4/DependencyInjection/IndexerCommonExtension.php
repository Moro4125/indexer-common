<?php

namespace Moro\Indexer\Common\Integration\Symfony4\DependencyInjection;

use Moro\Indexer\Common\BackendFacade;
use Moro\Indexer\Common\Bus\AdapterInterface as BusAdapterInterface;
use Moro\Indexer\Common\Bus\Manager\LazyManager as BusLazyManager;
use Moro\Indexer\Common\Bus\ManagerInterface as BusManagerInterface;
use Moro\Indexer\Common\ClientFacade;
use Moro\Indexer\Common\Dispatcher\Manager\LazyManager as DispatcherLazyManager;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as DispatcherManagerInterface;
use Moro\Indexer\Common\Dispatcher\Middleware\SchedulerMiddleware;
use Moro\Indexer\Common\Dispatcher\MiddlewareInterface;
use Moro\Indexer\Common\Index\Manager\LazyManager as IndexLazyManager;
use Moro\Indexer\Common\Index\ManagerInterface as IndexManagerInterface;
use Moro\Indexer\Common\Index\Storage\Decorator\AliasCacheDecorator as IndexStorageCacheDecorator;
use Moro\Indexer\Common\Index\StorageInterface as IndexStorageInterface;
use Moro\Indexer\Common\Integration\Symfony4\DependencyInjection\IndexerCommonConfiguration as Config;
use Moro\Indexer\Common\MonolithFacade;
use Moro\Indexer\Common\Regulation\Factory\ContainerFactory as RegulationFactory;
use Moro\Indexer\Common\Regulation\FactoryInterface as RegulationFactoryInterface;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Regulation\Manager\LazyManager as RegulationLazyManager;
use Moro\Indexer\Common\Regulation\ManagerInterface as RegulationManagerInterface;
use Moro\Indexer\Common\Regulation\Result\RegulationResult;
use Moro\Indexer\Common\Regulation\TypeInterface as RegulationTypeInterface;
use Moro\Indexer\Common\Scheduler\Entry\SchedulerEntry;
use Moro\Indexer\Common\Scheduler\Factory\ContainerFactory as SchedulerFactory;
use Moro\Indexer\Common\Scheduler\FactoryInterface as SchedulerFactoryInterface;
use Moro\Indexer\Common\Scheduler\Manager\LazyManager as SchedulerLazyManager;
use Moro\Indexer\Common\Scheduler\ManagerInterface as SchedulerManagerInterface;
use Moro\Indexer\Common\Scheduler\StorageInterface as SchedulerStorageInterface;
use Moro\Indexer\Common\Source\Adapter\Debug\HttpApiDebugAdapter;
use Moro\Indexer\Common\Source\Adapter\HttpApiAdapter;
use Moro\Indexer\Common\Source\AdapterInterface as SourceAdapterInterface;
use Moro\Indexer\Common\Source\Entity\UniversalEntity;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\Factory\ContainerFactory as SourceFactory;
use Moro\Indexer\Common\Source\FactoryInterface as SourceFactoryInterface;
use Moro\Indexer\Common\Source\Manager\LazyManager as SourceLazyManager;
use Moro\Indexer\Common\Source\ManagerInterface as SourceManagerInterface;
use Moro\Indexer\Common\Source\NormalizerInterface;
use Moro\Indexer\Common\Source\Type\Decorator\EntityCacheDecorator;
use Moro\Indexer\Common\Source\TypeInterface as SourceTypeInterface;
use Moro\Indexer\Common\Strategy\CheckEntity\CheckEntityStrategy;
use Moro\Indexer\Common\Strategy\CheckEntity\Decorator\SourceIgnoreDecorator;
use Moro\Indexer\Common\Strategy\CheckEntityInterface;
use Moro\Indexer\Common\Strategy\ReceiveIds\ReceiveIdsStrategy;
use Moro\Indexer\Common\Strategy\ReceiveIdsInterface;
use Moro\Indexer\Common\Strategy\ReceiveView\ReceiveViewStrategy;
use Moro\Indexer\Common\Strategy\ReceiveViews\ReceiveViewsStrategy;
use Moro\Indexer\Common\Strategy\ReceiveViewInterface;
use Moro\Indexer\Common\Strategy\ReceiveViewsInterface;
use Moro\Indexer\Common\Strategy\RemoveEntity\Decorator\EntityCacheStrategy as EntityCacheRemoveStrategy;
use Moro\Indexer\Common\Strategy\RemoveEntity\RemoveEntityStrategy;
use Moro\Indexer\Common\Strategy\RemoveEntityInterface;
use Moro\Indexer\Common\Strategy\UpdateEntity\Decorator\EntityCacheDecorator as EntityCacheUpdateStrategy;
use Moro\Indexer\Common\Strategy\UpdateEntity\Decorator\IndexRepeatDecorator;
use Moro\Indexer\Common\Strategy\UpdateEntity\Decorator\SourceRepeatDecorator;
use Moro\Indexer\Common\Strategy\UpdateEntity\UpdateEntityStrategy;
use Moro\Indexer\Common\Strategy\UpdateEntityInterface;
use Moro\Indexer\Common\Strategy\WaitingForAction\WaitingForActionStrategy;
use Moro\Indexer\Common\Strategy\WaitingForActionInterface;
use Moro\Indexer\Common\Transaction\Manager\LazyManager as TransactionLazyManager;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManagerInterface;
use Moro\Indexer\Common\View\KindInterface;
use Moro\Indexer\Common\View\Manager\LazyManager as ViewLazyManager;
use Moro\Indexer\Common\View\ManagerInterface as ViewManagerInterface;
use Moro\Indexer\Common\View\StorageInterface as ViewStorageInterface;
use Moro\Indexer\Common\View\TypeInterface as ViewTypeInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class IndexerCommonExtension
 * @package Moro\Indexer\Common\Integration\Symfony4\DependencyInjection
 */
class IndexerCommonExtension extends Extension implements CompilerPassInterface, PrependExtensionInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('monolog', [
            'channels' => ['indexer'],
        ]);
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Config();
        $config = $this->processConfiguration($configuration, $configs);

        // Bus manager.

        $container->register(BusAdapterInterface::class, $config[Config::P_BUS_ADAPTER_CLASS])
            ->setAutowired(true);

        $container->register(BusManagerInterface::class, $config[Config::P_BUS_MANAGER_CLASS])
            ->addMethodCall('setAdapter', [new Reference(BusAdapterInterface::class)])
            ->setPublic(true);

        $container->register(BusLazyManager::class, $config[Config::P_BUS_MANAGER_LAZY_CLASS])
            ->setArguments([new Reference(ContainerInterface::class), BusManagerInterface::class]);

        // Dispatcher manager.

        $container->register(DispatcherManagerInterface::class, $config[Config::P_EVENT_MANAGER_CLASS])
            ->setPublic(true);

        $container->register(DispatcherLazyManager::class, $config[Config::P_EVENT_MANAGER_LAZY_CLASS])
            ->setArguments([new Reference(ContainerInterface::class), DispatcherManagerInterface::class]);

        $container->register(SchedulerMiddleware::class)
            ->setArguments([new Reference(DispatcherLazyManager::class), new Reference(SchedulerLazyManager::class)])
            ->addTag(MiddlewareInterface::class);

        // Index manager.

        $container->register(IndexStorageInterface::class, $config[Config::P_INDEX_STORAGE_CLASS])
            ->setAutowired(true);

        $container->register(IndexManagerInterface::class, $config[Config::P_INDEX_MANAGER_CLASS])
            ->addMethodCall('setStorage', [new Reference(IndexStorageInterface::class)])
            ->setPublic(true);

        $container->register(IndexLazyManager::class, $config[Config::P_INDEX_MANAGER_LAZY_CLASS])
            ->setArguments([new Reference(ContainerInterface::class), IndexManagerInterface::class]);

        // Regulation manager.

        foreach (array_keys($config[Config::P_TYPES]) as $code) {
            $container->register($code . ':' . RegulationResult::class, RegulationResult::class)
                ->setShared(false)
                ->setPublic(true);

            $container->register($code . ':' . RegulationFactoryInterface::class, RegulationFactory::class)
                ->setArguments([new Reference(ContainerInterface::class)])
                ->addMethodCall('setResultKey', [$code . ':' . RegulationResult::class])
                ->addTag(RegulationFactoryInterface::class, ['code' => $code]);

            $container->register($code . ':' . RegulationTypeInterface::class, $config[Config::P_REGULATION_TYPE_CLASS])
                ->addMethodCall('setCode', [$code])
                ->addTag(RegulationTypeInterface::class, ['code' => $code]);
        }

        $container->register(RegulationManagerInterface::class, $config[Config::P_REGULATION_MANAGER_CLASS])
            ->setPublic(true);

        $container->register(RegulationLazyManager::class, $config[Config::P_REGULATION_MANAGER_LAZY_CLASS])
            ->setArguments([new Reference(ContainerInterface::class), RegulationManagerInterface::class]);

        // Scheduler manager.

        $container->register(SchedulerStorageInterface::class, $config[Config::P_SCHEDULER_STORAGE_CLASS])
            ->setAutowired(true);

        $container->register(SchedulerEntry::class, $config[Config::P_SCHEDULER_ENTRY_CLASS])
            ->setShared(false)
            ->setPublic(true);

        $container->register(SchedulerFactoryInterface::class, SchedulerFactory::class)
            ->setArguments([new Reference(ContainerInterface::class), SchedulerEntry::class]);

        $container->register(SchedulerManagerInterface::class, $config[Config::P_SCHEDULER_MANAGER_CLASS])
            ->addMethodCall('setStorage', [new Reference(SchedulerStorageInterface::class)])
            ->addMethodCall('setFactory', [new Reference(SchedulerFactoryInterface::class)])
            ->setPublic(true);

        $container->register(SchedulerLazyManager::class, $config[Config::P_SCHEDULER_MANAGER_LAZY_CLASS])
            ->setArguments([new Reference(ContainerInterface::class), SchedulerManagerInterface::class]);

        // Source manager.

        foreach ($config[Config::P_TYPES] as $code => $entityClass) {
            $definition = $container->register($code . ':' . EntityInterface::class, $entityClass)
                ->setShared(false)
                ->setPublic(true);

            if ($entityClass === UniversalEntity::class) {
                $definition->addArgument(new Reference(SourceLazyManager::class));
            }

            $container->register($code . ':' . SourceFactoryInterface::class, SourceFactory::class)
                ->setArguments([new Reference(ContainerInterface::class)])
                ->addMethodCall('setEntityKey', [$code . ':' . EntityInterface::class])
                ->addTag(SourceFactoryInterface::class, ['code' => $code]);

            $container->register($code . ':' . SourceTypeInterface::class, $config[Config::P_SOURCE_TYPE_CLASS])
                ->addMethodCall('setCode', [$code])
                ->addMethodCall('setAdapter', [new Reference($code . ':' . SourceAdapterInterface::class)])
                ->addTag(SourceTypeInterface::class, ['code' => $code]);

            $class = $config[Config::P_SOURCE_ADAPTERS][$code]['class'] ?? $config[Config::P_SOURCE_ADAPTER_CLASS];
            $definition = $container->register($code . ':' . SourceAdapterInterface::class, $class)
                ->setAutowired(true)
                ->addTag(SourceAdapterInterface::class, ['type' => $code]);

            if ($class === HttpApiAdapter::class || $class === HttpApiDebugAdapter::class) {
                $cfg = $config[Config::P_SOURCE_ADAPTERS][$code];
                $isPost = strtoupper($cfg[Config::P_SOURCE_HTTP_METHOD]) !== 'GET';
                $definition->addMethodCall('setUsePostMethod', [$isPost]);

                $url = explode(',', (string)$cfg[Config::P_SOURCE_HTTP_URL_LIST]);
                $arguments = [array_shift($url)];
                $arguments[] = array_shift($url) ?? 'from';
                $arguments[] = array_shift($url) ?? 'limit';
                $arguments[] = array_shift($url);
                $definition->addMethodCall('setUrlIdList', $arguments);

                $url = explode(',', (string)$cfg[Config::P_SOURCE_HTTP_URL_DATA]);
                $arguments = [array_shift($url)];
                $arguments[] = array_shift($url) ?? 'id';
                $arguments[] = array_shift($url);
                $definition->addMethodCall('setUrlEntityById', $arguments);

                if ($cfg[Config::P_SOURCE_HTTP_AUTH]) {
                    $auth = explode(':', $cfg[Config::P_SOURCE_HTTP_AUTH]);
                    $arguments = [array_shift($auth)];
                    $arguments[] = array_shift($auth);
                    $definition->addMethodCall('setBasicAuthorization', $arguments);
                }

                if ($cfg[Config::P_SOURCE_HTTP_PROXY]) {
                    $arguments = [$cfg[Config::P_SOURCE_HTTP_PROXY]];
                    $definition->addMethodCall('setProxy', $arguments);
                }
            }

            if ($limit = $config[Config::P_ENTITY_CACHE]) {
                $renamedId = $code . ':' . EntityCacheDecorator::class . '.inner';
                $container->register($code . ':' . EntityCacheDecorator::class, EntityCacheDecorator::class)
                    ->setDecoratedService($code . ':' . SourceTypeInterface::class, $renamedId)
                    ->addArgument(new Reference($renamedId))
                    ->addArgument($limit);
            }
        }

        $container->register(SourceManagerInterface::class, $config[Config::P_SOURCE_MANAGER_CLASS])
            ->setPublic(true);

        $container->register(SourceLazyManager::class, $config[Config::P_SOURCE_MANAGER_LAZY_CLASS])
            ->setArguments([new Reference(ContainerInterface::class), SourceManagerInterface::class]);

        // Transaction manager.

        $container->register(TransactionManagerInterface::class, $config[Config::P_TRANSACTION_MANAGER_CLASS])
            ->setPublic(true);

        $container->register(TransactionLazyManager::class, $config[Config::P_TRANSACTION_MANAGER_LAZY_CLASS])
            ->setArguments([new Reference(ContainerInterface::class), TransactionManagerInterface::class]);

        // View manager.

        foreach (array_keys($config[Config::P_TYPES]) as $code) {
            $container->register($code . ':' . ViewTypeInterface::class, $config[Config::P_VIEW_TYPE_CLASS])
                ->addMethodCall('setCode', [$code])
                ->addTag(ViewTypeInterface::class, ['code' => $code]);
        }

        $container->register(ViewStorageInterface::class, $config[Config::P_VIEW_STORAGE_CLASS])
            ->setAutowired(true);

        $container->register(ViewManagerInterface::class, $config[Config::P_VIEW_MANAGER_CLASS])
            ->addMethodCall('setStorage', [new Reference(ViewStorageInterface::class)])
            ->setPublic(true);

        $container->register(ViewLazyManager::class, $config[Config::P_VIEW_MANAGER_LAZY_CLASS])
            ->setArguments([new Reference(ContainerInterface::class), ViewManagerInterface::class]);

        // Strategies.

        $definition = $container->register(UpdateEntityInterface::class,
            $config[Config::P_STRATEGY_UPDATE_ENTITY_CLASS]);

        if ($definition->getClass() == UpdateEntityStrategy::class) {
            $definition->addArgument(new Reference(SourceLazyManager::class));
            $definition->addArgument(new Reference(RegulationLazyManager::class));
            $definition->addArgument(new Reference(IndexLazyManager::class));
            $definition->addArgument(new Reference(ViewLazyManager::class));
            $definition->addArgument(new Reference(SchedulerLazyManager::class));
            $definition->addArgument(new Reference(TransactionLazyManager::class));
            $definition->addArgument(new Reference(DispatcherLazyManager::class));
        }

        if ($config[Config::P_SOURCE_REPEAT]) {
            $renamedId = UpdateEntityInterface::class . '.inner';
            $container->register(SourceRepeatDecorator::class)
                ->setDecoratedService(UpdateEntityInterface::class, $renamedId, 20)
                ->addArgument(new Reference($renamedId))
                ->addArgument(new Reference(TransactionLazyManager::class))
                ->addArgument(new Reference(SchedulerLazyManager::class))
                ->addArgument(new Reference(DispatcherLazyManager::class))
                ->addArgument($config[Config::P_SOURCE_REPEAT_INTERVAL]);
        }

        $renamedId = IndexRepeatDecorator::class . '.inner';
        $container->register(IndexRepeatDecorator::class)
            ->setDecoratedService(UpdateEntityInterface::class, $renamedId, 15)
            ->addArgument(new Reference($renamedId))
            ->addArgument(new Reference(DispatcherLazyManager::class));

        if ($config[Config::P_ENTITY_CACHE]) {
            $renamedId = EntityCacheUpdateStrategy::class . '.inner';
            $container->register(EntityCacheUpdateStrategy::class)
                ->setDecoratedService(UpdateEntityInterface::class, $renamedId, 10)
                ->addArgument(new Reference($renamedId));
        }

        $definition = $container->register(RemoveEntityInterface::class,
            $config[Config::P_STRATEGY_REMOVE_ENTITY_CLASS]);

        if ($definition->getClass() == RemoveEntityStrategy::class) {
            $definition->addArgument(new Reference(IndexLazyManager::class));
            $definition->addArgument(new Reference(ViewLazyManager::class));
            $definition->addArgument(new Reference(TransactionLazyManager::class));
            $definition->addArgument(new Reference(DispatcherLazyManager::class));
        }

        if ($config[Config::P_ENTITY_CACHE]) {
            $renamedId = EntityCacheRemoveStrategy::class . '.inner';
            $container->register(EntityCacheRemoveStrategy::class)
                ->setDecoratedService(RemoveEntityInterface::class, $renamedId)
                ->addArgument(new Reference($renamedId));
        }

        $definition = $container->register(ReceiveIdsInterface::class, $config[Config::P_STRATEGY_RECEIVE_IDS_CLASS]);

        if ($definition->getClass() == ReceiveIdsStrategy::class) {
            $definition->addArgument(new Reference(IndexLazyManager::class));
        }

        $definition = $container->register(ReceiveViewInterface::class, $config[Config::P_STRATEGY_RECEIVE_VIEW_CLASS]);

        if ($definition->getClass() == ReceiveViewStrategy::class) {
            $definition->addArgument(new Reference(IndexLazyManager::class));
            $definition->addArgument(new Reference(ViewLazyManager::class));
        }

        $definition = $container->register(ReceiveViewsInterface::class,
            $config[Config::P_STRATEGY_RECEIVE_VIEWS_CLASS]);

        if ($definition->getClass() == ReceiveViewsStrategy::class) {
            $definition->addArgument(new Reference(IndexLazyManager::class));
            $definition->addArgument(new Reference(ViewLazyManager::class));
            $definition->addArgument(new Reference(TransactionLazyManager::class));
        }

        $definition = $container->register(WaitingForActionInterface::class,
            $config[Config::P_STRATEGY_WAITING_ACTION_CLASS]);

        if ($definition->getClass() == WaitingForActionStrategy::class) {
            $definition->addArgument(new Reference(BusLazyManager::class));
            $definition->addArgument(new Reference(SourceLazyManager::class));
            $definition->addArgument(new Reference(SchedulerLazyManager::class));
            $definition->addArgument(new Reference(DispatcherLazyManager::class));
            $definition->addArgument(new Reference(TransactionLazyManager::class));
        }

        $definition = $container->register(CheckEntityInterface::class, $config[Config::P_STRATEGY_CHECK_ENTITY_CLASS]);

        if ($definition->getClass() == CheckEntityStrategy::class) {
            $definition->addArgument(new Reference(SourceLazyManager::class));
            $definition->addArgument(new Reference(IndexLazyManager::class));
            $definition->addArgument(new Reference(SchedulerLazyManager::class));
            $definition->addArgument(new Reference(DispatcherLazyManager::class));
            $definition->addArgument(new Reference(TransactionLazyManager::class));
            $definition->addArgument(new Reference(SchedulerFactoryInterface::class));
            $definition->addArgument($config[Config::P_STRATEGY_CHECK_ENTITY_LIMIT] ?? null);
            $definition->addArgument($config[Config::P_STRATEGY_CHECK_ENTITY_WORKS] ?? null);
            $definition->addArgument($config[Config::P_STRATEGY_CHECK_ENTITY_STEPS] ?? null);
        }

        $renamedId = SourceIgnoreDecorator::class . '.inner';
        $container->register(SourceIgnoreDecorator::class)
            ->setDecoratedService(CheckEntityInterface::class, $renamedId, 15)
            ->addArgument(new Reference($renamedId))
            ->addArgument(new Reference(DispatcherLazyManager::class));

        // Facades.

        $container->register(MonolithFacade::class, MonolithFacade::class)
            ->addArgument(new Reference(UpdateEntityInterface::class))
            ->addArgument(new Reference(RemoveEntityInterface::class))
            ->addArgument(new Reference(ReceiveIdsInterface::class))
            ->addArgument(new Reference(ReceiveViewInterface::class))
            ->addArgument(new Reference(ReceiveViewsInterface::class))
            ->addArgument(new Reference(WaitingForActionInterface::class))
            ->addArgument(new Reference(CheckEntityInterface::class))
            ->addArgument(new Reference(DispatcherLazyManager::class));

        $container->register(BackendFacade::class, BackendFacade::class)
            ->addArgument(new Reference(UpdateEntityInterface::class))
            ->addArgument(new Reference(RemoveEntityInterface::class))
            ->addArgument(new Reference(ReceiveIdsInterface::class))
            ->addArgument(new Reference(ReceiveViewInterface::class))
            ->addArgument(new Reference(ReceiveViewsInterface::class))
            ->addArgument(new Reference(WaitingForActionInterface::class))
            ->addArgument(new Reference(CheckEntityInterface::class))
            ->addArgument(new Reference(DispatcherLazyManager::class))
            ->addArgument(new Reference(BusLazyManager::class));

        $container->register(ClientFacade::class, ClientFacade::class)
            ->addArgument(new Reference(BusLazyManager::class));
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // Dispatcher manager.

        $definition = $container->getDefinition(DispatcherManagerInterface::class);

        foreach ($container->findTaggedServiceIds('indexer.event_listener') as $id => $attributes) {
            foreach ($attributes as $attr) {
                $event = $attr['event'];
                $priority = $attr['priority'] ?? null;

                $definition->addMethodCall('attach', [$event, new Reference($id), $priority]);
            }
        }

        foreach ($container->findTaggedServiceIds(MiddlewareInterface::class) as $id => $attributes) {
            foreach ($attributes as $attr) {
                $priority = $attr['priority'] ?? null;

                $definition->addMethodCall('wrap', [new Reference($id), $priority]);
            }
        }

        // Index manager.

        $container->register(IndexStorageCacheDecorator::class, IndexStorageCacheDecorator::class)
            ->setDecoratedService(IndexStorageInterface::class)
            ->addArgument(new Reference(IndexStorageCacheDecorator::class . '.inner'));

        // Regulation manager.

        $definition = $container->getDefinition(RegulationManagerInterface::class);

        foreach ($container->findTaggedServiceIds(RegulationTypeInterface::class) as $id => $attributes) {
            $definition->addMethodCall('addType', [new Reference($id)]);
            $attributes = array_merge(...$attributes);

            $code = $attributes['code'];
            $type = $container->getDefinition($id);
            $type->addMethodCall('setFactory', [new Reference($code . ':' . RegulationFactoryInterface::class)]);

            foreach ($container->findTaggedServiceIds(InstructionInterface::class) as $serviceId => $attr) {
                $attr = array_merge(...$attr);

                if ($attr['type'] == $code) {
                    $type->addMethodCall('addInstruction', [new Reference($serviceId)]);
                }
            }
        }

        // Source manager.

        $definition = $container->getDefinition(SourceManagerInterface::class);

        foreach ($container->findTaggedServiceIds(SourceTypeInterface::class) as $id => $attributes) {
            $definition->addMethodCall('addType', [new Reference($id)]);
            $attributes = array_merge(...$attributes);

            $code = $attributes['code'];
            $type = $container->getDefinition($id);
            $type->addMethodCall('setFactory', [new Reference($code . ':' . SourceFactoryInterface::class)]);

            foreach ($container->findTaggedServiceIds(NormalizerInterface::class) as $serviceId => $attr) {
                $attr = array_merge(...$attr);

                if ($attr['type'] == $code) {
                    $type->addMethodCall('addNormalizer', [new Reference($serviceId)]);
                }
            }
        }

        // View manager.

        $definition = $container->getDefinition(ViewManagerInterface::class);

        foreach ($container->findTaggedServiceIds(ViewTypeInterface::class) as $id => $attributes) {
            $definition->addMethodCall('addType', [new Reference($id)]);
            $attributes = array_merge(...$attributes);

            $code = $attributes['code'];
            $type = $container->getDefinition($id);

            foreach ($container->findTaggedServiceIds(KindInterface::class) as $serviceId => $attr) {
                $attr = array_merge(...$attr);

                if ($attr['type'] == $code) {
                    $type->addMethodCall('addKind', [new Reference($serviceId)]);
                }
            }
        }

        // Logger.

        if ($container->has('monolog.logger')) {
            $definition = $container->getDefinition(MonolithFacade::class);
            $definition->addArgument(new Reference('monolog.logger.indexer'));

            $definition = $container->getDefinition(BackendFacade::class);
            $definition->addArgument(new Reference('monolog.logger.indexer'));

            $definition = $container->getDefinition(ClientFacade::class);
            $definition->addArgument(new Reference('monolog.logger.indexer'));

            foreach ($container->findTaggedServiceIds(SourceAdapterInterface::class) as $id => $tags) {
                $definition = $container->getDefinition($id);

                switch ($definition->getClass()) {
                    case HttpApiDebugAdapter::class:
                        $definition->setArguments([new Reference('monolog.logger.indexer')]);
                        break;
                }
            }
        }
    }
}