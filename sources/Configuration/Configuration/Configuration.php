<?php

namespace Moro\Indexer\Common\Configuration\Configuration;

use Moro\Indexer\Common\Accessories\ArraysGetByPathTrait;
use Moro\Indexer\Common\Configuration\ConfigurationInterface;
use Moro\Indexer\Common\Configuration\ManagerInterface;

/**
 * Class Configuration
 * @package Moro\Indexer\Common\Configuration\Configuration
 */
class Configuration implements ConfigurationInterface
{
    use ArraysGetByPathTrait {
        ArraysGetByPathTrait::_getByPath as private _getByPathParent;
    }

    /** @var ManagerInterface */
    protected $_manager;
    protected $_context;
    protected $_config;
    protected $_cache;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    /**
     * @param ManagerInterface $manager
     * @return ConfigurationInterface
     */
    public function setManager(ManagerInterface $manager): ConfigurationInterface
    {
        $this->_manager = $manager;

        return $this;
    }

    /**
     * @param array|null $context
     * @return ConfigurationInterface
     */
    public function setContext(?array $context): ConfigurationInterface
    {
        $this->_context = $context;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->_context;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $key = preg_replace_callback('/\\{(.*?)\\}/', function ($match) {
            return $this->_context[$match[1]] ?? $match[0];
        }, $key);

        $results = $this->_getByPath($key, $this->_config, $flag);

        return !empty($results);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $key = preg_replace_callback('/\\{(.*?)\\}/', function ($match) {
            return $this->_context[$match[1]] ?? $match[0];
        }, $key);

        $results = $this->_getByPath($key, $this->_config, $flag);

        return $flag ? ($results ?: null) : (count($results) ? reset($results) : null);
    }

    /**
     * @param $object
     * @param array|null $context
     * @return bool
     */
    public function apply($object, array $context = null): bool
    {
        return $this->_manager ? $this->_manager->apply($object, $context) : false;
    }

    /**
     * @param string $path
     * @param array $data
     * @param bool|null $flag
     * @return array
     */
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
}