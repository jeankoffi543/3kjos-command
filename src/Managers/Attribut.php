<?php

namespace Kjos\Command\Managers;

class Attribut
{
   protected ?string $name;
   protected ?string $value;
   protected ?ColumnType $columnType;
   protected ?array $columnModifiers = [];
   protected ?array $columnIndexes = [];

   public function setName(?string $name): static
   {
      $this->name = $name;
      return $this;
   }
   public function getName(): ?string
   {
      return $this->name;
   }

   public function setValue(?string $value): static
   {
      $this->value = $value;
      return $this;
   }
   public function getValue(): ?string
   {
      return $this->value;
   }

   public function setColumnType(?ColumnType $columnType): static
   {
      $this->columnType = $columnType;
      return $this;
   }
   public function getColumnType(): ?ColumnType
   {
      return $this->columnType;
   }

   public function setColumnModifiers(?array $columnModifier): static
   {  
      $this->columnModifiers = $columnModifier;
      return $this;
   }
   public function getModifiers(): ?array
   {
      return $this->columnModifiers;
   }

   public function setColumnIndexes(?array $columnIndex): static
   {
      $this->columnIndexes = $columnIndex;
      return $this;
   }
   public function getIndexes(): ?array
   {
      return $this->columnIndexes;
   }
}
