<?php

namespace Kjos\Command\Concerns;


class MethodFactory
{
   protected string $name;
   protected string $body = '';
   protected AttributesFactory $arguments;
   protected array $returnType = [];
   protected string $visibility = 'public';
   protected bool $static = false;
   protected bool $final = false;

   public function addName(string $name): void
   {
      $this->name = $name;
   }

   public function getName(): string
   {
      return $this->name;
   }

   public function addArguments(AttributesFactory $arguments): void
   {
      $this->arguments = $arguments;
   }

   public function getArguments(): AttributesFactory
   {
      return $this->arguments;
   }

   public function addReturnType(array $returnType): void
   {
      $this->returnType = $returnType;
   }

   public function getReturnType(): string
   {
      return empty($this->returnType) ? 'void' : implode(' | ', $this->returnType);
   }

   public function construct(): static
   {
      $this->body = <<<'EOT'
         {$this->visbility} function {$this->name}({$this->arguments->get()}) {$this->getReturnType()}
         {
            $this->body
         }
      EOT;
      return $this;
   }

   public function get(): string
   {
      return $this->body;
   }
}
