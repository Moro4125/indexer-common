<?php

namespace Moro\Indexer\Common\Source\Adapter;

use Moro\Indexer\Common\Source\AdapterInterface;
use Moro\Indexer\Common\Source\Exception\NotFoundException;

/**
 * Class MemoryAdapter
 * @package Moro\Indexer\Common\Source\Adapter
 */
class MemoryAdapter implements AdapterInterface
{
    protected $_list;
    protected $_data;

    /**
     * @param int $from
     * @param int $limit
     * @return array
     */
    public function receiveIdList(int $from, int $limit): array
    {
        $list = $this->_list ?? [];
        uasort($list, function ($a, $b) {
            return $b <=> $a;
        });

        return array_slice($list, $from, $limit, true);
    }

    /**
     * @param string $type
     * @param string $id
     * @return array
     */
    public function receiveEntityById(string $id): array
    {
        if (!array_key_exists($id, $this->_list ?? [])) {
            $exception = new NotFoundException(sprintf(NotFoundException::MSG, $id, 'unknown'));
            $exception->id = $id;

            throw $exception;
        }

        return $this->_data[$id];
    }

    /**
     * @param string $id
     * @param int $timestamp
     * @param array $record
     */
    public function addEntityRecord(string $id, int $timestamp, array $record)
    {
        $this->_list[$id] = $timestamp;
        $this->_data[$id] = $record;
    }
}