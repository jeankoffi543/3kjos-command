<?php

namespace Kjos\Command\Factories;

use Illuminate\Support\Facades\File;
use Kjos\Command\Concerns\InterractWithFileContent;

class FileFactory
{
   use InterractWithFileContent;

   protected ?string $namespace = null;
   protected ?PhpBodyFactory $phpBodyFactory = null;
   protected ?string $useStatements = null;

   protected string $startTage;
   protected string $endTage = '';
   protected array $imports = [];
   protected string $fileContent = '';
   protected BuilderFactory $factory;

   public function __construct(BuilderFactory $factory)
   {
      $this->factory = $factory;
      // set file path

      // create file if not exists
      kjos_create_file($this->factory->path);

      // now get file content
      $this->fileContent = kjos_get_file_content($this->factory->path);

      // set php body factory
      $this->phpBodyFactory = new PhpBodyFactory( $this->parseContent(), $this->factory);

   }


   public function addBody(PhpBodyFactory $phpBodyFactory): static
   {
      $this->phpBodyFactory = $phpBodyFactory;
      return $this;
   }

   public function getBody(): ?string
   {
      return $this->phpBodyFactory->get();
   }

   public function addPhpBodyFactory(PhpBodyFactory $phpBodyFactory): static
   {
      $this->phpBodyFactory = $phpBodyFactory;
      return $this;
   }

   public function getPhpBodyFactory(): ?PhpBodyFactory
   {
      return $this->phpBodyFactory;
   }

   public function addNamespace(?string $namespace = null): static
   {
      $this->namespace = kjos_ptrim($namespace) . ";";
      return $this;
   }

   public function getNamespace(): ?string
   {
      return $this->namespace;
   }

   public function addUseStatements(?string $useStatements = null): static
   {
      if ($useStatements) {
         $this->useStatements =  kjos_parse_statment($useStatements, $this->getUseStatements(), 'use');
      }
      return $this;
   }

   public function getUseStatements(): ?string
   {
      return $this->useStatements;
   }


   public function save(): void
   {
      File::put($this->factory->path, $this->get());
      exec("./vendor/bin/pint {$this->factory->path}", $output, $status);
   }

   public function getFileContent(): string
   {
      return $this->fileContent;
   }
   
}
