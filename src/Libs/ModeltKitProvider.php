<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Concerns\InterracWithModel;
use Kjos\Command\Factories\BuilderFactory;

class ModeltKitProvider
{
   use InterracWithModel;

   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }

   public function newFactory(): string
   {
      return <<<REQUEST
            protected static function newFactory()
            {
               return {$this->factory->getFactoryName()}::new();
            }
         REQUEST;
   }


   public function useStatments(): string
   {
      $factory = $this->factory->getAllNamspaces()["factoriesPath"] . '\\' . $this->factory->getFactoryName();
      return "use Illuminate\Database\Eloquent\Factories\HasFactory
            use Illuminate\Database\Eloquent\Model
            use {$factory}";
   }

   public function  parseColumns(): ?string
   {
      $columns = [];
      $attributs = $this->factory->entity->getAttributes();
      foreach ($attributs as $attribut) {
         $columns[] = "'{$attribut->getName()}'";
      }
      if ($columns) {
         $columns = PHP_EOL . implode(',' . PHP_EOL, $columns) . PHP_EOL;
         return "protected \$fillable = [{$columns}];";
      } else {
         return '';
      }
   }
}
