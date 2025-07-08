<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Concerns\Helpers\NameHelper;
use Kjos\Command\Managers\Path;
use Kjos\Command\Enums\ColumnType;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Factories\BuilderFactory;
use Kjos\Command\Managers\Entity;

class FactoryKitProvider
{
   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }

   public function definition(): string
   {
      $factories = [];
      $attributes = $this->factory->entity->getAttributes();

      /**
       * @var \Kjos\Command\Managers\Attribut $attribute
       */
      foreach ($attributes as $attribute) {
         $faker = ColumnType::factory($attribute);
         $factories[] = "'{$attribute->getName()}' => {$faker}"; ;
      }
      $factories = PHP_EOL . implode(',' . PHP_EOL, $factories) . PHP_EOL;

      return <<<FACTORY
              /**
                * Define the model's default state.
               *
               * @return array<string, mixed>
               */
               public function definition(): array
               {
                  return [{$factories}];
               }
      FACTORY;
   }

   public function generateRelationMethod(string $column, string $on, string $reference): string
   {
      $relationModelName = NameHelper::nameSingular($on, NameArgument::Studly);
      $relationName = NameHelper::nameSingular($on, NameArgument::Lower);
      return "public function {$relationName}()
              {
               return \$this->belongsTo({$relationModelName}::class, '{$column}', '{$reference}');
              }
            ";
   }

   public function useStatments(): string
   {
      $path = new Path();
      $nameStudySingular = NameHelper::nameSingular($this->factory->entity->getName(), NameArgument::Studly);
      $model = $path->getAllNamspaces()["modelsPath"] . '\\' . $nameStudySingular;
      return "use Illuminate\Database\Eloquent\Factories\Factory
            use {$model}";
   }
}
