<?php

namespace Moro\Indexer\Common\Regulation\Factory;

use Moro\Indexer\Common\Regulation\FactoryInterface;
use Moro\Indexer\Common\Regulation\Instruction\UniversalInstruction;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Regulation\Manager\RegulationManager;
use Moro\Indexer\Common\Regulation\ManagerInterface;
use Moro\Indexer\Common\Regulation\Result\RegulationResult;
use Moro\Indexer\Common\Regulation\ResultInterface;
use Moro\Indexer\Common\Regulation\Type\RegulationType;
use Moro\Indexer\Common\Regulation\TypeInterface;

/**
 * Class ClassFactory
 * @package Moro\Indexer\Common\Regulation\Factory
 */
class ClassFactory implements FactoryInterface
{
    protected $_managerClass     = RegulationManager::class;
    protected $_typeClass        = RegulationType::class;
    protected $_instructionClass = UniversalInstruction::class;
    protected $_resultClass      = RegulationResult::class;

    /**
     * @param string $class
     * @return $this
     */
    public function setManagerClass(string $class): FactoryInterface
    {
        $this->_managerClass = $class;

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setTypeClass(string $class): FactoryInterface
    {
        $this->_typeClass = $class;

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setInstructionClass(string $class): FactoryInterface
    {
        $this->_instructionClass = $class;

        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setResultClass(string $class): FactoryInterface
    {
        $this->_resultClass = $class;

        return $this;
    }

    /**
     * @return ManagerInterface
     */
    public function newManager(): ManagerInterface
    {
        return new $this->_managerClass;
    }

    /**
     * @param string $code
     * @return TypeInterface
     */
    public function newType(string $code): TypeInterface
    {
        /** @var TypeInterface $type */
        $type = new $this->_typeClass;
        $type->setCode($code);

        return $type;
    }

    /**
     * @return InstructionInterface
     */
    public function newInstruction(): InstructionInterface
    {
        return new $this->_instructionClass;
    }

    /**
     * @return ResultInterface
     */
    public function newResult(): ResultInterface
    {
        return new $this->_resultClass;
    }
}