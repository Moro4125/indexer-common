<?php

namespace Moro\Indexer\Common\Dispatcher\Event;

use Throwable;

/**
 * Class ExceptionIgnoreEvent
 * @package Moro\Indexer\Common\Dispatcher\Event
 */
class ExceptionIgnoreEvent extends AbstractEvent
{
    /** @var Throwable */
    private $exception;

    /** @var string */
    private $ignoredBy;

    /**
     * @param Throwable $throwable
     * @param string $ignoredBy
     */
    public function __construct(Throwable $throwable, string $ignoredBy)
    {
        $this->exception = $throwable;
        $this->ignoredBy = $ignoredBy;
    }

    /**
     * @return Throwable
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * @return string
     */
    public function getIgnoredBy(): string
    {
        return $this->ignoredBy;
    }
}