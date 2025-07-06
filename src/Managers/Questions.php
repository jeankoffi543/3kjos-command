<?php

namespace Kjos\Command\Managers;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\InterractWithQuestions;

class Questions
{
   use InterractWithQuestions;

   protected KjosMakeRouteApiCommand $command;
   private ?string $columnType = '';

   public function __construct(KjosMakeRouteApiCommand $command)
   {
      $this->command = $command;
   }

   public function ask(array $attributs = []): ?array
   {

      $attribut = (new Attribut())
         ->setName($this->name())
         ->setColumnType(
            (new ColumnType())
               ->setType($this->columnType = $this->type(1))
               ->setLength($this->length())
               ->setFixed($this->fixed())
               ->setCharset($this->charset())
               ->setTotal($this->total())
               ->setPlaces($this->places())
               ->setPrecision($this->precision())
               ->setEnum($this->enum())
               ->setSubtype($this->subtype())
               ->setSrid($this->srid())
               ->setDimensions($this->dimensions())
         )
         ->setColumnModifiers($this->modifiers([], 1))
         ->setColumnIndexes($this->indexes([], 1));

      $attributs[] = $attribut;

      $this->command->newLine(2);
      $this->command->info("<fg=black>New attribut added:</> <fg=blue>{$attribut->getName()}</>");

      return $this->addMoreQuestion($attributs);
   }
}
