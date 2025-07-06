<?php

namespace Kjos\Command\Services;

use Kjos\Command\Concerns\FileFactory;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Concerns\PhpBodyFactory;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Managers\Entity;

class ApiFactory
{
   protected ?FileFactory $fileFactory;
   protected array $parsedFileContent = [];
   protected string $routeGroups = '';
   protected Entity $entity;
   protected KjosMakeRouteApiCommand $command;
   protected string $path = '';

   public function __construct(Entity $entity, Path $path, KjosMakeRouteApiCommand $command)
   {
      $this->path = $path->getRouteApiPath();
      $this->command = $command;
      $this->entity = $entity;
      $this->fileFactory = new FileFactory($this->path, $command);
      $this->parsedFileContent = $this->fileFactory->parseContent();
      $this->routeGroups = $this->fileFactory->parseRouteGroup($this->entity->getName());
   }

   public function make()
   {
         // file php body
         $phpBodyFactory = new PhpBodyFactory($this->parsedFileContent, $this->command, $this->path);
         $phpBodyFactory->addRouteGroup($this->routeGroups);

         $this->fileFactory
            ->addUseStatements("use Illuminate\Support\Facades\Route")
            ->addBody($phpBodyFactory)
            ->save()
         ;
   }
}
