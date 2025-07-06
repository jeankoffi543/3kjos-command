<?php

namespace Kjos\Command;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Services\ApiFactory;
use Kjos\Command\Services\ControllerFactory;
use Kjos\Command\Services\DatasetFactory;
use Kjos\Command\Services\FactoryFactory;
use Kjos\Command\Services\MigrationFactory;
use Kjos\Command\Services\ModelFactory;
use Kjos\Command\Services\RequestFactory;
use Kjos\Command\Services\ResourceFactory;
use Kjos\Command\Services\SeederFactory;
use Kjos\Command\Services\ServiceFactory;
use Kjos\Command\Services\TestFactory;

class CrudGenerator
{
   public static function generate(Entity $entity, Path $path, KjosMakeRouteApiCommand $command)
   {
         (new ApiFactory($entity, $path, $command))->make();
         (new ControllerFactory($entity, $path, $command))->make();
         (new ResourceFactory($entity, $path, $command))->make();
         (new ServiceFactory($entity, $path, $command))->make();
         (new RequestFactory($entity, $path, $command))->make();
         (new ModelFactory($entity, $path, $command))->make();
         (new FactoryFactory($entity, $path, $command))->make();
         (new MigrationFactory($entity, $path, $command))->make();
         (new DatasetFactory($entity, $path, $command))->make();
         (new TestFactory($entity, $path, $command))->make();
         (new SeederFactory($entity, $path, $command))->make();
   }
}
