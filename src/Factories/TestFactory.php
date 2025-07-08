<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Libs\TestKitProvider;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class TestFactory extends BuilderFactory
{
   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getTestsPath("{$this->getTestName()}.php");
   }

   public function make()
   {
      (new TestKitProvider($this))->build();
   }
}
