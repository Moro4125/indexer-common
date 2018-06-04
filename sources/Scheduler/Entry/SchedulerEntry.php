<?php

namespace Moro\Indexer\Common\Scheduler\Entry;

use Moro\Indexer\Common\Scheduler\EntryInterface;

/**
 * Class SchedulerEntry
 * @package Moro\Indexer\Common\Scheduler\Entry
 */
class SchedulerEntry implements EntryInterface
{
    protected $_type;
    protected $_id;
    protected $_action;

    /**
     * @param string $type
     * @param string $id
     * @param string $action
     */
    public function __construct(string $type = null, string $id = null, string $action = null)
    {
        if ($type) {
            $this->setType($type);
        }

        if ($id) {
            $this->setId($id);
        }

        if ($action) {
            $this->setAction($action);
        }
    }

    /**
     * @param string $type
     * @return EntryInterface
     */
    public function setType(string $type): EntryInterface
    {
        $this->_type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->_type;
    }

    /**
     * @param string $id
     * @return EntryInterface
     */
    public function setId(string $id): EntryInterface
    {
        $this->_id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->_id;
    }

    /**
     * @param string $action
     * @return EntryInterface
     */
    public function setAction(string $action): EntryInterface
    {
        $this->_action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->_action;
    }
}