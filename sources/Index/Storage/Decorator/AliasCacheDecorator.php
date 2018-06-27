<?php

namespace Moro\Indexer\Common\Index\Storage\Decorator;

use Moro\Indexer\Common\Index\StorageInterface;

/**
 * Class AliasCacheDecorator
 * @package Moro\Indexer\Common\Index\Storage\Decorator
 */
class AliasCacheDecorator extends AbstractDecorator
{
    private static $_hasAlias;
    private static $_hasIndex;
    private static $_typeByIndex;

    public function __construct(StorageInterface $storage)
    {
        $this->_instance = $storage;
    }

    public function hasAlias(int $index): ?string
    {
        return self::$_hasAlias[$index] ?? (self::$_hasAlias[$index] = parent::hasAlias($index));
    }

    public function hasIndex(string $alias): ?int
    {
        return self::$_hasIndex[$alias] ?? (self::$_hasIndex[$alias] = parent::hasIndex($alias));
    }

    public function getTypeByIndex(string $alias): ?string
    {
        return self::$_typeByIndex[$alias] ?? (self::$_typeByIndex[$alias] = parent::getTypeByIndex($alias));
    }

    public function addIndex(string $alias, string $type): int
    {
        self::$_typeByIndex[$alias] = null;
        self::$_hasIndex[$alias] = null;

        $id = parent::addIndex($alias, $type);

        self::$_hasAlias[$id] = $alias;
        self::$_typeByIndex[$alias] = $type;
        self::$_hasIndex[$alias] = $id;

        return $id;
    }

    public function dropIndex(int $index): bool
    {
        self::clear();

        return parent::dropIndex($index);
    }

    static function clear()
    {
        self::$_hasAlias = null;
        self::$_hasIndex = null;
        self::$_typeByIndex = null;
    }
}