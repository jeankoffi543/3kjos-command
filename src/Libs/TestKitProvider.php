<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Concerns\InterracWithModel;
use Kjos\Command\Factories\BuilderFactory;

class TestKitProvider
{
   use InterracWithModel;

   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }

   public function build(): void
   {
      $structure = $this->makeStructure()->attributeToString();
      $peol = PHP_EOL;
      $structure = "\${$this->factory->getNamePluralLower()}Structure = [{$peol}{$structure}{$peol}];";
      $createDescribe = $this->createDescribe();
      $updateDescribe = $this->updateDescribe();
      $detailDescribe = $this->detailDescribe();
      $deleteDescribe = $this->deleteDescribe();
      $listingDescribe = $this->listingDescribe();

      $content = <<< DESCRIBE
      <?php

        /**
       * this tests content are auto generated and is a template. please update it to your needs
       * Test for : {$this->factory->getModelName()}
       */

      {$structure}
      
      {$createDescribe}

      {$updateDescribe}
      
      {$detailDescribe}
      
      {$deleteDescribe}
      
      {$listingDescribe}
      
      
      DESCRIBE;
      file_put_contents($this->factory->path, $content);
      exec("./vendor/bin/pint {$this->factory->path}", $output, $status);
   }

   private function createDescribe(): string
   {
      $content = <<< DESCRIBE
      describe('Should Store {$this->factory->getNameSingularLower()}', function () {
         it('should store {$this->factory->getNameSingularLower()}', function (\$guest{$this->factory->getModelName()}) {

            \$guest{$this->factory->getModelName()} = \$guest{$this->factory->getModelName()}->toArray();

            \$this->post('/api/{$this->factory->getRouteName()}', \$guest{$this->factory->getModelName()})
               ->assertCreated();
         })->with('guest {$this->factory->getNameSingularLower()}');

         it('should have correct {$this->factory->getNameSingularLower()}', function (\$guest{$this->factory->getModelName()}) {
            \$this->post('/api/{$this->factory->getRouteName()}/', \$guest{$this->factory->getModelName()}->toArray())
               ->assertBadRequest();
         })->with('guest {$this->factory->getNameSingularLower()}');
      });
      DESCRIBE;
      return $content;
   }

   private function updateDescribe(): string
   {
      $id = $this->factory->entity->getPrimaryKey() ? $this->factory->entity->getPrimaryKey() : 'id';

      $content = <<< DESCRIBE
      describe('Should update {$this->factory->getNameSingularLower()}', function () {
         it('Update {$this->factory->getNameSingularLower()}', function (\$guest{$this->factory->getModelName()}, \$created{$this->factory->getModelName()}) {
            \$guest{$this->factory->getModelName()} = \$guest{$this->factory->getModelName()}();
            \$created{$this->factory->getModelName()} = \$created{$this->factory->getModelName()}();

            \$guest{$this->factory->getModelName()} = \$guest{$this->factory->getModelName()}->toArray();

            \$this->put('/api/{$this->factory->getRouteName()}/\$created{$this->factory->getModelName()}->{$id}', \$guest{$this->factory->getModelName()})
               ->assertOk();
         })->with('guest {$this->factory->getNameSingularLower()}')->with('created {$this->factory->getNameSingularLower()}');

         it('should update with wrong {$this->factory->getNameSingularLower()}', function (\$guest{$this->factory->getModelName()}, \$created{$this->factory->getModelName()}) {
            \$guest{$this->factory->getModelName()} = \$guest{$this->factory->getModelName()}();
            \$created{$this->factory->getModelName()} = \$created{$this->factory->getModelName()}();

            \$guest{$this->factory->getModelName()} = \$guest{$this->factory->getModelName()}->toArray();

            \$this->put('/api/{$this->factory->getRouteName()}/\$created{$this->factory->getModelName()}->{$id}', \$guest{$this->factory->getModelName()})
               ->assertBadRequest();
         })->with('guest {$this->factory->getNameSingularLower()}');
      });
      DESCRIBE;
      return $content;
   }

   private function listingDescribe(): string
   {
      $content = <<< DESCRIBE
            describe('Should List {$this->factory->getNameSingularLower()}', function () use (\${$this->factory->getNamePluralLower()}Structure) {
               it('List {$this->factory->getNameSingularLower()}', function (\$created{$this->factory->getModelName()}) use (\${$this->factory->getNamePluralLower()}Structure) {

                  \$this->get('/api/{$this->factory->getRouteName()}')
                     ->assertOk()
                     ->assertJsonStructure(['data' => [
                     '*' => \${$this->factory->getNamePluralLower()}Structure,
                  ]]);
                  
               })->with('created {$this->factory->getNamePluralLower()}');
            });


            describe('Listing all {$this->factory->getNameSingularLower()} - paginate', function () use (\${$this->factory->getNamePluralLower()}Structure) {

               it('List all {$this->factory->getNameSingularLower()} - paginate', function (\$created{$this->factory->getModelName()}) use (\${$this->factory->getNamePluralLower()}Structure) {
               
                  // page 1
                  \$response = \$this->get('/api/{$this->factory->getRouteName()}?limit=6&page=1')
                     ->assertOk();

                  // Check if the {$this->factory->getNameSingularLower()} is created
                  \$response->assertJsonStructure(['data' => [
                        '*' => \${$this->factory->getNamePluralLower()}Structure,
                  ]]);

                  // Count created {$this->factory->getNameSingularLower()} to be (6)
                  json_decode(\$response->getContent(), true);

                  // page 2
                  \$response = \$this->get('/api/{$this->factory->getRouteName()}?limit=6&page=2')
                     ->assertOk();

                  // Check if the {$this->factory->getNameSingularLower()} is created
                  \$response->assertJsonStructure(['data' => [
                        '*' => \${$this->factory->getNamePluralLower()}Structure,
                  ]]);

                  // Count created {$this->factory->getNameSingularLower()} to be (6)
                  json_decode(\$response->getContent(), true);
               })->with('created {$this->factory->getNamePluralLower()}');
         });
      
      DESCRIBE;
      return $content;
   }

   private function detailDescribe(): string
   {
      $id = $this->factory->entity->getPrimaryKey() ? $this->factory->entity->getPrimaryKey() : 'id';
      $content = <<< DESCRIBE
      describe('Should get detail {$this->factory->getNameSingularLower()}', function () {
         it('Get {$this->factory->getNameSingularLower()}', function (\$created{$this->factory->getModelName()}) {
            \$this->get('/api/{$this->factory->getRouteName()}/\$created{$this->factory->getModelName()}[0]->{$id}')
               ->assertOk();
         })->with('created {$this->factory->getNamePluralLower()}');
      });
      DESCRIBE;
      return $content;
   }


   private function deleteDescribe(): string
   {
      $id = $this->factory->entity->getPrimaryKey() ? $this->factory->entity->getPrimaryKey() : 'id';
      $content = <<< DESCRIBE
      describe('Should Delete {$this->factory->getNameSingularLower()}', function () {
         it('delete {$this->factory->getNameSingularLower()}', function (\$created{$this->factory->getModelName()}) {
            \$this->delete('/api/{$this->factory->getRouteName()}/\$created{$this->factory->getModelName()}[0]->{$id}')
               ->assertOk();
         })->with('created {$this->factory->getNamePluralLower()}');
      });
      DESCRIBE;
      return $content;
   }

   private function makeStructure(): static
   {
      /**
       * @var \Kjos\Command\Managers\Attribut $attribute
       */
      $this->attributNames = collect($this->factory->entity->getAttributes())
         ->map(function ($attribute) {
            $a = $attribute->getName();
            return "'{$a}'";
         })->all();
      return $this;
   }

   private function attributeToString(): string
   {
      return implode(PHP_EOL . ',', $this->attributNames) . ',';
   }
}
