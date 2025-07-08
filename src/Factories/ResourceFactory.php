<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Factories\FileFactory;
use Kjos\Command\Factories\PhpBodyFactory;
use Kjos\Command\Libs\ResourceKitProvider;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class ResourceFactory extends BuilderFactory
{
   protected ?FileFactory $fileFactory;

   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getResourcesPath("{$this->getResourceName()}.php");
      $this->fileFactory = new FileFactory($this);
   }

   public function make()
   {
      $resourceKitProvider = new ResourceKitProvider($this);
         // file php body
         $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this);
         $phpBodyFactory->addClassDeclaration("class {$this->getResourceName()} extends JsonResource")
            ->addMethods($resourceKitProvider->toArray(), 'toArray');

         $this->fileFactory
            ->addNamespace("namespace {$this->getAllNamspaces()['resourcesPath']}")
            ->addUseStatements("Illuminate\Http\Request")
            ->addUseStatements("use Illuminate\Http\Resources\Json\JsonResource")
            ->addBody($phpBodyFactory)
            ->save()
         ;
   }
}
