<?php

namespace Moro\Indexer\Common\Dispatcher\Event;

/**
 * Class ViewSaveEvent
 * @package Moro\Indexer\Common\Dispatcher\Event
 */
class ViewSaveEvent extends AbstractEvent
{
    private $type;
    private $kind;
    private $id;

    /**
     * @param string $type
     * @param string $kind
     * @param string $id
     */
    public function __construct(string $type, string $kind, string $id)
    {
        $this->type = $type;
        $this->kind = $kind;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}