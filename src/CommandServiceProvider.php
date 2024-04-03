<?php

namespace kjos\Command;

use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    protected $commands = [

    ];

    public function boot()
    {
        // Load routes, views, migrations, etc.
        
    }

    public function register()
    {
        // Register bindings, listeners, etc.
        $this->registerCommands($this->commands);
    }

    protected function registerCommands(array $commands)
    {
        foreach ($commands as $commandName => $command) {
            $method = "register{$commandName}Command";

            if (method_exists($this, $method)) {
                $this->{$method}();
            } else {
                $this->app->singleton($command);
            }
        }

        $this->commands(array_values($commands));
    }

}