<?php

namespace Moro\Indexer\Common\View\Kind;

use Moro\Indexer\Common\Accessories\ArraysSetByPathTrait;
use Moro\Indexer\Common\Source\EntityInterface;
use Moro\Indexer\Common\View\KindInterface;
use Moro\Indexer\Common\View\TemplateInterface;

/**
 * Class UniversalKind
 * @package Moro\Indexer\Common\View\Kind
 */
class UniversalKind implements KindInterface
{
    use ArraysSetByPathTrait;

    private $_render;
    private $_template;
    private $_code;
    private $_parameters;

    /**
     * @param TemplateInterface|null $render
     */
    public function __construct(TemplateInterface $render = null)
    {
        $this->_render = $render;
    }

    /**
     * @param string $template
     * @return UniversalKind
     */
    public function setTemplate(string $template): UniversalKind
    {
        $this->_template = $template;

        return $this;
    }

    /**
     * @param string $code
     * @return UniversalKind
     */
    public function setCode(string $code): UniversalKind
    {
        $this->_code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code ?? static::class;
    }

    /**
     * @param string $name
     * @param string $path
     */
    public function addParameter(string $name, string $path)
    {
        $this->_parameters[] = [$name, $path];
    }

    /**
     * @param EntityInterface $entity
     * @return string
     */
    public function handle(EntityInterface $entity): string
    {
        $parameters = [];

        foreach ($this->_parameters ?? [] as list($name, $path)) {
            $flag = $this->_getFlagForPath($path);
            $value = $entity[$path];
            $parameters = $this->_setByPath($name, $value, $parameters, $flag);
        }

        return ($this->_render && $this->_template) ? $this->_render->render($this->_template,
            $parameters) : json_encode($parameters, JSON_UNESCAPED_UNICODE);
    }
}