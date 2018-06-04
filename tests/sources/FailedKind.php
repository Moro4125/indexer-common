<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Class FailedKind
 * @package Moro\Indexer\Test
 */
class FailedKind extends SimpleKind
{
    /**
     * @param EntityInterface $entity
     * @return string
     */
    public function handle(EntityInterface $entity): string
    {
        throw new \RuntimeException();
    }
}