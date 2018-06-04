<?php

namespace Moro\Indexer\Common\Regulation\Type;

use Moro\Indexer\Common\Regulation\Exception\InstructionFailedException;
use Moro\Indexer\Common\Regulation\FactoryInterface;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Regulation\ResultInterface;
use Moro\Indexer\Common\Regulation\TypeInterface;
use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Class RegulationType
 * @package Moro\Indexer\Common\Regulation\Type
 */
class RegulationType implements TypeInterface
{
    protected $_code;
    /** @var InstructionInterface[] */
    protected $_list;
    /** @var FactoryInterface */
    protected $_factory;

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): TypeInterface
    {
        assert(strlen($code));
        $this->_code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code;
    }

    /**
     * @param FactoryInterface $factory
     * @return TypeInterface
     */
    public function setResultFactory(FactoryInterface $factory): TypeInterface
    {
        $this->_factory = $factory;

        return $this;
    }

    /**
     * @param InstructionInterface $instruction
     * @return TypeInterface
     */
    public function addInstruction(InstructionInterface $instruction): TypeInterface
    {
        $this->_list[] = $instruction;

        return $this;
    }

    /**
     * @param EntityInterface $entity
     * @return ResultInterface
     */
    public function handle(EntityInterface $entity): ResultInterface
    {
        assert($this->_factory !== null);

        $result = $this->_factory->newResult();

        try {
            foreach ($this->_list ?? [] as $instruction) {
                $instruction->handle($entity, $result);
            }
        } catch (\Throwable $e) {
            $type = $this->getCode();
            $id = $entity->getId();
            $message = sprintf(InstructionFailedException::MSG, get_class($instruction), $type, $id);

            $exception = new InstructionFailedException($message, $e->getCode(), $e);
            $exception->type = $type;
            $exception->id = $id;

            throw $exception;
        }

        return $result;
    }
}