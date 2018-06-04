<?php

namespace Moro\Indexer\Common\Configuration\Regulation;

use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;

/**
 * Class UniversalInstructionConfigurator
 * @package Moro\Indexer\Common\Configuration\Regulation
 */
class UniversalInstructionConfigurator implements ConfiguratorInterface
{
    /**
     * @param ConfigurationInterface $configuration
     * @param \Moro\Indexer\Common\Regulation\Instruction\UniversalInstruction $instruction
     */
    public function apply(ConfigurationInterface $configuration, $instruction)
    {
        $ns = 'types/{code}/instructions/{index}';

        foreach ($configuration->get("$ns/conditions/*") ?? [] as $condition) {
            $instruction->addCondition($condition);
        }

        foreach ($configuration->get("$ns/variables") ?? [] as $name => $path) {
            $instruction->addVariable($name, $path);
        }

        foreach ($configuration->get("$ns/indexes") ?? [] as $name => $order) {
            $instruction->addIndex($name, $order);
        }

        foreach ($configuration->get("$ns/kinds") ?? [] as $kind) {
            $instruction->addKind($kind);
        }

        foreach ($configuration->get("$ns/scheduler") ?? [] as $datetime) {
            $instruction->addScheduler($datetime);
        }
    }
}