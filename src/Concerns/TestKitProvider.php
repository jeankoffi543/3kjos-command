<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Enums\NameArgument;

class TestKitProvider
{
   use InterracWithModel;


   public static function build(): void
   {
      $structure = self::makeStructure()::attributeToString();
      $namePluralLower = NameHelper::namePlural(self::$entity->getName(), NameArgument::Lower);
      $peol = PHP_EOL;
      $structure = "\${$namePluralLower}Structure = [{$peol}{$structure}{$peol}];";
      $createDescribe = self::createDescribe();
      $updateDescribe = self::updateDescribe();
      $detailDescribe = self::detailDescribe();
      $deleteDescribe = self::deleteDescribe();
      $listingDescribe = self::listingDescribe();

      $nameStudy = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Studly);

      $content = <<< DESCRIBE
      <?php

        /**
       * this tests content are auto generated and is a template. please update it to your needs
       * Test for : {$nameStudy}
       */

      {$structure}
      
      {$createDescribe}

      {$updateDescribe}
      
      {$detailDescribe}
      
      {$deleteDescribe}
      
      {$listingDescribe}
      
      
      DESCRIBE;
      $path = self::$path;
      file_put_contents($path, $content);
      exec("./vendor/bin/pint {$path}", $output, $status);
   }

   private static function createDescribe(): string
   {
      $nameStudy = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Studly);
      $name = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $routeName = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $guestData = "\$guest{$nameStudy}";

      $content = <<< DESCRIBE
      describe('Should Store {$name}', function () {
         it('should store {$name}', function ({$guestData}) {

            {$guestData} = {$guestData}->toArray();

            \$this->post('/api/{$routeName}', {$guestData})
               ->assertCreated();
         })->with('guest {$name}');

         it('should have correct {$name}', function ({$guestData}) {
            \$this->post('/api/{$routeName}/', {$guestData}->toArray())
               ->assertBadRequest();
         })->with('guest {$name}');
      });
      DESCRIBE;
      return $content;
   }

   private static function updateDescribe(): string
   {
      $nameStudy = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Studly);
      $name = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $routeName = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $guestData = "\$guest{$nameStudy}";
      $createdData = "\$created{$nameStudy}";

      $content = <<< DESCRIBE
      describe('Should update {$name}', function () {
         it('Update {$name}', function ({$guestData}, {$createdData}) {
            {$guestData} = {$guestData}();
            {$createdData} = {$createdData}();

            {$guestData} = {$guestData}->toArray();

            \$this->put('/api/{$routeName}/{$createdData}->id', {$guestData})
               ->assertOk();
         })->with('guest {$name}')->with('created {$name}');

         it('should update with wrong {$name}', function ({$guestData}, {$createdData}) {
            {$guestData} = {$guestData}();
            {$createdData} = {$createdData}();

            {$guestData} = {$guestData}->toArray();

            \$this->put('/api/{$routeName}/{$createdData}->id', {$guestData})
               ->assertBadRequest();
         })->with('guest {$name}');
      });
      DESCRIBE;
      return $content;
   }

   private static function listingDescribe(): string
   {
      $nameStudys = NameHelper::namePlural(self::$entity->getName(), NameArgument::Studly);
      $name = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $names = NameHelper::namePlural(self::$entity->getName(), NameArgument::Lower);
      $routeName = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $createdDatas = "\$created{$nameStudys}";
      $structureName = "{$name}Structure";

      $content = <<< DESCRIBE
      describe('Should List {$name}', function () use (\${$structureName}) {
         it('List {$name}', function ({$createdDatas}) use (\${$structureName}) {

            \$this->get('/api/{$routeName}')
               ->assertOk()
               ->assertJsonStructure(['data' => [
               '*' => \${$structureName},
            ]]);
            
         })->with('created {$names}');

             it('List all {$name} - paginate', function ({$createdDatas}) use (\${$structureName}) {
               // page 1
               \$response = \$this->get('/api/{$routeName}?limit=6&page=1')
                  ->assertOk();

               // Check if the {$name} is created
               \$response->assertJsonStructure(['data' => [
                     '*' => \${$structureName},
               ]]);

               // Count created {$name} to be (6)
               json_decode(\$response->getContent(), true);

               // page 2
               \$response = \$this->get('/api/{$routeName}?limit=6&page=2')
                  ->assertOk();

               // Check if the {$name} is created
               \$response->assertJsonStructure(['data' => [
                     '*' => \${$structureName},
               ]]);

               // Count created {$name} to be (6)
               json_decode(\$response->getContent(), true);
         })->with('created {$names}');
      });
      
      DESCRIBE;
      return $content;
   }

   private static function detailDescribe(): string
   {
      $nameStudys = NameHelper::namePlural(self::$entity->getName(), NameArgument::Studly);
      $name = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $names = NameHelper::namePlural(self::$entity->getName(), NameArgument::Lower);
      $routeName = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $createdDatas = "\$created{$nameStudys}";

      $content = <<< DESCRIBE
      describe('Should get detail {$name}', function () {
         it('Get {$name}', function ({$createdDatas}) {
            \$this->get('/api/{$routeName}/{$createdDatas}[0]->id')
               ->assertOk();
         })->with('created {$names}');
      });
      DESCRIBE;
      return $content;
   }


   private static function deleteDescribe(): string
   {
      $nameStudys = NameHelper::namePlural(self::$entity->getName(), NameArgument::Studly);
      $name = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $names = NameHelper::namePlural(self::$entity->getName(), NameArgument::Lower);
      $routeName = NameHelper::nameSingular(self::$entity->getName(), NameArgument::Lower);
      $createdDatas = "\$created{$nameStudys}";

      $content = <<< DESCRIBE
      describe('Should Delete {$name}', function () {
         it('delete {$name}', function ({$createdDatas}) {
            \$this->delete('/api/{$routeName}/{$createdDatas}[0]->id')
               ->assertOk();
         })->with('created {$names}');
      });
      DESCRIBE;
      return $content;
   }

   private static function makeStructure(): static
   {
      /**
       * @var \Kjos\Command\Managers\Attribut $attribute
       */
      self::$attributNames = collect(self::$entity->getAttributes())
         ->map(function ($attribute) {
            $a = $attribute->getName();
            return "'{$a}'";
         })->all();
      return new static();
   }

   private static function attributeToString(): string
   {
      return implode(PHP_EOL . ',', self::$attributNames) . ',';
   }
}
