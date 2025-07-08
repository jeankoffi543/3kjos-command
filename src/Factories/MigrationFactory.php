<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Libs\MigrationKitProvider;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class MigrationFactory extends BuilderFactory
{
   use InterractWithService;

   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getMigrationsPath();
   }

   public function make()
   {
      (new MigrationKitProvider($this))->genarateClassContent();
   }
}
