<?php

use Illuminate\Support\Str;

if (! function_exists('getRootNamespace')) {
    /**
     * Gets the root namespace of the application.
     *
     * @return string
     */
    function getRootNamespace()
    {
        // Get the RouteServiceProvider instance from the container
        return app()->getNamespace();
    }
}

if (! function_exists('generateApi')) {
    function generateApi($prefix, $force, $apiRoutePath, $errorHandler = null)
    {
        // Load the current contents of api.php
        if (! file_exists($apiRoutePath)) {
            file_put_contents($apiRoutePath, "<?php\n\n");
        }
        $apiRoutesContents = app()->files->get($apiRoutePath);
        $rootNamespace = getRootNamespace();
        $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);
        $controllersDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Controllers');

        $controllersDirectoryNamespace = str_replace('/', '\\', $controllersDirectory);
        $prefixLower = Str::lower($prefix);

        // Check if prefix already exists in api.php
        if (Str::contains($apiRoutesContents, "'prefix' => '{$prefixLower}'")) {
            app()->abort(400, "The prefix '{$prefix}' already exists in api.php");

            return true;
        }
        $controllerClass = $rootNamespace.$controllersDirectoryNamespace.'\\'.Str::studly($prefix).'Controller';

        // Add the new routes to api.php using the array syntax
        $newRoutes = <<<ROUTES
          
          \n// Routes for $prefix
              Route::group(['prefix' => '{$prefix}'], function () {
              Route::get('/', [{$controllerClass}::class, 'index']);
              Route::post('/', [{$controllerClass}::class, 'store']);
              Route::get('/{id}', [{$controllerClass}::class, 'show']);
              Route::put('/{id}', [{$controllerClass}::class, 'update']);
              Route::delete('/{id}', [{$controllerClass}::class, 'destroy']);
          });
          ROUTES;

        $php = <<< PHP
      <?php
      use Illuminate\Support\Facades\Route;

      PHP;

        // Write the new routes to api.php
        if (! Str::contains($apiRoutesContents, '<?php')) {
            app()->files->append($apiRoutePath, $php);
        }
        if (! Str::contains($apiRoutesContents, "use Illuminate\Support\Facades\Route;")) {
            appendUseStatement($apiRoutePath, "Illuminate\Support\Facades\Route");
        }
        app()->files->append($apiRoutePath, $newRoutes);
    }
}

if (! function_exists('getDirectoryFromNamespace')) {
    /**
     * Returns the directory path for a given namespace.
     *
     * This function takes a namespace and converts it to a directory path
     * using the composer.json psr-4 autoloading map. If no matching directory
     * is found, it returns null.
     *
     * @param  string  $namespace  The namespace to convert to a directory path.
     * @return string|null The directory path for the given namespace, or null if not found.
     */
    function getDirectoryFromNamespace($namespace)
    {
        // Ensure namespace is correctly formatted with trailing backslashes
        $namespace = trim($namespace, '\\').'\\';

        // Path to composer.json
        $composerJsonPath = base_path('composer.json');

        // Read composer.json file contents
        $composerConfig = json_decode(file_get_contents($composerJsonPath), true);

        // Check if the psr-4 autoload section is set and not empty
        if (isset($composerConfig['autoload']['psr-4'])) {
            // Iterate through the namespace mappings
            foreach ($composerConfig['autoload']['psr-4'] as $autoloadNamespace => $path) {
                // Check if the given namespace matches the current namespace in the autoloading map
                if (strpos($namespace, $autoloadNamespace) === 0) {
                    // Remove the base namespace part from the given namespace
                    $relativeNamespace = str_replace($autoloadNamespace, '', $namespace);
                    // Convert the relative namespace to a directory path
                    $relativePath = str_replace('\\', '/', $relativeNamespace);

                    // Construct the full directory path and return it
                    return base_path($path.$relativePath);
                }
            }
        }

        // Return null if no matching directory is found
        return null;
    }
}

if (! function_exists('generateControllers')) {
    function generateControllers($prefix, $force, $apiRoutePath, $errorHandler = null, $centralize = null, $factory = null)
    {
        $rootNamespace = getRootNamespace();
        $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);
        $controllersDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Controllers');

        $controllersDirectoryNamespace = str_replace('/', '\\', $controllersDirectory);
        $controllerPath = $nameSpaceRootDirectory.$controllersDirectory.'/'.Str::studly($prefix).'Controller.php';
        if ($errorHandler) {
            generateErrorHandlerTraits();
        }

        if ($centralize) {
            generateCentralizeController();
        }

        file_put_contents($controllerPath, "<?php\n\n // Controller for $prefix\n");

        $makeIndex = makeIndex($prefix, $errorHandler, $centralize);
        $makeShow = makeShow($prefix, $errorHandler, $centralize);
        $makeStore = makeStore($prefix, $errorHandler, $centralize);
        $makeUpdate = makeUpdate($prefix, $errorHandler, $centralize);
        $makeDestroy = makeDestroy($prefix, $errorHandler, $centralize);

        // Namesapces
        // Requests
        $requestsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Requests');
        if (! $requestsDirectory) {
            $requestsDirectory = str_replace('/Controllers', '', $controllersDirectory);
            mkdir($nameSpaceRootDirectory.$requestsDirectory.'/Requests', 0777, true);
            $requestsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Requests');
        }
        $requestsDirectoryNamespace = str_replace('/', '\\', $requestsDirectory);
        $request = $rootNamespace.$requestsDirectoryNamespace.'\\'.Str::studly($prefix).'Request';

        // Resources
        $resourcesDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Resources');
        if (! $resourcesDirectory) {
            $resourcesDirectory = str_replace('/Controllers', '', $controllersDirectory);
            mkdir($nameSpaceRootDirectory.$resourcesDirectory.'/Resources', 0777, true);
            $resourcesDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Resources');
        }
        $resourcesDirectoryNamespace = str_replace('/', '\\', $resourcesDirectory);
        $resource = $rootNamespace.$resourcesDirectoryNamespace.'\\'.Str::studly($prefix).'Resource';

        // Models
        $modelsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Models');
        if (! $modelsDirectory) {
            mkdir($nameSpaceRootDirectory.'/Models', 0777, true);
            $modelsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Models');
        }
        $modelsDirectoryNamespace = str_replace('/', '\\', $modelsDirectory);
        $model = $rootNamespace.$modelsDirectoryNamespace.'\\'.Str::studly($prefix);
        $prefix = Str::studly($prefix);

        $putNewControllers = <<<CONTROLLERS
        <?php
         
        class {$prefix}Controller extends Controller
        {
            //index
            {$makeIndex}

            //show
            {$makeShow}

            //store
            {$makeStore}

            // update
            {$makeUpdate}

            //destroy
            {$makeDestroy}
         }
         
        CONTROLLERS;

        app()->files->put($controllerPath, ltrim($putNewControllers));
        appendUseStatement($controllerPath, $request);
        appendUseStatement($controllerPath, $resource);
        appendUseStatement($controllerPath, $model);
        $errorHandler ?: appendUseStatement($controllerPath, "Illuminate\Routing\Controller");
        appendUseStatement($controllerPath, "Illuminate\Http\Response");
        appendUseStatement($controllerPath, "Illuminate\Http\Request");
        appendUseStatement($controllerPath, "Illuminate\Http\Resources\Json\AnonymousResourceCollection");
        appendUseStatement($controllerPath, "namespace {$rootNamespace}{$controllersDirectoryNamespace}", false);
    }
}

if (! function_exists('findBasesDirectory')) {
    /**
     * Finds the path to a directory with a given name, relative to a start directory.
     *
     * @param  string  $startDirectory  The directory to start searching from
     * @param  string  $name  The name of the directory to search for
     * @return string|null The relative path to the directory, or null if it is not found
     */
    function findBasesDirectory($startDirectory, $name = null)
    {
        // Create a Recursive Directory Iterator
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($startDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        // Iterate through the directory and subdirectories
        foreach ($iterator as $file) {
            if ($file->isDir() && $file->getFilename() === $name) {
                // Return the path to the directory named 'Controllers'
                $realPath = $file->getRealPath();

                // Return the relative path from the start directory to 'Controllers'
                $relativePath = substr($realPath, strlen($startDirectory));

                return $relativePath;
            }
        }

        // Return null if no 'Controllers' directory is found
        return null;
    }
}

if (! function_exists('appendUseStatement')) {
    function appendUseStatement($filePath, $newUseStatement, $prefixUse = true)
    {
        // Read the current contents of the file
        $fileContents = file_get_contents($filePath);
        // Check if the new use statement is already in the file
        if (strpos($fileContents, "use $newUseStatement;") !== false) {
            // The use statement already exists, so we don't need to append it
            return;
        }

        // Append the new use statement after the opening PHP tag
        if ($prefixUse) {
            $newFileContents = preg_replace('/^<\?php\s*/', "<?php\nuse $newUseStatement;\n", $fileContents, 1);
        } else {
            $newFileContents = preg_replace('/^<\?php\s*/', "<?php\n $newUseStatement;\n", $fileContents, 1);
        }

        // Save the new contents back to the file
        file_put_contents($filePath, $newFileContents);
    }
}

if (! function_exists('appendUseTrait')) {
    function appendUseTrait($filePath, $newUseTrait)
    {
        // Read the current contents of the file
        $fileContents = file_get_contents($filePath);

        // Check if the new use statement is already in the file
        if (strpos($fileContents, "use $newUseTrait;") !== false) {
            // The use statement already exists, so we don't need to append it
            return;
        }

        // Regex pattern to match the class declaration
        $pattern = '/class\s+(\w+)\s*{(.*?)}/s';

        // Find the first occurrence of the class declaration
        if (preg_match($pattern, $fileContents, $matches)) {
            $classDeclaration = $matches[0];

            // Extract the class name from the class declaration
            $className = $matches[1];

            // Replacement string with new use statement
            $replacement = "$classDeclaration\nuse $newUseTrait\n\n;";

            // Replace the content by adding the new use statement after the class declaration
            $newFileContents = str_replace($classDeclaration, $replacement, $fileContents);

            // Save the new contents back to the file
            file_put_contents($filePath, $newFileContents);
        }
    }
}

if (! function_exists('makeIndex')) {
    function makeIndex($prefix, $errorHandler, $centralize)
    {
        $prefixStr = Str::studly($prefix);
        $prefix = Str::lower($prefix);

        $modelName = $prefixStr;
        $resourceName = $prefixStr.'Resource';

        $centralizeWithErrorHandler = $centralize ?

            <<<INDEX1
            public function index(Request \$request): AnonymousResourceCollection
            {
               return \$this->errorHandler(function () use (\$request) {
                  return Central::index({$modelName}::class, {$resourceName}::class);
               });
          
            }
      INDEX1
            :
            <<<INDEX1
            public function index(Request \$request): AnonymousResourceCollection
            {
               return \$this->errorHandler(function () use (\$request) {
               \$limit = \$request->query('limit', 11);
  
               \${$prefix}Query = {$modelName}::query();
              
             /**
             * Paginate the results
             *
             * @var \Illuminate\Database\Eloquent\Collection \${$prefix}
             */
             \${$prefix} = \${$prefix}Query->paginate(\$limit);
  
             return {$resourceName}::collection(\${$prefix});   
          });
          
       }
      INDEX1;

        $centralizeWithoutErrorHandler = $centralize ?
            <<<INDEX2
         public function index(Request \$request): AnonymousResourceCollection
         {
          return Central::index({$modelName}::class, {$resourceName}::class);
         }
      INDEX2
            :
            <<<INDEX2
      public function index(Request \$request): AnonymousResourceCollection
      {
          \$limit = \$request->query('limit', 11);
  
          \${$prefix}Query = {$modelName}::query();
              
          /**
          * Paginate the results
          *
          * @var \Illuminate\Database\Eloquent\Collection \${$prefix}
          */
          \${$prefix} = \${$prefix}Query->paginate(\$limit);
  
          return {$resourceName}::collection(\${$prefix});
          }
      INDEX2;

        return $errorHandler ? $centralizeWithErrorHandler : $centralizeWithoutErrorHandler;
    }
}

if (! function_exists('makeUpdate')) {
    function makeUpdate($prefix, $errorHandler, $centralize)
    {
        $prefixStr = Str::studly($prefix);
        $prefix = Str::lower($prefix);

        $requestName = $prefixStr.'Request';
        $modelName = $prefixStr;
        $resourceName = $prefixStr.'Resource';

        $centralizeWithErrorHandler = $centralize ?
            <<<UPDATE1
      public function update({$requestName} \$request, \$id): Response|{$resourceName}
       {
             return \$this->errorHandler(function () use (\$request, \$id) {
               return Central::update({$modelName}::class, {$resourceName}::class, \$id, \$request->validated());
             });
       }
      UPDATE1
            :
            <<<UPDATE1
      public function update({$requestName} \$request, \$id): Response|{$resourceName}
       {
             return \$this->errorHandler(function () use (\$request, \$id) {
                \${$prefix} = {$modelName}::findOrFail(\$id);
                \${$prefix}->update(\$request->validated());
                return new {$resourceName}(\${$prefix});
             });
       }
      UPDATE1;

        $centralizeWithoutErrorHandler = $centralize ?
            <<<UPDATE2
         public function update({$requestName} \$request, \$id): Response|{$resourceName}
         {
            try{
                  return Central::update({$modelName}::class, {$resourceName}::class, \$id, \$request->validated());
               }catch(\Exception \$e){
                  return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
               }
      UPDATE2
            :
            <<<UPDATE2
       public function update({$requestName} \$request, \$id): Response|{$resourceName}
        {
         try{

            \${$prefix} = {$modelName}::find(\$id);
            if (!\${$prefix}) {
               return response('not_found', Response::HTTP_NOT_FOUND);
            }
            \${$prefix}->update(\$request->validated());
            return new {$resourceName}(\${$prefix});
            }catch(\Exception \$e){
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      UPDATE2;

        return $errorHandler ? $centralizeWithErrorHandler : $centralizeWithoutErrorHandler;
    }
}

if (! function_exists('makeStore')) {
    function makeStore($prefix, $errorHandler, $centralize)
    {
        $prefixStr = Str::studly($prefix);
        $prefix = Str::lower($prefix);

        $requestName = $prefixStr.'Request';
        $modelName = $prefixStr;
        $resourceName = $prefixStr.'Resource';

        $centralizeWithErrorHandler = $centralize ?
            <<<STORE1
      public function store({$requestName} \$request): Response|{$resourceName}
      {
         return \$this->errorHandler(function () use (\$request) {
            return Central::store({$modelName}::class, {$resourceName}::class, \$request->validated());  
         });
      }   
      STORE1
            :
            <<<STORE1
      public function store({$requestName} \$request): Response|{$resourceName}
      {
         return \$this->errorHandler(function () use (\$request) {
            return new {$resourceName}({$modelName}::create(\$request->validated()));      
         });
      }   
      STORE1;

        $centralizeWithoutErrorHandler = $centralize ?
            <<<STORE2
         public function store({$requestName} \$request): Response|{$resourceName}
        {
         try{
            return Central::store({$modelName}::class, {$resourceName}::class, \$request->validated());
            }catch(\Exception \$e){
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      STORE2
            :
            <<<STORE2
         public function store({$requestName} \$request): Response|{$resourceName}
         {
            try{
               return new {$resourceName}({$modelName}::create(\$request->validated()));
            }catch(\Exception \$e){
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      STORE2;

        return $errorHandler ? $centralizeWithErrorHandler : $centralizeWithoutErrorHandler;
    }
}

if (! function_exists('makeShow')) {
    function makeShow($prefix, $errorHandler, $centralize)
    {
        $prefixStr = Str::studly($prefix);
        $prefix = Str::lower($prefix);

        $requestName = $prefixStr.'Request';
        $modelName = $prefixStr;
        $resourceName = $prefixStr.'Resource';

        $centralizeWithErrorHandler = $centralize ?
            <<<SHOW1
      public function show(\$id): Response|{$resourceName}
      {
         return \$this->errorHandler(function () use (\$id) {
            return Central::show({$modelName}::class, {$resourceName}::class, \$id);
         });
      }
      SHOW1
            :
            <<<SHOW1
      public function show(\$id): Response|{$resourceName}
      {
         return \$this->errorHandler(function () use (\$id) {
            \${$prefix} = {$modelName}::findOrFail(\$id);
            return new {$resourceName}(\${$prefix});   
         });
      }
      SHOW1;

        $centralizeWithoutErrorHandler = $centralize ?
            <<<SHOW2
      public function show(\$id): Response|{$resourceName}
        {
         try{
            return Central::show({$modelName}::class, {$resourceName}::class, \$id);
            }catch(\Exception \$e){
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      SHOW2
            :
            <<<SHOW2
      public function show(\$id): Response|{$resourceName}
        {
         try{
            \${$prefix} = {$modelName}::find(\$id);
            if (!\${$prefix}) {
               return response('not_found', Response::HTTP_NOT_FOUND);
            }
            return new {$resourceName}(\${$prefix});
            }catch(\Exception \$e){
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }  
      SHOW2;

        return $errorHandler ? $centralizeWithErrorHandler : $centralizeWithoutErrorHandler;
    }
}

if (! function_exists('makeDestroy')) {
    function makeDestroy($prefix, $errorHandler, $centralize)
    {
        $prefixStr = Str::studly($prefix);
        $prefix = Str::lower($prefix);

        $requestName = $prefixStr.'Request';
        $modelName = $prefixStr;
        $resourceName = $prefixStr.'Resource';

        $centralizeWithErrorHandler = $centralize ?
            <<<DESTROY1
      public function destroy(\$id): Response|{$resourceName}
        {
            return \$this->errorHandler(function () use (\$id) {
               return Central::destroy({$modelName}::class, {$resourceName}::class, \$id);
            });
        }
      DESTROY1
            :
            <<<DESTROY1
      public function destroy(\$id): Response|{$resourceName}
        {
            return \$this->errorHandler(function () use (\$id) {
               \${$prefix} = {$modelName}::findOrFail(\$id);
               \${$prefix}->delete();
            });
        }
      DESTROY1;

        $centralizeWithoutErrorHandler = $centralize ?
            <<<DESTROY2
      public function destroy(\$id): Response|{$resourceName}
        {
         try{
            return Central::destroy({$modelName}::class, {$resourceName}::class, \$id);
            }catch(\Exception \$e){
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      DESTROY2
            :
            <<<DESTROY2
      public function destroy(\$id): Response|{$resourceName}
        {
         try{
            \${$prefix} = {$modelName}::find(\$id);
            if (!\${$prefix}) {
               return response('not_found', Response::HTTP_NOT_FOUND);
            }
            \${$prefix}->delete();
            }catch(\Exception \$e){
               return response('internal_server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
      DESTROY2;

        return $errorHandler ? $centralizeWithErrorHandler : $centralizeWithoutErrorHandler;
    }
}

if (! function_exists('generateModels')) {
    function generateModels($prefix, $modelData = null)
    {
        $rootNamespace = getRootNamespace();
        $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);
        $modelsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Models');

        $modelsDirectoryNamespace = str_replace('/', '\\', $modelsDirectory);
        $modelPath = $nameSpaceRootDirectory.$modelsDirectory.'/'.Str::studly($prefix).'.php';

        file_put_contents($modelPath, "<?php\n\n // Model for $prefix\n");

        // Namesapces

        // Models
        if (! $modelsDirectory) {
            mkdir($nameSpaceRootDirectory.'/Models', 0777, true);
            $modelsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Models');
        }
        $model = $rootNamespace.$modelsDirectoryNamespace.'\\'.Str::studly($prefix);
        $prefixLower = Str::lower($prefix);

        // Put prefix in carmel case
        $prefix = Str::studly($prefix);

        // Gnerate modele file content
        $putNewModel = <<<CONTROLLERS
   
        <?php
         use Illuminate\Database\Eloquent\Model;
         
        class {$prefix} extends Model
        {
            protected \$table = '{$prefixLower}';
            
            \n{$modelData['model_code']}

         }
         
        CONTROLLERS;

        // Add content to model file
        app()->files->put($modelPath, ltrim($putNewModel));

        // Add related model namespaces
        foreach ($modelData['model_namespace'] as $model) {
            appendUseStatement($model['directory'], $model['model']);
        }

        // Add namespace allway at the end
        appendUseStatement($modelPath, "Database\\Factories\\{$prefix}Factory");
        appendUseStatement($modelPath, "namespace {$rootNamespace}{$modelsDirectoryNamespace}", false);
    }
}

if (! function_exists('generateResources')) {
    function generateResources($prefix, $databaseFields)
    {
        $resourcesString = "'id' => \$this->resource->id,\n";
        foreach ($databaseFields as $field) {
            $resourcesString .= "'{$field['name']}' => \$this->resource->{$field['name']},\n";
        }
        $rootNamespace = getRootNamespace();
        $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);
        $resourcesDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Resources');

        $controllersDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Controllers');

        $resourcesDirectoryNamespace = str_replace('/', '\\', $resourcesDirectory);
        $resourcePath = $nameSpaceRootDirectory.$resourcesDirectory.'/'.Str::studly($prefix).'Resource.php';

        file_put_contents($resourcePath, "<?php\n\n // Resource for $prefix\n");

        // Namesapces

        if (! $resourcesDirectory) {
            $resourcesDirectory = str_replace('/Controllers', '', $controllersDirectory);
            mkdir($nameSpaceRootDirectory.$resourcesDirectory.'/Resources', 0777, true);
            $resourcesDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Resources');
        }
        $resourcesDirectoryNamespace = str_replace('/', '\\', $resourcesDirectory);
        $resource = $rootNamespace.$resourcesDirectoryNamespace.'\\'.Str::studly($prefix).'Resource';

        // Put prefix in carmel case
        $prefix = Str::studly($prefix);

        $putNewModel = <<<CONTROLLERS
   
        <?php
         namespace {$rootNamespace}{$resourcesDirectoryNamespace};
         use Illuminate\Http\Resources\Json\JsonResource;
         
         
        class {$prefix}Resource extends JsonResource
        {
            public function toArray(\$request): array
            {
                 return [
                  {$resourcesString}
                 ];

           }
        

         }
         
        CONTROLLERS;

        app()->files->put($resourcePath, ltrim($putNewModel));
    }
}

if (! function_exists('generateRequests')) {
    function generateRequests($prefix, $databaseFields)
    {
        $prefixLower = Str::lower($prefix);
        $rulesString = '';
        foreach ($databaseFields as $field) {
            $required = isset($field['nullable']) && $field['nullable'] === 'yes' ? 'nullable' : 'required';
            $type = isset($field['type']) ? '|'.$field['type'] : '';
            $length = isset($field['length']) ? '|'.$field['length'] : '';
            $unique = isset($field['unique']) && $field['unique'] === 'yes' ? '|'.'unique:'.$prefixLower.','.$field['name'] : '';
            $rulesString .= "'{$field['name']}' => '{$required}{$type}{$length}{$unique},',\n";
        }

        $rootNamespace = getRootNamespace();
        $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);
        $requestsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Requests');

        $controllersDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Controllers');

        $requestsDirectoryNamespace = str_replace('/', '\\', $requestsDirectory);
        $requestPath = $nameSpaceRootDirectory.$requestsDirectory.'/'.Str::studly($prefix).'Request.php';

        file_put_contents($requestPath, "<?php\n\n // Request for $prefix\n");

        // Namesapces

        if (! $requestsDirectory) {
            $requestsDirectory = str_replace('/Controllers', '', $controllersDirectory);
            mkdir($nameSpaceRootDirectory.$requestsDirectory.'/Requests', 0777, true);
            $requestsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Requests');
        }
        $requestsDirectoryNamespace = str_replace('/', '\\', $requestsDirectory);
        $request = $rootNamespace.$requestsDirectoryNamespace.'\\'.Str::studly($prefix).'Request';

        // Put prefix in carmel case
        $prefix = Str::studly($prefix);

        $putNewModel = <<<CONTROLLERS
   
        <?php
         namespace {$rootNamespace}{$requestsDirectoryNamespace};
         use Illuminate\Contracts\Validation\Validator;
         use Illuminate\Foundation\Http\FormRequest;
         use Illuminate\Http\Exceptions\HttpResponseException;
         
        class {$prefix}Request extends FormRequest
        {

            public function authorize()
            {
                return true;
            }

            /**
            * @return string[]
            */
            public function rules(): array
            {
               if (\$this->isMethod(FormRequest::METHOD_GET)) {
                  return [];
                  }

                  \$rules = [
                     {$rulesString}
                  ];

                  if (\$this->isMethod(FormRequest::METHOD_PUT)) {
                     \$rules = array_merge(\$rules, []);
                  }

                  return \$rules;
               }

               /**
               * @return mixed
               */
               public function failedValidation(Validator \$validator)
               {
                  throw new HttpResponseException(
                     response('bad_request', 400)
                  );
            }

         }
         
        CONTROLLERS;

        app()->files->put($requestPath, ltrim($putNewModel));
    }
}

if (! function_exists('generateMigrations')) {
    function generateMigrations($prefix, $schema)
    {
        $rootNamespace = getRootNamespace();
        $migrationsDirectory = findBasesDirectory(base_path().'/database', 'migrations');

        // Namesapces

        if (! $migrationsDirectory) {
            $migrationsDirectory = findBasesDirectory(base_path(), 'migrations');
            if (! $migrationsDirectory) {
                mkdir(base_path().'/database/migrations', 0777, true);
                $migrationsDirectory = findBasesDirectory(base_path().'/database', 'migrations');
                $migrationsDirectory = base_path('database'.$migrationsDirectory);
            }
        } else {
            $migrationsDirectory = base_path('database'.$migrationsDirectory);
        }

        $prefixLower = Str::lower($prefix);

        $migrationFileNamePattern = "*_create_{$prefixLower}.php";

        // Check if a migration file already exists
        $existingMigrationFiles = glob($migrationsDirectory.'/'.$migrationFileNamePattern);

        // If file do not exists create it
        if (! $existingMigrationFiles) {
            $migrationsPath = $migrationsDirectory.'/'.date('Y_m_d_His').'_create_'.Str::lower($prefix).'.php';
            file_put_contents($migrationsPath, "<?php\n\n // Migration for $prefix\n");
            $existingMigrationFiles = [$migrationsPath];
        }

        $putNewModel = <<<CONTROLLERS
   
        <?php
         use Illuminate\Database\Migrations\Migration;
         use Illuminate\Database\Schema\Blueprint;
         use Illuminate\Support\Facades\Schema;
         
        return new class extends Migration
        {

            /**
            * Run the migrations.
            */

            public function up(): void
            {
                {$schema}
            }

            /**
            *Reverse the migrations.
            *
            * @return void
            */
            public function down()
            {
               Schema::dropIfExists('{$prefixLower}');
            }
         };
         
        CONTROLLERS;

        app()->files->put(current($existingMigrationFiles), ltrim($putNewModel));
    }

    if (! function_exists('checkIfTimestanpsExists')) {
        function checkIfTimestampsExists($prefix)
        {
            $prefixLower = Str::lower($prefix);
            $migrationsDirectory = findBasesDirectory(base_path().'/database', 'migrations');

            if ($migrationsDirectory) {
                $migrationsDirectory = base_path('database'.$migrationsDirectory);
            } else {
                $migrationsDirectory = base_path($migrationsDirectory);
            }

            $migrationFileNamePattern = "*_create_{$prefixLower}.php";

            // Check if a migration file already exists
            $existingMigrationFiles = current(glob($migrationsDirectory.'/'.$migrationFileNamePattern));

            // Assuming $existingMigrationFiles contains the full path to the migration file
            if ($existingMigrationFiles) {
                $migrationContent = file_get_contents($existingMigrationFiles);

                // Now check if the string '$table->timestamps()' exists in the file content
                $containsTimestamps = strpos($migrationContent, '$table->timestamps()') !== false;

                return $containsTimestamps;
            }

            return false;
        }
    }

    // Remove test created files
    if (! function_exists('removeTestCreatedFiles')) {
        function removeTestDirectory()
        {
            $rootNamespace = getRootNamespace();
            $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);

            // Remove migrations files
            $migrationsDirectory = findBasesDirectory(base_path().'/database', 'migrations');
            $migrationsDirectory = base_path('database'.$migrationsDirectory);
            $files = glob($migrationsDirectory.'/*');

            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            // Remove api.php content
            $apiFilePath = base_path('routes/api.php');
            if (file_exists($apiFilePath)) {
                file_put_contents($apiFilePath, '');
            }

            // Remove controllers directory files
            $controllersDirectory = $nameSpaceRootDirectory.'Controllers';
            $files = glob($controllersDirectory.'/*');

            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            // Remove models directory files
            $modelsDirectory = $nameSpaceRootDirectory.'Models';
            $files = glob($modelsDirectory.'/*');

            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            // Remove resources directory files
            $resourcesDirectory = $nameSpaceRootDirectory.'Resources';
            $files = glob($resourcesDirectory.'/*');

            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            // Remove requests directory files
            $requestsDirectory = $nameSpaceRootDirectory.'Requests';
            $files = glob($requestsDirectory.'/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }
}

if (! function_exists('generateErrorHandlerTraits')) {
    function generateErrorHandlerTraits()
    {
        $rootNamespace = getRootNamespace();
        $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);
        $controllersDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Controllers');

        $controllersDirectoryNamespace = str_replace('/', '\\', $controllersDirectory);
        $controllerTraiterPath = $nameSpaceRootDirectory.$controllersDirectory.'/Handler.php';
        $controllerPath = $nameSpaceRootDirectory.$controllersDirectory.'/Controller.php';

        $handlerTrait = <<<HANDLER
      <?php
        trait Handler
        {
         protected function errorHandler(\\Closure \$callable)
         {
          try {
            return \$callable();
            } catch (ModelNotFoundException \$e) {
                return response('not_found', Response::HTTP_NOT_FOUND);
            } catch (QueryException \$e) {
                return response('not_found', Response::HTTP_NOT_FOUND);
            } catch (\Exception \$e) {
            if (\$e->getCode() === 404) {
                return response('not_found', Response::HTTP_NOT_FOUND);
            }
            if (\$e->getCode() === 403) {
                return response(\$e->getMessage(), Response::HTTP_FORBIDDEN);
            }
            if (\$e->getCode() === 422) {
                return response(\$e->getMessage(), Response::HTTP_FORBIDDEN);
            }
            return response(\$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
         }
        }
      HANDLER;

        $controller = <<<'CONTROLLER'
      <?php
         class Controller extends BaseController
         {
            use AuthorizesRequests, Handler, ValidatesRequests;
         }
      CONTROLLER;

        // Traits
        if (! file_exists($controllerTraiterPath)) {
            app()->files->put($controllerTraiterPath, ltrim($handlerTrait));
            appendUseStatement($controllerTraiterPath, "Illuminate\Database\Eloquent\ModelNotFoundException");
            appendUseStatement($controllerTraiterPath, "Illuminate\Database\QueryException");
            appendUseStatement($controllerTraiterPath, "Illuminate\Http\Response");
            appendUseStatement($controllerTraiterPath, "namespace {$rootNamespace}{$controllersDirectoryNamespace}", false);
        }

        // Controller
        if (! file_exists($controllerPath)) {
            app()->files->put($controllerPath, ltrim($controller));
            appendUseStatement($controllerPath, "Illuminate\Foundation\Auth\Access\AuthorizesRequests");
            appendUseStatement($controllerPath, "Illuminate\Foundation\Validation\ValidatesRequests");
            appendUseStatement($controllerPath, "Illuminate\Routing\Controller as BaseController");
            appendUseStatement($controllerPath, "namespace {$rootNamespace}{$controllersDirectoryNamespace}", false);
        }
    }
}

if (! function_exists('getAppDirectory')) {
    function getAppDirectory()
    {
        $rootNamespace = getRootNamespace();

        return getDirectoryFromNamespace($rootNamespace);
    }
}

if (! function_exists('format')) {
    function format($path)
    {
        $command = escapeshellcmd('./vendor/bin/pint '.escapeshellarg($path));
        exec($command, $output, $returnCode);
    }
}

if (! function_exists('generateCentralizeController')) {
    function generateCentralizeController()
    {
        $rootNamespace = getRootNamespace();
        $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);
        $controllersDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Controllers');

        $controllersDirectoryNamespace = str_replace('/', '\\', $controllersDirectory);
        $centralizePath = $nameSpaceRootDirectory.$controllersDirectory.'/Central.php';

        $index = getCentralIndex();
        $show = getCentralShow();
        $store = getCentralStore();
        $update = getCentralUpdate();
        $destroy = getCentralDestroy();

        $centralClass = <<<CENTRAL
      <?php
        class Central
        {
            {$index}

            {$show}

            {$store}

            {$update}

            {$destroy}
        }
      CENTRAL;

        // Central
        if (! file_exists($centralizePath)) {
            app()->files->put($centralizePath, ltrim($centralClass));
            appendUseStatement($centralizePath, "Illuminate\Http\Response");
            appendUseStatement($centralizePath, "namespace {$rootNamespace}{$controllersDirectoryNamespace}", false);
        }
    }
}

if (! function_exists('getCentralIndex')) {
    function getCentralIndex()
    {
        $centralIndex = <<<'INDEX'
         public static function index($model, $resource)
         {
            $limit = request()->integer('limit');
            $limit = $limit > 0 ? request()->integer('limit') : 10;
            $query = call_user_func([$model, 'query']);
            $query = $query->paginate($limit);
            return call_user_func([$resource, 'collection'], $query);
         }
      INDEX;

        return $centralIndex;
    }
}

if (! function_exists('getCentralShow')) {
    function getCentralShow()
    {
        $centralShow = <<<SHOW
            public static function show(\$model, \$resource, \$id)
            {
               \$model = call_user_func([\$model, 'findOrFail'], (int) \$id);
               return (new \ReflectionClass(\$resource))->newInstanceArgs([\$model]);
            }
      SHOW;

        return $centralShow;
    }
}

if (! function_exists('getCentralStore')) {
    function getCentralStore()
    {
        $centralStore = <<<STORE
               public static function store(\$model, \$resource, array \$data)
               {
                  return (new \ReflectionClass(\$resource))->newInstanceArgs([call_user_func([\$model, 'create'], \$data)]);
               }
         STORE;

        return $centralStore;
    }
}

if (! function_exists('getCentralUpdate')) {
    function getCentralUpdate()
    {
        $centralUpdate = <<<UPDATE
                           public static function update(\$model, \$resource, int \$id, array \$data)
                           {
                              \$model = call_user_func([\$model, 'findOrFail'], (int) \$id);
                              if (count(\$data)) {
                                 \$model->update(\$data);
                              }
                              return (new \ReflectionClass(\$resource))->newInstanceArgs([\$model]);
                           }
         UPDATE;

        return $centralUpdate;
    }
}

if (! function_exists('getCentralDestroy')) {
    function getCentralDestroy()
    {
        $centralDestroy = <<<'DESTROY'
                              public static function destroy($model, int $id)
                              {
                                 $model = call_user_func([$model, 'find'], (int) $id);

                                 if ($model) {
                                    $model->delete();
                                 }
                                 return response('success', Response::HTTP_OK);
                              }
         DESTROY;

        return $centralDestroy;
    }
}
