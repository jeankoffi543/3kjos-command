<?php

namespace Kjos\Command\Services;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\TestKitProvider;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Concerns\NameHelper;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class TestFactory
{
   private string $nameStudySingular = '';
   private string $nameStudyPlural = '';
   protected KjosMakeRouteApiCommand $command;
   protected Entity $entity;
   protected string $path = '';

   public function __construct(Entity $entity, Path $path, KjosMakeRouteApiCommand $command)
   {
      $this->entity = $entity;
      $this->nameStudySingular = NameHelper::nameSingular($this->entity->getName(), NameArgument::Studly);
      $this->nameStudyPlural = NameHelper::namePlural($this->entity->getName(), NameArgument::Studly);
      $this->path = $path->getTestsPath("{$this->nameStudyPlural}Test.php");
      $this->command = $command;
      TestKitProvider::init($this->entity, $this->path, $this->command);
   }

   public function make()
   {
      TestKitProvider::build();
   }
}
