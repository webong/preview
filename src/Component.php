<?php

namespace Preview;

use Illuminate\Support\Str;

abstract class Component
{
    public $id;
    public $props;

    public function __construct($props = [])
    {
        $this->id = Str::random(20);;
        $this->props = (array) $props;
    }

    abstract protected function render();

    public function resolveView()
    {
        return $this->render();
    }

    public function __call($method, $arguments)
    {
        if(method_exists($this, $method) && $method == 'render') {
            return $this->resolveView();
        }
    }

    public function __toString()
    {
        return (string) $this->resolveView();
    }
}
