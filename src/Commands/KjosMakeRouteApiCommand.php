<?php

namespace Kjos\Command\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Kjos\Command\Concerns\Path;
use Kjos\Command\CrudGenerator;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Managers\Questions;
use Kjos\Command\Services\DatasetFactory;
use Kjos\Command\Services\MigrationFactory;
use Kjos\Command\Services\TestFactoryFactory;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'kjos:make:api')]
class KjosMakeRouteApiCommand extends GeneratorCommand
{
    /**
     * Console command name
     *
     * @var string
     */
    protected $name = 'kjos:make:api';

    /**
     * Console command description
     *
     * @var string
     */
    protected $description = 'Create new site for installed app by creating the specific .env file and storage dirs.';

    protected $signature = 'kjos:make:api 
    {name=police : the prefix of the site}
    {--f|force : Force api creation if it already exists}
    {--eh|errorhandler : Enable error handling mode}
    {--c|centralize : Enable centralize mode}
    {--factory : Generate factory for model}
    {--endpoint_type=: the type of endpoint: group, standalone, apiResource, resource. default is group}
    {--t|test : Generate tests for the api}';

    private ?array $runtimeDatas = [];

    public function handle()
    {
        try {

            $path = new Path($this);
            $entity = new Entity($this->argument('name'));
            $question = new Questions($this);
          
            if(! $response = $question->ask([])) {
                $this->error('We cannot process your request, we don\'t have enough data.');
                return Command::FAILURE;
            }
            $entity->setAttributes($response);
            
            CrudGenerator::generate($entity, $path, $this);

            // $this->line($e->getMessage());
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return '';
    }
}
