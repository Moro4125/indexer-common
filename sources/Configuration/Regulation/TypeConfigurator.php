<?php

namespace Moro\Indexer\Common\Configuration\Regulation;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;
use Moro\Indexer\Common\Regulation\FactoryInterface;

/**
 * Class TypeConfigurator
 * @package Moro\Indexer\Common\Configuration\Regulation
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
     * @param \Moro\Indexer\Common\Regulation\TypeInterface $type
     */
    public function apply(ConfigurationInterface $configuration, $type)
    {
        $context = $configuration->getContext();
        $type->setFactory($this->_factory);

        foreach ($configuration->get('types/{code}/instructions|keys') ?? [] as $index) {
            $context['index'] = $index;
            $instruction = $this->_factory->newInstruction();
            $configuration->apply($instruction, $context);
            $type->addInstruction($instruction);
        }
    }
}