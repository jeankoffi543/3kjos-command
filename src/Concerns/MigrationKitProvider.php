<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Enums\ColumnIndex;
use Kjos\Command\Enums\ColumnModifier;
use Kjos\Command\Enums\ColumnType;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class MigrationKitProvider
{
   protected static string $tableName = '';
   protected static string $fileName  = '';
   protected static Entity $entity;
   protected static string $path;
   protected static KjosMakeRouteApiCommand $command;
   private static array $schema = [];

//    private const NONCHAINABLE = [
//     'foreign',          // à utiliser séparément
//     'renameIndex',
//     'dropPrimary',
//     'dropUnique',
//     'dropIndex',
//     'dropFullText',
//     'dropSpatialIndex',
//     'dropForeign',
// ];

   public static function init(Entity $entity, string $path, KjosMakeRouteApiCommand $command)
   {
      self::$entity = $entity;
      self::$tableName = NameHelper::namePlural($entity->getName(), NameArgument::Lower);
      self::$fileName = NameHelper::nameSingular($entity->getName(), NameArgument::Lower);
      self::$command = $command;
      self::$path = $path;
   }
   public static function genarateFileName(): string
   {
      return now()->format('Y_m_d_His') . '_create_' . self::$tableName . '.php';
   }

   public static function genarateClassContent(): void
   {
      if (self::migrationExists()) {
         self::$command->error('Migration already exists <fg=red> [skipped]</>');
         return;
      }
      $schema = self::schemaMethods()::build();

      $tableName = self::$tableName;
      $content = <<< DESCRIBE
         <?php
         use Illuminate\Database\Migrations\Migration;
         use Illuminate\Database\Schema\Blueprint;
         use Illuminate\Support\Facades\Schema;

         return new class extends Migration
         {
            /**
             * Run the migrations.
            *
            * @return void
            */
            public function up(): void
            {
               Schema::create('{$tableName}', function (Blueprint \$table) {
                     \$table->id();
                     {$schema}
                     \$table->timestamps();
               });
            }

            /**
             * Reverse the migrations.
            *
            * @return void
            */
            public function down(): void
            {
               Schema::dropIfExists('{$tableName}');
            }
         };
      DESCRIBE;

      $path = self::$path . '/' . self::genarateFileName();
      file_put_contents($path, $content);
      exec("./vendor/bin/pint {$path}", $output, $status);
   }

   private static function getSchema(): string
   {
      return implode(PHP_EOL, self::schemaToArray());
   }

   private static function schemaToArray(): array
   {
      $schemas = [];

      foreach (self::$entity->getAttributes() as $attribute) {
         $column = ColumnType::schema($attribute);       // ->unsignedBigInteger('user_id')
         $modifier = ColumnModifier::schema($attribute); // ->nullable()
         $index = ColumnIndex::schema($attribute);       // ->foreign('user_id')->references('id')->on('users')

         // Concaténer type + modificateur
         $baseSchema = trim("{$column}{$modifier}");

         // Séparer la contrainte foreign si elle existe
         if (preg_match("/->(foreign|constrained)\(/", $index)) {
            $schemas[] = "\$table{$baseSchema};";
            $schemas[] = "\$table{$index};";
         } else {
            $fullSchema = trim("{$baseSchema}{$index}");
            if (!empty($fullSchema) && !in_array($fullSchema, $schemas)) {
               $schemas[] = "\$table{$fullSchema};";
            }
         }
      }

      return $schemas;
   }

   private static function schemaMethods(): static
   {
      self::$schema = collect(self::schemaToArray())->mapWithKeys(function ($schema, $key) {
         $s = preg_split('/->/', preg_replace('/\$table->/', '', trim($schema, ';')));
         return [
            "line-{$key}" => self::filterSchemaLine(self::schemoToDetails($s)),
         ];
      })->toArray();

        return new static();
   }

   private static function filterSchemaLine(array $line): array
   {
      $seen = [];

      return collect($line)->filter(function ($item) use (&$seen) {
         if (empty($item) || in_array($item['name'], $seen, true)) {
            return false;
         }

         $seen[] = $item['name'];
         return true;
      })->toArray();
   }

   private static function schemoToDetails(array $schema): array
   {
      return collect($schema)->mapWithKeys(function ($item, $key) {
         preg_match('/(\w+)(\()(.*)(\))/', $item, $matches);

         $value_ = trim($matches[3], "'") === trim($matches[1], "'") ? '' : $matches[3];
         $methods_ = "{$matches[1]}({$value_})";

         return ["method-{$key}" => [
            'name' => $matches[1],
            'value' => $matches[3],
            'method' => $methods_,
         ]];
      })->toArray();
   }

   private static function build(): string
   {
      $schema = collect(self::$schema)->map(function ($items) {
         $methods = collect($items)->map(fn($i) => $i['method'])->toArray();
         $methods = implode('->', $methods);
         return "\$table->{$methods};";
      })->toArray();

      return implode(PHP_EOL, $schema);
   }

   private static function migrationExists(): bool
   {
      return file_exists(self::$path . '/' . self::genarateFileName());
   }
}
