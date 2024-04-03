<?php

namespace Kjos\Command\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:site')]
class KjosMakeRouteApiCommand extends GeneratorCommand
{
    /**
     * Console command name
     *
     * @var string
     */
    protected $name = 'make:site';

    /**
     * Console command description
     *
     * @var string
     */
    protected $description = 'Create new site for installed app by creating the specific .env file and storage dirs.';

    protected $site;

    public function handle()
    {
        $this->site = $this->argument('name');
        $app = $this->laravel;
        if (! method_exists($app, 'sitePath') || ! method_exists($app, 'useSite')) {
            $this->components->error('Application is not instant of '.\Keky\Maestro\Foundation\Application::class);

            return false;
        }
        $force = $this->option('force');
        $sitePath = $app->sitePath($this->site);

        if ($this->files->exists($sitePath)) {
            if ($force) {
                $this->files->deleteDirectory($sitePath);
                $this->makeDirectories($this->laravel, $sitePath);
            } else {
                $this->components->error('Site with domain '.$this->site.' already exists');

                return false;
            }
        } else {
            $this->makeDirectories($this->laravel, $sitePath);
        }
        if ($this->writeEnv($this->laravel, $sitePath)) {
            $app['config']['app.key'] = '';
            $app->useEnvironmentPath($sitePath);
            $this->call('key:generate');
        }
        $this->components->info('Site '.$this->site.' is added to the application.');
    }

    /**
     * @param  Application  $app
     * @param  string  $sitePath
     * @return void
     */
    protected function makeDirectories($app, $sitePath)
    {
        $configPath = $app->joinPaths($sitePath, 'config');
        $configCachePath = $app->joinPaths($sitePath, 'cache');
        $this->files->makeDirectory($sitePath, 0755, true);
        $this->files->makeDirectory($configPath, 0755, true);
        $this->files->makeDirectory($configCachePath, 0755, true);
        $this->makeDataBaseDirectories($app, $sitePath);
        $this->makeRoutesDirectories($app, $sitePath);
        $this->makeStorageDirectories($app, $sitePath);
    }

    /**
     * @param  Application  $app
     * @param  string  $sitePath
     * @return void
     */
    private function makeDataBaseDirectories($app, $sitePath)
    {
        $databasePath = $app->joinPaths($sitePath, 'database');
        $this->files->makeDirectory($app->joinPaths($databasePath, 'migrations'), 0755, true);
        $this->files->makeDirectory($app->joinPaths($databasePath, 'seeders'), 0755, true);
        $this->files->makeDirectory($app->joinPaths($databasePath, 'factories'), 0755, true);
    }

    /**
     * @param  Application  $app
     * @param  string  $sitePath
     * @return void
     */
    private function makeStorageDirectories($app, $sitePath)
    {
        $storagePath = $app->joinPaths($sitePath, 'storage');
        $this->files->makeDirectory($app->joinPaths($storagePath, 'app/public'), 0755, true);
        $this->files->makeDirectory($app->joinPaths($storagePath, 'framework/cache/data'), 0755, true);
        $this->files->makeDirectory($app->joinPaths($storagePath, 'framework/sessions'), 0755, true);
        $this->files->makeDirectory($app->joinPaths($storagePath, 'framework/testing'), 0755, true);
        $this->files->makeDirectory($app->joinPaths($storagePath, 'framework/views'), 0755, true);
        $this->files->makeDirectory($app->joinPaths($storagePath, 'logs'), 0755, true);
    }

    /**
     * @param  Application  $app
     * @return void
     */
    private function makeRoutesDirectories($app, $sitePath)
    {
        $routesPath = $app->joinPaths($sitePath, 'routes');
        $this->files->copyDirectory(__DIR__.'/../../routes', $routesPath);
    }

    /**
     * @param  Application  $app
     * @param  string  $sitePath
     * @return bool
     */
    protected function writeEnv($app, $sitePath)
    {
        $envPath = $app->joinPaths($sitePath, $app->environmentFile());
        $envStubPath = $this->getStub();

        return $this->files->copy($envStubPath, $envPath);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return base_path(config('maestro.env_stub', '.env.example'));
    }

    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create site even if it already exists'],
        ];
    }
}
