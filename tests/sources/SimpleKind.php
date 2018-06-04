<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\View\KindInterface;

/**
 * Class SimpleKind
 * @package Moro\Indexer\Test
 */
class SimpleKind implements KindInterface
{
    protected $_code;

    /**
     * SimpleKind constructor.
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->_code = $code;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code;
    }

    /**
     * @param EntityInterface $entity
     * @return string
     */
    public function handle(EntityInterface $entity): string
    {
        return json_encode($entity);
    }
}