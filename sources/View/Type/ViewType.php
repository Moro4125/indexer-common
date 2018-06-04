<?php

namespace Moro\Indexer\Common\View\Type;

use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\View\Exception\DuplicateKindException;
use Moro\Indexer\Common\View\Exception\KindFailedException;
use Moro\Indexer\Common\View\Exception\UnknownKindException;
use Moro\Indexer\Common\View\KindInterface;
use Moro\Indexer\Common\View\TypeInterface;

/**
 * Class ViewType
 * @package Moro\Indexer\Common\View\Type
 */
class ViewType implements TypeInterface
{
    /** @var KindInterface[] */
    protected $_list;
    protected $_code;

    /**
     * @param string $code
     * @return ViewType
     */
    public function setCode(string $code): TypeInterface
    {
        $this->_code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code ?? get_class($this);
    }

    /**
     * @param KindInterface $kind
     * @return TypeInterface
     */
    public function addKind(KindInterface $kind): TypeInterface
    {
        $code = $kind->getCode();

        if (isset($this->_list[$code])) {
            throw new DuplicateKindException(sprintf(DuplicateKindException::MSG, $code, $this->getCode()));
        }

        $this->_list[$code] = $kind;
        return $this;
    }

    /**
     * @param string $kind
     * @param EntityInterface $entity
     * @return string
     */
    public function handle(string $kind, EntityInterface $entity): string
    {
        if (empty($this->_list[$kind])) {
            $type = $this->getCode();
            $exception = new UnknownKindException(sprintf(UnknownKindException::MSG, $kind, $type));
            $exception->setType($type);
            $exception->setKind($kind);

            throw $exception;
        }

        try {
            $content = $this->_list[$kind]->handle($entity);
        } catch (\Throwable $e) {
            $id = $entity->getId();
            $type = $this->getCode();
            $message = sprintf(KindFailedException::MSG, $type, $kind, $id);

            $exception = new KindFailedException($message, $e->getCode(), $e);
            $exception->type = $type;
            $exception->kind = $kind;
            $exception->id = $id;

            throw $exception;
        }

        return $content;
    }
}