<?php

namespace Kjos\Command\Factories;

use Kjos\Command\Factories\FileFactory;
use Kjos\Command\Concerns\InterractWithService;
use Kjos\Command\Factories\PhpBodyFactory;
use Kjos\Command\Libs\RequestKitProvider;
use Kjos\Command\Managers\Entity;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class RequestFactory extends BuilderFactory
{
   use InterractWithService;

   protected ?FileFactory $fileFactory;
   protected array $parsedFileContent = [];
   
   public function __construct(Entity $entity, KjosMakeRouteApiCommand $command)
   {
      parent::__construct($entity, $command);
      $this->path = $this->getRequestPath("{$this->getRequestName()}.php");
      $this->fileFactory = new FileFactory($this);
   }

   public function make()
   {
      $requestKitProvider = new RequestKitProvider($this);
      // file php body
      $phpBodyFactory = new PhpBodyFactory($this->fileFactory->parseContent(), $this);
      $phpBodyFactory->addClassDeclaration("class {$this->getRequestName()} extends FormRequest")
      ->addMethods($requestKitProvider->authorize(), 'authorize')
      ->addMethods($requestKitProvider->failedValidation(), 'failedValidation')
      ->addMethods($requestKitProvider->rules(), 'rules')
      ->addMethods($requestKitProvider->prepareForValidation(), 'prepareForValidation');

      $this->fileFactory
         ->addNamespace("namespace {$this->getAllNamspaces()['requestsPath']}")
         ->addUseStatements("use Illuminate\Contracts\Validation\Validator")
         ->addUseStatements("use Illuminate\Foundation\Http\FormRequest")
         ->addUseStatements("use Illuminate\Http\Exceptions\HttpResponseException")
         ->addUseStatements("use Illuminate\Validation\Rule")
         ->addUseStatements("use Illuminate\Http\Response")
         ->addBody($phpBodyFactory)
         ->save();
   }
}
