<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class ModeltKitProvider
{
   use InterracWithModel;

   public static function newFactory(Entity $entity): string
   {
      $nameStudySingular = NameHelper::nameSingular($entity->getName(), NameArgument::Studly);

      return <<<REQUEST
            protected static function newFactory()
            {
               return {$nameStudySingular}Factory::new();
            }
         REQUEST;
   }


   public static function useStatments(Entity $entity): string
   {
      $path = new Path();
      $nameStudySingular = NameHelper::nameSingular($entity->getName(), NameArgument::Studly);
      $factory = $path->getAllNamspaces()["factoriesPath"] . '\\' . $nameStudySingular . 'Factory';
      return "use Illuminate\Database\Eloquent\Factories\HasFactory
            use Illuminate\Database\Eloquent\Model
            use {$factory}";
   }

   public static function  parseColumns(Entity $entity): ?string
   {
      $columns = [];
      $attributs = $entity->getAttributes();
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
