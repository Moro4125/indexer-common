<?php

namespace Moro\Indexer\Common\Scheduler\Factory;

use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class ContainerFactory
 * @package Moro\Indexer\Common\Scheduler\Factory
 */
class ContainerFactory implements FactoryInterface
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    protected $_entity;

    /**
     * @param ContainerInterface $container
     * @param string $entity
     */
    public function __construct(ContainerInterface $container, string $entity)
    {
        $this->_container = $container;
        $this->_entity = $entity;
    }

    /**
     * @return EntryInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function newEntry(): EntryInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->_container->get($this->_entity);
    }
}