<?php

namespace Kjos\Command\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Kjos\Command\Services\MyTestCommandRunner;

class KjosTestRouteApiCommand extends Command
{
    /**
     * Console command name
     *
     * @var string
     */
    protected $name = 'kjos:test:api';

    /**
     * Console command description
     *
     * @var string
     */
    protected $description = 'Create new site for installed app by creating the specific .env file and storage dirs.';

    protected $signature = 'kjos:test:api 
    {name? : the prefix of the site}
    {--f|force : Force api creation if it already exists}
    {--eh|errorhandler : Enable error handling mode}
    {--c|centralize : Enable centralize mode}
    {--factory : Generate factory for model}
    {--endpoint_type=: the type of endpoint: group, standalone, apiResource, resource. default is group}
    {--t|test : Generate tests for the api}';


    public function handle()
    {
        try {

            $filesystem = app(Filesystem::class);

            $output = MyTestCommandRunner::runWithAnswers(new KjosMakeRouteApiCommand($filesystem), [
                'ask' => ['John'],
                'confirm' => [true],
                'choice' => ['blue'],
            ]);
            dd($output);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
