<?php

namespace Kjos\Command\Services;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Concerns\NameHelper;
use Kjos\Command\Concerns\Path;
use Kjos\Command\Concerns\PhpBodyFactory;
use Kjos\Command\Concerns\RequestKitProvider;
use Kjos\Command\Enums\NameArgument;
use Kjos\Command\Managers\Entity;

class RequestFactory
{
   use InterractWithService;

   protected ?FileFactory $fileFactory;
   protected array $parsedFileContent = [];
   private array $namespaces = [];
   private string $nameStudySingular = '';
   protected KjosMakeRouteApiCommand $command;
   protected Entity $entity;
   protected string $path = '';

   public function __construct(Entity $entity, Path $path, KjosMakeRouteApiCommand $command)
   {
      $this->entity = $entity;
      $this->nameStudySingular = NameHelper::nameSingular($this->entity->getName(), NameArgument::Studly);
      $this->path = $path->getRequestPath("{$this->nameStudySingular}Request.php");
      $this->namespaces = $path->getAllNamspaces();
      $this->fileFactory = new FileFactory($this->path, $command);
      $this->command = $command;
   }

   public function make()
   {
      // file php body
      $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this->command, $this->path);
      $phpBodyFactory->addClassDeclaration("class {$this->nameStudySingular}Request extends FormRequest")
      ->addMethods(RequestKitProvider::authorize(), 'authorize')
      ->addMethods(RequestKitProvider::failedValidation(), 'failedValidation')
      ->addMethods(RequestKitProvider::rules($this->entity), 'rules')
      ->addMethods(RequestKitProvider::prepareForValidation(), 'prepareForValidation');

      $this->fileFactory
         ->addNamespace("namespace {$this->namespaces['requestsPath']}")
         ->addUseStatements("use Illuminate\Contracts\Validation\Validator")
         ->addUseStatements("use Illuminate\Foundation\Http\FormRequest")
         ->addUseStatements("use Illuminate\Http\Exceptions\HttpResponseException")
         ->addUseStatements("use Illuminate\Validation\Rule")
         ->addUseStatements("use Illuminate\Http\Response")
         ->addBody($phpBodyFactory)
         ->save();
   }
}
