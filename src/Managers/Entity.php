<?php

namespace Kjos\Command\Managers;

class Entity
{
   protected string $name = '';
   protected array $attributes = [];
   protected string $primaryKey = '';

   public function __construct(string $name)
   {
      $this->name = $name;
   }

   public function setName (string $name): static
   {
      $this->name = $name;
      return $this;
   }
   public function getName(): string
   {
      return $this->name;
   }

   public function setAttributes(array $attribute): static
   {
      $this->attributes = $attribute;
      return $this;
   }
   public function getAttributes(): array
   {
      return $this->attributes;
   }

   public function setPrimaryKey(string $primaryKey): static
   {
      $this->primaryKey = $primaryKey;
      return $this;
   }
   public function getPrimaryKey(): string
   {
      return $this->primaryKey;
   }
}