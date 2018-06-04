<?php

namespace Moro\Indexer\Common\Accessories;

use RuntimeException;

/**
 * Trait ArraysSetByPathTrait
 * @package Moro\Indexer\Common\Accessories
 */
trait ArraysSetByPathTrait
{
    protected function _setByPath(string $path, $value, array $data, bool $flag = null): array
    {
        if ($path === '') {
            return $value;
        }

        $pattern = '/(?<!\\\\)(\\/|\\[\\]|\\?)/';
        $chunks = preg_split($pattern, $path, -1, PREG_SPLIT_DELIM_CAPTURE);
        $chunks = array_map('trim', array_filter($chunks, function ($chunk) {
            return trim($chunk) !== '';
        }));

        try {
            return $this->_setByChunks($chunks, $value, $data, $flag);
        } catch (RuntimeException $exception) {
            $message = sprintf('Error in path "%1$s": %2$s', $path, $exception->getMessage());
            throw new RuntimeException($message, $exception->getCode(), $exception);
        }
    }

    protected function _setByChunks(array $chunks, $value, array $data, ?bool $flag): array
    {
        $chunk = array_shift($chunks);

        if ($chunk === '/') {
            $chunk = array_shift($chunks);
        }

        if ($optional = (reset($chunks) === '?')) {
            array_shift($chunks);
        }

        if ($chunk === '[]') {
            if (count($chunks)) {
                throw new RuntimeException('Wrong definition of the path.');
            }

            $data = array_merge($data, ($flag && is_array($value)) ? $value : [$value]);
        } elseif (false !== strpos($chunk, '*')) {
            $pattern = '/^' . implode('.*?', array_map('preg_quote', explode('*', $chunk))) . '$/';

            foreach ($data as $k => &$v) {
                if (preg_match($pattern, $k) && (!$optional || empty($v))) {
                    $v = $this->_setByChunks($chunks, $value, $v, $flag);
                }
            }
        } elseif ($optional && !empty($data[$chunk])) {
            return $data;
        } elseif (count($chunks)) {
            if (!array_key_exists($chunk, $data) || !is_array($data[$chunk])) {
                $data[$chunk] = [];
            }

            $data[$chunk] = $this->_setByChunks($chunks, $value, $data[$chunk], $flag);
        } elseif ($value === null) {
            unset($data[$chunk]);
        } else {
            $data[$chunk] = $value;
        }

        return $data;
    }

    protected function _getFlagForPath(string $path): bool
    {
        $pattern = '/(?<!\\\\)( |\\/|\\[|\\]|!=|>|<|=|"|\'|,|\\(|\\)|\\|)/';
        $chunks = preg_split($pattern, $path, -1, PREG_SPLIT_DELIM_CAPTURE);

        $quot = null;
        $deep = 0;

        while (null !== $chunk = array_shift($chunks)) {
            if (!$quot && !$deep && false !== strpos($chunk, '*')) {
                return true;
            } elseif ($quot ? ($chunk === $quot) : ($chunk === '"' || $chunk === "'")) {
                $quot = $quot ? null : $chunk;
            } elseif (!$quot && ($chunk === '[' || $chunk === '(')) {
                $deep++;
            } elseif (!$quot && ($chunk === ']' || $chunk === ')')) {
                $deep--;
            }
        }

        return false;
    }
}