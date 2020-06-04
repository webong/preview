<?php

namespace Preview;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;

class Preview
{
    protected $name;

    protected $props;

    public function __construct($name = null, $props = null)
    {
        $this->name = $name;
        $this->props = \Pre\Phpx\Html\propsFrom($props);
    }

    public function addClass($className)
    {
        $existingClassName = $this->props->className;

        if (is_null($existingClassName)) {
            $updatedClassName = $className;
        } elseif (is_string($existingClassName)) {
            $updatedClassName = "$existingClassName $className";
        } elseif ($existingClassName instanceof Closure) {
            $updatedClassName = [$existingClassName, $className];
        } elseif (is_array($existingClassName)) {
            $updatedClassName = array_push($existingClassName, $className);
        }

        $this->props->className = $updatedClassName;

        return $this;
    }

    public function appendChild($child)
    {
        $existingChildren = $this->props->children;

        if (is_null($existingChildren)) {
            $updatedChildren = [ $child ];
        } elseif (is_string($existingChildren)) {
            $updatedChildren = $existingChildren + $child;
        } elseif ($existingChildren instanceof Closure) {
            $updatedChildren = [$existingChildren, $child];
        } elseif (is_array($existingChildren)) {
            array_push($existingChildren, $child);
            $updatedChildren = $existingChildren;
        }

        $this->props->children = $updatedChildren;

        return $this;
    }

    public function prependChild($child)
    {
        $existingChildren = $this->props->children;

        if (is_null($existingChildren)) {
            $updatedChildren = $child;
        } elseif (is_string($existingChildren)) {
            $updatedChildren = $child + $existingChildren;
        } elseif ($existingChildren instanceof Closure) {
            $updatedChildren = [$child, $existingChildren];
        } elseif (is_array($existingChildren)) {
            array_unshift($existingChildren, $child);
            $updatedChildren = $existingChildren;
        }

        $this->props->children = $updatedChildren;

        return $this;
    }

    private static function render($component, $props)
    {
        return \Pre\Phpx\Html\render($component, $props);
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return static::render($this->name, $this->props);
    }

    /**
     * Get the string contents of the view.
     *
     * @return string
     *
     * @throws \Throwable
     */
    public function __toString()
    {
        try {
            return (string) $this->toHtml();
        } catch (\Throwable $e) {
            $handler = app()[ExceptionHandler::class];
            $handler->report($e);
            return $handler->render(app('request'), $e);
        }
    }
}
