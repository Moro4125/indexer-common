<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Source\NormalizerInterface;

/**
 * Class DummyNormalizer
 * @package Moro\Indexer\Test
 */
class DummyNormalizer implements NormalizerInterface
{
    /**
     * @param array $record
     * @return array|null
     */
    public function normalize(array $record): ?array
    {
        return null;
    }
}