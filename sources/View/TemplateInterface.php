<?php

namespace Moro\Indexer\Common\View;

/**
 * Interface TemplateInterface
 * @package Moro\Indexer\Common\View
 */
interface TemplateInterface
{
    /**
     * @param string $template
     * @param array $parameters
     * @return string
     */
    function render(string $template, array $parameters): string;
}