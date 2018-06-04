<?php

namespace Moro\Indexer\Common\View;

use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\TypeInterface as BaseInterface;
use Moro\Indexer\Common\View\Exception\DuplicateKindException;
use Moro\Indexer\Common\View\Exception\KindFailedException;
use Moro\Indexer\Common\View\Exception\UnknownKindException;

/**
 * Interface TypeInterface
 * @package Moro\Indexer\Common\View
 */
interface TypeInterface extends BaseInterface
{
    /**
     * @param string $code
     * @return TypeInterface
     */
    function setCode(string $code): TypeInterface;

    /**
     * @param KindInterface $kind
     * @return TypeInterface
     *
     * @throws DuplicateKindException
     */
    function addKind(KindInterface $kind): TypeInterface;

    /**
     * @param string $kind
     * @param EntityInterface $entity
     * @return string
     *
     * @throws UnknownKindException
     * @throws KindFailedException
     */
    function handle(string $kind, EntityInterface $entity): string;
}