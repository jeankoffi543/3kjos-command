<?php

namespace Kjos\Command\Services;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\FactoryKitProvider;
use Kjos\Command\Concerns\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Concerns\NameHelper;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Concerns\PhpBodyFactory;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class FactoryFactory
{
   use InterractWithService;

   protected ?FileFactory $fileFactory;
   protected array $parsedFileContent = [];
   private array $namespaces = [];
   private string $nameStudySingular = '';
   protected KjosMakeRouteApiCommand $command;
   protected Entity $entity;
   protected string $path = '';

   public function __construct(Entity $entity, Path $path, KjosMakeRouteApiCommand $command)
   {
      $this->entity = $entity;
      $this->nameStudySingular = NameHelper::nameSingular($this->entity->getName(), NameArgument::Studly);
      $this->path = $path->getFactoriesPath("{$this->nameStudySingular}Factory.php");
      $this->namespaces = $path->getAllNamspaces();
      $this->fileFactory = new FileFactory($this->path, $command);
      $this->command = $command;
   }

   public function make()
   {
      // file php body
      $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this->command, $this->path);
      $phpBodyFactory->addClassDeclaration("class {$this->nameStudySingular}Factory extends Factory")
         ->addProperties("protected \$model = {$this->nameStudySingular}::class;", 'model')
         ->addMethods(FactoryKitProvider::definition($this->entity), "definition");

      $this->fileFactory
         ->addNamespace("namespace {$this->namespaces['factoriesPath']}")
         ->addUseStatements(FactoryKitProvider::useStatments($this->entity))
         ->addBody($phpBodyFactory)
         ->save();
   }
}
