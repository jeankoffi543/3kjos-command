<?php

namespace Kjos\Command\Enums;

use Kjos\Command\Managers\Attribut;

enum ColumnModifier: string
{
   use Values;

   case After = 'after';
   case AutoIncrement = 'autoIncrement';
   case Charset = 'charset';
   case Collation = 'collation';
   case Comment = 'comment';
   case Default = 'default';
   case First = 'first';
   case From = 'from';
   case Invisible = 'invisible';
   case Nullable = 'nullable';
   case StoredAs = 'storedAs';
   case Unsigned = 'unsigned';
   case UseCurrent = 'useCurrent';
   case UseCurrentOnUpdate = 'useCurrentOnUpdate';
   case VirtualAs = 'virtualAs';
   case GeneratedAs = 'generatedAs';
   case Always = 'always';
   case Change = 'change';
   case RenameColumn = 'renameColumn';
   case DropColumn = 'dropColumn';
   case DropMorphs = 'dropMorphs';
   case DropRememberToken = 'dropRememberToken';
   case DropSoftDeletes = 'dropSoftDeletes';
   case DropSoftDeletesTz = 'dropSoftDeletesTz';
   case DropTimestamps = 'dropTimestamps';
   case DropTimestampsTz = 'dropTimestampsTz';

   public static function categories(): array
   {
      return collect(self::cases())->groupBy(fn($case) => $case->category())->map(
         fn($group) => $group->map(fn($mod) => [
            'modifier' => $mod->value,
            'description' => $mod->description()
         ])->values()->all()
      )->all();
   }

   public static function tableRows(): array
   {
      return collect(self::cases())
         ->map(fn($case) => [$case->category(), $case->value, $case->description()])
         ->toArray();
   }

   public function category(): string
   {
      return match ($this) {
         self::After, self::First => 'Column Order',
         self::Charset, self::Collation => 'Encoding',
         self::Comment => 'Metadata',
         self::AutoIncrement, self::From => 'Auto-Increment / Identity',
         self::Invisible => 'Visibility',
         self::Nullable => 'Nullability',
         self::Unsigned => 'Numeric Attributes',
         self::Default, self::UseCurrent, self::UseCurrentOnUpdate => 'Defaults',
         self::StoredAs, self::VirtualAs, self::GeneratedAs, self::Always => 'Generated Columns',
         self::Change => 'Modifying Columns',
         self::RenameColumn => 'Renaming',
         self::DropColumn, self::DropMorphs, self::DropRememberToken, self::DropSoftDeletes, self::DropSoftDeletesTz, self::DropTimestamps, self::DropTimestampsTz => 'Dropping Columns',
      };
   }

   public function description(): string
   {
      return match ($this) {
         self::After => 'Place the column after another column (MariaDB / MySQL).',
         self::AutoIncrement => 'Set INTEGER columns as auto-incrementing (primary key).',
         self::Charset => 'Specify a character set for the column (MariaDB / MySQL).',
         self::Collation => 'Specify a collation for the column.',
         self::Comment => 'Add a comment to a column (MariaDB / MySQL / PostgreSQL).',
         self::Default => 'Specify a default value for the column.',
         self::First => 'Place the column first in the table (MariaDB / MySQL).',
         self::From => 'Set the starting value of an auto-incrementing field.',
         self::Invisible => 'Make the column invisible to SELECT * queries (MariaDB / MySQL).',
         self::Nullable => 'Allow NULL values in the column.',
         self::StoredAs => 'Create a stored generated column.',
         self::Unsigned => 'Set INTEGER columns as UNSIGNED (MariaDB / MySQL).',
         self::UseCurrent => 'Set TIMESTAMP to CURRENT_TIMESTAMP by default.',
         self::UseCurrentOnUpdate => 'Set TIMESTAMP to CURRENT_TIMESTAMP on update.',
         self::VirtualAs => 'Create a virtual generated column.',
         self::GeneratedAs => 'Create an identity column with sequence options (PostgreSQL).',
         self::Always => 'Force sequence values to override input (PostgreSQL).',
         self::Change => 'Modify the type or attributes of an existing column.',
         self::RenameColumn => 'Rename an existing column.',
         self::DropColumn => 'Drop one or more columns.',
         self::DropMorphs => 'Drop morphable_id and morphable_type columns.',
         self::DropRememberToken => 'Drop the remember_token column.',
         self::DropSoftDeletes => 'Drop the deleted_at column.',
         self::DropSoftDeletesTz => 'Alias of dropSoftDeletes().',
         self::DropTimestamps => 'Drop the created_at and updated_at columns.',
         self::DropTimestampsTz => 'Alias of dropTimestamps().',
         default => '',
      };
   }

   public function options(): array
   {
      return match ($this) {
         self::Charset => ['value' => 'charset'], // utf8, utf8mb4, etc.
         self::Collation => ['value' => 'collation'], // utf8mb4_unicode_ci, etc.
         self::Comment,
         self::Default,
         self::From => ['value' => 'scalar'], // string/int/float
         self::StoredAs => ['value' => 'expression|nullable'],
         self::VirtualAs,
         self::GeneratedAs => ['value' => 'expression'],
         self::After,
         self::RenameColumn => ['value' => 'scalar|from|to'],
         self::DropColumn => ['value' => 'scalar'],
         self::Nullable => ['value' => 'boolean'],
         self::AutoIncrement,
         self::Unsigned,
         self::UseCurrent,
         self::UseCurrentOnUpdate,
         self::Always,
         self::Invisible,
         self::First,
         self::Change,
         self::DropMorphs,
         self::DropRememberToken,
         self::DropSoftDeletes,
         self::DropSoftDeletesTz,
         self::DropTimestamps,
         self::DropTimestampsTz => ['value' => null],
         default => [],
      };
   }

   public static function standaloneTableModifiers(): array
   {
      return [
         self::DropSoftDeletes,
         self::DropSoftDeletesTz,
         self::DropRememberToken,
         self::DropMorphs,
         self::DropTimestamps,
         self::DropTimestampsTz,
      ];
   }

   public static function has(string|array|null $columnModifier): bool
   {
      if (is_array($columnModifier)) {
         return count(array_intersect($columnModifier, self::values())) > 0;
      } else {
         return in_array($columnModifier, self::values());
      }
   }

   public static function hasStandalone(string|array|null $columnModifier): bool
   {
      $standaloneValues = array_map(fn($e) => $e->value, self::standaloneTableModifiers());

      if (is_array($columnModifier)) {
         $modValues = array_map(fn($e) => $e instanceof self ? $e->value : $e, $columnModifier);
         return count(array_intersect($modValues, $standaloneValues)) > 0;
      }

      return in_array(
         $columnModifier instanceof self ? $columnModifier->value : $columnModifier,
         $standaloneValues
      );
   }

   public static function rules(?array $modifier = []): string
   {
      $rules = [];

      // nullable
      if (($modifier['boolean'] ?? false) && ($modifier['novalue'] ?? null) === 'nullable') {
         $rules[] = "'nullable'";
      }

      // default
      if (isset($modifier['scalar'])) {
         if (is_bool($modifier['scalar'])) {
            $rules[] = "'boolean'";
         } elseif (is_numeric($modifier['scalar'])) {
            $rules[] = "'numeric'";
         } elseif (is_string($modifier['scalar'])) {
            $rules[] = "'string'";
         }
      }

      // charset / collation
      if (!empty($modifier['charset'])) {
         // Optionnel, peut être ajouté selon ta logique métier
         $rules[] = "'string'"; // on suppose que cela s'applique à une string
      }

      if (!empty($modifier['collation'])) {
         $rules[] = "'string'";
      }

      // expression (ex: storedAs, virtualAs, generatedAs)
      if (!empty($modifier['expression'])) {
         $rules[] = "'string'"; // ou 'regex' ou un Rule::when selon contexte
      }

      // unsigned => uniquement numérique
      if (($modifier['novalue'] ?? null) === 'unsigned') {
         $rules[] = "'numeric'";
         $rules[] = "'min:0'";
      }

      // Commentaire ou autre sans impact de validation
      // => ignoré sauf si tu veux imposer string
      if (($modifier['novalue'] ?? null) === 'comment') {
         $rules[] = "'string'";
      }

      $rules = array_unique(array_filter($rules));

      return implode(',', $rules);
   }

   public static function schema(Attribut $attribute): ?string
   {
      $modifiers = $attribute->getModifiers();
      $schema = [];

      foreach ($modifiers as $modifierName => $modifier) {
         $schema_ = [];

         foreach ($modifier->toArray() as $m) {
            // Exploser la chaîne et filtrer les valeurs vides
            $parts = array_filter(
               explode(',', $m),
               fn($v) => trim($v) !== ''
            );

            // Transformer les éléments (mettre des quotes si string)
            $quotedParts = array_map(function ($v) {
               return is_numeric($v) ? $v : ("'" . trim($v) . "'");
            }, $parts);

            // Ajouter le résultat formaté dans le sous-tableau
            if (!empty($quotedParts)) {
               $schema_[] = implode(', ', $quotedParts);
            }
         }

         // Générer l'appel de méthode si des arguments sont présents
         $joined = implode(', ', $schema_);
         $schema[] = "->{$modifierName}({$joined})";
      }

      return implode('', array_filter($schema));
   }
}
