<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Libs\SeederKitProvider;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class SeederFactory extends BuilderFactory
{

   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getSeedersPath("{$this->getSeederName()}.php");
   }

   public function make()
   {
      (new SeederKitProvider($this))->build();
   }
}
