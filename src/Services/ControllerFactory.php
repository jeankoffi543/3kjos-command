<?php

namespace Kjos\Command\Services;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\ControllerKitProvider;
use Kjos\Command\Concerns\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Concerns\NameHelper;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Concerns\PhpBodyFactory;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Enums\Namespaces;
use Kjos\Command\Managers\Entity;

class ControllerFactory
{
   use InterractWithService;

   protected string $name;
   protected ?FileFactory $fileFactory;
   protected string $routeGroups = '';
   private array $namespaces = [];
   private string $nameStudySingular = '';
   protected KjosMakeRouteApiCommand $command;
   protected string $path = '';

   public function __construct(Entity $entity, Path $path, KjosMakeRouteApiCommand $command)
   {
      $this->name = $entity->getName();
      $this->nameStudySingular = NameHelper::nameSingular($this->name, NameArgument::Studly);
      $this->path = $path->getControllersPath("{$this->nameStudySingular}Controller.php");
      ControllerKitProvider::init($this->name, $command);
      $this->namespaces = $path->getAllNamspaces();
      $this->fileFactory = new FileFactory($this->path, $command);
      $this->command = $command;
   }

   public function make()
   {
         // file php body
         $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this->command, $this->path);
         $phpBodyFactory->addClassDeclaration("class {$this->name}Controller extends BaseController")
            ->addProperties(ControllerKitProvider::getServiceProperty($this->namespaces['servicesPath']), 'service')
            ->addMethods(ControllerKitProvider::getServices($this->namespaces['servicesPath']), 'getServices')
            ->addMethods(ControllerKitProvider::index(), 'index')
            ->addMethods(ControllerKitProvider::show(), 'show')
            ->addMethods(ControllerKitProvider::store(), 'store')
            ->addMethods(ControllerKitProvider::update(), 'update')
            ->addMethods(ControllerKitProvider::destroy(), 'destroy');

         $this->fileFactory
            ->addNamespace("namespace {$this->namespaces['controllersPath']}")
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
