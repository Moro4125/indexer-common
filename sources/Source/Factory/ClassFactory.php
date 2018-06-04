<?php

namespace Moro\Indexer\Common\Source\Factory;

use Moro\Indexer\Common\Source\Adapter\MemoryAdapter;
use Moro\Indexer\Common\Source\AdapterInterface;
use Moro\Indexer\Common\Source\Entity\UniversalEntity;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\FactoryInterface;
use Moro\Indexer\Common\Source\Manager\SourceManager;
use Moro\Indexer\Common\Source\ManagerInterface;
use Moro\Indexer\Common\Source\Normalizer\UniversalNormalizer;
use Moro\Indexer\Common\Source\NormalizerInterface;
use Moro\Indexer\Common\Source\Type\SourceType;
use Moro\Indexer\Common\Source\TypeInterface;

/**
 * Class ClassFactory
 * @package Moro\Indexer\Common\Source\Factory
 */
class ClassFactory implements FactoryInterface
{
    protected $_managerClass    = SourceManager::class;
    protected $_typeClass       = SourceType::class;
    protected $_normalizerClass = UniversalNormalizer::class;
    protected $_entityClass     = UniversalEntity::class;
    protected $_adapterClass    = MemoryAdapter::class;

    /**
     * @param string $class
     */
    public function setManagerClass(string $class)
    {
        $this->_managerClass = $class;
    }

    /**
     * @param string $class
     */
    public function setTypeClass(string $class)
    {
        $this->_typeClass = $class;
    }

    /**
     * @param string $class
     */
    public function setNormalizerClass(string $class)
    {
        $this->_normalizerClass = $class;
    }

    /**
     * @param string $class
     */
    public function setEntityClass(string $class)
    {
        $this->_entityClass = $class;
    }

    /**
     * @param string $class
     */
    public function setAdapterClass(string $class)
    {
        $this->_adapterClass = $class;
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
     * @return NormalizerInterface
     */
    public function newNormalizer(): NormalizerInterface
    {
        return new $this->_normalizerClass;
    }

    /**
     * @return EntityInterface
     */
    public function newEntity(): EntityInterface
    {
        return new $this->_entityClass;
    }

    /**
     * @return AdapterInterface
     */
    public function newAdapter(): AdapterInterface
    {
        return new $this->_adapterClass;
    }
}