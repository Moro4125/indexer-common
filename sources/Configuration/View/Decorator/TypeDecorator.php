<?php

namespace Moro\Indexer\Common\Configuration\View\Decorator;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\View\FactoryInterface;
use Moro\Indexer\Common\View\Type\Decorator\AbstractDecorator;
use Moro\Indexer\Common\View\TypeInterface;

/**
 * Class TypeDecorator
 * @package Moro\Indexer\Common\Configuration\View\Decorator
 */
class TypeDecorator extends AbstractDecorator
{
    protected $_configuration;
    protected $_factory;
    protected $_code;

    public function __construct(ConfigurationInterface $configuration, FactoryInterface $factory, string $code)
    {
        $this->_configuration = $configuration;
        $this->_factory = $factory;
        $this->_code = $code;
    }

    public function getCode(): string
    {
        return $this->_code;
    }

    public function getDecoratedInstance(): TypeInterface
    {
        if (empty($this->_instance)) {
            $context = ['code' => $this->_code];

            $this->_instance = $this->_factory->newType($this->_code);
            $this->_configuration->apply($this->_instance, $context);
        }

        return $this->_instance;
    }
}