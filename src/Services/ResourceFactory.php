<?php

namespace Kjos\Command\Services;

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Str;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\ControllerKitProvider;
use Kjos\Command\Concerns\FileFactory;
use Kjos\Command\Concerns\NameHelper;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Concerns\PhpBodyFactory;
use Kjos\Command\Concerns\ResourceKitProvider;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Enums\Namespaces;
use Kjos\Command\Managers\Entity;

class ResourceFactory
{
   protected ?FileFactory $fileFactory;
   private array $namespaces = [];
   private string $nameStudySingular = '';
   protected KjosMakeRouteApiCommand $command;
   protected Entity $entity;
   protected string $path = '';

   public function __construct(Entity $entity, Path $path, KjosMakeRouteApiCommand $command)
   {
      $this->entity = $entity;
      $this->nameStudySingular = NameHelper::nameSingular($this->entity->getName(), NameArgument::Studly);
      $this->path = $path->getResourcesPath("{$this->nameStudySingular}Resource.php");
      $this->namespaces = $path->getAllNamspaces();
      $this->fileFactory = new FileFactory($this->path, $command);
      $this->command = $command;
   }

   public function make()
   {
         // file php body
         $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this->command, $this->path);
         $phpBodyFactory->addClassDeclaration("class {$this->nameStudySingular}Resource extends JsonResource")
            ->addMethods(ResourceKitProvider::toArray($this->entity), 'toArray');

         $this->fileFactory
            ->addNamespace("namespace {$this->namespaces['resourcesPath']}")
            ->addUseStatements("Illuminate\Http\Request")
            ->addUseStatements("use Illuminate\Http\Resources\Json\JsonResource")
            ->addBody($phpBodyFactory)
            ->save()
         ;
   }
}
