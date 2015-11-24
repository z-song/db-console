<?php

namespace Encore\Dbconsole\Providers;

use Encore\Dbconsole\Commands\DbConsoleCommand;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider{


    public function register()
    {
        $this->app['dbconsole'] = $this->app->share(function () {
            return new DbConsoleCommand();
        });
    }

    public function boot()
    {
        $this->commands('dbconsole');
    }
}