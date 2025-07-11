<?php

namespace Kjos\Command\Concerns;

trait InterraxtWithController
{
   private function defaultIndex(): string
   {
      return <<<INDEX
      public function index(Request \$request): AnonymousResourceCollection
      {
        \$limit = \$request->query('limit', config('3kjos-command.route.pagination.limit'));

        \${$this->factory->getNameSingularLower()}Query = {$this->factory->getNameSingularStudly()}::query();

        /**
         * Paginate the results
         *
         * @var \Illuminate\Database\Eloquent\Collection \${$this->factory->getNameSingularLower()}
         */
        \${$this->factory->getNameSingularLower()} = \${$this->factory->getNameSingularLower()}Query->paginate(\$limit);

        return {$this->factory->getNameSingularStudly()}Resource::collection(\${$this->factory->getNameSingularLower()});
      }
      INDEX;
   }

   private function centralizeAndErrorHandlerIndex(): string
   {
      return <<<INDEX
         public function index(Request \$request): AnonymousResourceCollection
         {
            return \$this->invokeWithCatching(function () {
            return \$this->service->index();
         });
      }
      INDEX;
   }

   private function errorHandlerIndex(): string
   {
      return <<<INDEX
         public function index(Request \$request): AnonymousResourceCollection
         {
            return \$this->invokeWithCatching(function () {
               \$limit = \$request->query('limit', config('3kjos-command.route.pagination.limit'));

               \${$this->factory->getNameSingularLower()}Query = {$this->factory->getNameSingularStudly()}::query();

               /**
                  * Paginate the results
                  *
                  * @var \Illuminate\Database\Eloquent\Collection \${$this->factory->getNameSingularLower()}
                  */
               \${$this->factory->getNameSingularLower()} = \${$this->factory->getNameSingularLower()}Query->paginate(\$limit);

               return {$this->factory->getNameSingularStudly()}Resource::collection(\${$this->factory->getNameSingularLower()});
         });
      }
      INDEX;
   }

   private function centralizeIndex(): string
   {
      return <<<INDEX
         public function index(Request \$request): AnonymousResourceCollection
         {
            return \$this->service->index();
         }
      INDEX;
   }


   private function defaultShow(): string
   {
      return <<<SHOW
         public function show(int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            try {
                  \${$this->factory->getNameSingularLower()} = {$this->factory->getNameSingularStudly()}::find(\$id);
                  if (! \${$this->factory->getNameSingularLower()}) {
                     return response('not_found', Response::HTTP_NOT_FOUND);
                  }

                  return new {$this->factory->getNameSingularStudly()}Resource(\${$this->factory->getNameSingularLower()});
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      SHOW;
   }

   private function centralizeAndErrorHandlerShow(): string
   {
      return <<<SHOW
         public function show(int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            return \$this->invokeWithCatching(function () use (\$id) {
                  return \$this->service->show(\$id);
            });
         }
         SHOW;
   }

   private function errorHandlerShow(): string
   {
      return <<<SHOW
         public function show(int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            return \$this->invokeWithCatching(function () use (\$id) {
                  \${$this->factory->getNameSingularLower()} = {$this->factory->getNameSingularStudly()}::find(\$id);
                  if (! \${$this->factory->getNameSingularLower()}) {
                     return response('not_found', Response::HTTP_NOT_FOUND);
                  }

                  return new {$this->factory->getNameSingularStudly()}Resource(\${$this->factory->getNameSingularLower()});
            });
         }
      SHOW;
   }

   private function centralizeShow(): string
   {
      return <<<SHOW
         public function show(int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            try {
               return \$this->service->show(\$id);
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      SHOW;
   }


   private function defaultStore(): string
   {
      return <<<STORE
         public function store({$this->factory->getNameSingularStudly()}Request \$request): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            try {
               return new {$this->factory->getNameSingularStudly()}Resource({$this->factory->getNameSingularStudly()}::create(\$request->validated()));
            } catch (\Exception \$e) {
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      STORE;
   }

   private function centralizeAndErrorHandlerStore(): string
   {
      return <<<STORE
         public function store({$this->factory->getNameSingularStudly()}Request \$request): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request) {
               return \$this->service->store(\$request->validated());
            });
         }
      STORE;
   }

   private function errorHandlerStore(): string
   {
      return <<<STORE
         public function store({$this->factory->getNameSingularStudly()}Request \$request): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request) {
               return new {$this->factory->getNameSingularStudly()}Resource({$this->factory->getNameSingularStudly()}::create(\$request->validated()));
            });
         }
      STORE;
   }

   private function centralizeStore(): string
   {
      return <<<STORE
         public function store(\$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            try {
               return \$this->service->store(\$request->validated());
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      STORE;
   }

   private function defaultUpdate(): string
   {
      return <<<UPDATE
        public function update({$this->factory->getNameSingularStudly()}Request \$request, int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
        {
            try {

               \${$this->factory->getNameSingularLower()} = {$this->factory->getNameSingularStudly()}::find(\$id);
               if (! \${$this->factory->getNameSingularLower()}) {
                  return response('not_found', Response::HTTP_NOT_FOUND);
               }
               \${$this->factory->getNameSingularLower()}->update(\$request->validated());

               return new {$this->factory->getNameSingularStudly()}Resource(\${$this->factory->getNameSingularLower()});
            } catch (\Exception \$e) {
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      UPDATE;
   }

   private function centralizeAndErrorHandlerUpdate(): string
   {
      return <<<UPDATE
         public function update({$this->factory->getNameSingularStudly()}Request \$request, int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request, \$id) {
                  return \$this->service->update(\$id, \$request->validated());
            });
         }

      UPDATE;
   }

   private function errorHandlerUpdate(): string
   {
      return <<<UPDATE
         public function update({$this->factory->getNameSingularStudly()}Request \$request, int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request, \$id) {
               
               \${$this->factory->getNameSingularLower()} = {$this->factory->getNameSingularStudly()}::find(\$id);
               if (! \${$this->factory->getNameSingularLower()}) {
                  return response('not_found', Response::HTTP_NOT_FOUND);
               }
               \${$this->factory->getNameSingularLower()}->update(\$request->validated());

               return new {$this->factory->getNameSingularStudly()}Resource(\${$this->factory->getNameSingularLower()});
            });
         }
         UPDATE;
   }

   private function centralizeUpdate(): string
   {
      return <<<UPDATE
        public function update({$this->factory->getNameSingularStudly()}Request \$request, int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            try {
               return \$this->service->update(\$id, \$request->validated());
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      UPDATE;
   }

   private function defaultDestroy(): string
   {
      return <<<UPDATE
         public function destroy(int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            try {
                  \${$this->factory->getNameSingularLower()} = {$this->factory->getNameSingularStudly()}::find(\$id);
                  if (! \${$this->factory->getNameSingularLower()}) {
                     return response('not_found', Response::HTTP_NOT_FOUND);
                  }
                  \${$this->factory->getNameSingularLower()}->delete();
            } catch (\Exception \$e) {
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      UPDATE;
   }

   private function centralizeAndErrorHandlerDestroy(): string
   {
      return <<<UPDATE
         public function destroy(int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request, \$id) {
                  return \$this->service->destroy(\$id);
            });
         }

      UPDATE;
   }

   private function errorHandlerDestroy(): string
   {
      return <<<UPDATE
         public function destroy(int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
         {
            return \$this->invokeWithCatching(function () use (\$request, \$id) {
               
               \${$this->factory->getNameSingularLower()} = {$this->factory->getNameSingularStudly()}::find(\$id);
               if (! \${$this->factory->getNameSingularLower()}) {
                  return response('not_found', Response::HTTP_NOT_FOUND);
               }
               \${$this->factory->getNameSingularLower()}->delete();
            });
         }
         UPDATE;
   }

   private function centralizeDestroy(): string
   {
      return <<<UPDATE
        public function destroy(int \$id): Response|{$this->factory->getNameSingularStudly()}Resource
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
