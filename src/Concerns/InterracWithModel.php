<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Concerns\Helpers\NameHelper;
use Kjos\Command\Enums\NameArgument;

trait InterracWithModel
{
   protected array $attributNames = [];

   public function relationsMethod(): array
   {
      $relations = [];
      $attributes = $this->factory->entity->getAttributes();

      /**
       * @var \Kjos\Command\Managers\Attribut $attribute
       */
      foreach ($attributes as $attribute) {
         foreach ($attribute->getIndexes() as $index) {
            /**
             * @var \Kjos\Command\Managers\ColumnIndex $index
             */
            $method = data_get($index->toArray(), 'method');
            $options = data_get($index->toArray(), 'options');
            $columns = data_get($index->toArray(), 'options.columns');
            $references = data_get($index->toArray(), 'options.references');
            $on = data_get($index->toArray(), 'options.on');

            if ($method === 'foreign') {
               $relationModelName = NameHelper::nameSingular($on, NameArgument::Lower);
               if ($options !== null) {
                  if ($columns !== null && is_array($columns)) {
                     foreach ($columns as $column) {
                        $relations[$relationModelName] = $this->generateRelationMethod($column, $on, $references);
                     }
                  } else {
                     $relations[$relationModelName] = $this->generateRelationMethod($columns, $on, $references);
                  }
               }
            }
         }
      }

      return $relations;
   }

   public function  columns(): array
   {
      $columns = [];
      $attributs = $this->factory->entity->getAttributes();
      foreach ($attributs as $attribut) {
         $columns[] = $attribut->getName();
      }

      return $columns;
   }

   public function relationName(): array
   {
      $relations = [];

      return $relations;
   }


   public function generateRelationMethod(?string $column, string $on, string $reference): string
   {
      $relationModelName = NameHelper::nameSingular($on, NameArgument::Studly);
      $relationName = NameHelper::nameSingular($on, NameArgument::Lower);
      $column = $column ?? $reference;
      return "public function {$relationName}()
              {
               return \$this->belongsTo({$relationModelName}::class, '{$column}', '{$reference}');
              }
            ";
   }


   private function getNamespace(string $name, ?string $namePath = 'modelsPath'): string
   {
      $name = NameHelper::nameSingular($name, NameArgument::Studly);
      return $this->factory->getAllNamspaces()[$namePath] . '\\' . $name;
   }

   private function useStatments(array $relations): string
   {
      $main = $this->getNamespace($this->factory->entity->getName());
      $use = ["use {$main}"];
      foreach ($relations as $relation) {
         $medel = $this->getNamespace($relation);
         $use[] = "use {$medel}";
      }
      return implode(";\r\n", $use) . ";";
   }

   public function relations(): array
   {
      $relations = [];
      $attributes = $this->factory->entity->getAttributes();

      /**
       * @var \Kjos\Command\Managers\Attribut $attribute
       */
      foreach ($attributes as $attribute) {
         foreach ($attribute->getIndexes() as $index) {
            /**
             * @var \Kjos\Command\Managers\ColumnIndex $index
             */
            $method = data_get($index->toArray(), 'method');
            $on = data_get($index->toArray(), 'options.on');

            if ($method === 'foreign') {
               $relations[] = NameHelper::nameSingular($on, NameArgument::Studly);              
            }
         }
      }

      return $relations;
   }
}
