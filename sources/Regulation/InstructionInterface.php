<?php

namespace Moro\Indexer\Common\Regulation;

use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Interface InstructionInterface
 * @package Moro\Indexer\Common\Regulation
 */
interface InstructionInterface
{
    /**
     * @param EntityInterface $entity
     * @param ResultInterface $collection
     */
    function handle(EntityInterface $entity, ResultInterface $collection);
}