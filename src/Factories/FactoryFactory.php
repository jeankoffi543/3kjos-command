<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Libs\FactoryKitProvider;
use Kjos\Command\Factories\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Factories\PhpBodyFactory;
use Kjos\Command\Managers\Entity;

class FactoryFactory extends BuilderFactory
{
   use InterractWithService;

   protected ?FileFactory $fileFactory;
   protected array $parsedFileContent = [];
   

   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getFactoriesPath("{$this->getFactoryName()}.php");
      $this->fileFactory = new FileFactory($this);
   }

   public function make()
   {
      $factoryKitProvider = new FactoryKitProvider($this);

      // file php body
      $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this);
      $phpBodyFactory->addClassDeclaration($this->getClassDeclaration())
         ->addProperties("protected \$model = {$this->getModelName()}::class;", 'model')
         ->addMethods($factoryKitProvider->definition(), "definition");

      $this->fileFactory
         ->addNamespace("namespace {$this->getAllNamspaces()['factoriesPath']}")
         ->addUseStatements($factoryKitProvider->useStatments())
         ->addBody($phpBodyFactory)
         ->save();
   }


   private function getClassDeclaration(): string
   {
      return <<<CLASS
         /** @extends Factory<{$this->getModelName()}> */         
         class {$this->getFactoryName()} extends Factory
      CLASS;
   }
}
