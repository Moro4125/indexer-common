<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Bus\AdapterInterface;

/**
 * Class DummyAdapter
 * @package Moro\Indexer\Test
 */
class DummyAdapter implements AdapterInterface
{
    protected $_messages = [];
    protected $_callback;

    public function setCallback(callable $callback)
    {
        $this->_callback = $callback;
    }

    /**
     * @param string $from
     * @param string $identifier
     * @param string $target
     * @param array $message
     * @return array
     */
    public function call(string $from, string $identifier, string $target, array $message): array
    {
        $this->send($from, $identifier, $target, $message);
        call_user_func($this->_callback);
        return $this->read($from, $identifier) ?? [];
    }

    /**
     * @param string $from
     * @param string $identifier
     * @param string $target
     * @param array $message
     */
    public function send(string $from, string $identifier, string $target, array $message)
    {
        array_push($this->_messages, $message);
    }

    /**
     * @param string $for
     * @param string|null $identifier
     * @param string|null $from
     * @return array|null
     */
    public function read(string $for, string $identifier = null, string $from = null): ?array
    {
        return array_shift($this->_messages) ?: null;
    }
}