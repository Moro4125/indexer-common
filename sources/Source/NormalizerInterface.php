<?php

namespace Moro\Indexer\Common\Source;

/**
 * Interface NormalizerInterface
 * @package Moro\Indexer\Common\Source
 */
interface NormalizerInterface
{
    /**
     * @param array $record
     * @return array|null
     */
    function normalize(array $record): ?array;
}