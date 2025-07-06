<?php

namespace Kjos\Command\Concerns;

use Illuminate\Database\Eloquent\Relations\Relation;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Str;

class DatasetKitProvider
{
   use InterracWithModel;



   public static function genarateFileName(): string
   {
      return self::$name . '.php';
   }

   public static function genarateClassContent(): void
   {
      if (self::datasetExists()) {
         self::$command->error('Migration already exists <fg=red> [skipped]</>');
         return;
      }


      // $relations = self::guessRelationMethods(self::getNamespace(self::$entity->getName()));
      // if(empty($relations)) {
      //    self::$command->warn('cannot create dataset for ' . self::$entity->getName() . ' because it has no relations or model not found <fg=red> [skipped]</>');
      //    return;
      // }

      $uses = self::useStatments(self::relations(self::$entity));

      $for = self::buildFor(self::relations(self::$entity));
      $name = self::$modelName;
      $datasetPlural = NameHelper::namePlural(self::$entity->getName(), NameArgument::Lower);
      $datasetSingular = NameHelper::namesingular(self::$entity->getName(), NameArgument::Lower);
      $limit = config('3kjos-command.tests.dataset.create_many_limit', 5);
      $content = <<< DESCRIBE
         <?php
         
         {$uses}
         
         dataset('created {$datasetSingular}', [
            fn () => {$name}::factory()
               {$for}
               ->createOne(),
         ]);

         dataset('created {$datasetPlural}', [
            fn () => {$name}::factory()
               {$for}
               ->createMany({$limit}),
         ]);

         dataset('guest {$datasetSingular}', [
            fn () => {$name}::factory()
               {$for}
               ->makeOne(),
         ]);

      DESCRIBE;
      $path = self::$path;
      file_put_contents($path, $content);
      exec("./vendor/bin/pint {$path}", $output, $status);
   }


   private static function datasetExists(): bool
   {
      return file_exists(self::$path . '/' . self::genarateFileName());
   }

   private static function buildFor(array $relations): string
   {
      $relation_ = [];
      foreach ($relations as $relation) {
         $medel = NameHelper::nameSingular($relation, NameArgument::Studly);
         $relation_[] = "->for({$medel}::factory())";
      }
      return implode('', $relation_);
   }

   private static function guessRelationMethods($modelClass): array
   {
      try {
         $reflection = new \ReflectionClass($modelClass);
         $instance = new $modelClass;
      } catch (\Throwable $e) {
         return [];
      }
      $methods = [];

      // Récupère tous les traits utilisés par la classe (récursivement)
      $traits = class_uses_recursive($modelClass);
      $traitMethods = [];

      foreach ($traits as $trait) {
         $traitReflection = new \ReflectionClass($trait);
         foreach ($traitReflection->getMethods() as $traitMethod) {
            $traitMethods[] = $traitMethod->getName();
         }
      }

      foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
         // Ignore les méthodes avec paramètres
         if ($method->getNumberOfParameters() > 0) {
            continue;
         }

         // Ignore les méthodes venant des traits
         if (in_array($method->getName(), $traitMethods)) {
            continue;
         }

         try {
            $return = $method->invoke($instance);

            if ($return instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
               $methods[] = $method->getName();
            }
         } catch (\Throwable $e) {
            // Ignore les erreurs
         }
      }

      return $methods;
   }


}
