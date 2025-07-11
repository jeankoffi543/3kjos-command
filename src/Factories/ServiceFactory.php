<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Factories\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Factories\PhpBodyFactory;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class ServiceFactory extends BuilderFactory
{
   use InterractWithService;

   protected ?FileFactory $fileFactory;
   protected array $parsedFileContent = [];
   
   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getServicesPath("{$this->getServiceName()}.php");
      $this->fileFactory = new FileFactory($this);
   }

   public function make()
   {
      // file php body
      $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this);
      $phpBodyFactory->addClassDeclaration("class {$this->getServiceName()} extends BaseService")
         ->addProperties("protected \$model = {$this->getModelName()}::class;", 'model')
         ->addProperties("protected \$resource = {$this->getResourceName()}::class;", 'resource');

      $this->fileFactory
         ->addNamespace("namespace {$this->getAllNamspaces()['servicesPath']}")
         ->addUseStatements($this->parseNamespace())
         ->addUseStatements("use Kjos\Command\Managers\Service as BaseService")
         ->addBody($phpBodyFactory)
         ->save()
      ;
   }
}
