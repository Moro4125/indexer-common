<?php

namespace Moro\Indexer\Common\Bus\Manager;

use Moro\Indexer\Common\Bus\AdapterInterface;
use Moro\Indexer\Common\Bus\ManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class LazyManager
 * @package Moro\Indexer\Common\Bus\Manager
 */
class LazyManager implements ManagerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    protected $_manager;
    protected $_instance;

    /**
     * @param ContainerInterface $container
     * @param string|null $manager
     */
    public function __construct(ContainerInterface $container, string $manager = null)
    {
        $this->_container = $container;
        $this->_manager = $manager;
    }

    /**
     * @param AdapterInterface $storage
     * @return ManagerInterface
     */
    public function setAdapter(AdapterInterface $storage): ManagerInterface
    {
        $this->_getManager()->setAdapter($storage);
        return $this;
    }

    /**
     * @param string $owner
     * @return ManagerInterface
     */
    public function setOwner(string $owner): ManagerInterface
    {
        $this->_getManager()->setOwner($owner);

        return $this;
    }

    /**
     * @param string $target
     * @return ManagerInterface
     */
    public function setTarget(string $target): ManagerInterface
    {
        $this->_getManager()->setTarget($target);

        return $this;
    }

    /**
     * @param string $id
     * @return ManagerInterface
     */
    public function setIdentifier(string $id): ManagerInterface
    {
        $this->_getManager()->setIdentifier($id);

        return $this;
    }

    /**
     * @param array $message
     * @param string|null $target
     * @return array
     */
    public function call(array $message, string $target = null): array
    {
        return $this->_getManager()->call($message, $target);
    }

    /**
     * @param array $message
     * @param string|null $target
     */
    public function send(array $message, string $target = null)
    {
        $this->_getManager()->send($message, $target);
    }

    /**
     * @param string|null $for
     * @param string|null $from
     * @return array|null
     */
    public function read(string $for = null, string $from = null): ?array
    {
        return $this->_getManager()->read($for, $from);
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @return ManagerInterface
     */
    protected function _getManager(): ManagerInterface
    {
        $this->_manager = $this->_manager ?? ManagerInterface::class;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_instance = $this->_instance ?? $this->_container->get($this->_manager);

        return $this->_instance;
    }
}