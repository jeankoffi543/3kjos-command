<?php

namespace Kjos\Command\Services;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Concerns\NameHelper;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Concerns\PhpBodyFactory;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class ServiceFactory
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
      $this->path = $path->getServicesPath("{$this->nameStudySingular}Service.php");
      $this->namespaces = $path->getAllNamspaces();
      $this->fileFactory = new FileFactory($this->path, $command);
      $this->command = $command;
   }

   public function make()
   {
      // file php body
      $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this->command, $this->path);
      $phpBodyFactory->addClassDeclaration("class {$this->nameStudySingular}Service extends Service")
         ->addProperties("protected string \$model = {$this->nameStudySingular}::class;", 'model')
         ->addProperties("protected string \$resource = {$this->nameStudySingular}Resource::class;", 'resource');

      $this->fileFactory
         ->addNamespace("namespace {$this->namespaces['servicesPath']}")
         ->addUseStatements($this->parseNamespace())
         ->addUseStatements("use Kjos\Command\Managers\Service")
         ->addBody($phpBodyFactory)
         ->save()
      ;
   }
}
