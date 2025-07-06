<?php

namespace Kjos\Command\Concerns;

use Illuminate\Support\Facades\File;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class FileFactory
{
   use InterractWithFileContent;

   protected string $path;
   protected ?string $namespace = null;
   protected ?PhpBodyFactory $phpBodyFactory = null;
   protected ?string $useStatements = null;

   protected string $startTage;
   protected string $endTage = '';
   protected array $imports = [];
   protected string $fileContent = '';
   protected array $config = [];
   protected KjosMakeRouteApiCommand $command;

   public function __construct(string $path, KjosMakeRouteApiCommand $command)
   {
      $this->command = $command;
      $this->config = kjos_get_config();
      // init config for interracting with file content
      $this->initConfig($this->config, $this->command);
      // set file path
      $this->path = $path;

      // create file if not exists
      kjos_create_file($path);

      // now get file content
      $this->fileContent = kjos_get_file_content($this->path);

      // set php body factory
      $this->phpBodyFactory = new PhpBodyFactory( $this->parseContent(), $this->command, $this->path);

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
      File::put($this->path, $this->get());
      exec("./vendor/bin/pint {$this->path}", $output, $status);
   }

   public function getFileContent(): string
   {
      return $this->fileContent;
   }
   
}
