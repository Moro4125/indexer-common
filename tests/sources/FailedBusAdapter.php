<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Bus\AdapterInterface;

/**
 * Class FailedBusAdapter
 * @package Moro\Indexer\Test
 */
class FailedBusAdapter implements AdapterInterface
{
    /**
     * @param string $from
     * @param string $identifier
     * @param string $target
     * @param array $message
     * @return array
     */
    public function call(string $from, string $identifier, string $target, array $message): array
    {
        throw new \RuntimeException();
    }

    /**
     * @param string $from
     * @param string $identifier
     * @param string $target
     * @param array $message
     */
    public function send(string $from, string $identifier, string $target, array $message)
    {
        throw new \RuntimeException();
    }

    /**
     * @param string $for
     * @param string|null $identifier
     * @param string|null $from
     * @return array|null
     */
    public function read(string $for, string $identifier = null, string $from = null): ?array
    {
        throw new \RuntimeException();
    }
}