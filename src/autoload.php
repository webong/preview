<?php

spl_autoload_register(
    function ($class) {
        $dir = __DIR__.'/..';

        if (!file_exists($dir.'/vendor/autoload.php')) {
            $dir = __DIR__.'/../..';
        }

        if (!file_exists($dir.'/vendor/autoload.php')) {
            $dir = __DIR__.'/../../..';
        }

        if (!file_exists($dir.'/vendor/autoload.php')) {
            $dir = __DIR__.'/../../../..';
        }

        $loader = require "{$dir}/vendor/autoload.php";
        $reflector = new ReflectionClass($loader);
        $findFile = $reflector->getMethod('findFileWithExtension');
        $findFile->setAccessible(true);
        $file = $findFile->invoke($loader, $class, '.pre');
        if ($file) {
            $interceptor = new \Nikic\IncludeInterceptor\Interceptor(function($path) {
                return app('view')->file($path);
            });
        
            $interceptor->setUp(); // Start intercepting includes
        
            require_once $file;
        
            $interceptor->tearDown(); // Stop intercepting includes

            return true;
        }
    },
    true,
    true
);