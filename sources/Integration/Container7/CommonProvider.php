<?php

namespace Moro\Indexer\Common\Integration\Container7;

use Moro\Container7\Aliases;
use Moro\Container7\Container;
use Moro\Container7\Parameters;
use Moro\Container7\Provider;
use Moro\Container7\Tags;
use Moro\Indexer\Common\Action\CheckEntity\CheckEntityAction;
use Moro\Indexer\Common\Action\CheckEntity\Decorator\SourceIgnoreDecorator;
use Moro\Indexer\Common\Action\CheckEntityInterface;
use Moro\Indexer\Common\Action\ReceiveIds\ReceiveIdsAction;
use Moro\Indexer\Common\Action\ReceiveIdsInterface;
use Moro\Indexer\Common\Action\ReceiveView\ReceiveViewAction;
use Moro\Indexer\Common\Action\ReceiveViewInterface;
use Moro\Indexer\Common\Action\ReceiveViews\ReceiveViewsAction;
use Moro\Indexer\Common\Action\ReceiveViewsInterface;
use Moro\Indexer\Common\Action\RemoveEntity\Decorator\EntityCacheDecorator as EntityCacheRemoveAction;
use Moro\Indexer\Common\Action\RemoveEntity\RemoveEntityAction;
use Moro\Indexer\Common\Action\RemoveEntityInterface;
use Moro\Indexer\Common\Action\UpdateEntity\Decorator\EntityCacheDecorator as EntityCacheUpdateAction;
use Moro\Indexer\Common\Action\UpdateEntity\Decorator\IndexRepeatDecorator;
use Moro\Indexer\Common\Action\UpdateEntity\Decorator\SourceRepeatDecorator;
use Moro\Indexer\Common\Action\UpdateEntity\UpdateEntityAction;
use Moro\Indexer\Common\Action\UpdateEntityInterface;
use Moro\Indexer\Common\Action\WaitingForAction\WaitingForActionAction;
use Moro\Indexer\Common\Action\WaitingForActionInterface;
use Moro\Indexer\Common\BackendFacade;
use Moro\Indexer\Common\Bus\AdapterInterface as BusAdapterInterface;
use Moro\Indexer\Common\Bus\Manager\BusManager;
use Moro\Indexer\Common\Bus\Manager\LazyManager as BusLazyManager;
use Moro\Indexer\Common\Bus\ManagerInterface as BusManagerInterface;
use Moro\Indexer\Common\Dispatcher\Manager\EventManager;
use Moro\Indexer\Common\Dispatcher\Manager\LazyManager as DispatcherLazyManager;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as DispatcherManagerInterface;
use Moro\Indexer\Common\Dispatcher\Middleware\SchedulerMiddleware;
use Moro\Indexer\Common\Dispatcher\MiddlewareInterface;
use Moro\Indexer\Common\Index\Manager\IndexManager;
use Moro\Indexer\Common\Index\Manager\LazyManager as IndexLazyManager;
use Moro\Indexer\Common\Index\ManagerInterface as IndexManagerInterface;
use Moro\Indexer\Common\Index\StorageInterface as IndexStorageInterface;
use Moro\Indexer\Common\Regulation\Factory\ContainerFactory as RegulationFactory;
use Moro\Indexer\Common\Regulation\FactoryInterface as RegulationFactoryInterface;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Regulation\Manager\LazyManager as RegulationLazyManager;
use Moro\Indexer\Common\Regulation\Manager\RegulationManager;
use Moro\Indexer\Common\Regulation\ManagerInterface as RegulationManagerInterface;
use Moro\Indexer\Common\Regulation\Result\RegulationResult;
use Moro\Indexer\Common\Regulation\Type\RegulationType;
use Moro\Indexer\Common\Regulation\TypeInterface as RegulationTypeInterface;
use Moro\Indexer\Common\Scheduler\Entry\SchedulerEntry;
use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\Factory\ContainerFactory as SchedulerFactory;
use Moro\Indexer\Common\Scheduler\FactoryInterface as SchedulerFactoryInterface;
use Moro\Indexer\Common\Scheduler\Manager\LazyManager as SchedulerLazyManager;
use Moro\Indexer\Common\Scheduler\Manager\SchedulerManager;
use Moro\Indexer\Common\Scheduler\ManagerInterface as SchedulerManagerInterface;
use Moro\Indexer\Common\Scheduler\StorageInterface as SchedulerStorageInterface;
use Moro\Indexer\Common\ServiceFacade;
use Moro\Indexer\Common\Source\AdapterInterface;
use Moro\Indexer\Common\Source\Entity\UniversalEntity;
use Moro\Indexer\Common\Source\Factory\ContainerFactory as SourceFactory;
use Moro\Indexer\Common\Source\FactoryInterface as SourceFactoryInterface;
use Moro\Indexer\Common\Source\Manager\LazyManager as SourceLazyManager;
use Moro\Indexer\Common\Source\Manager\SourceManager;
use Moro\Indexer\Common\Source\ManagerInterface as SourceManagerInterface;
use Moro\Indexer\Common\Source\NormalizerInterface;
use Moro\Indexer\Common\Source\Type\Decorator\EntityCacheDecorator;
use Moro\Indexer\Common\Source\Type\SourceType;
use Moro\Indexer\Common\Source\TypeInterface as SourceTypeInterface;
use Moro\Indexer\Common\Strategy\Read\ExternalReadStrategy;
use Moro\Indexer\Common\Strategy\Read\InternalReadStrategy;
use Moro\Indexer\Common\Strategy\Write\ExternalWriteStrategy;
use Moro\Indexer\Common\Strategy\Write\InternalWriteStrategy;
use Moro\Indexer\Common\Transaction\Manager\LazyManager as TransactionLazyManager;
use Moro\Indexer\Common\Transaction\Manager\TransactionManager;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManagerInterface;
use Moro\Indexer\Common\View\KindInterface;
use Moro\Indexer\Common\View\Manager\LazyManager as ViewLazyManager;
use Moro\Indexer\Common\View\Manager\ViewManager;
use Moro\Indexer\Common\View\ManagerInterface as ViewManagerInterface;
use Moro\Indexer\Common\View\StorageInterface as ViewStorageInterface;
use Moro\Indexer\Common\View\Type\ViewType;
use Moro\Indexer\Common\View\TypeInterface as ViewTypeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CommonProvider
 * @package Moro\Indexer\Common\Integration
 */
class CommonProvider
{
    const P_DEBUG                          = 'indexer/debug';
    const P_TYPES                          = 'indexer/types';
    const P_FACADE                         = 'indexer/facade'; // monolith, backend, client
    const P_ENTITY_CACHE                   = 'indexer/entity-cache'; // integer, 0 - disabled
    const P_BUS_ADAPTER_CLASS              = 'indexer/bus/adapter.class';
    const P_BUS_MANAGER_CLASS              = 'indexer/bus/manager.class';
    const P_BUS_MANAGER_LAZY_CLASS         = 'indexer/bus/manager-lazy.class';
    const P_EVENT_MANAGER_CLASS            = 'indexer/event/manager.class';
    const P_EVENT_MANAGER_LAZY_CLASS       = 'indexer/event/manager-lazy.class';
    const P_SOURCE_REPEAT                  = 'indexer/source/repeat'; // boolean
    const P_SOURCE_REPEAT_INTERVAL         = 'indexer/source/repeat.interval';
    const P_SOURCE_TYPE_CLASS              = 'indexer/source/type.class';
    const P_SOURCE_ADAPTER_CLASS           = 'indexer/source/adapter.class';
    const P_SOURCE_MANAGER_CLASS           = 'indexer/source/manager.class';
    const P_SOURCE_MANAGER_LAZY_CLASS      = 'indexer/source/manager-lazy.class';
    const P_SOURCE_MANAGER_LAZY_TARGET     = 'indexer/source/manager-lazy.target';
    const P_REGULATION_TYPE_CLASS          = 'indexer/regulation/type.class';
    const P_REGULATION_MANAGER_CLASS       = 'indexer/regulation/manager.class';
    const P_REGULATION_MANAGER_LAZY_CLASS  = 'indexer/regulation/manager-lazy.class';
    const P_REGULATION_MANAGER_LAZY_TARGET = 'indexer/regulation/manager-lazy.target';
    const P_INDEX_STORAGE_CLASS            = 'indexer/index/storage.class';
    const P_INDEX_MANAGER_CLASS            = 'indexer/index/manager.class';
    const P_INDEX_MANAGER_LAZY_CLASS       = 'indexer/index/manager-lazy.class';
    const P_VIEW_TYPE_CLASS                = 'indexer/view/type.class';
    const P_VIEW_STORAGE_CLASS             = 'indexer/view/storage.class';
    const P_VIEW_MANAGER_CLASS             = 'indexer/view/manager.class';
    const P_VIEW_MANAGER_LAZY_CLASS        = 'indexer/view/manager-lazy.class';
    const P_VIEW_MANAGER_LAZY_TARGET       = 'indexer/view/manager-lazy.target';
    const P_SCHEDULER_ENTRY_CLASS          = 'indexer/scheduler/entry.class';
    const P_SCHEDULER_STORAGE_CLASS        = 'indexer/scheduler/storage.class';
    const P_SCHEDULER_MANAGER_CLASS        = 'indexer/scheduler/manager.class';
    const P_SCHEDULER_MANAGER_LAZY_CLASS   = 'indexer/scheduler/manager-lazy.class';
    const P_TRANSACTION_MANAGER_CLASS      = 'indexer/transaction/manager.class';
    const P_TRANSACTION_MANAGER_LAZY_CLASS = 'indexer/transaction/manager-lazy.class';
    const P_STRATEGY_UPDATE_ENTITY_CLASS   = 'indexer/strategy/update-entity.class';
    const P_STRATEGY_REMOVE_ENTITY_CLASS   = 'indexer/strategy/remove-entity.class';
    const P_STRATEGY_RECEIVE_IDS_CLASS     = 'indexer/strategy/receive-ids.class';
    const P_STRATEGY_RECEIVE_VIEW_CLASS    = 'indexer/strategy/receive-view.class';
    const P_STRATEGY_RECEIVE_VIEWS_CLASS   = 'indexer/strategy/receive-views.class';
    const P_STRATEGY_WAITING_ACTION_CLASS  = 'indexer/strategy/waiting-action.class';
    const P_STRATEGY_CHECK_ENTITY_CLASS    = 'indexer/strategy/check-entity.class';

    const V_FACADE_MONOLITH = 'monolith';
    const V_FACADE_BACKEND  = 'backend';
    const V_FACADE_CLIENT   = 'client';

    public function parameters(Parameters $parameters)
    {
        $parameters->append([
            self::P_DEBUG                          => null,
            self::P_TYPES                          => [],
            self::P_FACADE                         => self::V_FACADE_MONOLITH,
            self::P_ENTITY_CACHE                   => 16,
            self::P_BUS_MANAGER_CLASS              => BusManager::class,
            self::P_BUS_MANAGER_LAZY_CLASS         => BusLazyManager::class,
            self::P_EVENT_MANAGER_CLASS            => EventManager::class,
            self::P_EVENT_MANAGER_LAZY_CLASS       => DispatcherLazyManager::class,
            self::P_SOURCE_REPEAT                  => false,
            self::P_SOURCE_REPEAT_INTERVAL         => 16,
            self::P_SOURCE_TYPE_CLASS              => SourceType::class,
            self::P_SOURCE_MANAGER_CLASS           => SourceManager::class,
            self::P_SOURCE_MANAGER_LAZY_CLASS      => SourceLazyManager::class,
            self::P_SOURCE_MANAGER_LAZY_TARGET     => SourceManagerInterface::class,
            self::P_REGULATION_TYPE_CLASS          => RegulationType::class,
            self::P_REGULATION_MANAGER_CLASS       => RegulationManager::class,
            self::P_REGULATION_MANAGER_LAZY_CLASS  => RegulationLazyManager::class,
            self::P_REGULATION_MANAGER_LAZY_TARGET => RegulationManagerInterface::class,
            self::P_INDEX_MANAGER_CLASS            => IndexManager::class,
            self::P_INDEX_MANAGER_LAZY_CLASS       => IndexLazyManager::class,
            self::P_VIEW_TYPE_CLASS                => ViewType::class,
            self::P_VIEW_MANAGER_CLASS             => ViewManager::class,
            self::P_VIEW_MANAGER_LAZY_CLASS        => ViewLazyManager::class,
            self::P_VIEW_MANAGER_LAZY_TARGET       => ViewManagerInterface::class,
            self::P_SCHEDULER_ENTRY_CLASS          => SchedulerEntry::class,
            self::P_SCHEDULER_MANAGER_CLASS        => SchedulerManager::class,
            self::P_SCHEDULER_MANAGER_LAZY_CLASS   => SchedulerLazyManager::class,
            self::P_TRANSACTION_MANAGER_CLASS      => TransactionManager::class,
            self::P_TRANSACTION_MANAGER_LAZY_CLASS => TransactionLazyManager::class,
            self::P_STRATEGY_UPDATE_ENTITY_CLASS   => UpdateEntityAction::class,
            self::P_STRATEGY_REMOVE_ENTITY_CLASS   => RemoveEntityAction::class,
            self::P_STRATEGY_RECEIVE_IDS_CLASS     => ReceiveIdsAction::class,
            self::P_STRATEGY_RECEIVE_VIEW_CLASS    => ReceiveViewAction::class,
            self::P_STRATEGY_RECEIVE_VIEWS_CLASS   => ReceiveViewsAction::class,
            self::P_STRATEGY_WAITING_ACTION_CLASS  => WaitingForActionAction::class,
            self::P_STRATEGY_CHECK_ENTITY_CLASS    => CheckEntityAction::class,
        ]);
    }

    public function tags(Tags $tags)
    {
        $tags->register(SourceFactoryInterface::class);
        $tags->register(RegulationFactoryInterface::class);

        $tags->register(NormalizerInterface::class);
        $tags->register(InstructionInterface::class);
        $tags->register(KindInterface::class);

        $tags->register(SourceTypeInterface::class);
        $tags->register(RegulationTypeInterface::class);
        $tags->register(ViewTypeInterface::class);

        $tags->add(MiddlewareInterface::class, SchedulerMiddleware::class);
    }

    public function boot(Container $container, Parameters $parameters, Aliases $aliases)
    {
        assert($parameters->has($k = self::P_BUS_ADAPTER_CLASS), sprintf('Parameter "%1$s" required.', $k));
        assert($parameters->has($k = self::P_INDEX_STORAGE_CLASS), sprintf('Parameter "%1$s" required.', $k));
        assert($parameters->has($k = self::P_VIEW_STORAGE_CLASS), sprintf('Parameter "%1$s" required.', $k));
        assert($parameters->has($k = self::P_SCHEDULER_STORAGE_CLASS), sprintf('Parameter "%1$s" required.', $k));

        if ($container->hasProvider(ConfigurationProvider::class)) {
            return;
        }

        $configuration = [];
        $sourceTypeClass = $parameters->get(self::P_SOURCE_TYPE_CLASS);
        $regulationTypeClass = $parameters->get(self::P_REGULATION_TYPE_CLASS);
        $viewTypeClass = $parameters->get(self::P_VIEW_TYPE_CLASS);
        $types = $parameters->get(self::P_TYPES);

        assert($types, sprintf('Please, add [type name => entity class] to parameter "%1$s"', self::P_TYPES));
        assert($parameters->has($k = self::P_SOURCE_ADAPTER_CLASS), sprintf('Parameter "%1$s" required.', $k));

        foreach ($types as $code => $entityClass) {
            $configuration['singletons'][] = [
                'interface' => ViewTypeInterface::class,
                'class'     => $viewTypeClass,
                'calls'     => [
                    [
                        'method' => 'setCode',
                        'args'   => [$code]
                    ],
                ],
            ];

            $entityRefs = $code . ':source.entity';

            if ($container->has($entityClass)) {
                $aliases->add($entityRefs, $entityClass);
            } else {
                $configuration['factories'][] = [
                    'alias' => $entityRefs,
                    'class' => $entityClass,
                ];
            }

            $configuration['factories'][] = [
                'class' => SourceFactory::class,
                'args'  => ['@' . Container::class],
                'tags'  => [SourceFactoryInterface::class, $code],
                'calls' => [
                    [
                        'method' => 'setEntityKey',
                        'args'   => [$entityRefs],
                    ],
                ],
            ];

            $configuration['singletons'][] = [
                'interface' => SourceTypeInterface::class,
                'class'     => $sourceTypeClass,
                'tags'      => [SourceTypeInterface::class, $code],
                'calls'     => [
                    [
                        'method' => 'setCode',
                        'args'   => [$code]
                    ],
                ],
            ];

            $entityRefs = $code . ':regulation.result';

            $configuration['factories'][] = [
                'alias' => $entityRefs,
                'class' => RegulationResult::class,
            ];

            $configuration['singletons'][] = [
                'class' => RegulationFactory::class,
                'args'  => ['@' . Container::class],
                'tags'  => [RegulationFactoryInterface::class, $code],
                'calls' => [
                    [
                        'method' => 'setResultKey',
                        'args'   => [$entityRefs],
                    ]
                ],
            ];

            $configuration['singletons'][] = [
                'interface' => RegulationTypeInterface::class,
                'class'     => $regulationTypeClass,
                'calls'     => [
                    [
                        'method' => 'setCode',
                        'args'   => [$code]
                    ],
                ],
            ];
        }

        $container->addProvider(Provider::fromConfiguration(__METHOD__, $configuration));
    }

    protected function _findNearestService(Container $container, string $interface, string $type)
    {
        $service = null;
        /** @var Parameters $parameters */
        $parameters = $container->get(Parameters::class);
        /** @var string $facade */
        $facade = $parameters->get(self::P_FACADE);
        /** @var Tags $tags */
        $tags = $container->get(Tags::class);

        if ($container->hasCollection($interface)) {
            $collection = $container->getCollection($interface);

            if ($tags->hasTag($facade)) {
                $tagCollection = $collection->with($facade);

                if ($tagCollection->count()) {
                    $collection = $tagCollection;
                }
            }

            if ($tags->hasTag($type)) {
                $tagCollection = $collection->with($type);

                if ($tagCollection->count()) {
                    $collection = $tagCollection;
                }
            }

            foreach ($collection->for($type) as $adapter) {
                $service = $adapter;
            }
        } else {
            $service = $container->get(AdapterInterface::class, $type);
        }

        return $service;
    }

    public function busAdapter(Container $container, Parameters $parameters): BusAdapterInterface
    {
        $class = $parameters->get(self::P_BUS_ADAPTER_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function busManager(Container $container, Parameters $parameters): BusManagerInterface
    {
        $class = $parameters->get(self::P_BUS_MANAGER_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function busManagerInit(BusManagerInterface $manager, BusAdapterInterface $adapter)
    {
        $manager->setAdapter($adapter);
    }

    public function busManagerLazy(Container $container, Parameters $parameters): BusLazyManager
    {
        $class = $parameters->get(self::P_BUS_MANAGER_LAZY_CLASS);

        if ($class != BusLazyManager::class && $container->has($class)) {
            return $container->get($class);
        }

        return new $class($container, BusManagerInterface::class);
    }

    public function dispatcherManager(Container $container, Parameters $parameters): DispatcherManagerInterface
    {
        $class = $parameters->get(self::P_EVENT_MANAGER_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function dispatcherManagerInit(DispatcherManagerInterface $dispatcher, Container $container, Tags $tags)
    {
        foreach ($container->getCollection(MiddlewareInterface::class) as $key => $middleware) {
            $meta = $tags->metaByTagAndKey(MiddlewareInterface::class, $key);
            $dispatcher->wrap($middleware, $meta['priority'] ?? DispatcherManagerInterface::MIDDLE);
        }
    }

    public function dispatcherManagerLazy(Container $container, Parameters $parameters): DispatcherLazyManager
    {
        $class = $parameters->get(self::P_EVENT_MANAGER_LAZY_CLASS);

        if ($class != DispatcherLazyManager::class && $container->has($class)) {
            return $container->get($class);
        }

        return new $class($container, DispatcherManagerInterface::class);
    }

    public function dispatcherSchedulerMiddleware(
        DispatcherLazyManager $dispatcher,
        SchedulerLazyManager $manager
    ): SchedulerMiddleware {
        return new SchedulerMiddleware($dispatcher, $manager);
    }

    public function sourceAdapter(Container $container, Parameters $parameters, ...$arguments): AdapterInterface
    {
        $class = $parameters->get(self::P_SOURCE_ADAPTER_CLASS);

        return $container->has($class) ? $container->get($class, reset($arguments)) : new $class;
    }

    public function sourceEntityUniversal(SourceLazyManager $manager, ...$arguments): UniversalEntity
    {
        unset($arguments);

        return new UniversalEntity($manager);
    }

    public function sourceTypeInit(SourceTypeInterface $type, Container $container)
    {
        $code = $type->getCode();

        $collection = $container->getCollection(NormalizerInterface::class)
            ->with($code);

        foreach ($collection as $normalizer) {
            $type->addNormalizer($normalizer);
        }

        $collection = $container->getCollection(SourceFactoryInterface::class)
            ->with($code);

        foreach ($collection as $factory) {
            $type->setFactory($factory);
        }

        $type->setAdapter($this->_findNearestService($container, AdapterInterface::class, $code));
    }

    public function sourceTypeDecorateEntityCache(
        SourceTypeInterface $type,
        Parameters $parameters
    ): ?EntityCacheDecorator {
        if ($limit = $parameters->get(self::P_ENTITY_CACHE)) {
            return new EntityCacheDecorator($type, $limit);
        }

        return null;
    }

    public function sourceManager(Container $container, Parameters $parameters): SourceManagerInterface
    {
        $class = $parameters->get(self::P_SOURCE_MANAGER_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function sourceManagerInit(SourceManagerInterface $manager, Container $container)
    {
        $collection = $container->getCollection(SourceTypeInterface::class)
            ->exclude(Tags::RUNTIME);

        foreach ($collection as $typeService) {
            $manager->addType($typeService);
        }
    }

    public function sourceManagerLazy(Container $container, Parameters $parameters): SourceLazyManager
    {
        $class = $parameters->get(self::P_SOURCE_MANAGER_LAZY_CLASS);

        if ($class != SourceLazyManager::class && $container->has($class)) {
            return $container->get($class);
        }

        return new $class($container, $parameters->get(self::P_SOURCE_MANAGER_LAZY_TARGET));
    }

    public function regulationTypeInit(RegulationTypeInterface $type, Container $container)
    {
        $code = $type->getCode();

        $collection = $container->getCollection(InstructionInterface::class)
            ->with($code);

        foreach ($collection as $instruction) {
            $type->addInstruction($instruction);
        }

        $collection = $container->getCollection(RegulationFactoryInterface::class)
            ->with($code);

        foreach ($collection as $factory) {
            $type->setFactory($factory);
        }
    }

    public function regulationManager(Container $container, Parameters $parameters): RegulationManagerInterface
    {
        $class = $parameters->get(self::P_REGULATION_MANAGER_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function regulationManagerInit(RegulationManagerInterface $manager, Container $container)
    {
        $collection = $container->getCollection(RegulationTypeInterface::class)
            ->exclude(Tags::RUNTIME);

        foreach ($collection as $typeService) {
            $manager->addType($typeService);
        }
    }

    public function regulationManagerLazy(Container $container, Parameters $parameters): RegulationLazyManager
    {
        $class = $parameters->get(self::P_REGULATION_MANAGER_LAZY_CLASS);

        if ($class != RegulationLazyManager::class && $container->has($class)) {
            return $container->get($class);
        }

        return new $class($container, $parameters->get(self::P_REGULATION_MANAGER_LAZY_TARGET));
    }

    public function indexStorage(Container $container, Parameters $parameters): IndexStorageInterface
    {
        $class = $parameters->get(self::P_INDEX_STORAGE_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function indexManager(Container $container, Parameters $parameters): IndexManagerInterface
    {
        $class = $parameters->get(self::P_INDEX_MANAGER_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function indexManagerInit(IndexManagerInterface $manager, IndexStorageInterface $storage)
    {
        $manager->setStorage($storage);
    }

    public function indexManagerLazy(Container $container, Parameters $parameters): IndexLazyManager
    {
        $class = $parameters->get(self::P_INDEX_MANAGER_LAZY_CLASS);

        if ($class != IndexLazyManager::class && $container->has($class)) {
            return $container->get($class);
        }

        return new $class($container, IndexManagerInterface::class);
    }

    public function viewTypeInit(ViewTypeInterface $type, Container $container)
    {
        $code = $type->getCode();

        $collection = $container->getCollection(KindInterface::class)
            ->with($code);

        foreach ($collection as $kind) {
            $type->addKind($kind);
        }
    }

    public function viewStorage(Container $container, Parameters $parameters): ViewStorageInterface
    {
        $class = $parameters->get(self::P_VIEW_STORAGE_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function viewManager(Container $container, Parameters $parameters): ViewManagerInterface
    {
        $class = $parameters->get(self::P_VIEW_MANAGER_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function viewManagerInit(ViewManagerInterface $manager, Container $container, ViewStorageInterface $storage)
    {
        $collection = $container->getCollection(ViewTypeInterface::class)
            ->exclude(Tags::RUNTIME);

        foreach ($collection as $typeService) {
            $manager->addType($typeService);
        }

        $manager->setStorage($storage);
    }

    public function viewManagerLazy(Container $container, Parameters $parameters): ViewLazyManager
    {
        $class = $parameters->get(self::P_VIEW_MANAGER_LAZY_CLASS);

        if ($class != ViewLazyManager::class && $container->has($class)) {
            return $container->get($class);
        }

        return new $class($container, $parameters->get(self::P_VIEW_MANAGER_LAZY_TARGET));
    }

    public function schedulerEntry(Container $container, Parameters $parameters, ...$arguments): EntryInterface
    {
        $class = $parameters->get(self::P_SCHEDULER_ENTRY_CLASS);
        unset($arguments);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function schedulerEntryFactory(Container $container): SchedulerFactoryInterface
    {
        return new SchedulerFactory($container, EntryInterface::class);
    }

    public function schedulerStorage(Container $container, Parameters $parameters): SchedulerStorageInterface
    {
        $class = $parameters->get(self::P_SCHEDULER_STORAGE_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function schedulerManager(Container $container, Parameters $parameters): SchedulerManagerInterface
    {
        $class = $parameters->get(self::P_SCHEDULER_MANAGER_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function schedulerManagerInit(
        SchedulerManagerInterface $manager,
        SchedulerStorageInterface $storage,
        SchedulerFactoryInterface $factory
    ) {
        $manager->setStorage($storage);
        $manager->setFactory($factory);
    }

    public function schedulerManagerLazy(Container $container, Parameters $parameters): SchedulerLazyManager
    {
        $class = $parameters->get(self::P_SCHEDULER_MANAGER_LAZY_CLASS);

        if ($class != SchedulerLazyManager::class && $container->has($class)) {
            return $container->get($class);
        }

        return new $class($container, SchedulerManagerInterface::class);
    }

    public function transactionManager(Container $container, Parameters $parameters): TransactionManagerInterface
    {
        $class = $parameters->get(self::P_TRANSACTION_MANAGER_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function transactionManagerLazy(Container $container, Parameters $parameters): TransactionLazyManager
    {
        $class = $parameters->get(self::P_TRANSACTION_MANAGER_LAZY_CLASS);

        if ($class != TransactionLazyManager::class && $container->has($class)) {
            return $container->get($class);
        }

        return new $class($container, TransactionManagerInterface::class);
    }

    public function strategyUpdateEntity(Container $container, Parameters $parameters): UpdateEntityInterface
    {
        $class = $parameters->get(self::P_STRATEGY_UPDATE_ENTITY_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function strategyUpdateEntityImplementation(
        SourceLazyManager $source,
        RegulationLazyManager $regulation,
        IndexLazyManager $index,
        ViewLazyManager $view,
        SchedulerLazyManager $scheduler,
        TransactionLazyManager $transaction,
        DispatcherLazyManager $events
    ): UpdateEntityAction {
        return new UpdateEntityAction($source, $regulation, $index, $view, $scheduler, $transaction, $events);
    }

    public function strategyUpdateEntityRepeatDecorator(
        UpdateEntityInterface $strategy,
        TransactionLazyManager $transaction,
        SchedulerLazyManager $scheduler,
        DispatcherLazyManager $events,
        Parameters $parameters
    ): ?SourceRepeatDecorator {
        if ($parameters->get(self::P_SOURCE_REPEAT)) {
            $interval = $parameters->get(self::P_SOURCE_REPEAT_INTERVAL);

            return new SourceRepeatDecorator($strategy, $transaction, $scheduler, $events, $interval);
        }

        return null;
    }

    public function strategyUpdateEntityIndexDecorator(
        UpdateEntityInterface $strategy,
        DispatcherLazyManager $events
    ): ?IndexRepeatDecorator {
        return new IndexRepeatDecorator($strategy, $events);
    }

    public function strategyUpdateEntityCacheDecorator(
        UpdateEntityInterface $strategy,
        Parameters $parameters
    ): ?EntityCacheUpdateAction {
        if ($limit = $parameters->get(self::P_ENTITY_CACHE)) {
            return new EntityCacheUpdateAction($strategy);
        }

        return null;
    }

    public function strategyRemoveEntity(Container $container, Parameters $parameters): RemoveEntityInterface
    {
        $class = $parameters->get(self::P_STRATEGY_REMOVE_ENTITY_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function strategyRemoveEntityCacheDecorator(
        RemoveEntityInterface $strategy,
        Parameters $parameters
    ): ?RemoveEntityInterface {
        if ($limit = $parameters->get(self::P_ENTITY_CACHE)) {
            return new EntityCacheRemoveAction($strategy);
        }

        return null;
    }

    public function strategyRemoveEntityImplementation(
        IndexLazyManager $index,
        ViewLazyManager $view,
        TransactionLazyManager $transaction,
        DispatcherLazyManager $events
    ): RemoveEntityAction {
        return new RemoveEntityAction($index, $view, $transaction, $events);
    }

    public function strategyReceiveIds(Container $container, Parameters $parameters): ReceiveIdsInterface
    {
        $class = $parameters->get(self::P_STRATEGY_RECEIVE_IDS_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function strategyReceiveIdsImplementation(IndexLazyManager $index): ReceiveIdsAction
    {
        return new ReceiveIdsAction($index);
    }

    public function strategyReceiveView(Container $container, Parameters $parameters): ReceiveViewInterface
    {
        $class = $parameters->get(self::P_STRATEGY_RECEIVE_VIEW_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function strategyReceiveViews(Container $container, Parameters $parameters): ReceiveViewsInterface
    {
        $class = $parameters->get(self::P_STRATEGY_RECEIVE_VIEWS_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function strategyReceiveViewsImplementation(
        IndexLazyManager $index,
        ViewLazyManager $view,
        TransactionLazyManager $transaction
    ): ReceiveViewsAction {
        return new ReceiveViewsAction($index, $view, $transaction);
    }

    public function strategyReceiveViewImplementation(
        IndexLazyManager $index,
        ViewLazyManager $view
    ): ReceiveViewAction {
        return new ReceiveViewAction($index, $view);
    }

    public function strategyWaitingForAction(Container $container, Parameters $parameters): WaitingForActionInterface
    {
        $class = $parameters->get(self::P_STRATEGY_WAITING_ACTION_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function strategyWaitingForActionImplementation(
        BusLazyManager $bus,
        SourceLazyManager $source,
        SchedulerLazyManager $scheduler,
        DispatcherLazyManager $events,
        TransactionLazyManager $transaction
    ): WaitingForActionAction {
        return new WaitingForActionAction($bus, $source, $scheduler, $events, $transaction);
    }

    public function strategyCheckEntity(Container $container, Parameters $parameters): CheckEntityInterface
    {
        $class = $parameters->get(self::P_STRATEGY_CHECK_ENTITY_CLASS);

        return $container->has($class) ? $container->get($class) : new $class;
    }

    public function strategyCheckEntitySourceDecorator(
        CheckEntityInterface $strategy,
        DispatcherLazyManager $events
    ): ?CheckEntityInterface {
        return new SourceIgnoreDecorator($strategy, $events);
    }

    public function strategyCheckEntityImplementation(
        SourceLazyManager $source,
        IndexLazyManager $index,
        SchedulerLazyManager $scheduler,
        DispatcherLazyManager $events,
        TransactionLazyManager $transaction,
        SchedulerFactoryInterface $factory
    ): CheckEntityAction {
        return new CheckEntityAction($source, $index, $scheduler, $events, $transaction, $factory);
    }

    public function serviceFacade(
        InternalReadStrategy $reader,
        InternalWriteStrategy $writer
    ): ServiceFacade {
        return new ServiceFacade($reader, $writer);
    }

    public function internalReadStrategy(
        Container $container,
        Parameters $parameters,
        ReceiveIdsInterface $ids,
        ReceiveViewInterface $view,
        ReceiveViewsInterface $views
    ): InternalReadStrategy {
        $logger = ($parameters->get(self::P_DEBUG) && $container->has(LoggerInterface::class)) ? $container->get(LoggerInterface::class) : null;

        return new InternalReadStrategy($ids, $view, $views, $logger);
    }

    public function internalWriteStrategy(
        Container $container,
        Parameters $parameters,
        UpdateEntityInterface $update,
        RemoveEntityInterface $remove
    ): InternalWriteStrategy {
        $logger = ($parameters->get(self::P_DEBUG) && $container->has(LoggerInterface::class)) ? $container->get(LoggerInterface::class) : null;

        return new InternalWriteStrategy($update, $remove, $logger);
    }

    public function externalReadStrategy(
        Container $container,
        Parameters $parameters,
        BusLazyManager $bus
    ): ExternalReadStrategy {
        $logger = ($parameters->get(self::P_DEBUG) && $container->has(LoggerInterface::class)) ? $container->get(LoggerInterface::class) : null;

        return new ExternalReadStrategy($bus, $logger);
    }

    public function externalWriteStrategy(
        Container $container,
        Parameters $parameters,
        BusLazyManager $bus
    ): ExternalWriteStrategy {
        $logger = ($parameters->get(self::P_DEBUG) && $container->has(LoggerInterface::class)) ? $container->get(LoggerInterface::class) : null;

        return new ExternalWriteStrategy($bus, $logger);
    }

    public function backendFacade(
        Container $container,
        Parameters $parameters,
        UpdateEntityInterface $update,
        RemoveEntityInterface $remove,
        ReceiveIdsInterface $ids,
        ReceiveViewInterface $view,
        ReceiveViewsInterface $views,
        WaitingForActionInterface $waiting,
        CheckEntityInterface $check,
        DispatcherLazyManager $events,
        BusLazyManager $bus
    ): BackendFacade {
        $logger = ($parameters->get(self::P_DEBUG) && $container->has(LoggerInterface::class)) ? $container->get(LoggerInterface::class) : null;

        return new BackendFacade($update, $remove, $ids, $view, $views, $waiting, $check, $events, $bus, $logger);
    }
}