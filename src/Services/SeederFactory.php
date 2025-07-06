<?php

namespace Kjos\Command\Services;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\SeederKitProvider;
use Kjos\Command\Concerns\NameHelper;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class SeederFactory
{

   private string $nameStudy = '';
   protected KjosMakeRouteApiCommand $command;
   protected Entity $entity;
   protected string $path = '';

   public function __construct(Entity $entity, Path $path, KjosMakeRouteApiCommand $command)
   {
      $this->entity = $entity;
      $this->nameStudy = NameHelper::nameSingular($this->entity->getName(), NameArgument::Studly);
      $this->path = $path->getSeedersPath("{$this->nameStudy}Seeder.php");
      $this->command = $command;
      SeederKitProvider::init($this->entity, $this->path, $this->command);
   }

   public function make()
   {
      SeederKitProvider::build();
   }
}
