<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Factories\BuilderFactory;

class ResourceKitProvider
{
   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }

   public function toArray(): string
   {
      $resource = [];
      $primaryKey = $this->factory->entity->getPrimaryKey() ?? 'id';

      $resourceId = "'{$primaryKey}' => \$this->resource->{$primaryKey},";

      foreach ($this->factory->entity->getAttributes() as $attribute) {
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
