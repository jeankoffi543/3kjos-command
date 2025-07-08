<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Libs\DatasetKitProvider;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class DatasetFactory extends BuilderFactory
{
   use InterractWithService;

   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getDatasetsPath("{$this->getDatasetName()}.php");
   }

   public function make()
   {
      (new DatasetKitProvider($this))->genarateClassContent();
   }
}
