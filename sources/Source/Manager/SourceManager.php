<?php

namespace Moro\Indexer\Common\Source\Manager;

use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\Exception\DuplicateTypeException;
use Moro\Indexer\Common\Source\Exception\UnknownTypeException;
use Moro\Indexer\Common\Source\ManagerInterface;
use Moro\Indexer\Common\Source\TypeInterface;

/**
 * Class SourceManager
 * @package Moro\Indexer\Common\Source\Manager
 */
class SourceManager implements ManagerInterface
{
    /** @var TypeInterface[] */
    protected $_map;

    /**
     * @param TypeInterface $type
     * @return $this
     */
    public function addType(TypeInterface $type)
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
     * @param int $from
     * @param int $limit
     * @return array
     */
    public function getIdList(string $type, int $from, int $limit): array
    {
        if (empty($this->_map[$type])) {
            $exception = new UnknownTypeException(sprintf(UnknownTypeException::MSG, $type));
            $exception->setType($type);
            throw $exception;
        }

        return $this->_map[$type]->getIdList($from, $limit);
    }

    /**
     * @param string $type
     * @param string $id
     * @return EntityInterface
     */
    public function getEntity(string $type, string $id): EntityInterface
    {
        if (empty($this->_map[$type])) {
            $exception = new UnknownTypeException(sprintf(UnknownTypeException::MSG, $type));
            $exception->setType($type);
            throw $exception;
        }

        return $this->_map[$type]->getEntityById($id);
    }
}