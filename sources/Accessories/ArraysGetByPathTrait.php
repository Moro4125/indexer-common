<?php

namespace Moro\Indexer\Common\Accessories;

use RuntimeException;

/**
 * Trait ArraysGetByPathTrait
 * @package Moro\Indexer\Common\Accessories
 */
trait ArraysGetByPathTrait
{
    private $_silent = true;

    protected function _getByPath(string $path, array $data, bool &$flag = null)
    {
        $pattern = '/(?<!\\\\)( |\\/|\\[|\\]|!=|>|<|=|"|\'|,|\\(|\\)|\\|)/';
        $chunks = preg_split($pattern, $path, -1, PREG_SPLIT_DELIM_CAPTURE);
        $quot = false;
        $chunks = array_filter($chunks, function ($chunk) use (&$quot) {
            $quot = ($quot === $chunk) ? false : (($chunk === "'" || $chunk === '"') ? $chunk : $quot);

            return ($quot === false) ? ($chunk !== ' ' && $chunk !== '') : ($chunk !== '');
        });
        $chunks = array_map(function ($chunk) {
            return preg_replace('/\\\\(\\\\)?/', '$1', $chunk);
        }, $chunks);

        try {
            return $this->_getByChunks($chunks, $data, $flag, $data);
        } catch (RuntimeException $exception) {
            $message = sprintf('Error in path "%1$s": %2$s', $path, $exception->getMessage());
            throw new RuntimeException($message, $exception->getCode(), $exception);
        }
    }

    protected function _getByChunks(array $chunks, array $data, ?bool &$flag, array $root)
    {
        $pathsChunks = $this->_getArgument(['|'], $chunks, $chunk);
        $filters = [];

        while ($chunk === '|') {
            $filters[] = $this->_getArgument(['|'], $chunks, $chunk);
        }

        $results = $this->_searchResults($data, $flag, $pathsChunks, $root);

        foreach ($filters as $filterChunks) {
            $results = $this->_filterResults($results, $flag, $filterChunks, $root);
        }

        return $results;
    }

    protected function _searchResults(array $data, ?bool &$flag, array $chunks, array $root)
    {
        $results = [];
        $chunk = array_shift($chunks);

        if ($chunk === '"' || $chunk === "'") {
            $c = $chunk;

            while ((null !== $chunk = array_shift($chunks)) && $chunk !== $c) {
                $v[] = $chunk;
            }

            return [implode('', $v ?? [])];
        }

        while ($chunk === '[') {
            $l = $this->_getArgument(['!=', '>', '<', '=', ']'], $chunks, $chunk, $data, $root);

            if (']' !== $c = $chunk) {
                $r = $this->_getArgument([']'], $chunks, $chunk, $data, $root);

                if ($c === '!=' && array_intersect($l, $r)) {
                    return [];
                } elseif ($c === '=' && !array_intersect($l, $r)) {
                    return [];
                } elseif ($c === '>' && (!count($l) || !count($r) || reset($l) <= reset($r))) {
                    return [];
                } elseif ($c === '<' && (!count($l) || !count($r) || reset($l) >= reset($r))) {
                    return [];
                }
            } elseif (count($l) <= 1 && !reset($l)) {
                return [];
            }

            $chunk = array_shift($chunks);
        }

        if ($chunk === '/') {
            $chunk = array_shift($chunks);
        }

        if ($chunk === null) {
            $this->_replaceNode($data, $root);

            return [$data];
        }

        if (strpos($chunk, '*') !== false) {
            $flag = true;
            $pattern = '/^' . implode('.*?', array_map('preg_quote', explode('*', $chunk))) . '$/';

            foreach ($data as $key => $value) {
                if (preg_match($pattern, (string)$key)) {
                    $this->_updateResults($value, $root, $chunks, $flag, $results);
                }
            }
        } elseif (array_key_exists($chunk, $data)) {
            $this->_updateResults($data[$chunk], $root, $chunks, $flag, $results);
        } elseif (!$this->_silent) {
            if ($this->_replaceNode($data, $root)) {
                array_unshift($chunks, $chunk);

                return $this->_searchResults($data, $flag, $chunks, $root);
            }

            throw new RuntimeException(static::class);
        }

        return $results;
    }

    protected function _updateResults($value, $root, $chunks, &$flag, &$results)
    {
        if (!$chunks) {
            $this->_replaceNode($value, $root);
            $results[] = $value;

            return;
        }

        try {
            $silent = $this->_silent;
            $this->_silent = false;

            if (is_array($value)) {
                $values = $this->_searchResults($value, $flag, $chunks, $root);
                $results = array_merge($results, $values);

                return;
            }
        } catch (RuntimeException $exception) {
            if (!$silent || $exception->getMessage() != static::class) {
                throw $exception;
            }
        }
        finally {
            $this->_silent = $silent;
        }

        try {
            $silent = $this->_silent;
            $this->_silent = true;

            $this->_replaceNode($value, $root);

            if (is_array($value)) {
                $values = $this->_searchResults($value, $flag, $chunks, $root);
                $results = array_merge($results, $values);
            }
        }
        finally {
            $this->_silent = $silent;
        }
    }

    protected function _filterResults(array $results, ?bool &$flag, array $chunks, array $root): array
    {
        $name = (string)array_shift($chunks);
        $args = [];

        if (reset($chunks) === '(') {
            array_shift($chunks);

            if ($name === 'path') {
                $args[] = $this->_getArgument([')'], $chunks);
            } else {
                do {
                    $args[] = $this->_getArgument([')', ','], $chunks, $chunk, $root, $root);
                } while ($chunk === ',');
            }
        }

        if (!empty($chunks)) {
            throw new RuntimeException('Wrong definition of filter. ' . var_export($chunks, true));
        }

        return $this->_executeFilter($name, $results, $args, $flag);
    }

    protected function _getArgument(array $stop, array &$chunks, &$chunk = null, array $data = null, array $root = null)
    {
        $quot = null;
        $deep = 0;

        while (null !== $chunk = array_shift($chunks)) {
            if (!$quot && !$deep && in_array($chunk, $stop)) {
                break;
            } elseif ($quot ? ($chunk === $quot) : ($chunk === '"' || $chunk === "'")) {
                $quot = $quot ? null : $chunk;
            } elseif (!$quot && ($chunk === '[' || $chunk === '(')) {
                $deep++;
            } elseif (!$quot && ($chunk === ']' || $chunk === ')')) {
                $deep--;
            }

            $r[] = $chunk;
        }

        if ($data !== null) {
            $d = (isset($r) && reset($r) === '/') ? $root : $data;

            return $this->_getByChunks($r ?? [], $d, $flag, $root);
        }

        return $r ?? [];
    }

    protected function _replaceNode(&$value, $root): bool
    {
        if (is_array($value) && isset($value['@path']) && is_string($value['@path'])) {
            $value = $this->_getByPath($value['@path'], $root, $flag);
            $value = count($value) ? ($flag ? $value : reset($value)) : null;

            return true;
        }

        if (is_string($value) && strncmp($value, '%', 1) === 0 && substr($value, -1) === '%') {
            $value = $this->_getByPath(substr($value, 1, -1), $root, $flag);
            $value = count($value) ? ($flag ? $value : reset($value)) : null;

            return true;
        }

        return false;
    }

    protected function _executeFilter(string $name, array $results, array $arguments, ?bool &$flag): array
    {
        if (in_array($name, ['path', 'null', 'bool', 'int', 'integer', 'float', 'double', 'string', 'array', 'keys'])) {
            $return = [];

            foreach ($results as $result) {
                switch ($name) {
                    case 'path':
                        $flag2 = null;
                        $result = $this->_searchResults($result, $flag2, reset($arguments), $result);
                        $return = array_merge($return, $result);
                        break;
                    case 'null':
                        $return[] = null;
                        break;
                    case 'bool':
                        $return[] = (bool)$result;
                        break;
                    case 'int':
                    case 'integer':
                        $return[] = (int)$result;
                        break;
                    case 'float':
                        $return[] = (double)$result;
                        break;
                    case 'double':
                        $return[] = (float)$result;
                        break;
                    case 'string':
                        $return[] = (string)$result;
                        break;
                    case 'array':
                        $return[] = (array)$result;
                        break;
                    case 'keys':
                        if ($args = array_column($arguments, 0)) {
                            $return[] = array_intersect_key($result, array_fill_keys($args, null));
                        } else {
                            $return[] = array_keys($result);
                        }
                        break;
                }
            }

            return $return;
        }

        if (in_array($name, ['timestamp', 'datetime', 'not_empty'])) {
            $return = [];

            $arg1 = array_shift($arguments) ?? [];
            $arg1 = array_shift($arg1) ?? '';

            foreach ($results as $result) {
                switch ($name) {
                    case 'timestamp':
                        if (is_string($result)) {
                            $return[] = strtotime($result . $arg1);
                        }

                        break;
                    case 'datetime':
                        if (is_integer($result)) {
                            $return[] = date($arg1 ?: \DateTime::ATOM, $result);
                        }

                        break;
                    case 'not_empty':
                        if (!empty($result)) {
                            $return[] = $result;
                        }
                        break;
                }
            }

            return $return;
        }

        $single = !$flag;
        $flag = true;

        if ($name === 'merge') {
            return array_values(array_merge($results, reset($arguments) ?: []));
        }

        if (in_array($name, ['unique', 'intersect', 'diff', 'different'])) {
            $a = array_map(function ($v) {
                return json_encode($v);
            }, $results);

            if ($name === 'unique') {
                return array_values(array_intersect_key($results, array_unique($a)));
            }

            $b = array_map(function ($v) {
                return json_encode($v);
            }, reset($arguments) ?: []);

            switch ($name) {
                case 'intersect':
                    return array_values(array_intersect_key($results, array_intersect($a, $b)));
                case 'diff':
                case 'different':
                    return array_values(array_intersect_key($results, array_diff($a, $b)));
            }
        }

        $flag = false;

        if (in_array($name, ['is_string', 'is_numeric', 'is_int', 'is_array', 'is_bool', 'is_float', 'is_double'])) {
            $count = 0;

            foreach ($results as $result) {
                switch ($name) {
                    case 'is_string':
                        $count += is_string($result);
                        break;
                    case 'is_numeric':
                        $count += is_numeric($result);
                        break;
                    case 'is_int':
                        $count += is_int($result);
                        break;
                    case 'is_array':
                        $count += is_array($result);
                        break;
                    case 'is_bool':
                        $count += is_bool($result);
                        break;
                    case 'is_double':
                        $count += is_double($result);
                        break;
                    case 'is_float':
                        $count += is_float($result);
                        break;
                }
            }

            return [count($results) == $count];
        }

        if ($single) {
            $results = reset($results);
        }

        switch ($name) {
            case 'count':
                return [$results ? count($results) : 0];
            case 'first':
                return $results ? [reset($results)] : [];
            case 'last':
                return $results ? [end($results)] : [];
            case 'nth':
            case 'number':
                $argument = reset($arguments);
                $number = intval(reset($argument));

                return ($number > 0) ? array_slice($results, $number - 1, 1) : [];
            case 'join':
                $argument = reset($arguments);
                $glue = reset($argument);

                return [$results ? implode($glue, $results) : ''];
        }

        throw new RuntimeException(sprintf('Unknown filter "%1$s".', $name));
    }
}