<?php

namespace Kjos\Command\Libs;

use Kjos\Command\Enums\ColumnIndex;
use Kjos\Command\Enums\ColumnModifier;
use Kjos\Command\Enums\ColumnType;
use Kjos\Command\Factories\BuilderFactory;

class MigrationKitProvider
{
   protected static string $fileName  = '';
   private array $schema = [];
   private BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
   }

   public function genarateFileName(): string
   {
      return now()->format('Y_m_d_His') . '_create_' . $this->factory->getTable() . '.php';
   }

   public function genarateClassContent(): void
   {
      if ($this->migrationExists()) {
         $this->factory->command->error('Migration already exists <fg=red> [skipped]</>');
         return;
      }
      $schema = $this->schemaMethods()::build();

      $tableName = $this->factory->getTable();
      $tableId = $this->factory->entity->getPrimaryKey() ? "'{$this->factory->entity->getPrimaryKey()}'" : '';
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
                     \$table->id({$tableId});
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

      $path = $this->factory->path . '/' . $this->genarateFileName();
      file_put_contents($path, $content);
      exec("./vendor/bin/pint {$path}", $output, $status);
   }

   private function getSchema(): string
   {
      return implode(PHP_EOL, $this->schemaToArray());
   }

   private function schemaToArray(): array
   {
      $schemas = [];

      foreach ($this->factory->entity->getAttributes() as $attribute) {
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

   private function schemaMethods(): static
   {
      $this->schema = collect($this->schemaToArray())->mapWithKeys(function ($schema, $key) {
         $s = preg_split('/->/', preg_replace('/\$table->/', '', trim($schema, ';')));
         return [
            "line-{$key}" => $this->filterSchemaLine($this->schemoToDetails($s)),
         ];
      })->toArray();

        return $this;
   }

   private function filterSchemaLine(array $line): array
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

   private function schemoToDetails(array $schema): array
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

   private function build(): string
   {
      $schema = collect($this->schema)->map(function ($items) {
         $methods = collect($items)->map(fn($i) => $i['method'])->toArray();
         $methods = implode('->', $methods);
         return "\$table->{$methods};";
      })->toArray();

      return implode(PHP_EOL, $schema);
   }

   private function migrationExists(): bool
   {
      return file_exists($this->factory->path . '/' . $this->genarateFileName());
   }
}
