<?php

namespace Moro\Indexer\Common\View;

use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Interface KindInterface
 * @package Moro\Indexer\Common\View
 */
interface KindInterface
{
    /**
     * @return string
     */
    function getCode(): string;

    /**
     * @param EntityInterface $entity
     * @return string
     */
    function handle(EntityInterface $entity): string;
}