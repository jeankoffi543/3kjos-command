<?php

namespace Kjos\Command\Managers;

class ColumnModifier
{
   protected ?string $charset = null;
   protected ?string $collation = null;
   protected mixed $scalar = null;
   protected ?string $expression = null;
   protected ?bool $boolean = null;
   protected ?string $novalue = null;
 
   public function setCharset(?string $charset): static
   {
      $this->charset = $charset;
      return $this;
   }
   public function getCharset(): ?string
   {
      return $this->charset;
   }

   public function setCollation(?string $collation): static
   {
      $this->collation = $collation;
      return $this;
   }
   public function getCollation(): ?string
   {
      return $this->collation;
   }

   public function setScalar(mixed $scalar): static
   {
      $this->scalar = $scalar;
      return $this;
   }
   public function getScalar(): mixed
   {
      return $this->scalar;
   }

   public function setExpression(?string $expression): static
   {
      $this->expression = $expression;
      return $this;
   }
   public function getExpression(): ?string
   {
      return $this->expression;
   }

   public function setBoolean(?bool $boolean): static
   {
      $this->boolean = boolval($boolean);
      return $this;
   }
   public function getBoolean(): ?bool
   {
      return $this->boolean;
   }

   public function setNoValue(?string $novalue): static
   {
      $this->novalue = $novalue;
      return $this;
   }
   public function getNoValue(): ?string
   {
      return $this->novalue;
   }

   public function toArray(): array
   {
      return [
         'charset' => $this->getCharset(),
         'collation' => $this->getCollation(),
         'scalar' => $this->getScalar(),
         'expression' => $this->getExpression(),
         'boolean' => $this->getBoolean(),
         'novalue' => $this->getNoValue(),
      ];
   }
}
