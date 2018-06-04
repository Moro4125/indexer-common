<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Source\EntityInterface;

/**
 * Class FailedEntity
 * @package Moro\Indexer\Test
 */
class FailedEntity implements EntityInterface
{
    public function load(array $source)
    {
        throw new \RuntimeException(self::class);
    }

    public function getId(): string
    {
        return 'zero';
    }

    public function getName(): string
    {
        throw new \RuntimeException(self::class);
    }

    public function getUpdatedAt(): int
    {
        return time();
    }

    public function getActiveFrom(): int
    {
        return time();
    }

    public function getActiveTo(): ?int
    {
        return null;
    }

    public function jsonSerialize()
    {
        return null;
    }
}