<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Enums\Namespaces;

trait InterractWithService
{
   public function parseNamespace(): string
   {
      $namesapaces = [];
      foreach ($this->namespaces as $key => $namespace) {
         $namesapaces[] = match (Namespaces::tryFrom(rtrim($key, 'Path'))) {
            Namespaces::REQUESTS => "use {$namespace}\\{$this->getRequestName()}",
            Namespaces::RESOURCES => "use {$namespace}\\{$this->getResourceName()}",
            Namespaces::MODELS => "use {$namespace}\\{$this->getModelName()}",
            Namespaces::FACTORIES => "use {$namespace}\\{$this->getFactoryName()}",
            default => null,
         };
      }
      return implode(",", array_filter($namesapaces));
   }
}
