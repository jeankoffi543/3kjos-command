<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Concerns\InterracWithModel;
use Kjos\Command\Concerns\Helpers\NameHelper;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Factories\BuilderFactory;
use ReflectionClass;
use ReflectionMethod;

class DatasetKitProvider
{
   use InterracWithModel;

   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }


   public function genarateFileName(): string
   {
      return $this->factory->getDatasetName() . '.php';
   }

   public function genarateClassContent(): void
   {
      if ($this->datasetExists()) {
         $this->factory->command->error('Dataset ' . $this->factory->getDatasetName() . ' already exists <fg=red> [skipped]</>');
         return;
      }

      $uses = $this->useStatments($this->relations($this->factory->entity));

      $for = $this->buildFor($this->relations($this->factory->entity));

      $limit = config('3kjos-command.tests.dataset.create_many_limit', 5);
      $content = <<< DESCRIBE
         <?php
         
         {$uses}
         
         dataset('created {$this->factory->getNameSingularLower()}', [
            fn () => {$this->factory->getModelName()}::factory()
               {$for}
               ->createOne(),
         ]);

         dataset('created {$this->factory->getNamePluralLower()}', [
            fn () => {$this->factory->getModelName()}::factory()
               {$for}
               ->createMany({$limit}),
         ]);

         dataset('guest {$this->factory->getNameSingularLower()}', [
            fn () => {$this->factory->getModelName()}::factory()
               {$for}
               ->makeOne(),
         ]);

      DESCRIBE;
      file_put_contents($this->factory->path, $content);
      exec("./vendor/bin/pint {$this->factory->path}", $output, $status);
   }


   private function datasetExists(): bool
   {
      return file_exists($this->factory->path . '/' . $this->genarateFileName());
   }

   private function buildFor(array $relations): string
   {
      $relation_ = [];
      foreach ($relations as $relation) {
         $medel = NameHelper::nameSingular($relation, NameArgument::Studly);
         $relation_[] = "->for({$medel}::factory())";
      }
      return implode('', $relation_);
   }

   private function guessRelationMethods($modelClass): array
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
