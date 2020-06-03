<?php

namespace Preview;

use Illuminate\Support\ServiceProvider;
use Preview\Console\PreviewCacheCommand;

class PreviewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Pre compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $this->app->singleton('preview.compiler', function () {
            return new PreviewCompiler(
                $this->app['files'],
                $this->app['config']['view.compiled']
            );
        });

        $this->app['view']->addExtension('pre', 'preview', function () {
            return new PreviewEngine($this->app['preview.compiler']);
        });

        $this->app->singleton('command.preview.cache', function ($app) {
           return new PreviewCacheCommand;
       });

        $this->commands([
           'command.preview.cache',
       ]);
    }
}
