<?php

namespace Moro\Indexer\Common\Source;

use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use Moro\Indexer\Common\TypeInterface as BaseInterface;

/**
 * Interface TypeInterface
 * @package Moro\Indexer\Common\Source
 */
interface TypeInterface extends BaseInterface
{
    /**
     * @param string $code
     * @return $this
     */
    function setCode(string $code): TypeInterface;

    /**
     * @param AdapterInterface $adapter
     * @return $this
     */
    function setAdapter(AdapterInterface $adapter): TypeInterface;

    /**
     * @param NormalizerInterface $normalizer
     * @return TypeInterface
     */
    function addNormalizer(NormalizerInterface $normalizer): TypeInterface;

    /**
     * @param FactoryInterface $factory
     * @return $this
     */
    function setFactory(FactoryInterface $factory);

    /**
     * @param int $from
     * @param int $limit
     * @return array
     */
    function getIdList(int $from, int $limit): array;

    /**
     * @param string $id
     * @return EntityInterface
     *
     * @throws NotFoundException
     * @throws WrongStructureException
     * @throws AdapterFailedException
     */
    function getEntityById(string $id): EntityInterface;
}