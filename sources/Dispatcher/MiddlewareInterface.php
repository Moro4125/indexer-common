<?php

namespace Moro\Indexer\Common\Dispatcher;

/**
 * Interface MiddlewareInterface
 * @package Moro\Indexer\Common\Dispatcher
 */
interface MiddlewareInterface
{
    function handle(EventInterface $event, callable $next);
}