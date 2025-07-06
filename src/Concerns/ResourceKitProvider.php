<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Managers\Entity;

class ResourceKitProvider
{
   public static function toArray(Entity $entity): string
   {
      $resource = [];
      $resourceId = config('3kjos-command.resource.use_id') ?
          "'id' => \$this->resource->id," : 
          "'{$entity->getName()}_id' => \$this->resource->{$entity->getName()},";

      foreach ($entity->getAttributes() as $key => $attribute) {
         $resource[] = "'{$attribute->getName()}' => \$this->resource->{$attribute->getName()}";
      }
      $resource = implode(",\n", $resource);

      return <<<RESOURCE
            public function toArray(Request \$request): array
            {
               return [
                  {$resourceId}
                  {$resource}
               ];
            }
         RESOURCE;
   }
}
