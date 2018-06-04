<?php

namespace Moro\Indexer\Common\Regulation\Instruction;

use ArrayAccess;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Regulation\ResultInterface;
use Moro\Indexer\Common\Source\Entity\UniversalEntity;
use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Class UniversalInstruction
 * @package Moro\Indexer\Common\Regulation\Instruction
 */
class UniversalInstruction implements InstructionInterface
{
    protected $_conditions;
    protected $_variables;
    protected $_indexes;
    protected $_kinds;
    protected $_scheduler;

    /** @var InstructionInterface[] */
    protected $_thenInstructions;
    /** @var InstructionInterface[] */
    protected $_elseInstructions;

    /**
     * @param string $condition
     * @return UniversalInstruction
     */
    public function addCondition(string $condition): UniversalInstruction
    {
        $this->_conditions[] = $condition;

        return $this;
    }

    /**
     * @param string $name
     * @param string $path
     * @return UniversalInstruction
     */
    public function addVariable(string $name, string $path): UniversalInstruction
    {
        $this->_variables[$name] = $path;

        return $this;
    }

    /**
     * @param string $index
     * @param string $order
     * @return UniversalInstruction
     */
    public function addIndex(string $index, string $order): UniversalInstruction
    {
        $this->_indexes[$index] = $order;

        return $this;
    }

    /**
     * @param string $kind
     * @return UniversalInstruction
     */
    public function addKind(string $kind): UniversalInstruction
    {
        $this->_kinds[] = $kind;

        return $this;
    }

    /**
     * @param string $variable
     * @return UniversalInstruction
     */
    public function addScheduler(string $variable): UniversalInstruction
    {
        $this->_scheduler[] = $variable;

        return $this;
    }

    public function addThenInstruction(InstructionInterface $instruction): UniversalInstruction
    {
        $this->_thenInstructions[] = $instruction;

        return $this;
    }

    public function addElseInstruction(InstructionInterface $instruction): UniversalInstruction
    {
        $this->_elseInstructions[] = $instruction;

        return $this;
    }

    /**
     * @param EntityInterface $entity
     * @param ResultInterface $result
     */
    public function handle(EntityInterface $entity, ResultInterface $result)
    {
        $variables = [];

        if (!$entity instanceof ArrayAccess) {
            $data = json_decode(json_encode($entity), true);
            $entity = new UniversalEntity();
            $entity->load($data);
        }

        foreach ($this->_conditions ?? [] as $condition) {
            if (!isset($entity[$condition])) {
                foreach ($this->_elseInstructions ?? [] as $instruction) {
                    $instruction->handle($entity, $result);
                }

                return;
            }
        }

        foreach ($this->_thenInstructions ?? [] as $instruction) {
            $instruction->handle($entity, $result);
        }

        foreach ($this->_variables ?? [] as $name => $path) {
            $variables[$name] = $entity[$path] ?? null;
        }

        foreach ($this->_indexes ?? [] as $name => $value) {
            $order = $this->_mapVariables($variables, $value);
            $order = reset($order);

            foreach ($this->_mapVariables($variables, $name) as $alias) {
                $result->addToIndex($alias, $order);
            }
        }

        foreach ($this->_kinds ?? [] as $kind) {
            $result->addUsedKind($kind);
        }

        foreach ($this->_scheduler ?? [] as $value) {
            $times = $this->_mapVariables($variables, $value);
            $time = reset($times);

            $timezone = new \DateTimeZone('UTC');
            $datetime = new \DateTime($time, $timezone);
            $timestamp = $datetime->getTimestamp();

            $result->addToScheduler($timestamp);
        }
    }

    protected function _mapVariables(array $variables, string $value): array
    {
        if (!preg_match_all('/\\{(.*?)\\}/', $value, $matches, PREG_SET_ORDER)) {
            return [$value];
        }

        $results = [];
        $pattern = [];

        foreach ($matches as $match) {
            $pattern[$match[0]] = $variables[$match[1]] ?? $match[0];

            if (!is_array($pattern[$match[0]])) {
                $pattern[$match[0]] = [$pattern[$match[0]]];
            }
        }

        foreach ($this->_cartesian($pattern) as $replace) {
            $results[] = strtr($value, $replace);
        }

        return $results;
    }

    protected function _cartesian($input)
    {
        $input = array_filter($input);
        $result = [[]];

        foreach ($input as $key => $values) {
            $append = [];

            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }
}