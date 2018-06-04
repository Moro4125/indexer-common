<?php

namespace Moro\Indexer\Common\Source;

use Moro\Indexer\Common\Source\Exception\NotFoundException;

/**
 * Interface AdapterInterface
 * @package Moro\Indexer\Common\Source
 */
interface AdapterInterface
{
    /**
     * @param int $from
     * @param int $limit
     * @return array ID => TIMESTAMP
     */
    function receiveIdList(int $from, int $limit): array;

    /**
     * @param string $id
     * @return array
     *
     * @throws NotFoundException
     */
    function receiveEntityById(string $id): array;
}