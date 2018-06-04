<?php

namespace Moro\Indexer\Common\Source\Type\Decorator;

use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\TypeInterface;

/**
 * Class EntityCacheDecorator
 * @package Moro\Indexer\Common\Source\Type\Decorator
 */
class EntityCacheDecorator extends AbstractDecorator
{
    private static $cache;
    private        $_limit;
    private        $_code;

    public function __construct(TypeInterface $type, int $limit = null)
    {
        $this->_instance = $type;
        $this->_limit = $limit ?? 16;
    }

    public function getCode(): string
    {
        $code = $this->_code ?? ($this->_code = $this->getDecoratedInstance()
                ->getCode());

        return $code;
    }

    public function getEntityById(string $id): EntityInterface
    {
        $code = $this->_code ?? $this->getCode();

        $entity = self::$cache[$code][$id] ?? $this->getDecoratedInstance()
                ->getEntityById($id);
        unset(self::$cache[$code][$id]);
        self::$cache[$code][$id] = $entity;

        if (count(self::$cache[$code]) > $this->_limit) {
            array_shift(self::$cache[$code]);
        }

        return $entity;
    }

    static function clearEntity(string $code, string $id)
    {
        unset(self::$cache[$code][$id]);
    }
}