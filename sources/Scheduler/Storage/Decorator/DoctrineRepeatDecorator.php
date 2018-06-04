<?php /** @noinspection PhpRedundantCatchClauseInspection */

namespace Moro\Indexer\Common\Scheduler\Storage\Decorator;

use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\StorageInterface;

/**
 * Class DoctrineRepeatDecorator
 * @package Moro\Indexer\Common\Scheduler\Storage\Decorator
 */
class DoctrineRepeatDecorator extends AbstractDecorator
{
    private $_retry;

    public function __construct(StorageInterface $storage, int $retry = null)
    {
        $this->_instance = $storage;
        $this->_retry = $retry ?? 3;
    }

    public function defer(int $timestamp, EntryInterface $entry)
    {
        $retry = $this->_retry;

        while ($retry) {
            try {
                $this->getDecoratedInstance()
                    ->defer($timestamp, $entry);

                return;
            } catch (UniqueConstraintViolationException $exception) {
                $retry--;
            } catch (RetryableException $exception) {
                $retry--;
            }

            if ($retry) {
                usleep(1000);
            }
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        throw $exception;
    }

    public function derive(EntryInterface $entry): bool
    {
        while (true) {
            try {
                return $this->getDecoratedInstance()
                    ->derive($entry);
            } catch (RetryableException $exception) {
                unset($exception);
            }
        }

        return false;
    }
}