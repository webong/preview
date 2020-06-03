<?php

use Preview\Preview;
use function Opis\Closure\serialize;
use Opis\Closure\SerializableClosure;

function render($name, $props = null)
{
    return new Preview($name, $props);
}

function preview($name, $props = null)
{
    return render($name, $props);
}

function getProps(){
    $props = func_get_arg(0);
    return $props;
}

function component($code)
{
    $wrapper = $code;

    if ($code instanceof Closure) {
        // Wrap the closure
        $wrapper = new SerializableClosure($code);
    }

    // Now it can be serialized
    $serialized = serialize($wrapper);

}
