<?php

namespace Moro\Indexer\Common\Source;

use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use JsonSerializable;

/**
 * Interface EntityInterface
 * @package Moro\Indexer\Common\Source
 */
interface EntityInterface extends JsonSerializable
{
    /**
     * @param array $source
     * @return $this
     *
     * @throws WrongStructureException
     */
    function load(array $source);

    /**
     * @return string
     */
    function getId(): string;

    /**
     * @return string
     */
    function getName(): string;

    /**
     * @return int
     */
    function getUpdatedAt(): int;

    /**
     * @return int
     */
    function getActiveFrom(): int;

    /**
     * @return int|null
     */
    function getActiveTo(): ?int;
}