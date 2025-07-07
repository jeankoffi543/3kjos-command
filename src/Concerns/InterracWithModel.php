<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

trait InterracWithModel
{
   protected static string $name = '';
   protected static string $fileName  = '';
   protected static string $modelName  = '';
   protected static Entity $entity;
   protected static string $path;
   protected static Path $pathObject;
   protected static KjosMakeRouteApiCommand $command;
   protected static array $attributNames = [];

   public static function init(Entity $entity, string $path, KjosMakeRouteApiCommand $command)
   {
      self::$entity = $entity;
      self::$name = NameHelper::namePlural($entity->getName(), NameArgument::Studly);
      self::$fileName = NameHelper::nameSingular($entity->getName(), NameArgument::Lower);
      self::$modelName = NameHelper::nameSingular($entity->getName(), NameArgument::Studly);
      self::$command = $command;
      self::$path = $path;
      self::$pathObject = new Path();
   }


   public static function relationsMethod(Entity $entity): array
   {
      $relations = [];
      $attributes = $entity->getAttributes();

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
               $relationModelName = NameHelper::nameSingular($on, NameArgument::Studly);
               if ($options !== null) {
                  if ($columns !== null && is_array($columns)) {
                     foreach ($columns as $column) {
                        $relations[$relationModelName] = self::generateRelationMethod($column, $on, $references);
                     }
                  } else {
                     $relations[$relationModelName] = self::generateRelationMethod($columns, $on, $references);
                  }
               }
            }
         }
      }

      return $relations;
   }

   public static function  columns(Entity $entity): array
   {
      $columns = [];
      $attributs = $entity->getAttributes();
      foreach ($attributs as $attribut) {
         $columns[] = $attribut->getName();
      }

      return $columns;
   }

   public static function relationName(Entity $entity): array
   {
      $relations = [];

      return $relations;
   }


   public static function generateRelationMethod(?string $column, string $on, string $reference): string
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


   private static function getNamespace(string $name, ?string $namePath = 'modelsPath'): string
   {
      $name = NameHelper::nameSingular($name, NameArgument::Studly);
      return self::$pathObject->getAllNamspaces()[$namePath] . '\\' . $name;
   }

   private static function useStatments(array $relations): string
   {
      $main = self::getNamespace(self::$entity->getName());
      $use = ["use {$main}"];
      foreach ($relations as $relation) {
         $medel = self::getNamespace($relation);
         $use[] = "use {$medel}";
      }
      return implode(";\r\n", $use) . ";";
   }

   public static function relations(Entity $entity): array
   {
      $relations = [];
      $attributes = $entity->getAttributes();

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
