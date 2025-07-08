<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Libs\ControllerKitProvider;
use Kjos\Command\Factories\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Factories\PhpBodyFactory;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class ControllerFactory extends BuilderFactory
{
   use InterractWithService;

   protected ?FileFactory $fileFactory;

   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getControllersPath("{$this->getControllerName()}.php");
      $this->fileFactory = new FileFactory($this);
   }

   public function make()
   {
      $controllerKiteProvider = new ControllerKitProvider($this);
         // file php body
         $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this);
         $phpBodyFactory->addClassDeclaration("class {$this->getControllerName()} extends BaseController")
            ->addProperties($controllerKiteProvider->getServiceProperty($this->namespaces['servicesPath']), 'service')
            ->addMethods($controllerKiteProvider->getServices($this->namespaces['servicesPath']), 'getServices')
            ->addMethods($controllerKiteProvider->index(), 'index')
            ->addMethods($controllerKiteProvider->show(), 'show')
            ->addMethods($controllerKiteProvider->store(), 'store')
            ->addMethods($controllerKiteProvider->update(), 'update')
            ->addMethods($controllerKiteProvider->destroy(), 'destroy');

         $this->fileFactory
            ->addNamespace("namespace {$this->getAllNamspaces()['controllersPath']}")
            ->addUseStatements($this->parseNamespace())
            ->addUseStatements("use Illuminate\Http\Resources\Json\AnonymousResourceCollection")
            ->addUseStatements("use Illuminate\Http\Response")
            ->addUseStatements("Illuminate\Routing\Controller")
            ->addUseStatements("use Illuminate\Http\Request")
            ->addUseStatements("Kjos\Command\Managers\Controller as BaseController")
            ->addBody($phpBodyFactory)
            ->save()
         ;
   }
}
