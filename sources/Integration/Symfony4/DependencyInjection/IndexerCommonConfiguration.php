<?php

namespace Moro\Indexer\Common\Integration\Symfony4\DependencyInjection;

use Moro\Indexer\Common\Bus\Manager\BusManager;
use Moro\Indexer\Common\Bus\Manager\LazyManager as BusLazyManager;
use Moro\Indexer\Common\Dispatcher\Manager\EventManager;
use Moro\Indexer\Common\Dispatcher\Manager\LazyManager as DispatcherLazyManager;
use Moro\Indexer\Common\Index\Manager\IndexManager;
use Moro\Indexer\Common\Index\Manager\LazyManager as IndexLazyManager;
use Moro\Indexer\Common\Regulation\Manager\LazyManager as RegulationLazyManager;
use Moro\Indexer\Common\Regulation\Manager\RegulationManager;
use Moro\Indexer\Common\Regulation\Type\RegulationType;
use Moro\Indexer\Common\Scheduler\Entry\SchedulerEntry;
use Moro\Indexer\Common\Scheduler\Manager\LazyManager as SchedulerLazyManager;
use Moro\Indexer\Common\Scheduler\Manager\SchedulerManager;
use Moro\Indexer\Common\Source\Manager\LazyManager as SourceLazyManager;
use Moro\Indexer\Common\Source\Manager\SourceManager;
use Moro\Indexer\Common\Source\Type\SourceType;
use Moro\Indexer\Common\Strategy\CheckEntity\CheckEntityStrategy;
use Moro\Indexer\Common\Strategy\ReceiveIds\ReceiveIdsStrategy;
use Moro\Indexer\Common\Strategy\ReceiveView\ReceiveViewStrategy;
use Moro\Indexer\Common\Strategy\ReceiveViews\ReceiveViewsStrategy;
use Moro\Indexer\Common\Strategy\RemoveEntity\RemoveEntityStrategy;
use Moro\Indexer\Common\Strategy\UpdateEntity\UpdateEntityStrategy;
use Moro\Indexer\Common\Strategy\WaitingForAction\WaitingForActionStrategy;
use Moro\Indexer\Common\Transaction\Manager\LazyManager as TransactionLazyManager;
use Moro\Indexer\Common\Transaction\Manager\TransactionManager;
use Moro\Indexer\Common\View\Manager\LazyManager as ViewLazyManager;
use Moro\Indexer\Common\View\Manager\ViewManager;
use Moro\Indexer\Common\View\Type\ViewType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class IndexerCommonConfiguration
 * @package Moro\Indexer\Common\Integration\Symfony4
 */
class IndexerCommonConfiguration implements ConfigurationInterface
{
    const P_DEBUG                          = 'debug';
    const P_TYPES                          = 'types';
    const P_FACADE                         = 'facade'; // monolith, backend, client
    const P_ENTITY_CACHE                   = 'entity_cache'; // integer, 0 - disabled, default - 16
    const P_BUS_ADAPTER_CLASS              = 'bus_adapter_class';
    const P_BUS_MANAGER_CLASS              = 'bus_manager_class';
	const P_BUS_MANAGER_LAZY_CLASS         = 'bus_manager_lazy_class';
	const P_EVENT_MANAGER_CLASS            = 'event_manager_class';
	const P_EVENT_MANAGER_LAZY_CLASS       = 'event_manager_lazy_class';
	const P_SOURCE_REPEAT                  = 'source_repeat';
	const P_SOURCE_REPEAT_INTERVAL         = 'source_repeat_interval';
	const P_SOURCE_TYPE_CLASS              = 'source_type_class';
	const P_SOURCE_ADAPTER_CLASS           = 'source_adapter_class';
	const P_SOURCE_ADAPTERS                = 'source_adapters';
	const P_SOURCE_HTTP_PROXY              = 'http_proxy';
	const P_SOURCE_HTTP_METHOD             = 'http_method';
	const P_SOURCE_HTTP_URL_LIST           = 'http_url_list';
	const P_SOURCE_HTTP_URL_DATA           = 'http_url_data';
	const P_SOURCE_HTTP_AUTH               = 'http_auth';
	const P_SOURCE_MANAGER_CLASS           = 'source_manager_class';
	const P_SOURCE_MANAGER_LAZY_CLASS      = 'source_manager_lazy_class';
	const P_REGULATION_TYPE_CLASS          = 'regulation_type_class';
	const P_REGULATION_MANAGER_CLASS       = 'regulation_manager_class';
    const P_REGULATION_MANAGER_LAZY_CLASS  = 'regulation_manager_lazy_class';
    const P_INDEX_STORAGE_CLASS            = 'index_storage_class';
    const P_INDEX_MANAGER_CLASS            = 'index_manager_class';
    const P_INDEX_MANAGER_LAZY_CLASS       = 'index_manager_lazy_class';
    const P_VIEW_TYPE_CLASS                = 'view_type_class';
    const P_VIEW_STORAGE_CLASS             = 'view_storage_class';
    const P_VIEW_MANAGER_CLASS             = 'view_manager_class';
    const P_VIEW_MANAGER_LAZY_CLASS        = 'view_manager_lazy_class';
    const P_SCHEDULER_ENTRY_CLASS          = 'scheduler_entry_class';
    const P_SCHEDULER_STORAGE_CLASS        = 'scheduler_storage_class';
    const P_SCHEDULER_MANAGER_CLASS        = 'scheduler_manager_class';
    const P_SCHEDULER_MANAGER_LAZY_CLASS   = 'scheduler_manager_lazy_class';
    const P_TRANSACTION_MANAGER_CLASS      = 'transaction_manager_class';
    const P_TRANSACTION_MANAGER_LAZY_CLASS = 'transaction_manager_lazy_class';
	const P_STRATEGY_UPDATE_ENTITY_CLASS   = 'strategy_update_entity_class';
	const P_STRATEGY_REMOVE_ENTITY_CLASS   = 'strategy_remove_entity_class';
	const P_STRATEGY_RECEIVE_IDS_CLASS     = 'strategy_receive_ids_class';
	const P_STRATEGY_RECEIVE_VIEW_CLASS    = 'strategy_receive_view_class';
	const P_STRATEGY_RECEIVE_VIEWS_CLASS   = 'strategy_receive_views_class';
	const P_STRATEGY_WAITING_ACTION_CLASS  = 'strategy_waiting_action_class';
	const P_STRATEGY_CHECK_ENTITY_CLASS    = 'strategy_check_entity_class';
	const P_STRATEGY_CHECK_ENTITY_LIMIT    = 'strategy_check_entity_limit';
	const P_STRATEGY_CHECK_ENTITY_WORKS    = 'strategy_check_entity_works';
	const P_STRATEGY_CHECK_ENTITY_STEPS    = 'strategy_check_entity_steps';

    const V_FACADE_MONOLITH = 'monolith';
    const V_FACADE_BACKEND  = 'backend';
    const V_FACADE_CLIENT   = 'client';

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('moro_indexer_common');

        $root = $rootNode->children();

        $root->booleanNode(self::P_DEBUG)
            ->defaultValue(false);

        $root->arrayNode(self::P_TYPES)
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('scalar');

        $root->scalarNode(self::P_FACADE)
            ->defaultValue(self::V_FACADE_MONOLITH);

        $root->integerNode(self::P_ENTITY_CACHE)
            ->defaultValue(16);

        $root->scalarNode(self::P_BUS_ADAPTER_CLASS);

        $root->scalarNode(self::P_BUS_MANAGER_CLASS)
            ->defaultValue(BusManager::class);

        $root->scalarNode(self::P_BUS_MANAGER_LAZY_CLASS)
            ->defaultValue(BusLazyManager::class);

        $root->scalarNode(self::P_EVENT_MANAGER_CLASS)
            ->defaultValue(EventManager::class);

        $root->scalarNode(self::P_EVENT_MANAGER_LAZY_CLASS)
            ->defaultValue(DispatcherLazyManager::class);

        $root->booleanNode(self::P_SOURCE_REPEAT)
            ->defaultValue(false);

        $root->integerNode(self::P_SOURCE_REPEAT_INTERVAL)
            ->defaultValue(16);

        $root->scalarNode(self::P_SOURCE_ADAPTER_CLASS)
            ->defaultNull();

        $item = $root->arrayNode(self::P_SOURCE_ADAPTERS)
            ->arrayPrototype()
            ->children();
        $item->scalarNode('class')
            ->defaultNull();
        $item->scalarNode(self::P_SOURCE_HTTP_PROXY)
            ->defaultNull();
        $item->scalarNode(self::P_SOURCE_HTTP_METHOD)
            ->defaultValue('GET');
        $item->scalarNode(self::P_SOURCE_HTTP_URL_LIST)
            ->defaultNull();
        $item->scalarNode(self::P_SOURCE_HTTP_URL_DATA)
            ->defaultNull();
        $item->scalarNode(self::P_SOURCE_HTTP_AUTH)
            ->defaultNull();

        $root->scalarNode(self::P_SOURCE_TYPE_CLASS)
            ->defaultValue(SourceType::class);

        $root->scalarNode(self::P_SOURCE_MANAGER_CLASS)
            ->defaultValue(SourceManager::class);

        $root->scalarNode(self::P_SOURCE_MANAGER_LAZY_CLASS)
            ->defaultValue(SourceLazyManager::class);

        $root->scalarNode(self::P_REGULATION_TYPE_CLASS)
            ->defaultValue(RegulationType::class);

        $root->scalarNode(self::P_REGULATION_MANAGER_CLASS)
            ->defaultValue(RegulationManager::class);

        $root->scalarNode(self::P_REGULATION_MANAGER_LAZY_CLASS)
            ->defaultValue(RegulationLazyManager::class);

        $root->scalarNode(self::P_INDEX_STORAGE_CLASS);

        $root->scalarNode(self::P_INDEX_MANAGER_CLASS)
            ->defaultValue(IndexManager::class);

        $root->scalarNode(self::P_INDEX_MANAGER_LAZY_CLASS)
            ->defaultValue(IndexLazyManager::class);

        $root->scalarNode(self::P_VIEW_STORAGE_CLASS);

        $root->scalarNode(self::P_VIEW_TYPE_CLASS)
            ->defaultValue(ViewType::class);

        $root->scalarNode(self::P_VIEW_MANAGER_CLASS)
            ->defaultValue(ViewManager::class);

        $root->scalarNode(self::P_VIEW_MANAGER_LAZY_CLASS)
            ->defaultValue(ViewLazyManager::class);

        $root->scalarNode(self::P_SCHEDULER_STORAGE_CLASS);

        $root->scalarNode(self::P_SCHEDULER_ENTRY_CLASS)
            ->defaultValue(SchedulerEntry::class);

        $root->scalarNode(self::P_SCHEDULER_MANAGER_CLASS)
            ->defaultValue(SchedulerManager::class);

        $root->scalarNode(self::P_SCHEDULER_MANAGER_LAZY_CLASS)
            ->defaultValue(SchedulerLazyManager::class);

        $root->scalarNode(self::P_TRANSACTION_MANAGER_CLASS)
            ->defaultValue(TransactionManager::class);

        $root->scalarNode(self::P_TRANSACTION_MANAGER_LAZY_CLASS)
            ->defaultValue(TransactionLazyManager::class);

        $root->scalarNode(self::P_STRATEGY_UPDATE_ENTITY_CLASS)
            ->defaultValue(UpdateEntityStrategy::class);

        $root->scalarNode(self::P_STRATEGY_REMOVE_ENTITY_CLASS)
            ->defaultValue(RemoveEntityStrategy::class);

        $root->scalarNode(self::P_STRATEGY_RECEIVE_IDS_CLASS)
            ->defaultValue(ReceiveIdsStrategy::class);

        $root->scalarNode(self::P_STRATEGY_RECEIVE_VIEW_CLASS)
            ->defaultValue(ReceiveViewStrategy::class);

        $root->scalarNode(self::P_STRATEGY_RECEIVE_VIEWS_CLASS)
            ->defaultValue(ReceiveViewsStrategy::class);

        $root->scalarNode(self::P_STRATEGY_WAITING_ACTION_CLASS)
            ->defaultValue(WaitingForActionStrategy::class);

        $root->scalarNode(self::P_STRATEGY_CHECK_ENTITY_CLASS)
            ->defaultValue(CheckEntityStrategy::class);

        $root->scalarNode(self::P_STRATEGY_CHECK_ENTITY_LIMIT)
            ->defaultNull();

        $root->scalarNode(self::P_STRATEGY_CHECK_ENTITY_WORKS)
            ->defaultNull();

        $root->scalarNode(self::P_STRATEGY_CHECK_ENTITY_STEPS)
            ->defaultNull();

        return $treeBuilder;
    }
}