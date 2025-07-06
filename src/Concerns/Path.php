<?php

namespace Kjos\Command\Concerns;

use Illuminate\Console\OutputStyle;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;

class Path
{
   protected ?string $basePath;
   protected ?string $modelsPath;
   protected ?string $factoriesPath;
   protected ?string $controllersPath;
   protected ?string $resourcesPath;
   protected ?string $servicesPath;
   protected ?string $seedersPath;
   protected ?string $viewsPath;
   protected ?string $requestsPath;
   protected ?string $migrationsPath;
   protected ?string $testsPath;
   protected ?string $routeApiPath;
   protected ?string $routeWebPath;
   protected ?string $routeConsolePath;
   protected ?string $datasetsPath;
   protected ?string $namespaceRoot;
   protected ?array $namespaces = [];
   protected ?KjosMakeRouteApiCommand $command = null;


   public function __construct(?KjosMakeRouteApiCommand $command = null)
   {
      $this->command = $command;
      $this->intit();
   }

   public function getRequestPath(?string $filename = null): string
   {
      return $filename ? $this->requestsPath . DIRECTORY_SEPARATOR . $filename : $this->requestsPath;
   }

   public function getMigrationsPath(): string
   {
      return $this->migrationsPath;
   }

   public function getDatasetsPath(?string $filename = null): string
   {
      return $this->datasetsPath . DIRECTORY_SEPARATOR . $filename;
   }

   public function getTestsPath(?string $filename = null): string
   {
      return $this->testsPath . DIRECTORY_SEPARATOR . $filename;
   }

   public function getRouteApiPath(): string
   {
      return $this->routeApiPath;
   }

   public function getRouteWebPath(): string
   {
      return $this->routeWebPath;
   }

   public function getRouteConsolePath(): string
   {
      return $this->routeConsolePath;
   }

   public function getViewsPath(): string
   {
      return $this->viewsPath;
   }

   public function getSeedersPath(?string $filename = null): string
   {
      return $this->seedersPath . DIRECTORY_SEPARATOR . $filename;
   }

   public function getServicesPath(?string $filename = null): string
   {
      return $this->servicesPath . DIRECTORY_SEPARATOR . $filename;
   }

   public function getFactoriesPath(?string $filename = null): string
   {
      return $this->factoriesPath . DIRECTORY_SEPARATOR . $filename;
   }

   public function getControllersPath(?string $filename = null): string
   {
      return $this->controllersPath . DIRECTORY_SEPARATOR . $filename;
   }

   public function getResourcesPath(?string $filename = null): string
   {
      return $this->resourcesPath . DIRECTORY_SEPARATOR . $filename;
   }

   public function getModelsPath(?string $filename = null): string
   {
      return $this->modelsPath . DIRECTORY_SEPARATOR . $filename;
   }

   public function getBasePath(): string
   {
      return $this->basePath;
   }

   public function getNamespaceRoot(): string
   {
      return $this->namespaceRoot;
   }


   private function intit()
   {
      $this->checkIfInstalled();
      $this->initPaths();
   }

   private function checkIfInstalled()
   {
      if (! config('3kjos-command')) {
         throw new \Exception('3kjos-command is not installed: run -> php artisan vendor:publish --tag=3kjos-command');
      }
   }

   public function getAllNamspaces(): array
   {
      return $this->namespaces;
   }


   private function initPaths(): void
   {
      foreach (config('3kjos-command.paths') as $key => $path) {
         if ($key === 'routes') {
            foreach (config('3kjos-command.paths.routes') as $key => $path) {
               kjos_throw_file(base_path($path));
               $key = ucfirst($key);
               $key = "route{$key}Path";
               $this->{$key} = base_path($path);
            }
         } else {
            kjos_throw_directory(base_path($path));
            $key = "{$key}Path";
            $this->{$key} = base_path($path);
            $this->namespaces[$key] = kjos_get_namespace(base_path($path));
         }
      }
   }
}
