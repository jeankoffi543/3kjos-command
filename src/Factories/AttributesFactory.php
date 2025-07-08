<?php

namespace Kjos\Command\Factories;

class AttributesFactory
{
   protected string $varName;
   protected string $type = '';
   protected string $defaultValue = '';
   protected string $visbility = '';
   protected array $content = [];

   public function setName(string $name): void
   {
      $this->varName = $name;
   }

   public function getName(): string
   {
      return $this->varName;
   }

   public function setVisbility(string $visbility): void
   {
      $this->visbility = $visbility;
   }

   public function getVisbility(): string
   {
      return $this->visbility;
   }

   public function getType(): string
   {
      return $this->type;
   }

   public function setType(string $type): void
   {
      $this->type = $type;
   }

   public function setDefaultValue(string $defaultValue): void
   {
      $this->defaultValue = $defaultValue;
   }

   public function getDefaultValue(): string
   {
      return $this->defaultValue !== '' ? "{= $this->defaultValue}" : '';
   }

   public function get(): string
   {
      return "{$this->getVisbility()} {$this->getType()} {$this->getName()} {$this->getDefaultValue()}";
   }
}
