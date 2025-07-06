<?php

namespace Kjos\Command\Managers;

class ColumnIndex
{
   protected ?array $options = null;
   protected ?string $method = null;
   
   public function setOptions(?array $options): static
   {
      $this->options = $options;
      return $this;
   }
   public function getOptions(): ?array
   {
      return $this->options;
   }

   public function setMethod(?string $method): static
   {
      $this->method = $method;
      return $this;
   }
   public function getMethod(): mixed
   {
      return $this->method;
   }

   public function toArray(): array
   {
      return [
         'method' => $this->getMethod(),
         'options' => $this->getOptions(),
      ];
   }
}
