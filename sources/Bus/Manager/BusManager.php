<?php

namespace Moro\Indexer\Common\Bus\Manager;

use Moro\Indexer\Common\Bus\AdapterInterface;
use Moro\Indexer\Common\Bus\Exception\AdapterFailedException;
use Moro\Indexer\Common\Bus\ManagerInterface;

/**
 * Class BusManager
 * @package Moro\Indexer\Common\Bus\Manager
 */
class BusManager implements ManagerInterface
{
    /** @var AdapterInterface */
    protected $_adapter;
    protected $_owner;
    protected $_target;
    protected $_identifier;

    /**
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter): ManagerInterface
    {
        $this->_adapter = $adapter;

        return $this;
    }

    /**
     * @param string $owner
     * @return ManagerInterface
     */
    public function setOwner(string $owner): ManagerInterface
    {
        $this->_owner = $owner;

        return $this;
    }

    /**
     * @param string $target
     * @return ManagerInterface
     */
    public function setTarget(string $target): ManagerInterface
    {
        $this->_target = $target;

        return $this;
    }

    /**
     * @param string $id
     * @return ManagerInterface
     */
    public function setIdentifier(string $id): ManagerInterface
    {
        $this->_identifier = $id;

        return $this;
    }

    /**
     * @return string
     */
    protected function getIdentifier(): string
    {
        if ($this->_identifier === null) {
            $this->_identifier = getmypid() . '@' . ($_SERVER['SERVER_ADDR'] ?? 'localhost');
        }

        return $this->_identifier;
    }

    /**
     * @param array $message
     * @param string|null $target
     * @return array
     */
    public function call(array $message, string $target = null): array
    {
        assert($this->_adapter !== null);
        assert($this->_owner !== null);

        $identifier = $this->getIdentifier();
        $message['timestamp'] = $message['timestamp'] ?? time();
        $message['sender'] = $message['sender'] ?? [$this->_owner, $identifier];

        try {
            return $this->_adapter->call($this->_owner, $identifier, $target ?? $this->_target, $message);
        } catch (\Throwable $e) {
            $class = get_class($this->_adapter);
            $message = sprintf(AdapterFailedException::MSG, $class, __FUNCTION__);

            $exception = new AdapterFailedException($message, $e->getCode(), $e);
            $exception->class = $class;
            $exception->method = __FUNCTION__;

            throw $exception;
        }
    }

    /**
     * @param array $message
     * @param string|null $target
     */
    public function send(array $message, string $target = null)
    {
        assert($this->_adapter !== null);
        assert($this->_owner !== null);

        $identifier = $this->getIdentifier();
        $message['timestamp'] = $message['timestamp'] ?? time();
        $message['sender'] = $message['sender'] ?? [$this->_owner, $identifier];

        try {
            $this->_adapter->send($this->_owner, $identifier, $target ?? $this->_target, $message);
        } catch (\Throwable $e) {
            $class = get_class($this->_adapter);
            $message = sprintf(AdapterFailedException::MSG, $class, __FUNCTION__);

            $exception = new AdapterFailedException($message, $e->getCode(), $e);
            $exception->class = $class;
            $exception->method = __FUNCTION__;

            throw $exception;
        }
    }

    /**
     * @param string|null $for
     * @param string|null $from
     * @return array|null
     */
    public function read(string $for = null, string $from = null): ?array
    {
        assert($this->_adapter !== null);
        assert($this->_owner !== null);

        $identifier = $this->getIdentifier();

        try {
            return $this->_adapter->read($for ?? $this->_owner, $identifier, $target ?? $this->_target);
        } catch (\Throwable $e) {
            $class = get_class($this->_adapter);
            $message = sprintf(AdapterFailedException::MSG, $class, __FUNCTION__);

            $exception = new AdapterFailedException($message, $e->getCode(), $e);
            $exception->class = $class;
            $exception->method = __FUNCTION__;

            throw $exception;
        }
    }
}