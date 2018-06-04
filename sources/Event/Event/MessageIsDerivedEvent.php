<?php

namespace Moro\Indexer\Common\Event\Event;

/**
 * Class MessageIsDerivedEvent
 * @package Moro\Indexer\Common\Event\Event
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