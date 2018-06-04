<?php

namespace Moro\Indexer\Common\Transaction;

/**
 * Class FacadeInterface
 * @package Moro\Indexer\Common\Transaction
 */
class TransactionFacade
{
    /** @var ManagerInterface */
    protected $_manager;

    /**
     * @param ManagerInterface $manager
     */
    public function __construct(ManagerInterface $manager)
    {
        $this->_manager = $manager;
    }

    /**
     * @return bool
     */
    public function activate(): bool
    {
        return $this->_manager->activate($this);
    }
}