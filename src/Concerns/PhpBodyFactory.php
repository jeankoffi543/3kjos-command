<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class PhpBodyFactory
{
   protected ?string $methods = null;
   protected string $constructor;
   protected ?string $properties = null;
   protected ?string $extends;
   protected ?string $implements;
   protected array $uses = [];
   protected string $body;
   protected ?string $classDeclaration = null;
   protected ?string $traits = null;
   protected ?string $routeGroup = null;
   protected array $content = [];
   protected ?KjosMakeRouteApiCommand $command = null;
   protected string $path = '';

   public function __construct(array $content = [], ?KjosMakeRouteApiCommand $command = null, string $path = '')
   {
      $this->path = $path;
      $this->constructor = '';
      $this->extends = null;
      $this->implements = null;
      $this->traits = null;
      $this->body = '';
      $this->content = $content;
      $this->command = $command;
      $this->classDeclaration = $content['declaration'] ?? '';
      $this->traits = $content['traits'] ?? '';
      $this->properties = $content['properties'] ?? '';
      $this->methods = $content['full_methods'] ?? '';
   }

   public function addClassDeclaration(?string $classDeclaration = null): static
   {
      $this->classDeclaration = $classDeclaration ?? '';
      return $this;
   }

   public function getClassDeclaration(): ?string
   {
      return $this->classDeclaration;
   }

   public function addTraits(?string $traits = null): static
   {
      $path = kjos_get_namespace($this->path);
      $exists = false;
      if (isset($this->content['traits_to_array'])) {
         foreach ($this->content['traits_to_array'] as $t) {
            if ($t === trim($traits)) {
               $exists = true;
               $this->command->warn("[Warning] Property {$t}: already exists in {$path} <fg=red>[skipped]</>");
               break;
            }
         }
      }
      if (!$exists) {
         $this->traits = $this->traits . $traits ?? '';
      }
      return $this;
   }

   public function getTraits(): ?string
   {
      return $this->traits;
   }

   public function addProperties(?string $properties = null, ?string $name = null): static
   {
      $path = kjos_get_namespace($this->path);
      $exists = false;
      if (isset($this->content['properties_to_array'])) {
         foreach ($this->content['properties_to_array'] as $m) {
            if ($name === $m['name']) {
               $exists = true;
               $this->command->warn("[Warning] Property {$name}: already exists in {$path} <fg=red>[skipped]</>");
               break;
            }
         }
      }
      if (!$exists) {
         $this->properties = $this->properties . "\n" . $properties ?? '';
      }
      return $this;
   }

   public function getProperties(): ?string
   {
      return $this->properties;
   }

   public function addMethods(?string $methods = null, string $name = ''): static
   {
      $path = kjos_get_namespace($this->path);
      $exists = false;
      if (isset($this->content['methods'])) {
         foreach ($this->content['methods'] as $m) {
            if ($name === $m['name']) {
               $exists = true;
               $this->command->warn("[Warning] Method {$name}: already exists in {$path} <fg=red>[skipped]</>");
               break;
            }
         }
      }
      if (!$exists) {
         $this->methods = $this->methods . "\n" . $methods ?? '';
      }
      return $this;
   }

   public function getMethods(): ?string
   {
      return $this->methods;
   }

   public function addRouteGroup(?string $routeGroup = null): static
   {
      $this->routeGroup = $routeGroup ?? '';
      return $this;
   }

   public function getRouteGroup(): ?string
   {
      return $this->routeGroup;
   }


   public function getClassOpenTag(): string
   {
      return $this->getClassDeclaration() ? '{' : '';
   }

   public function getClassCloseTag(): string
   {
      return $this->getClassDeclaration() ? '}' : '';
   }

   public function get(): string
   {
      return <<<PHP
         {$this->classDeclaration} 
         {$this->getClassOpenTag()}
            {$this->getTraits()} 
            {$this->getProperties()} 
            {$this->getMethods()}
         {$this->getClassCloseTag()}
         {$this->getRouteGroup()}
         PHP;
   }
}
