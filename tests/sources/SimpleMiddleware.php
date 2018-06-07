<?php

namespace Moro\Indexer\Test;

use Moro\Indexer\Common\Dispatcher\EventInterface;
use Moro\Indexer\Common\Dispatcher\MiddlewareInterface;

/**
 * Class SimpleMiddleware
 * @package Moro\Indexer\Test
 */
class SimpleMiddleware implements MiddlewareInterface
{
    private $_handler;

    public function __construct(callable $handler)
    {
        $this->_handler = $handler;
    }

    public function handle(EventInterface $event, callable $next)
    {
        $handler = $this->_handler;
        $handler($event, $next);
    }
}