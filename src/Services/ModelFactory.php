<?php

namespace Kjos\Command\Services;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Concerns\ModeltKitProvider;
use Kjos\Command\Concerns\NameHelper;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Concerns\PhpBodyFactory;
use Kjos\Command\Concerns\RequestKitProvider;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class ModelFactory
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
      $this->path = $path->getModelsPath("{$this->nameStudySingular}.php");
      $this->namespaces = $path->getAllNamspaces();
      $this->fileFactory = new FileFactory($this->path, $command);
      $this->command = $command;
   }

   public function make()
   {
      // file php body
      $tableName = NameHelper::namePlural($this->entity->getName(), NameArgument::Lower);
      $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this->command, $this->path);
      $phpBodyFactory->addClassDeclaration("class {$this->nameStudySingular} extends Model")
         ->addTraits("use HasFactory;")
         ->addProperties("protected \$table = '{$tableName}';", 'table')
         ->addProperties(ModeltKitProvider::parseColumns($this->entity), 'fillable')
         ->addMethods(ModeltKitProvider::newFactory($this->entity), "newFactory");

      foreach (ModeltKitProvider::relations($this->entity) as $name => $relation) {
         $phpBodyFactory->addMethods($relation, $name);
      }

      $this->fileFactory
         ->addNamespace("namespace {$this->namespaces['modelsPath']}")
         ->addUseStatements(ModeltKitProvider::useStatments($this->entity))
         ->addBody($phpBodyFactory)
         ->save();
   }
}
