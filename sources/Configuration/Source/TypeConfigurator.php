<?php

namespace Moro\Indexer\Common\Configuration\Source;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;
use Moro\Indexer\Common\Source\FactoryInterface;

/**
 * Class TypeConfigurator
 * @package Moro\Indexer\Common\Configuration\Source
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
     * @param \Moro\Indexer\Common\Source\TypeInterface $type
     */
    public function apply(ConfigurationInterface $configuration, $type)
    {
        $context = $configuration->getContext();

        $adapter = $this->_factory->newAdapter();
        $configuration->apply($adapter, $context);
        $type->setAdapter($adapter);

        foreach ($configuration->get('types/{code}/normalizers|keys') ?? [] as $index) {
            $context['index'] = $index;
            $normalizer = $this->_factory->newNormalizer();
            $configuration->apply($normalizer, $context);
            $type->addNormalizer($normalizer);
        }

        $type->setEntityFactory($this->_factory);
    }
}