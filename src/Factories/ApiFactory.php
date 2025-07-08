<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Factories\FileFactory;
use Kjos\Command\Factories\PhpBodyFactory;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Managers\Entity;

class ApiFactory extends BuilderFactory
{
   protected ?FileFactory $fileFactory;
   protected array $parsedFileContent = [];
   protected string $routeGroups = '';

   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getRouteApiPath();
      $this->fileFactory = new FileFactory($this);
      $this->parsedFileContent = $this->fileFactory->parseContent();
      $this->routeGroups = $this->fileFactory->parseRouteGroup($this->entity->getName());
   }

   public function make()
   {
         // file php body
         $phpBodyFactory = new PhpBodyFactory($this->parsedFileContent, $this);
         $phpBodyFactory->addRouteGroup($this->routeGroups);

         $this->fileFactory
            ->addUseStatements("use Illuminate\Support\Facades\Route")
            ->addBody($phpBodyFactory)
            ->save()
         ;
   }
}
