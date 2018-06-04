<?php

namespace Moro\Indexer\Common\Regulation\Manager;

use Moro\Indexer\Common\Regulation\ResultInterface;
use Moro\Indexer\Common\Regulation\Exception\DuplicateTypeException;
use Moro\Indexer\Common\Regulation\Exception\UnknownTypeException;
use Moro\Indexer\Common\Regulation\ManagerInterface;
use Moro\Indexer\Common\Regulation\TypeInterface;
use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Class RegulationManager
 * @package Moro\Indexer\Common\Regulation\Manager
 */
class RegulationManager implements ManagerInterface
{
    /** @var TypeInterface[] */
    protected $_map;

    /**
     * @param TypeInterface $type
     * @return ManagerInterface
     */
    public function addType(TypeInterface $type): ManagerInterface
    {
        $code = $type->getCode();

        if (isset($this->_map[$code])) {
            throw new DuplicateTypeException(sprintf(DuplicateTypeException::MSG, $code));
        }

        $this->_map[$code] = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return array_keys($this->_map ?? []);
    }

    /**
     * @param string $type
     * @param EntityInterface $entity
     * @return ResultInterface
     */
    public function handle(string $type, EntityInterface $entity): ResultInterface
    {
        if (empty($this->_map[$type])) {
            $exception = new UnknownTypeException(sprintf(UnknownTypeException::MSG, $type));
            $exception->setType($type);
            throw $exception;
        }

        return $this->_map[$type]->handle($entity);
    }
}