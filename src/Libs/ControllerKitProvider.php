<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Concerns\InterraxtWithController;
use Kjos\Command\Factories\BuilderFactory;

class ControllerKitProvider
{
   use InterraxtWithController;

   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }

   public function getServiceProperty(string $namespace): string
   {
      if (
         (
            $this->factory->command->option('centralize') &&
            $this->factory->command->option('errorhandler')
         ) ||
         (
            $this->factory->command->option('centralize')
         )
      ) {
         return "/** @var \\{$namespace}\\{$this->factory->getServiceName()} */ \n protected \$service;";
      } else {
         return '';
      }
   }

   public function getServices(string $namespace): string
   {
      if (
         (
            $this->factory->command->option('centralize')
           
         ) ||
         (
            $this->factory->command->option('centralize')
         )
      ) {
         return <<<SERVICE
            public function getServices(): array
            {
               return [
                  'service' => \\{$namespace}\\{$this->factory->getServiceName()}::class,
               ];
            }
            SERVICE;
      } else {
         return '';
      }
   }

   public function index(): string
   {
      if (
         !$this->factory->command->option('centralize') &&
         !$this->factory->command->option('errorhandler')
      ) {
         return $this->defaultIndex();
      } else if (
         $this->factory->command->option('centralize') &&
         $this->factory->command->option('errorhandler')
      ) {
         return $this->centralizeAndErrorHandlerIndex();
      } else if ($this->factory->command->option('centralize')) {
         return $this->centralizeIndex();
      } else if ($this->factory->command->option('errorhandler')) {
         return $this->errorHandlerIndex();
      }
      return $this->defaultIndex();
   }

   public function show(): string
   {
      if (
         !$this->factory->command->option('centralize') &&
         !$this->factory->command->option('errorhandler')
      ) {
         return $this->defaultShow();
      } else if (
         $this->factory->command->option('centralize') &&
         $this->factory->command->option('errorhandler')
      ) {
         return $this->centralizeAndErrorHandlerShow();
      } else if ($this->factory->command->option('centralize')) {
         return $this->centralizeShow();
      } else if ($this->factory->command->option('errorhandler')) {
         return $this->errorHandlerShow();
      }
      return $this->defaultShow();
   }

   public function store(): string
   {
      if (
         !$this->factory->command->option('centralize') &&
         !$this->factory->command->option('errorhandler')
      ) {
         return $this->defaultStore();
      } else if (
         $this->factory->command->option('centralize') &&
         $this->factory->command->option('errorhandler')
      ) {
         return $this->centralizeAndErrorHandlerStore();
      } else if ($this->factory->command->option('centralize')) {
         return $this->centralizeStore();
      } else if ($this->factory->command->option('errorhandler')) {
         return $this->errorHandlerStore();
      }
      return $this->defaultStore();
   }

   public function update(): string
   {
      if (
         !$this->factory->command->option('centralize') &&
         !$this->factory->command->option('errorhandler')
      ) {
         return $this->defaultUpdate();
      } else if (
         $this->factory->command->option('centralize') &&
         $this->factory->command->option('errorhandler')
      ) {
         return $this->centralizeAndErrorHandlerUpdate();
      } else if ($this->factory->command->option('centralize')) {
         return $this->centralizeUpdate();
      } else if ($this->factory->command->option('errorhandler')) {
         return $this->errorHandlerUpdate();
      }
      return $this->defaultUpdate();
   }

   public function destroy(): string
   {
      if (
         !$this->factory->command->option('centralize') &&
         !$this->factory->command->option('errorhandler')
      ) {
         return $this->defaultDestroy();
      } else if (
         $this->factory->command->option('centralize') &&
         $this->factory->command->option('errorhandler')
      ) {
         return $this->centralizeAndErrorHandlerDestroy();
      } else if ($this->factory->command->option('centralize')) {
         return $this->centralizeDestroy();
      } else if ($this->factory->command->option('errorhandler')) {
         return $this->errorHandlerDestroy();
      }
      return $this->defaultDestroy();
   }
}
