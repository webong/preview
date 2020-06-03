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

    abstract public function render();

    public function __toString()
    {
        return (string) $this->render();
    }
}
