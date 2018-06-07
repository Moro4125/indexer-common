<?php

namespace Moro\Indexer\Common\Dispatcher\Event;

/**
 * Class MessageIsDerivedEvent
 * @package Moro\Indexer\Common\Dispatcher\Event
 */
class MessageIsDerivedEvent extends AbstractEvent
{
    private $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function getMessage(): array
    {
        return $this->message;
    }
}