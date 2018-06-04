<?php

namespace Moro\Indexer\Common\Source\Normalizer;

use Moro\Indexer\Common\Accessories\ArraysGetByPathTrait;
use Moro\Indexer\Common\Accessories\ArraysSetByPathTrait;
use Moro\Indexer\Common\Source\NormalizerInterface;

/**
 * Class UniversalNormalizer
 * @package Moro\Indexer\Common\Source\Normalizer
 */
class UniversalNormalizer implements NormalizerInterface
{
    use ArraysGetByPathTrait;
    use ArraysSetByPathTrait;

    private $_conditions;
    private $_rules;

    public function __construct()
    {
        $this->_conditions = [];
        $this->_rules = [];
    }

    public function addCondition(string $path)
    {
        $this->_conditions[] = $path;
    }

    public function addRule(string $from, string $to)
    {
        $this->_rules[] = [$from, $to];
    }

    public function normalize(array $record): ?array
    {
        foreach ($this->_conditions as $path) {
            if (!$this->_getByPath($path, $record)) {
                return null;
            }
        }

        $result = [];

        foreach ($this->_rules as list($from, $to)) {
            $flag = false;
            $values = $this->_getByPath($from, $record, $flag);
            $value = $values ? ($flag ? $values : reset($values)) : [];
            $result = $this->_setByPath($to, $value, $result, $flag);
        }

        return $result;
    }
}