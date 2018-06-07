<?php

namespace Moro\Indexer\Common\Dispatcher\Event;

use Throwable;

/**
 * Class ExceptionRepairedEvent
 * @package Moro\Indexer\Common\Dispatcher\Event
 */
class ExceptionRepairedEvent extends AbstractEvent
{
    /** @var Throwable */
    private $exception;

    /** @var string */
    private $repairedBy;

    /**
     * @param Throwable $throwable
     * @param string $repairedBy
     */
    public function __construct(Throwable $throwable, string $repairedBy)
    {
        $this->exception = $throwable;
        $this->repairedBy = $repairedBy;
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
    public function getRepairedBy(): string
    {
        return $this->repairedBy;
    }
}