<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Factories\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Libs\ModeltKitProvider;
use Kjos\Command\Factories\PhpBodyFactory;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class ModelFactory extends BuilderFactory
{
   use InterractWithService;

   protected ?FileFactory $fileFactory;
   protected array $parsedFileContent = [];
   
   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getModelsPath("{$this->getModelName()}.php");
      $this->fileFactory = new FileFactory($this);
   }

   public function make()
   {
      $modeltKitProvider = new ModeltKitProvider($this);

      // file php body
      $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this);
      $phpBodyFactory->addClassDeclaration("class {$this->getModelName()} extends Model")
         ->addTraits("use HasFactory;");
         if($this->entity->getPrimaryKey()) {
            $phpBodyFactory->addProperties("protected \$primaryKey = '{$this->entity->getPrimaryKey()}';", 'primaryKey');
         }
         $phpBodyFactory
         ->addProperties("protected \$table = '{$this->getTable()}';", 'table')
         ->addProperties($modeltKitProvider->parseColumns(), 'fillable')
         ->addMethods($modeltKitProvider->newFactory(), "newFactory");
      foreach ($modeltKitProvider->relationsMethod() as $name => $relation) {
         $phpBodyFactory->addMethods($relation, $name);
      }

      $this->fileFactory
         ->addNamespace("namespace {$this->getAllNamspaces()['modelsPath']}")
         ->addUseStatements($modeltKitProvider->useStatments())
         ->addBody($phpBodyFactory)
         ->save();
   }
}
