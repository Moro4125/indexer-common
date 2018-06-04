<?php

namespace Moro\Indexer\Common\View\Factory;

use Moro\Indexer\Common\View\FactoryInterface;
use Moro\Indexer\Common\View\Kind\UniversalKind;
use Moro\Indexer\Common\View\KindInterface;
use Moro\Indexer\Common\View\Manager\ViewManager;
use Moro\Indexer\Common\View\ManagerInterface;
use Moro\Indexer\Common\View\Type\ViewType;
use Moro\Indexer\Common\View\TypeInterface;

/**
 * Class ClassFactory
 * @package Moro\Indexer\Common\View\Factory
 */
class ClassFactory implements FactoryInterface
{
    private $_managerClass = ViewManager::class;
    private $_typeClass    = ViewType::class;
    private $_kindClass    = UniversalKind::class;

    /**
     * @param string $class
     * @return ClassFactory
     */
    public function setManagerClass(string $class): ClassFactory
    {
        $this->_managerClass = $class;

        return $this;
    }

    /**
     * @param string $class
     * @return ClassFactory
     */
    public function setTypeClass(string $class): ClassFactory
    {
        $this->_typeClass = $class;

        return $this;
    }

    /**
     * @param string $class
     * @return ClassFactory
     */
    public function setKindClass(string $class): ClassFactory
    {
        $this->_kindClass = $class;

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
     * @return KindInterface
     */
    public function newKind(): KindInterface
    {
        return new $this->_kindClass;
    }
}