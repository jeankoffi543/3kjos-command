<?php

namespace Kjos\Command\Enums;

use Kjos\Command\Managers\Entity;
use Illuminate\Support\Str;
use Kjos\Command\Managers\Attribut;

enum ColumnIndex: string
{
   use Values;

   case Primary = 'primary';
   case Unique = 'unique';
   case Index = 'index';
   case FullText = 'fullText';
   case SpatialIndex = 'spatialIndex';

      // Foreign keys
   case Foreign = 'foreign';
   case ForeignId = 'foreignId';
   case Constrained = 'constrained';
   case CascadeOnDelete = 'cascadeOnDelete';
   case CascadeOnUpdate = 'cascadeOnUpdate';
   case RestrictOnDelete = 'restrictOnDelete';
   case RestrictOnUpdate = 'restrictOnUpdate';
   case NullOnDelete = 'nullOnDelete';
   case NullOnUpdate = 'nullOnUpdate';
   case NoActionOnDelete = 'noActionOnDelete';
   case NoActionOnUpdate = 'noActionOnUpdate';

   case RenameIndex = 'renameIndex';
   case DropPrimary = 'dropPrimary';
   case DropUnique = 'dropUnique';
   case DropIndex = 'dropIndex';
   case DropFullText = 'dropFullText';
   case DropSpatialIndex = 'dropSpatialIndex';
   case DropForeign = 'dropForeign';

   public function category(): string
   {
      return match ($this) {
         self::Primary, self::Unique, self::Index, self::FullText, self::SpatialIndex => 'Basic Indexes',
         self::Foreign, self::ForeignId, self::Constrained,
         self::CascadeOnDelete, self::CascadeOnUpdate,
         self::RestrictOnDelete, self::RestrictOnUpdate,
         self::NullOnDelete, self::NullOnUpdate,
         self::NoActionOnDelete, self::NoActionOnUpdate => 'Foreign Keys',
         self::RenameIndex => 'Renaming',
         self::DropPrimary, self::DropUnique, self::DropIndex, self::DropFullText, self::DropSpatialIndex, self::DropForeign => 'Dropping Indexes',
      };
   }

   public function description(): string
   {
      return match ($this) {
         self::Primary => 'Adds a primary key (single or composite).',
         self::Unique => 'Adds a unique index.',
         self::Index => 'Adds a basic index.',
         self::FullText => 'Adds a full text index (MySQL / PostgreSQL).',
         self::SpatialIndex => 'Adds a spatial index (except SQLite).',

         self::Foreign => 'Adds a foreign key referencing another table.',
         self::ForeignId => 'Adds a foreignId (unsignedBigInteger) column.',
         self::Constrained => 'Constrains a foreignId to a specific table and column.',

         self::CascadeOnDelete => 'Deletes should cascade.',
         self::CascadeOnUpdate => 'Updates should cascade.',
         self::RestrictOnDelete => 'Restrict delete if related records exist.',
         self::RestrictOnUpdate => 'Restrict update if related records exist.',
         self::NullOnDelete => 'Set null on delete.',
         self::NullOnUpdate => 'Set null on update.',
         self::NoActionOnDelete => 'No action on delete.',
         self::NoActionOnUpdate => 'No action on update.',

         self::RenameIndex => 'Rename an index.',

         self::DropPrimary => 'Drop a primary key.',
         self::DropUnique => 'Drop a unique index.',
         self::DropIndex => 'Drop a basic index.',
         self::DropFullText => 'Drop a full text index.',
         self::DropSpatialIndex => 'Drop a spatial index.',
         self::DropForeign => 'Drop a foreign key constraint.',
      };
   }

   public function options(): array
   {
      return match ($this) {
         //Index simples (avec colonnes + nom optionnel)
         self::Primary,
         self::Unique,
         self::Index,
         self::FullText,
         self::SpatialIndex => [
            'type' => 'index',
            'options' => [
               'columns' => '?|array|string',
               'name' => '?string',
            ],
         ],

         //Clés étrangères manuelles
         self::Foreign => [
            'type' => 'foreign',
            'options' => [
               'columns' => 'array',
               'references' => 'string',
               'on' => 'string',
            ],
         ],

         //Clé étrangère simplifiée
         self::ForeignId => [
            'type' => 'foreignId',
            'options' => [
               'name' => 'string',
            ],
         ],

         //Contrainte "constrained"
         self::Constrained => [
            'type' => 'constrained',
            'options' => [
               'table' => '?string',
               'column' => '?string',
               'indexName' => '?string',
            ],
         ],

         //Contraintes d'action (sur delete / update)
         self::CascadeOnDelete,
         self::CascadeOnUpdate,
         self::RestrictOnDelete,
         self::RestrictOnUpdate,
         self::NullOnDelete,
         self::NullOnUpdate,
         self::NoActionOnDelete,
         self::NoActionOnUpdate => [
            'type' => 'action',
            'options' => [], // pas d’option, juste une méthode fluide
         ],

         //Renommer un index
         self::RenameIndex => [
            'type' => 'rename',
            'options' => [
               'from' => 'string',
               'to' => 'string',
            ],
         ],

         //Suppression d’un index
         self::DropPrimary,
         self::DropUnique,
         self::DropIndex,
         self::DropFullText,
         self::DropSpatialIndex,
         self::DropForeign => [
            'type' => 'drop',
            'options' => [
               'name' => 'string',
            ],
         ],

         default => [],
      };
   }


   public static function withOption(string $option): array
   {
      return array_filter(self::cases(), fn($case) => in_array($option, $case->options()));
   }

   public static function tableRows(): array
   {
      return collect(self::cases())->map(
         fn($case) => [$case->category(), $case->value, $case->description()]
      )->toArray();
   }

   public static function standaloneTableIndexes(): array
   {
      return [
         self::Primary,
         self::Unique,
         self::Index,
         self::FullText,
         self::SpatialIndex,
         self::RenameIndex,
         self::DropPrimary,
         self::DropUnique,
         self::DropIndex,
         self::DropFullText,
         self::DropSpatialIndex,
         self::DropForeign,
      ];
   }

   public static function has(string|array|null $columnIndex): bool
   {
      if (is_array($columnIndex)) {
         return count(array_intersect($columnIndex, self::values())) > 0;
      } else {
         return in_array($columnIndex, self::values());
      }
   }

   public static function hasStandalone(string|array|null $columnIndex): bool
   {
      $standaloneValues = array_map(fn($e) => $e->value, self::standaloneTableIndexes());

      if (is_array($columnIndex)) {
         $modValues = array_map(fn($e) => $e instanceof self ? $e->value : $e, $columnIndex);
         return count(array_intersect($modValues, $standaloneValues)) > 0;
      }

      return in_array(
         $columnIndex instanceof self ? $columnIndex->value : $columnIndex,
         $standaloneValues
      );
   }

   public static function rules(string $entityName, Attribut $attribut, ?array $index = []): string
   {
      $rules = [];
      if (!isset($index['method'])) {
         return '';
      }

      $name = trim(Str::lower(Str::plural($entityName)));
      $method = $index['method'];


      $rules[] = match ($method) {
         'unique' => self::generateUniqueRule($index, $attribut, $name),     // ex: ['unique:table,column']
         'primary' => "'required'",  // en général requis si clé primaire
         'index', 'fullText', 'spatialIndex' => '', // pas de règle directe
         default => '',
      };

      // Foreign keys (constrained)
      if (in_array($method, [
         'foreign',
         'foreignId',
         'constrained',
         'cascadeOnDelete',
         'cascadeOnUpdate',
         'restrictOnDelete',
         'restrictOnUpdate',
         'nullOnDelete',
         'nullOnUpdate',
         'noActionOnDelete',
         'noActionOnUpdate'
      ])) {
         $rules[] = self::generateExistsRule($index, $attribut, $name); // ex: ['exists:other_table,id']
      }

      // Drop / rename => pas de validation sur données utilisateurs
      if (str_starts_with($method, 'drop') || str_starts_with($method, 'rename')) {
         return '';
      }

      // Supprime les vides ou doublons
      $rules = array_unique(array_filter($rules));
      return implode(',', $rules);
   }

   public static function generateUniqueRule(array $index, Attribut $attribute, string $name): string
   {
      $rUnique = '';
      $attributeName = trim($attribute->getName());

      if (!empty($index['options']['columns'])) {
         $columns = $index['options']['columns'];

         // Si `columns` est un tableau (ex: ['name', 'label'])
         if (is_array($columns)) {
            if ($columns === $index['method']) {
               $rUnique = "'unique:{$name},{$attributeName}'";
            } else {
               $rUnique = "Rule::unique('{$name}')->where(fn(\$q) => " . implode('', array_map(
                  fn($col) => "\$q->where('{$col}', \$request->{$col})->",
                  $columns
               )) . "true)";
            }
         }

         // Sinon, c’est une simple colonne (ex: 'email')
         elseif (is_string($columns)) {
            $columns = empty($column) ? $attributeName : trim($columns);
            $rUnique = "'unique:{$name},{$columns}'";
         }
      }
      return $rUnique;
   }

   public static function generateExistsRule(array $index, Attribut $attribute, string $name): string
   {
      $attributeName = trim($attribute->getName());
      $name = $index['options']['on'] ?? $name;
      $ref  = trim($index['options']['references']) ?? $attributeName;

      $columns = $index['options']['columns'] ?? [];

      if (is_array($columns) && count($columns) > 1) {
         $whereParts = [];
         foreach ($columns as $column) {
            $whereParts[] = "->where('{$column}', \$request->{$column})";
         }

         $whereClause = implode('', $whereParts);
         return "Rule::exists('{$name}')->where(fn(\$q) => \$q{$whereClause})";
      }

      // Si un seul champ ou format string
      $column = is_array($columns) ? current($columns) : $columns;
      return "'exists:{$name},{$ref}'";
   }

   public static function schema(Attribut $attribute): ?string
   {
      $indexes = $attribute->getindexes();
      $schema = [];

      foreach ($indexes as $index) {

         /**
          * @var \Kjos\Command\Managers\ColumnIndex $index
          */
         $method = $index->getMethod();
         $options = $index->getOptions();
         $options_ = self::tryFrom($method)->options();

         $schema_ = match ($options_['type']) {
            'index' => function () use ($options, $method) {
               $columns = data_get($options, 'columns') ?? '';
               $name = data_get($options, 'name') ?? '';

               $args = [];

               if (!empty($columns)) {
                  $args[] =  $columns = self::parseArray($columns);
               }
               if (!empty($name)) {
                  $args[] = "'{$name}'";
               }

               $argString = implode(', ', $args);

               return "->{$method}({$argString})";
            },
            'foreign' => function () use ($options, $method) {
               $columns = data_get($options, 'columns') ?? '';
               $columns = self::parseArray($columns);

               $references = data_get($options, 'references') ?? '';
               $on = data_get($options, 'on') ?? '';
               if (empty($columns) || empty($references) || empty($on)) {
                  return '';
               }

               return "->{$method}({$columns})->references('{$references}')->on('{$on}')";
            },
            'foreignId' => function () use ($options, $method) {
               $name = data_get($options, 'name') ?? '';

               if (empty($name)) {
                  return '';
               }

               return "->{$method}('{$name}')";
            },
            'constrained' => function () use ($options, $method) {
               $table = data_get($options, 'table') ?? '';
               $column = data_get($options, 'column') ?? '';
               $indexName = data_get($options, 'indexName') ?? '';

               $args = [];

               if (!empty($table)) {
                  $args[] = "'$table'";
               }
               if (!empty($column)) {
                  $args[] = "'$column'";
               }
               if (!empty($indexName)) {
                  $args[] = "'$indexName'";
               }

               $argString = implode(', ', $args);

               return "->{$method}({$argString})";
            },
            'action' => function () use ($method) {
               return "->{$method}()";
            },
            'rename' => function () use ($options, $method) {
               $from = data_get($options, 'from') ?? '';
               $to = data_get($options, 'to') ?? '';

               if (empty($from) || empty($to)) {
                  return '';
               }

               return "->{$method}('{$from}', '{$to}')";
            },
            'drop' => function () use ($method) {
               return "->{$method}()";
            },
            default => '',
         };

         $schema[] = $schema_();
      }

      return implode('', array_filter($schema));
   }

   private static function parseArray(mixed $array): string
   {
      if (is_array($array)) {
         $array = collect($array)
            ->filter(fn($col) => !empty($col))
            ->map(fn($col) => "'$col'")
            ->implode(', ');
         $array = "[$array]";
      } else {
         $array = "'{$array}'";
      }

      return $array;
   }
}
