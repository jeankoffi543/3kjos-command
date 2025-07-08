<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\Helpers\NameHelper;
use Kjos\Command\Managers\Path;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class BuilderFactory extends Path
{
   protected string $primaryKey = 'id';
   protected string $table = '';
   protected string $className = '';
   protected string $namespace = '';
   public string $path = '';
   protected string $nameSingularLower = '';
   protected string $nameSingularStudly = '';
   protected string $namePluralLower = '';
   protected string $namePluralStudly = '';
   protected string $controllerName = '';
   protected string $requestName = '';
   protected string $resourceName = '';
   protected string $serviceName = '';
   protected string $modelName = '';
   protected string $factoryName = '';
   protected string $seederName = '';
   protected string $viewName = '';
   protected string $datasetName = '';
   protected string $testName = '';
   protected string $routeName = '';
   public Entity $entity;
   public KjosMakeRouteApiCommand $command;
   
   
   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct();
      
      $this->command = $command;
      $this->entity = $entity;
      $this->nameSingularLower = NameHelper::namesingular($this->entity->getName(), NameArgument::Lower);
      $this->nameSingularStudly = NameHelper::namesingular($this->entity->getName(), NameArgument::Studly);
      $this->namePluralLower = NameHelper::nameplural($this->entity->getName(), NameArgument::Lower);
      $this->namePluralStudly = NameHelper::nameplural($this->entity->getName(), NameArgument::Studly);
      $this->table = NameHelper::nameplural($this->entity->getName(), NameArgument::Lower);

      $this->controllerName = $this->nameSingularStudly . 'Controller';
      $this->requestName = $this->nameSingularStudly . 'Request';
      $this->resourceName = $this->nameSingularStudly . 'Resource';
      $this->serviceName = $this->nameSingularStudly . 'Service';
      $this->modelName = $this->nameSingularStudly;
      $this->factoryName = $this->nameSingularStudly . 'Factory';
      $this->seederName = $this->nameSingularStudly . 'Seeder';
      $this->viewName = $this->nameSingularStudly;
      $this->datasetName = $this->namePluralStudly . 'Dataset';
      $this->testName = $this->nameSingularStudly . 'Test';
      $this->routeName = $this->namePluralLower;
   }

   public function getTable(): string
   {
      return $this->table;
   }

   public function getPrimaryKey(): string
   {
      return $this->primaryKey;
   }

   public function setPrimaryKey(string $primaryKey): void
   {
      $this->primaryKey = $primaryKey;
   }
   
   public function getNameSingularLower(): string
   {
      return $this->nameSingularLower;
   }
   
   public function getNameSingularStudly(): string
   {
      return $this->nameSingularStudly;
   }
   
   public function getNamePluralLower(): string
   {
      return $this->namePluralLower;
   }
   
   public function getNamePluralStudly(): string
   {
      return $this->namePluralStudly;
   }

   public function getClassName(): string
   {
      return $this->className;
   }

   public function getNamespace(): string
   {
      return $this->namespace;
   }

   public function getPath(): string
   {
      return $this->path;
   }

   public function getControllerName(): string
   {
      return $this->controllerName;
   }

   public function getRequestName(): string
   {
      return $this->requestName;
   }

   public function getResourceName(): string
   {
      return $this->resourceName;
   }

   public function getServiceName(): string
   {
      return $this->serviceName;
   }

   public function getModelName(): string
   {
      return $this->modelName;
   }

   public function getFactoryName(): string
   {
      return $this->factoryName;
   }

   public function getSeederName(): string
   {
      return $this->seederName;
   }

   public function getViewName(): string
   {
      return $this->viewName;
   }

   public function getDatasetName(): string
   {
      return $this->datasetName;
   }

   public function getTestName(): string
   {
      return $this->testName;
   }

   public function getRouteName(): string
   {
      return $this->routeName;
   }
}
