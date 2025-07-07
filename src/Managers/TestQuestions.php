<?php

namespace Kjos\Command\Managers;

use Kjos\Command\Enums\Collation;
use Kjos\Command\Enums\ColumnModifier as ColumnModifierEnum;
use Kjos\Command\Enums\ColumnType as ColumnTypeEnum;
use Kjos\Command\Enums\ColumnIndex as ColumnIndexEnum;
use Kjos\Command\Enums\SpatialSubtype;
use Faker\Generator;
use Illuminate\Database\Query\Expression;

// use Illuminate\Testing\PendingCommand;

class TestQuestions
{
   private $test;
   private string $columnType;
   private Generator $faker;


   public function __construct($test, Generator $faker)
   {
      $this->test = $test;
      $this->faker = $faker;
   }

   public function ask(string $field): static
   {
      $this->name($field)
         ->type()
         ->length()
         ->fixed()
         ->charset()
         ->total()
         ->places()
         ->precision()
         ->enum()
         ->subtype()
         ->srid()
         ->dimensions()
         ->modifiers()
         ->indexes();

         return $this->addMoreQuestion($field);
   }

   private function addMoreQuestion(string $field): static
   {
      $response = $this->faker->randomElement(['add', 'x']);
      $this->test->expectsQuestion("Enter your response [add/cancel/x]. Default is", $response);
      if ($response === 'add') {
         dump('we add');
         return $this->ask($field);
      } else {
         dump('we x');
         return $this;
      } 

      return $this;
   }


   public function type(): static
   {
      // $this->columnType = $this->faker->randomElement(ColumnTypeEnum::values());
      $this->columnType = 'string';
      $this->test->expectsQuestion(
         'use arrow to select your database field type. Ex: string. Press [Enter] to skip or type /q.',
         $this->columnType
      );
      return $this;
   }

   public function length(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('length'))) {
         $this->test->expectsQuestion('Enter the field length. Ex: 255. Press [Enter] to skip or type /q.', $this->faker->numberBetween(1, 65535));
      }
      return $this;
   }

   public function fixed(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('fixed'))) {
         return $this->test->expectsConfirmation('Is the field fixed?', $this->faker->randomElement([true, false]));
      }
      return $this;
   }

   public function charset(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('charset'))) {
         $this->parseCharset();
      }
      return $this;
   }

   public function total(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('total'))) {
         $this->test->expectsQuestion('Enter the field total. Ex: 6. Press [Enter] to skip or type /q.', $this->faker->numberBetween(1, 65535));
      }
      return $this;
   }

   public function places(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('places'))) {
         $this->test->expectsQuestion('Enter the field places. Ex: 2. Press [Enter] to skip or type /q.', $this->faker->numberBetween(1, 65535));
      }
      return $this;
   }

   public function precision(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('precision'))) {
         $this->test->expectsQuestion('Enter the field precision. Ex: 2. Press [Enter] to skip or type /q.', $this->faker->numberBetween(1, 65535));
      }
      return $this;
   }

   public function enum(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('enum'))) {
         $this->test->expectsQuestion("Enter the enum field value, separated by commas. Ex: 1,2,3. Press [Enter] to skip or type /q.", $this->faker->randomElement(['admin,user,guest', 'student,teacher,guest', 'english,french']));
      }
      return $this;
   }

   public function subtype(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('subtype'))) {
         $this->test->expectsQuestion('Enter the field subtype. Ex: 2. Press [Enter] to skip or type /q.', $this->faker->randomElement(SpatialSubtype::cases()));
      }
      return $this;
   }

   public function srid(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('srid'))) {
         $this->test->expectsQuestion('Enter the field srid. Ex: 2. Press [Enter] to skip or type /q.', $this->faker->numberBetween(1, 65535));
      }
      return $this;
   }

   public function dimensions(): static
   {
      if (in_array(ColumnTypeEnum::tryFrom($this->columnType), ColumnTypeEnum::withOption('dimensions'))) {
         $this->test->expectsQuestion('Enter the field dimensions. Ex: 2. Ex: 2. Press [Enter] to skip or type /q.', $dimensions = $this->faker->numberBetween(1, 65535));
      }
      return $this;
   }

   public function name(string $name): static
   {
      $this->test->expectsQuestion('Enter the field name. Ex: email <fg=red>[required]</>', $name);
      return $this;
   }


   public function modifiers(): static
   {
      $modifier = $this->faker->randomElement([...ColumnModifierEnum::values(), "/q"]);
      $this->test->expectsQuestion(
         'Use arrow to add a modifier (Ex: <fg=green>nullable</>). Press [Enter] to skip or type "/q".',
         $modifier,
      );

      // Cas "passer à la suite"
      if ($this->skip($modifier)) {
         return $this;
      }

      // Vérification de validité
      if (!in_array($modifier, ColumnModifierEnum::values())) {
         $this->modifiers();
      }

      // Ajouter le modificateur
      $this->parseModifiers($modifier);
      $this->modifiers();

      return $this;
   }

   private function skip(mixed $value): bool
   {
      if (is_array($value)) {
         if (empty($value) || current($value) == "/q") {
            return true;
         }
      } else {
         if (empty($value) || in_array(strtolower($value), ["/q"])) {
            return true;
         }
      }
      return false;
   }

   public function parseModifiers(mixed $modifier): void
   {
      $options = ColumnModifierEnum::tryFrom($modifier)->options()['value'] ?? null;
      match ($options) {
         'scalar' => $this->parseScalar(),
         'scalar|from|to' => $this->parseScalar($options, $modifier),
         'boolean' => $this->parseBoolean(),
         'charset' => $this->parseCharset(),
         'collation' => $this->parseCollation(),
         'expression' => $this->parseExpression(),
         'expression|nullable' => $this->parseExpression($options),
         null => $modifier,
         default => null,
      };
   }

   public function parseCharset(): static
   {
      $this->test->expectsQuestion('Enter the field charset. Ex: utf8 or press enter to skip', $this->faker->randomElement(['utf8', 'utf8mb4']));
      return $this;
   }

   public function parseCollation(): static
   {
      $this->test->expectsQuestion('Enter the field collation. Ex: UTF8MB4_UNICODE_CI or press enter to skip', $this->faker->randomElement(Collation::cases())->value);
      return $this;
   }

   public function parseScalar(?string $options = null, ?string $modifier = null): void
   {
      $options = array_filter(explode('|', $options));
      if (empty($options)) {
         $this->test->expectsQuestion('Enter the field value. Or press enter to skip', $this->faker->word());
      } else {
         foreach ($options as $option) {
            if ($option === 'from' || $option === 'to') {
               $this->test->expectsQuestion("{$modifier} <fg=red>[{$option}]</> value. Or press enter to skip", $this->faker->word());
            }
         }
      }
   }

   public function parseBoolean(): void
   {
      $this->test->expectsQuestion('Set the field value to true or false?', $this->faker->randomElement([true, false]));
   }

   public function parseExpression(?string $options = null): void
   {
      $expression = $this->faker->randomElement(['now()', 'JSON_ARRAY()', null]);
      $options = array_filter(explode('|', $options));
      if (empty($options)) {
         $this->test->expectsQuestion(
            "Enter the field expression or enter <fg=red>[empty]</> for no expression. Ex: now() or press enter to skip",
            $expression
         );
         if ($this->skip($expression)) {
            return;
         }
      } else {
         $this->test->expectsQuestion('Enter the field expression. Ex: now() or press enter to skip', $expression);
      }
   }

   public function indexes(): static
   {
      $index = $this->faker->randomElement([...ColumnIndexEnum::values(), "/q"]);

      $this->test->expectsQuestion(
         'use arrow add a index to the field. Ex: <fg=green>unique</>. Press [Enter] to skip or type /q.',
         $index,
      );

      if ($this->skip($index)) {
         return $this;
      }

      if (! in_array($index, ColumnIndexEnum::values())) {
         $this->indexes();
      }

      $this->parseIndexes($index);

      $this->indexes();
      return $this;
   }


   public function parseIndexes(mixed $value): void
   {
      $columnIndexEnum = ColumnIndexEnum::tryFrom($value);
      $options = $columnIndexEnum->options();
      $this->parseIndexesOptions($options['options'], $value);
   }

   public function parseIndexesOptions(array $options = [], string $value): void
   {
      $values = [];
      foreach ($options as $key => $option) {
         $op = match ($option) {
            'array' => function () use ($key, $value) {
               $this->test->expectsQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value, separated by commas. Ex: value1, value2, value3. Press [Enter] to skip or type /q. <fg=red>required</>",
                  $this->faker->randomElement(["{$this->faker->word()}, {$this->faker->word()}", $this->faker->word(), null])
               );
            },
            'array|string' => function () use ($key, $value) {
               $this->test->expectsQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value, For array: separated by commas or string one value. Ex: value1, value2, value3. Press [Enter] to skip or type /q. <fg=red>required</>",
                  $this->faker->word()
               );
            },

            '?|array|string' => function () use ($key, $value) {
               $this->test->expectsQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value, For array: separated by commas or string one value. Ex: value1, value2, value3. Press [Enter] fo null or type /q for skip. <fg=red>required</>",
                  $this->faker->word()
               );
            },

            'string' => function () use ($key, $value) {
               $this->test->expectsQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value. Press [Enter] to skip or type /q. <fg=red>required</>",
                  $this->faker->word()
               );
            },

            '?string' => function () use ($key, $value) {
               $this->test->expectsQuestion(
                  "Enter the <fg=blue>{$value}</> [<fg=red>{$key}</>] value. Press [Enter] to skip or type /q.",
                  $this->faker->word()
               );
            },
            default => null
         };

         $op();
      }
   }
}
