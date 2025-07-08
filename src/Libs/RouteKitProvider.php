<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Concerns\InterractWithFileContent;
use Kjos\Command\Factories\BuilderFactory;

class RouteKitProvider
{
   use InterractWithFileContent;
   
   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }

   
}
