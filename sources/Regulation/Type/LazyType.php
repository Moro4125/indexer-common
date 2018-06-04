<?php

namespace Moro\Indexer\Common\Regulation\Type;

use Moro\Indexer\Common\Regulation\Type\Decorator\AbstractDecorator;
use Moro\Indexer\Common\Regulation\TypeInterface;
use Psr\Container\ContainerInterface;

/**
 * Class LazyType
 * @package Moro\Indexer\Common\Source\Type
 */
class LazyType extends AbstractDecorator
{
    /**
     * @var ContainerInterface
     */
    protected $_container;
    protected $_type;

    /**
     * @param ContainerInterface $container
     * @param string|null $type
     */
    public function __construct(ContainerInterface $container, string $type = null)
    {
        $this->_container = $container;
        $this->_type = $type;
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @return TypeInterface
     */
    public function getDecoratedInstance(): TypeInterface
    {
        $this->_type = $this->_type ?? TypeInterface::class;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_instance = $this->_instance ?? $this->_container->get($this->_type);

        return $this->_instance;
    }
}