<?php

namespace Moro\Indexer\Common\Source\Type\Decorator;

use Moro\Indexer\Common\DecoratorInterface;
use Moro\Indexer\Common\Source\AdapterInterface;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\FactoryInterface;
use Moro\Indexer\Common\Source\NormalizerInterface;
use Moro\Indexer\Common\Source\TypeInterface;

/**
 * Class AbstractDecorator
 * @package Moro\Indexer\Common\Source\Type\Decorator
 */
abstract class AbstractDecorator implements TypeInterface, DecoratorInterface
{
    protected $_instance;

    /**
     * @return TypeInterface
     */
    public function getDecoratedInstance(): TypeInterface
    {
        return $this->_instance;
    }

    /**
     * @param string $name
     * @return TypeInterface
     */
    public function setCode(string $name): TypeInterface
    {
        $this->getDecoratedInstance()
            ->setCode($name);

        return $this;
    }

    /**
     * @param AdapterInterface $adapter
     * @return TypeInterface
     */
    public function setAdapter(AdapterInterface $adapter): TypeInterface
    {
        $this->getDecoratedInstance()
            ->setAdapter($adapter);

        return $this;
    }

    /**
     * @param NormalizerInterface $normalizer
     * @return TypeInterface
     */
    public function addNormalizer(NormalizerInterface $normalizer): TypeInterface
    {
        $this->getDecoratedInstance()
            ->addNormalizer($normalizer);

        return $this;
    }

    /**
     * @param FactoryInterface $factory
     * @return $this|TypeInterface
     */
    public function setEntityFactory(FactoryInterface $factory)
    {
        $this->getDecoratedInstance()
            ->setEntityFactory($factory);

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->getDecoratedInstance()
            ->getCode();
    }

    /**
     * @param int $from
     * @param int $limit
     * @return array
     */
    public function getIdList(int $from, int $limit): array
    {
        return $this->getDecoratedInstance()
            ->getIdList($from, $limit);
    }

    /**
     * @param string $id
     * @return EntityInterface
     */
    public function getEntityById(string $id): EntityInterface
    {
        return $this->getDecoratedInstance()
            ->getEntityById($id);
    }
}