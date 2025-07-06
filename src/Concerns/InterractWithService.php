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
            Namespaces::REQUESTS => "use {$namespace}\\{$this->nameStudySingular}Request",
            Namespaces::RESOURCES => "use {$namespace}\\{$this->nameStudySingular}Resource",
            Namespaces::MODELS => "use {$namespace}\\{$this->nameStudySingular}",
            Namespaces::FACTORIES => "use {$namespace}\\{$this->nameStudySingular}Factory",
            default => null,
         };
      }
      return implode(",", array_filter($namesapaces));
   }
}
