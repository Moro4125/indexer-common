<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;

/**
 * Class SimpleEntity
 * @package Moro\Indexer\Test
 */
class SimpleEntity implements EntityInterface
{
    protected $_source;

    public function __construct(string $id = null, string $name = null)
    {
        if ($id !== null) {
            $this->_source['id'] = $id;
        }

        if ($name !== null) {
            $this->_source['name'] = $name;
        }
    }

    public function load(array $source)
    {
        if (!isset($source['id']) || empty($source['name'])) {
            throw new WrongStructureException('Keys "id" and "name" required.');
        }

        $source['id'] = (string)$source['id'];
        $this->_source = $source;
        return $this;
    }

    public function getId(): string
    {
        return $this->_source['id'];
    }

    public function getName(): string
    {
        return $this->_source['name'];
    }

    public function getUpdatedAt(): int
    {
        return $this->_source['updated_at'] ?? 0;
    }

    public function getActiveFrom(): int
    {
        return $this->_source['active_from'] ?? time();
    }

    public function getActiveTo(): ?int
    {
        return $this->_source['active_to'] ?? null;
    }

    public function jsonSerialize()
    {
        return $this->_source;
    }
}