<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Regulation\ResultInterface;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Class SimpleInstruction
 * @package Moro\Indexer\Test
 */
class SimpleInstruction implements InstructionInterface
{
    protected $_required;
    protected $_target;
    protected $_kinds;

    /**
     * @param array $required
     * @param array $target
     * @param array|null $kinds
     */
    public function __construct(array $required, array $target, array $kinds = null)
    {
        $this->_required = $required;
        $this->_target = $target;
        $this->_kinds = $kinds ?? [];
    }

    /**
     * @param EntityInterface $entity
     * @param ResultInterface $collection
     */
    public function handle(EntityInterface $entity, ResultInterface $collection)
    {
        if (in_array($entity->getName(), $this->_required)) {
            foreach ($this->_target as $order => $target) {
                $collection->addToIndex($target, $entity->getUpdatedAt() ?: $order);
            }

            foreach ($this->_kinds as $kind) {
                $collection->addUsedKind($kind);
            }
        }
    }
}