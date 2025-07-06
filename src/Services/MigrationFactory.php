<?php

namespace Kjos\Command\Services;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Concerns\MigrationKitProvider;
use Kjos\Command\Concerns\NameHelper;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class MigrationFactory
{
   use InterractWithService;

   private string $nameStudySingular = '';
   protected KjosMakeRouteApiCommand $command;
   protected Entity $entity;
   protected string $path = '';

   public function __construct(Entity $entity, Path $path, KjosMakeRouteApiCommand $command)
   {
      $this->entity = $entity;
      $this->nameStudySingular = NameHelper::nameSingular($this->entity->getName(), NameArgument::Studly);
      $this->path = $path->getMigrationsPath();
      $this->command = $command;
      MigrationKitProvider::init($this->entity, $this->path, $this->command);
   }

   public function make()
   {
      MigrationKitProvider::genarateClassContent();
   }
}
