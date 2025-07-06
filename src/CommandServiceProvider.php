<?php

namespace Kjos\Command;

use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    protected $commands = [
        'KjosMakeRouteApi' => \Kjos\Command\Commands\KjosMakeRouteApiCommand::class,
        'KjosTestRouteApi' => \Kjos\Command\Commands\KjosTestRouteApiCommand::class,
    ];

    public function boot()
    {
        // Load routes, views, migrations, etc.
    }

    public function register()
    {
        $this->publishes([
            KJOS_COMMAND_MODULE_PATH . '/.stub/3kjos-command.php' => config_path('3kjos-command.php'),
        ], '3kjos-command');
        
        $this->app->bind(\Kjos\Command\Managers\Service::class);

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
