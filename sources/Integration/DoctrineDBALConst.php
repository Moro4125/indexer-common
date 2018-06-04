<?php

namespace Moro\Indexer\Common\Integration;

/**
 * Interface DoctrineDBALConst
 * @package Moro\Indexer\Common\Integration
 */
interface DoctrineDBALConst
{
    const TABLE_BUS = 'indexer_bus';

    const COL_BUS_SENDER     = 'sender';
    const COL_BUS_IDENTIFIER = 'identifier';
    const COL_BUS_TARGET     = 'target';
    const COL_BUS_MESSAGE    = 'message';
    const COL_BUS_CREATED_AT = 'created_at';
    const COL_BUS_LOCKED_AT  = 'locked_at';
    const COL_BUS_LOCKED_BY  = 'locked_by';

    const TABLE_INDEX_LIST = 'indexer_index_list';

    const COL_INDEX_LIST_ID      = 'id';
    const COL_INDEX_LIST_NAME    = 'name';
    const COL_INDEX_LIST_TYPE_ID = 'type_id';

    const TABLE_INDEX_DATA = 'indexer_index_data';

    const COL_INDEX_DATA_INDEX_ID   = 'index_id';
    const COL_INDEX_DATA_ENTITY_ID  = 'entity_id';
    const COL_INDEX_DATA_ORDER      = 'the_order';
    const COL_INDEX_DATA_UPDATED_AT = 'updated_at';

    const TABLE_SCHEDULER = 'indexer_scheduler';

    const COL_SCHEDULER_ORDER     = 'order_at';
    const COL_SCHEDULER_TYPE_ID   = 'type_id';
    const COL_SCHEDULER_ENTITY_ID = 'entity_id';
    const COL_SCHEDULER_ACTION    = 'action';

    const TABLE_VIEW = 'indexer_view';

    const COL_VIEW_TYPE_ID    = 'type_id';
    const COL_VIEW_KIND_ID    = 'kind_id';
    const COL_VIEW_ENTITY_ID  = 'entity_id';
    const COL_VIEW_CONTENT    = 'content';
    const COL_VIEW_UPDATED_AT = 'updated_at';
}