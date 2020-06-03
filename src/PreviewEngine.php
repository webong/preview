<?php

namespace Preview;

use Illuminate\View\Engines\CompilerEngine;

class PreviewEngine extends CompilerEngine
{
    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param  string  $__path
     * @param  array  $__data
     * @return string
     */
    protected function evaluatePath($__path, $__data)
    {
        return file_get_contents($__path);
    }

}