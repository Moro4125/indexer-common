<?php

namespace Moro\Indexer\Common\Source\Type;

use Moro\Indexer\Common\Source\AdapterInterface;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use Moro\Indexer\Common\Source\FactoryInterface;
use Moro\Indexer\Common\Source\NormalizerInterface;
use Moro\Indexer\Common\Source\TypeInterface;

/**
 * Class SourceType
 * @package Moro\Indexer\Common\Source\Type
 */
class SourceType implements TypeInterface
{
    protected $_code;
    /** @var AdapterInterface */
    protected $_adapter;
    /** @var NormalizerInterface[] */
    protected $_normalizers;
    /** @var FactoryInterface */
    protected $_factory;

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code): TypeInterface
    {
        assert(strlen($code));

        $this->_code = $code;

        return $this;
    }

    /**
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter): TypeInterface
    {
        $this->_adapter = $adapter;

        return $this;
    }

    /**
     * @param NormalizerInterface $normalizer
     * @return TypeInterface
     */
    public function addNormalizer(NormalizerInterface $normalizer): TypeInterface
    {
        $this->_normalizers[] = $normalizer;

        return $this;
    }

    /**
     * @param FactoryInterface $factory
     * @return $this
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->_factory = $factory;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code;
    }

    /**
     * @param int $from
     * @param int $limit
     * @return array
     */
    public function getIdList(int $from, int $limit): array
    {
        assert($from >= 0);
        assert($limit > 0);
        assert($this->_adapter !== null);

        return $this->_adapter->receiveIdList($from, $limit);
    }

    /**
     * @param string $id
     * @return EntityInterface
     */
    public function getEntityById(string $id): EntityInterface
    {
        assert($this->_factory !== null);
        assert($this->_adapter !== null);

        try {
            $source = $this->_adapter->receiveEntityById($id);
        } catch (NotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $type = $this->getCode();
            $message = sprintf(AdapterFailedException::MSG, get_class($this->_adapter), $type, $id);

            $exception = new AdapterFailedException($message . PHP_EOL . $e->getMessage(), $e->getCode(), $e);
            $exception->type = $type;
            $exception->id = $id;

            throw $exception;
        }

        try {
            if ($this->_normalizers) {
                $normalizers = $this->_normalizers;
                $executed = true;

                while ($normalizers && $executed) {
                    $executed = [];

                    foreach ($normalizers as $index => $normalizer) {
                        if (null !== $result = $normalizer->normalize($source)) {
                            $source = $result;
                            $executed[] = $normalizer;
                            unset($normalizers[$index]);
                        }
                    }
                }
            }

            $entity = $this->_factory->newEntity()
                ->load($source);

            return $entity;
        } catch (WrongStructureException $e) {
            $message = sprintf(WrongStructureException::MSG_1, $id, $this->getCode());
            $message .= ($e->getMessage() !== WrongStructureException::MSG_0) ? PHP_EOL . $e->getMessage() : '';

            $exception = new WrongStructureException($message, $e->getCode(), $e);
            $exception->id = $id;
            $exception->type = $this->getCode();

            throw $exception;
        }
    }
}