<?php

namespace Moro\Indexer\Common\Configuration\Adapter;

use Moro\Indexer\Common\Configuration\AdapterInterface;

/**
 * Class MemoryAdapter
 * @package Moro\Indexer\Common\Configuration\Adapter
 */
class MemoryAdapter implements AdapterInterface
{
    private $_configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->_configuration = $configuration;
    }

    /**
     * @return array
     */
    public function load(): array
    {
        return $this->_configuration ?? [];
    }

    /**
     * @param array $configuration
     * @return bool
     */
    public function save(array $configuration): bool
    {
        return false;
    }
}