<?php

namespace Kjos\Command\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Managers\Questions;
use Kjos\Command\Services\CrudGenerator;
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
    {--endpoint_type= : the type of endpoint: group, standalone, apiResource, resource. default is group}
    {--t|test : Generate tests for the api}';

    public function handle()
    {
        try {
            $this->checkIfInstalled();

            $entity = new Entity($this->argument('name'));
            $entity->setPrimaryKey($this->primaryKey());

            $question = new Questions($this);

            if (! $response = $question->ask([])) {
                $this->error('We cannot process your request, we don\'t have enough data.');
                return Command::FAILURE;
            }
            $entity->setAttributes($response);

            CrudGenerator::generate($entity, $this);

            // $this->line($e->getMessage());
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e);
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

    private function checkIfInstalled()
    {
        if (! config('3kjos-command')) {
            throw new \Exception('3kjos-command is not installed: run -> php artisan vendor:publish --tag=3kjos-command');
        }
    }

       private function primaryKey(): ?string
   {
      $primaryKey = $this->ask('Enter the primary key or type <fg=yellow>[enter]</> to use default. Ex: user_id', null);
      if (! empty($primaryKey) && ! is_string($primaryKey)) {
         $this->error("Invalid name: '{$primaryKey}', it must be a string");
         return $this->primaryKey();
      }
      return $primaryKey;
   }
}
