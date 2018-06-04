<?php

namespace Moro\Indexer\Common\Configuration\View;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;
use Moro\Indexer\Common\View\FactoryInterface;

/**
 * Class TypeConfigurator
 * @package Moro\Indexer\Common\Configuration\View
 */
class TypeConfigurator implements ConfiguratorInterface
{
    protected $_factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param \Moro\Indexer\Common\View\TypeInterface $type
     */
    public function apply(ConfigurationInterface $configuration, $type)
    {
        $context = $configuration->getContext();

        foreach ($configuration->get('types/{code}/kinds|keys') ?? [] as $kind) {
            $context['kind'] = $kind;
            $kind = $this->_factory->newKind();
            $configuration->apply($kind, $context);
            $type->addKind($kind);
        }
    }
}