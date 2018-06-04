<?php

namespace Moro\Indexer\Common\Source\Entity;

use Moro\Indexer\Common\Accessories\ArraysGetByPathTrait;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use Moro\Indexer\Common\Source\ManagerInterface;

/**
 * Class UniversalEntity
 * @package Moro\Indexer\Common\Source\Entity
 */
class UniversalEntity implements EntityInterface, \ArrayAccess
{
    use ArraysGetByPathTrait {
        ArraysGetByPathTrait::_getByPath as private _getByPathParent;
        ArraysGetByPathTrait::_replaceNode as private _replaceNodeParent;
        ArraysGetByPathTrait::_executeFilter as private _executeFilterParent;
    }

    const PREG_NO_LIMIT = -1;

    private $_manager;
    private $_data;
    private $_cache;

    public function __construct(ManagerInterface $manager = null)
    {
        $this->_manager = $manager;
    }

    public function load(array $source)
    {
        if (empty($source['id'])) {
            throw new WrongStructureException('Field "id" is not exists.');
        }

        $this->_data = $source;
        return $this;
    }

    public function getId(): string
    {
        return $this->_data['id'] ?? '';
    }

    public function getName(): string
    {
        return $this->_data['name'] ?? '';
    }

    public function getUpdatedAt(): int
    {
        return $this->_data['updated_at'] ?? 0;
    }

    public function getActiveFrom(): int
    {
        return $this->_data['active_from'] ?? time();
    }

    public function getActiveTo(): ?int
    {
        return $this->_data['active_to'] ?? null;
    }

    public function jsonSerialize()
    {
        return (array)$this->_data;
    }

    public function offsetExists($offset)
    {
        $results = $this->_getByPath($offset, $this->_data, $flag);

        return !empty($results);
    }

    public function offsetGet($offset)
    {
        $results = $this->_getByPath($offset, $this->_data, $flag);

        return $flag ? ($results ?: null) : (count($results) ? reset($results) : null);
    }

    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException(sprintf('Class %1$s is "read only".', static::class));
    }

    public function offsetUnset($offset)
    {
        throw new \RuntimeException(sprintf('Class %1$s is "read only".', static::class));
    }

    protected function _getByPath(string $path, array $data, bool &$flag = null)
    {
        if (isset($this->_cache[$path])) {
            list($results, $flag) = $this->_cache[$path];
        } else {
            $results = $this->_getByPathParent($path, $data, $flag);
            $this->_cache[$path] = [$results, $flag];
        }

        return $results;
    }

    protected function _replaceNode($value, $root)
    {
        if ($this->_manager) {
            if (is_array($value) && ($v = $value['@id'] ?? null) && is_string($v) && strpos($v, ':')) {
                list($type, $id,) = explode(':', $v);
                try {
                    $value = (array)$this->_manager->getEntity($type, $id)
                        ->jsonSerialize();
                } catch (NotFoundException $exception) {
                    $value = null;
                }
            }
        }

        return $this->_replaceNodeParent($value, $root);
    }

    protected function _executeFilter(string $name, array $results, array $arguments, ?bool &$flag): array
    {
        if ($name === 'invert') {
            foreach ($results as &$result) {
                if (is_string($result)) {
                    $result = self::invertString($result);
                }
            }

            return $results;
        }

        return $this->_executeFilterParent($name, $results, $arguments, $flag);
    }

    static function invertString(string $string): string
    {
        static $invert;

        if (empty($invert)) {
            $charsChunks = [
                '0123456789',
                'abcdefghijklmnopqrstuvwxyz',
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            ];
            foreach ($charsChunks as $chars) {
                if (preg_match_all('/./', $chars, $matches, PREG_SET_ORDER)) {
                    $chars = array_column($matches, 0);
                    $chars = array_combine($chars, array_reverse($chars, false));
                    $invert = array_merge($invert ?? [], $chars);
                }
            }
        }

        return strtr($string, $invert);
    }
}