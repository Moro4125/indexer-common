<?php

namespace Moro\Indexer\Common\View\Storage;

use Moro\Indexer\Common\View\StorageInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FilesystemStorage
 * @package Moro\Indexer\Common\View\Storage
 */
class FilesystemStorage implements StorageInterface
{
    /**
     * @var Filesystem
     */
    protected $_fs;

    /**
     * @var string
     */
    protected $_dir;

    /**
     * @var bool
     */
    protected $_init;

    /**
     * @var array
     */
    protected $_kinds;

    /**
     * @param Filesystem $fs
     * @param string $directory
     */
    public function __construct(Filesystem $fs, string $directory)
    {
        $this->_fs = $fs;
        $this->_dir = $directory;
        $this->_init = false;
        $this->_kinds = [];
    }

    /**
     * @param string $type
     * @param string $id
     * @return array
     */
    public function find(string $type, string $id): array
    {
        $this->_init || $this->_initialization();

        $folder = $this->_getFolder($type, $id);
        $prefix = $this->_getFilename($id);
        $length = strlen($prefix);
        $result = [];

        if ($this->_fs->exists($folder)) {
            foreach (new \DirectoryIterator($folder) as $item) {
                if (strncmp($item->getFilename(), $prefix, $length) === 0) {
                    $result[] = $this->_getOriginalKind(substr($item->getFilename(), $length));
                }
            }
        }

        return $result;
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @param string $content
     */
    public function save(string $type, string $kind, string $id, string $content)
    {
        $this->_init || $this->_initialization();

        $folder = $this->_getFolder($type, $id);
        $filename = $this->_getFilename($id, $kind);
        $path = $folder . DIRECTORY_SEPARATOR . $filename;

        $this->_fs->dumpFile($path, $content);
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return null|string
     */
    public function load(string $type, string $kind, string $id): ?string
    {
        $this->_init || $this->_initialization();

        $folder = $this->_getFolder($type, $id);
        $filename = $this->_getFilename($id, $kind);
        $path = $folder . DIRECTORY_SEPARATOR . $filename;

        return $this->_fs->exists($path) ? file_get_contents($path) : null;
    }

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     * @return bool
     */
    public function drop(string $type, string $kind, string $id): bool
    {
        $this->_init || $this->_initialization();

        $folder = $this->_getFolder($type, $id);
        $filename = $this->_getFilename($id, $kind);
        $path = $folder . DIRECTORY_SEPARATOR . $filename;

        $flag = $this->_fs->exists($path);
        $this->_fs->remove($path);

        return $flag;
    }

    /**
     * Initialization.
     */
    protected function _initialization()
    {
        if (!$this->_fs->exists($this->_dir)) {
            $this->_fs->mkdir($this->_dir);
        }
    }

    /**
     * @param string $type
     * @param string $id
     * @return string
     */
    protected function _getFolder(string $type, string $id): string
    {
        $sha1 = sha1($id);
        $x32 = $this->_convertSha1toX32($sha1);
        $chunk = substr($x32, 1, 2);
        $type = preg_replace('{[^A-Za-z0-9]+}', '-', $type);
        $folder = $type . '_' . substr($sha1, 0, 1);

        return $this->_dir . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $chunk;
    }

    /**
     * @param string $id
     * @param string|null $kind
     * @return string
     */
    protected function _getFilename(string $id, string $kind = null): string
    {
        $name = preg_replace('{[^A-Za-z0-9]+}', '-', $id) . '_';
        $kind === null || $name .= $this->_getNormalizedKind($kind);

        return $name;
    }

    /**
     * @param string $kind
     * @return string
     */
    protected function _getOriginalKind(string $kind): string
    {
        if (isset($this->_kinds[$kind])) {
            return $this->_kinds[$kind];
        }

        $filePath = $this->_dir . DIRECTORY_SEPARATOR . $kind;

        if ($this->_fs->exists($filePath)) {
            $realKind = file_get_contents($filePath);
            $this->_kinds[$kind] = $realKind;

            return $realKind;
        } else {
            return $kind;
        }
    }

    /**
     * @param string $kind
     * @return string
     */
    protected function _getNormalizedKind(string $kind): string
    {
        $normalized = preg_replace('{[^A-Za-z0-9]+}', '-', $kind);

        if (!isset($this->_kinds[$normalized])) {
            $this->_kinds[$normalized] = $kind;
            $filePath = $this->_dir . DIRECTORY_SEPARATOR . $normalized;

            if (!$this->_fs->exists($filePath)) {
                $this->_fs->dumpFile($filePath, $kind);
            }
        }

        return $normalized;
    }

    /**
     * @param string $hash
     * @return string
     */
    protected function _convertSha1toX32(string $hash): string
    {
        assert(strlen($hash) == 40);

        return str_pad(base_convert(substr($hash, 0, 10), 16, 32), 8, '0',
                STR_PAD_LEFT) . str_pad(base_convert(substr($hash, 10, 10), 16, 32), 8, '0',
                STR_PAD_LEFT) . str_pad(base_convert(substr($hash, 20, 10), 16, 32), 8, '0',
                STR_PAD_LEFT) . str_pad(base_convert(substr($hash, 30, 10), 16, 32), 8, '0', STR_PAD_LEFT);
    }
}