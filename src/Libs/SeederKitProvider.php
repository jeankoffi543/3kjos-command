<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Concerns\InterracWithModel;
use Kjos\Command\Factories\BuilderFactory;

class SeederKitProvider
{
   use InterracWithModel;

   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }


   public function build(): void
   {
      $namespace = $this->getNamespace($this->factory->entity->getName(), 'modelsPath');
      $limit = config('3kjos-command.seeders.create_many_limit', 10);
      $content = <<< DESCRIBE
      <?php

         namespace Database\Seeders;
         use Illuminate\Database\Seeder;
         use {$namespace};
         class {$this->factory->getSeederName()} extends Seeder
         {
            public function run(): void
            {
               {$this->factory->getModelName()}::factory()->count({$limit})->create();
            }
         }
      DESCRIBE;
      file_put_contents($this->factory->path, $content);
      exec("./vendor/bin/pint {$this->factory->path}", $output, $status);
   }
}
