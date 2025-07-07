<?php

namespace Kjos\Command;

use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
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

          $this->commands([
           \Kjos\Command\Commands\KjosMakeRouteApiCommand::class,
           \Kjos\Command\Commands\CleanupPublishedFilesCommand::class,
        ]);
    }
}
