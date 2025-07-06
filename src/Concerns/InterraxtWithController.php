<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Enums\NameArgument;

trait InterraxtWithController
{
   private static array $config = [];
   private static string $name = '';
   private static KjosMakeRouteApiCommand $command;
   public static string $nameLowerSingular = '';
   public static string $nameStudySingular = '';
   public static function init(string $name, KjosMakeRouteApiCommand $command): void
   {
      // self::$config = $config;
      self::$command = $command;
      self::$name = $name;
      self::$nameLowerSingular = NameHelper::nameSingular(self::$name, NameArgument::Lower);
      self::$nameStudySingular = NameHelper::nameSingular(self::$name, NameArgument::Studly);
   }

   private static function defaultIndex(): string
   {
      $nameLowerSingular = self::$nameLowerSingular;
      $nameStudySingular = self::$nameStudySingular;
      return <<<INDEX
      public function index(Request \$request): AnonymousResourceCollection
      {
        \$limit = \$request->query('limit', config('3kjos-command.route.pagination.limit'));

        \${$nameLowerSingular}Query = {$nameStudySingular}::query();

        /**
         * Paginate the results
         *
         * @var \Illuminate\Database\Eloquent\Collection \${$nameLowerSingular}
         */
        \${$nameLowerSingular} = \${$nameLowerSingular}Query->paginate(\$limit);

        return {$nameStudySingular}Resource::collection(\${$nameLowerSingular});
      }
      INDEX;
   }

   private static function centralizeAndErrorHandlerIndex(): string
   {
      return <<<INDEX
         public function index(Request \$request): AnonymousResourceCollection
         {
            return \$this->invokeWithCatching(function () {
            return \$this->service::index();
         });
      }
      INDEX;
   }

   private static function errorHandlerIndex(): string
   {
      $nameLowerSingular = self::$nameLowerSingular;
      $nameStudySingular = self::$nameStudySingular;
      return <<<INDEX
         public function index(Request \$request): AnonymousResourceCollection
         {
            return \$this->invokeWithCatching(function () {
               \$limit = \$request->query('limit', config('3kjos-command.route.pagination.limit'));

               \${$nameLowerSingular}Query = {$nameStudySingular}::query();

               /**
                  * Paginate the results
                  *
                  * @var \Illuminate\Database\Eloquent\Collection \${$nameLowerSingular}
                  */
               \${$nameLowerSingular} = \${$nameLowerSingular}Query->paginate(\$limit);

               return {$nameStudySingular}Resource::collection(\${$nameLowerSingular});
         });
      }
      INDEX;
   }

   private static function centralizeIndex(): string
   {
      return <<<INDEX
         public function index(Request \$request): AnonymousResourceCollection
         {
            return \$this->service::index();
         }
      INDEX;
   }


   private static function defaultShow(): string
   {
      $nameLowerSingular = self::$nameLowerSingular;
      $nameStudySingular = self::$nameStudySingular;
      return <<<SHOW
         public function show(\$id): Response|{$nameStudySingular}Resource
         {
            try {
                  \${$nameLowerSingular} = {$nameStudySingular}::find(\$id);
                  if (! \${$nameLowerSingular}) {
                     return response('not_found', Response::HTTP_NOT_FOUND);
                  }

                  return new {$nameStudySingular}Resource(\${$nameLowerSingular});
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      SHOW;
   }

   private static function centralizeAndErrorHandlerShow(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<SHOW
         public function show(\$id): Response|{$nameStudySingular}Resource
         {
            return \$this->invokeWithCatching(function () use (\$id) {
                  return \$this->service::show(\$id);
            });
         }
         SHOW;
   }

   private static function errorHandlerShow(): string
   {
      $nameLowerSingular = self::$nameLowerSingular;
      $nameStudySingular = self::$nameStudySingular;
      return <<<SHOW
         public function show(\$id): Response|{$nameStudySingular}Resource
         {
            return \$this->invokeWithCatching(function () use (\$id) {
                  \${$nameLowerSingular} = {$nameStudySingular}::find(\$id);
                  if (! \${$nameLowerSingular}) {
                     return response('not_found', Response::HTTP_NOT_FOUND);
                  }

                  return new {$nameStudySingular}Resource(\${$nameLowerSingular});
            });
         }
      SHOW;
   }

   private static function centralizeShow(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<SHOW
         public function show(\$id): Response|{$nameStudySingular}Resource
         {
            try {
               return \$this->service::show(\$id);
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      SHOW;
   }


   private static function defaultStore(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<STORE
         public function store({$nameStudySingular}Request \$request): Response|{$nameStudySingular}Resource
         {
            try {
               return new {$nameStudySingular}Resource({$nameStudySingular}::create(\$request->validated()));
            } catch (\Exception \$e) {
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      STORE;
   }

   private static function centralizeAndErrorHandlerStore(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<STORE
         public function store({$nameStudySingular}Request \$request): Response|{$nameStudySingular}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request) {
               return \$this->service::store(\$request->validated());
            });
         }
      STORE;
   }

   private static function errorHandlerStore(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<STORE
         public function store({$nameStudySingular}Request \$request): Response|{$nameStudySingular}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request) {
               return new {$nameStudySingular}Resource({$nameStudySingular}::create(\$request->validated()));
            });
         }
      STORE;
   }

   private static function centralizeStore(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<STORE
         public function store(\$id): Response|{$nameStudySingular}Resource
         {
            try {
               return \$this->service::store(\$request->validated());
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      STORE;
   }

   private static function defaultUpdate(): string
   {
      $nameLowerSingular = self::$nameLowerSingular;
      $nameStudySingular = self::$nameStudySingular;
      return <<<UPDATE
        public function update({$nameStudySingular}Request \$request, \$id): Response|{$nameStudySingular}Resource
        {
            try {

               \${$nameLowerSingular} = {$nameStudySingular}::find(\$id);
               if (! \${$nameLowerSingular}) {
                  return response('not_found', Response::HTTP_NOT_FOUND);
               }
               \${$nameLowerSingular}->update(\$request->validated());

               return new {$nameStudySingular}Resource(\${$nameLowerSingular});
            } catch (\Exception \$e) {
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      UPDATE;
   }

   private static function centralizeAndErrorHandlerUpdate(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<UPDATE
         public function update({$nameStudySingular}Request \$request, \$id): Response|{$nameStudySingular}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request, \$id) {
                  return \$this->service->update(\$id, \$request->validated());
            });
         }

      UPDATE;
   }

   private static function errorHandlerUpdate(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      $nameLowerSingular = self::$nameLowerSingular;
      return <<<UPDATE
         public function update({$nameStudySingular}Request \$request): Response|{$nameStudySingular}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request) {
               
               \${$nameLowerSingular} = {$nameStudySingular}::find(\$id);
               if (! \${$nameLowerSingular}) {
                  return response('not_found', Response::HTTP_NOT_FOUND);
               }
               \${$nameLowerSingular}->update(\$request->validated());

               return new {$nameStudySingular}Resource(\${$nameLowerSingular});
            });
         }
         UPDATE;
   }

   private static function centralizeUpdate(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<UPDATE
        public function update({$nameStudySingular}Request \$request, \$id): Response|{$nameStudySingular}Resource
         {
            try {
               return \$this->service->update(\$id, \$request->validated());
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      UPDATE;
   }

   private static function defaultDestroy(): string
   {
      $nameLowerSingular = self::$nameLowerSingular;
      $nameStudySingular = self::$nameStudySingular;
      return <<<UPDATE
         public function destroy(\$id): Response|{$nameStudySingular}Resource
         {
            try {
                  \${$nameLowerSingular} = {$nameStudySingular}::find(\$id);
                  if (! \${$nameLowerSingular}) {
                     return response('not_found', Response::HTTP_NOT_FOUND);
                  }
                  \${$nameLowerSingular}->delete();
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      UPDATE;
   }

   private static function centralizeAndErrorHandlerDestroy(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<UPDATE
         public function destroy(\$id): Response|{$nameStudySingular}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request, \$id) {
                  return \$this->service->destroy(\$id);
            });
         }

      UPDATE;
   }

   private static function errorHandlerDestroy(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      $nameLowerSingular = self::$nameLowerSingular;
      return <<<UPDATE
         public function destroy(\$id): Response|{$nameStudySingular}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request) {
               
               \${$nameLowerSingular} = {$nameStudySingular}::find(\$id);
               if (! \${$nameLowerSingular}) {
                  return response('not_found', Response::HTTP_NOT_FOUND);
               }
               \${$nameLowerSingular}->delete();
            });
         }
         UPDATE;
   }

   private static function centralizeDestroy(): string
   {
      $nameStudySingular = self::$nameStudySingular;
      return <<<UPDATE
        public function destroy(\$id): Response|{$nameStudySingular}Resource
         {
            try {
               return \$this->service->destroy(\$id);
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      UPDATE;
   }
}
