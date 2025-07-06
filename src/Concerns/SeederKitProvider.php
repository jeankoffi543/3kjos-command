<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Enums\NameArgument;

class SeederKitProvider
{
   use InterracWithModel;


   public static function build(): void
   {
      $name = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Studly);
      $namespace = self::getNamespace(self::$entity->getName(), 'modelsPath');
      $limit = config('3kjos-command.seeders.create_many_limit', 10);
      $content = <<< DESCRIBE
      <?php

         namespace Database\Seeders;
         use Illuminate\Database\Seeder;
         use {$namespace};
         class {$name}Seeder extends Seeder
         {
            public function run(): void
            {
               {$name}::factory()->count({$limit})->create();
            }
         }
      DESCRIBE;
      $path = self::$path;
      file_put_contents($path, $content);
      exec("./vendor/bin/pint {$path}", $output, $status);
   }
}
