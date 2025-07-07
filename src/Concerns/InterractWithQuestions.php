<?php

namespace Kjos\Command\Concerns;

use Illuminate\Database\Query\Expression;
use Kjos\Command\Enums\Charset;
use Kjos\Command\Enums\Collation;
use Kjos\Command\Enums\ColumnModifier as ColumnModifierEnum;
use Kjos\Command\Enums\ColumnType as ColumnTypeEnum;
use Kjos\Command\Enums\ColumnIndex as ColumnIndexEnum;
use Kjos\Command\Enums\SpatialSubtype;
use Kjos\Command\Managers\Attribut;
use Kjos\Command\Managers\ColumnIndex;
use Kjos\Command\Managers\ColumnModifier;

trait InterractWithQuestions
{
   private function addMoreQuestion(array $attributs = []): mixed
   {
      $this->command->newLine(2);
      $this->command->info("To add another attribut type <fg=yellow>[add]</> or <fg=yellow>[cancel]</> to cancel current execution.");
      $this->command->info("To exécute type <fg=yellow>[x]</>.");
      $response = $this->command->ask("Enter your response [add/cancel/x]. Default is", 'add');

      if ($response === 'add') {
         return $this->ask($attributs);
      } else if ($response === 'cancel') {
         return null;
      } else if ($response === 'x') {
         if (! $this->hasType($attributs)) {
            return null;
         }
         return $attributs;
      } else {
         $this->command->error("Invalid response. Default is <fg=yellow>[add]</>");
         return $this->addMoreQuestion($attributs);
      }
   }

   private function type($i): ?string
   {
      if ($i == 1) {
         $this->command->info("================================COLUMN TYPES============================================");

         $this->command->table(
            ['Category', 'Types & Descriptions'],
            collect(ColumnTypeEnum::cases())
               ->groupBy(fn($type) => $type->category())
               ->map(function ($types, $category) {
                  $descriptions = $types->map(function ($type) {
                     return "`{$type->value}` – {$type->description()}";
                  })->implode("\n");

                  return [$category, $descriptions];
               })
               ->values()
               ->toArray()
         );
      }


      $type =  $this->command->anticipate(
         'use arrow to select your database field type. Ex: string. Press [Enter] to skip or type /q.',
         ColumnTypeEnum::values(),
         null
      );

      // Cas "passer à la suite"
      if ($this->skip($type)) {
         return null;
      }


      if (!in_array($type, ColumnTypeEnum::values())) {
         $this->command->error("Invalid type: {$type}");
         $i++;
         return $this->type($i);
      }

      return $type;
   }

   private function length(): ?int
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('length'))) {
         $length = $this->stringQuestion('Enter the field length. Ex: 255. Press [Enter] to skip or type /q.', 'int');
         if ($this->columnType === 'string' && $length > 65535) {
            $this->command->error('The field length must be less than 65535');
            return $this->length();
         }
         if ($this->skip($length)) {
            return null;
         }
         return $length;
      }
      return null;
   }

   private function fixed(): ?bool
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('fixed'))) {
         return $this->confirm('Is the field fixed?', false);
      }
      return false;
   }

   private function charset(): ?string
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('charset'))) {

         return $this->parseCharset();
      }
      return null;
   }

   private function total(): ?int
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('total'))) {
         $total = $this->stringQuestion('Enter the field total. Ex: 6. Press [Enter] to skip or type /q.', 'int');
         if ($this->skip($total)) {
            return null;
         }
         return $total;
      }
      return null;
   }

   private function places(): ?int
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('places'))) {
         $places = $this->stringQuestion('Enter the field places. Ex: 2. Press [Enter] to skip or type /q.', 'int');
         if ($this->skip($places)) {
            return null;
         }
         return $places;
      }
      return null;
   }

   private function precision(): ?int
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('precision'))) {
         $precision = $this->stringQuestion('Enter the field precision. Ex: 2. Press [Enter] to skip or type /q.', 'int');
         if ($this->skip($precision)) {
            return null;
         }
         return $precision;
      }
      return null;
   }

   private function enum(): ?array
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('enum'))) {
         $enum = $this->arrayQuestion("Enter the enum field value, separated by commas. Ex: 1,2,3. Press [Enter] to skip or type /q.");
         if ($this->skip($enum)) {
            return [];
         }
         return $enum;
      }
      return null;
   }

   private function subtype(): ?int
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('subtype'))) {
         $subtype = $this->command->ask('Enter the field subtype. Ex: 2. Press [Enter] to skip or type /q.', null);
         if ($this->skip($subtype)) {
            return null;
         }
         if (SpatialSubtype::has($subtype) === false) {
            $this->command->error("Invalid subtype: {$subtype}");
            // Afficher les charsets disponibles sous forme de tableau
            $this->command->info('Available subtype:');
            $this->command->table(
               ['Subtypes', 'Description'],
               collect(SpatialSubtype::cases())->map(fn($case) => [$case->value, $case->description()])->toArray()
            );

            return $this->subtype();
         }
         return $subtype;
      }
      return null;
   }

   private function srid(): ?int
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('srid'))) {
         $srid = $this->stringQuestion('Enter the field srid. Ex: 2. Press [Enter] to skip or type /q.', 'int');
         if ($this->skip($srid)) {
            return null;
         }
         return $srid;
      }
      return null;
   }

   private function dimensions(): ?int
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('dimensions'))) {
         $dimensions = $this->stringQuestion('Enter the field dimensions. Ex: 2. Ex: 2. Press [Enter] to skip or type /q.', 'int');
         if ($this->skip($dimensions)) {
            return null;
         }
         return $dimensions;
      }
      return null;
   }

   private function name(): ?string
   {
      $name = $this->command->ask('Enter the field name. Ex: email <fg=red>[required]</>');
      if (empty($name) || ! kjos_is_string($name)) {
         $this->command->error("Invalid name: '{$name}', it must be a string");
         return $this->name();
      }
      return $name;
   }

   /**
    * Checks if any of the given attributes is a ColumnType.
    *
    * @param array<\Kjos\Command\Managers\Attribut> $attributes
    * @return bool
    */
   private function hasType(array $attributes = []): bool
   {
      foreach ($attributes as $attribute) {
         $modifiers = $attribute->getModifiers();
         $indexes = $attribute->getIndexes();
         foreach ($modifiers as $modifier) {
            if (ColumnModifierEnum::hasStandalone($modifier->toArray())) return true;
         }

         foreach ($indexes as $index) {
            if (ColumnIndexEnum::hasStandalone($index->toArray()['method'])) return true;
         }

         if (ColumnTypeEnum::has($attribute->getColumnType()->getType())) {
            return true;
         }
      }

      return false;
   }

   private function modifiers(array $modifiers = [], int $i = 1): ?array
   {
      $columnModifier = new ColumnModifier();

      if ($i === 1) {
         $this->command->info("================================COLUMN MODIFIERS============================================");
         $this->command->table(
            ['Category', 'Modificateur', 'Description'],
            ColumnModifierEnum::tableRows()
         );
      }

      $modifier = $this->command->anticipate(
         'Use arrow to add a modifier (Ex: <fg=green>nullable</>). Press [Enter] to skip or type "/q".',
         ColumnModifierEnum::values(),
         null
      );
      
      // Cas "passer à la suite"
      if ($this->skip($modifier)) {
         return array_filter($modifiers);
      }

      // Vérification de validité
      if (!in_array($modifier, ColumnModifierEnum::values())) {
         $this->command->error("Invalid modifier: {$modifier}");
         return $this->modifiers($modifiers, ++$i);
      }

      // Ajouter le modificateur
      $modifiers[$modifier] = $this->parseModifiers($columnModifier, $modifier);
      // Nouvelle itération ?
      return $this->modifiers($modifiers, ++$i);

      // return array_filter($modifiers);
   }


   private function parseModifiers(ColumnModifier $columnModifier, mixed $modifier): mixed
   {
      $options = ColumnModifierEnum::tryFrom($modifier)->options()['value'] ?? null;
      $m = match ($options) {
         'scalar' => $columnModifier->setScalar($this->parseScalar()),
         'scalar|from|to' => $columnModifier->setScalar($this->parseScalar($options, $modifier)),
         'boolean' => $columnModifier->setBoolean($this->parseBoolean()),
         'charset' => $columnModifier->setCharset($this->parseCharset()),
         'collation' => $columnModifier->setCollation($this->parseCollation()),
         'expression' => $columnModifier->setExpression($this->parseExpression()),
         'expression|nullable' => $columnModifier->setExpression($this->parseExpression($options, $modifier)),
         null => $columnModifier->setNoValue($modifier),
         default => $this->command->error("Invalid modifier: {$modifier}"),
      };

      return $m;
   }

   private function parseCharset(): ?string
   {
      $charset = $this->command->ask('Enter the field charset. Ex: utf8 or press enter to skip', null);
      if (empty($charset)) return null;
      if (Charset::has($charset) === false) {
         $this->command->error("Invalid charset: {$charset}");
         // Afficher les charsets disponibles sous forme de tableau
         $this->command->info('Available charsets:');
         $this->command->table(
            ['Charset', 'Description'],
            collect(Charset::cases())->map(fn($case) => [$case->value, $case->description()])->toArray()
         );

         return $this->parseCharset();
      }
      return $charset;
   }

   private function parseCollation(): ?string
   {
      $collation = $this->command->ask('Enter the field collation. Ex: UTF8MB4_UNICODE_CI or press enter to skip', null);
      if (empty($collation)) return null;
      if (Collation::has($collation) === false) {
         $this->command->error("Invalid collation: {$collation}");
         // Afficher les charsets disponibles sous forme de tableau
         $this->command->info('Available collation:');
         $this->command->table(
            ['Collation', 'Description'],
            collect(Collation::cases())->map(fn($case) => [$case->value, $case->description()])->toArray()
         );

         return $this->parseCollation();
      }
      return $collation;
   }

   private function parseScalar(?string $options = null, ?string $modifier = null): ?string
   {
      $from = $to = '';
      $options = array_filter(explode('|', $options));
      $response = '';
      if (empty($options)) {
         $response = $this->command->ask('Enter the field value. Or press enter to skip', null);
      } else {
         foreach ($options as $option) {
            if ($option === 'from' || $option === 'to') {
               $response =  $this->command->ask("{$modifier} <fg=red>[{$option}]</> value. Or press enter to skip", null);

               if ($option === 'from') {
                  $from = $response;
               }
               if ($option === 'to') {
                  $to = $response;
               }

               if ($this->skip($response)) {
                  $from = $to = '';
                  return null;
               }
            }
         }
         $response = $from . ',' . $to;
      }

      return $response;
   }

   private function parseBoolean(): ?bool
   {
      return $this->confirm('Set the field value to true or false?');
   }

   private function parseExpression(?string $options = null, ?string $modifier = null): Expression | string | null
   {
      $options = array_filter(explode('|', $options));
      if (empty($options)) {
         $raw = $this->command->ask("Enter the field expression or enter <fg=red>[empty]</> for no expression. Ex: now() or press enter to skip", null);
         if ($this->skip($raw)) {
            return null;
         }
         if ($raw === 'empty') {
            return $modifier;
         }
         return $raw;
      } else {
         $raw = $this->command->ask('Enter the field expression. Ex: now() or press enter to skip', null);

         if ($raw === null || trim($raw) === '' || $this->skip($raw)) {
            return null;
         }

         // Optionnel : valider que l'expression semble correcte
         if (! preg_match('/^\w+\(.*\)$/', $raw)) {
            $this->command->error("The value must look like a valid SQL expression (e.g., now(), JSON_ARRAY(), etc.).");
            return $this->parseExpression(); // ici on retourne la récursion
         }

         return $raw;
      }
   }

   private function indexes(array $options = [], int $i): array
   {
      $columnIndex = new ColumnIndex();

      if ($i == 1) {
         $this->command->info("================================COLUMN INDEXES============================================");
         $this->command->table(
            ['Category', 'Index', 'Description'],
            ColumnIndexEnum::tableRows()
         );
      }

      $value =  $this->command->anticipate(
         'use arrow add a index to the field. Ex: <fg=green>unique</>. Press [Enter] to skip or type /q.',
         ColumnIndexEnum::values(),
         null
      );
      // Cas "passer à la suite"
      if ($this->skip($value)) {
         return $options;
      }


      if (! in_array($value, ColumnIndexEnum::values())) {
         $this->command->error("Invalid indexes: {$value}");
         $i++;
         return $this->indexes($options, $i);
      }
      $i++;
      $columnIndex->setMethod($value);
      $columnIndex->setOptions($this->parseIndexes($value));
      $options[] = $columnIndex;

      return $this->indexes($options, $i);
      // return $options;
   }


   private function parseIndexes(mixed $value): array
   {
      $columnIndexEnum = ColumnIndexEnum::tryFrom($value);
      if ($columnIndexEnum === null) {
         return $this->command->error("Invalid indexes: {$value}");
      }
      $options = $columnIndexEnum->options();
      return $this->parseIndexesOptions($options['options'], $value);
   }

   private function parseIndexesOptions(array $options = [], string $value): array
   {
      $values = [];
      foreach ($options as $key => $option) {
         $op = match ($option) {
            'array' => function () use ($key, $value) {
               $response = $this->arrayQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value, separated by commas. Ex: value1, value2, value3. Press [Enter] to skip or type /q. <fg=red>required</>"
               );

               if ($this->skip($response)) {
                  return []; // L'utilisateur a sauté
               }

               return $response; // Réponse valide
            },
            'array|string' => function () use ($key, $value) {
               $response = $this->arrayQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value, For array: separated by commas or string one value. Ex: value1, value2, value3. Press [Enter] to skip or type /q. <fg=red>required</>",
                  'string'
               );

               if ($this->skip($response)) {
                  return []; // L'utilisateur a sauté
               }

               return $response; // Réponse valide
            },

            '?|array|string' => function () use ($key, $value) {
               $response = $this->arrayQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value, For array: separated by commas or string one value. Ex: value1, value2, value3. Press [Enter] fo null or type /q for skip. <fg=red>required</>",
                  'string'
               );

               if ($response === '/q') {
                  return [];
               }
               if (empty($response)) {
                  return $value;
               }

               return $response; // Réponse valide
            },

            'string' => function () use ($key, $value, $values) {
               $response = $this->stringQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value. Press [Enter] to skip or type /q. <fg=red>required</>",
                  'string'
               );
               if (! $this->filterForeign($value, $values, $key)) return null;

               if ($this->skip($response)) {
                  return null; // skip → on retourne null
               }

               return $response;
            },

            '?string' => function () use ($key, $value) {
               $response = $this->stringQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value. Press [Enter] to skip or type /q.",
                  '?string'
               );

               if ($this->skip($response)) {
                  return null;
               }

               return $response;
            },
            default => null
         };


         if (! empty($op = $op())) {
            $values[$key] = $op;
         }
      }
      return array_filter($values);
   }

   private function arrayQuestion(string $text = "Enter the field value, separated by commas. Ex: value1, value2, value3 or press enter to skip", string $withType = ''): array|string
   {
      $value = $this->command->ask($text, null);
      if ($value === null) return [];

      $enum = array_map('trim', explode(',', trim($value)));

      if (!empty($withType) && $withType === 'string') {
         if (count($enum) === 1) {
            return $enum[0]; // retourne une string
         }

         if (empty($enum)) {
            $this->command->error("Invalid input: empty value.");
            return $this->arrayQuestion($text, $withType);
         }

         // erreur : multiple valeurs mais type = string
         $this->command->error("Invalid: multiple values entered, expected only one string. Got: " . implode(', ', $enum));
         return $this->arrayQuestion($text, $withType);
      }

      return $enum; // retourne array
   }


   private function stringQuestion(string $text = '', string $type = 'string'): ?string
   {
      $value = $this->command->ask($text, null);
      if ($value === null) return null;
      $isType = match ($type) {
         'string' =>  is_string($value),
         '?string' =>  is_string($value) || $value === null,
         'int' => is_numeric($value),
         '?int' => is_int($value) || $value === null,
         'all' => true,
         default => $this->command->error("Invalid type: {$type}"),
      };
      if (!$isType) {
         $this->command->error("Invalid: {$value}, it must be a {$type}");
         return $this->stringQuestion($text, $type);
      }
      return trim($value);
   }

   private function confirm($text = 'Add another indexes?', bool $default = true): bool
   {
      return boolval($this->command->confirm($text, $default));
   }

   private function skip(mixed $value): bool
   {
      if (is_array($value)) {
         if (empty($value) || current($value) === '/q') {
            $this->command->warn("<fg=red>[Skipped]</>");
            return true;
         }
      } else {
         if (empty($value) || in_array(strtolower($value), ['/q'])) {
            $this->command->warn("<fg=red>[Skipped]</>");
            return true;
         }
      }
      return false;
   }

   private function filterForeign(?string $value = null, array $values, string $key): ?string
   {
      if ($value === ColumnIndexEnum::Foreign->value) {
         // check if column exists
         $foreign = data_get($values, $key);
         if ($foreign !== null) {
            $columns = data_get($foreign, 'columns');
            $on = data_get($foreign, 'on');
            $references = data_get($foreign, 'references');

            if ($key === 'references' && empty($columns)) {
               $this->command->error("columns is required. <fg=red>[skipped]</>");
               return null;
            } else if ($key === 'on' &&  (empty($columns) ||  empty($references))) {
               $this->command->error("Columns, or references is required. <fg=red>[skipped]</>");
               return null;
            }
         }
      }
      return $value;
   }
}
