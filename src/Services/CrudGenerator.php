<?php

namespace Kjos\Command\Services;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Factories\ApiFactory;
use Kjos\Command\Factories\ControllerFactory;
use Kjos\Command\Factories\DatasetFactory;
use Kjos\Command\Factories\FactoryFactory;
use Kjos\Command\Factories\MigrationFactory;
use Kjos\Command\Factories\ModelFactory;
use Kjos\Command\Factories\RequestFactory;
use Kjos\Command\Factories\ResourceFactory;
use Kjos\Command\Factories\SeederFactory;
use Kjos\Command\Factories\ServiceFactory;
use Kjos\Command\Factories\TestFactory;

class CrudGenerator
{
   public static function generate(Entity $entity, KjosMakeRouteApiCommand $command)
   {
         (new ApiFactory($entity, $command))->make();
         (new ControllerFactory($entity, $command))->make();
         (new ResourceFactory($entity, $command))->make();
         (new ServiceFactory($entity, $command))->make();
         (new RequestFactory($entity, $command))->make();
         (new ModelFactory($entity, $command))->make();
         (new FactoryFactory($entity, $command))->make();
         (new MigrationFactory($entity, $command))->make();
         (new DatasetFactory($entity, $command))->make();
         (new TestFactory($entity, $command))->make();
         (new SeederFactory($entity, $command))->make();
   }
}
