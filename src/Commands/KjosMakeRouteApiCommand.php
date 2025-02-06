<?php

namespace Kjos\Command\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'kjos:make:api')]
class KjosMakeRouteApiCommand extends GeneratorCommand
{
    /**
     * Console command name
     *
     * @var string
     */
    protected $name = 'kjos:make:api';

    /**
     * Console command description
     *
     * @var string
     */
    protected $description = 'Create new site for installed app by creating the specific .env file and storage dirs.';

    protected $signature = 'kjos:make:api 
    {name : the prefix of the site}
    {--f|force : Force api creation if it already exists}
    {--eh|errorhandler : Enable error handling mode}
    {--c|centralize : Enable centralize mode}
    {--factory : Generate factory for model}
    {--t|test : Generate tests for the api}';

    private ?array $runtimeDatas = [];

    public function handle()
    {
        $prefix = $this->argument('name');
        $apiRoutePath = base_path('routes/api.php');
        $force = $this->option('force');
        $errorHandler = $this->option('errorhandler');
        $centralize = $this->option('centralize');
        $factory = $this->option('factory');
        $test = $this->option('test');

        // Add new routes to api.php
        generateApi($prefix, $force, $apiRoutePath);

        // Add Corresponding controller file
        generateControllers($prefix, $force, $apiRoutePath, $errorHandler, $centralize, $factory);

        // Questions
        $this->askSomesQuestions($prefix);

        $this->info("API routes for {$prefix} prefix have been added.");

        // Format code
        format($apiRoutePath);
        format(getAppDirectory());
        format(base_path('database'));
        format(base_path('tests'));

        return false;
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return '';
    }

    protected function askSomesQuestions($prefix)
    {
        $databaseFields = [];

        $loop = 0;
        while ($createDataBaseFields = $this->ask('Do you want to create database fields?', 'yes') === 'yes') {
            $databaseFields[] = $this->fieldsQuestion($prefix, $loop);
            $loop++;
        }

        // if ($createDataBaseFields !== 'no' && $createDataBaseFields !== 'yes') {
        //     $this->info('input not valid.');
        //     $this->askSomesQuestions($prefix);
        // }

        // Add models
        $generateModel = $this->generateModel($prefix, $databaseFields);
        generateModels($prefix, $generateModel);
        // Add migrations
        generateMigrations($prefix, $this->generateSchema($prefix, $databaseFields));

        // Add resources
        generateResources($prefix, $databaseFields);

        // Add Requests
        generateRequests($prefix, $databaseFields);

        if ($this->option('test')) {
            $this->generateTest($prefix, $databaseFields);
        }
    }

    protected function fieldsQuestion($prefix, $loop = null): ?array
    {
        $fieldTypes = [
            'bigIncrements',
            'bigInteger',
            'binary',
            'boolean',
            'char',
            'date',
            'dateTime',
            'dateTimeTz',
            'decimal',
            'double',
            'enum',
            'float',
            'geometry',
            'geometryCollection',
            'increments',
            'integer',
            'ipAddress',
            'json',
            'jsonb',
            'lineString',
            'longText',
            'macAddress',
            'mediumIncrements',
            'mediumInteger',
            'mediumText',
            'morphs',
            'multiLineString',
            'multiPoint',
            'multiPolygon',
            'nullableMorphs',
            'nullableTimestamps',
            'nullableUuidMorphs',
            'point',
            'polygon',
            'rememberToken',
            'set',
            'smallIncrements',
            'smallInteger',
            'softDeletes',
            'softDeletesTz',
            'string',
            'text',
            'time',
            'timeTz',
            'timestamp',
            'timestampTz',
            'timestamps',
            'tinyIncrements',
            'tinyInteger',
            'tinyText',
            'unsignedBigInteger',
            'unsignedDecimal',
            'unsignedInteger',
            'unsignedMediumInteger',
            'unsignedSmallInteger',
            'unsignedTinyInteger',
            'uuid',
            'uuidMorphs',
            'year',
        ];

        $fields = [];

        // ask for field's type
        $selectedType = $this->choice(
            'use arrow to select your database field type. Ex: string',
            $fieldTypes,
            $defaultIndex = 0 // Optional: The index of the default option
        );
        $fields['type'] = $selectedType;

        // for certain field types like 'string' or 'char', you may want to ask for a length
        if (in_array($selectedType, ['string', 'char'])) {
            do {
                $length = $this->ask('Enter the field length. Ex: 255');
            } while (! is_numeric($length));
            $fields['length'] = $length;
        }

        // ask for field's name
        do {
            $input = $this->ask('Enter your database field name. Ex: name');
        } while (! $input);
        $fields['name'] = $input;

        // ask for field nullable
        do {
            $input = $this->ask('Field is nullable?', 'yes');
        } while ($input !== 'yes' && $input !== 'no');
        $fields['nullable'] = $input;

        // ask for field unique
        do {
            $input = $this->ask('Field is unique?', 'no');
        } while ($input !== 'yes' && $input !== 'no');
        $fields['unique'] = $input;

        // ask for field indexed
        do {
            $input = $this->ask('Field is can be indexed?', 'no');
        } while ($input !== 'yes' && $input !== 'no');
        $fields['indexed'] = $input === 'yes' ? true : false;

        // ask for a default value if needed
        if ($this->ask('Does the field have a default value?', 'no') === 'yes') {
            $defaultValue = $this->ask('Enter the default value:');
            $fields['default'] = $defaultValue;
        }

        // ask if timestamps should be added (created_at and updated_at)
        if ($loop === 0) {
            $this->runtimeDatas['timestamps_exists'] = false;
            if ($this->ask('Should the table have timestamps (created_at and updated_at)?', 'yes') === 'yes') {
                $fields['timestamps'] = true;
                $this->runtimeDatas['timestamps_exists'] = true;
            }
        } else {
            if (! $this->runtimeDatas['timestamps_exists']) {
                if ($this->ask('Should the table have timestamps (created_at and updated_at)?', 'yes') === 'yes') {
                    $fields['timestamps'] = true;
                    $this->runtimeDatas['timestamps_exists'] = true;
                }
            }
        }

        // ask if the field should have a comment
        if ($this->ask('Would you like to add a comment to the field?', 'no') === 'yes') {
            $comment = $this->ask('Enter the field comment');
            $fields['comment'] = $comment;
        }

        // ask if the field is a foreign key
        if ($this->ask('Is the field a foreign key?', 'no') === 'yes') {
            $relatedTable = $this->ask('Enter the related table name:');
            $relatedField = $this->ask("Enter the related table field name, typically 'id':", 'id');
            $fields['foreign'] = [
                'table' => $relatedTable,
                'field' => $relatedField,
            ];
        }

        return $fields;
    }

    protected function generateSchema($prefix, $fields): ?string
    {
        $prefix = Str::lower($prefix);
        // Generate schema
        $schema = "Schema::create('{$prefix}', function (Blueprint \$table) {\n";
        $schema .= "            \$table->id();\n";

        foreach ($fields as $field) {
            $line = '            $table->'.$field['type']."('".$field['name']."')";

            if (isset($field['length'])) {
                $line .= '('.$field['length'].')';
            }

            if (isset($field['nullable']) && $field['nullable'] === true) {
                $line .= '->nullable()';
            }

            if (isset($field['default'])) {
                $line .= "->default('".$field['default']."')";
            }

            if (isset($field['unsigned']) && $field['unsigned'] === true) {
                $line .= '->unsigned()';
            }

            if (isset($field['indexed']) && $field['indexed'] === true) {
                $line .= '->index()';
            }

            if (isset($field['unique']) && $field['unique'] === true) {
                $line .= '->unique()';
            }

            if (isset($field['foreign'])) {
                $line .= ";\n            \$table->foreign('".$field['name']."')->references('".$field['foreign']['field']."')->on('".$field['foreign']['table']."')";
            }

            if (isset($field['comment'])) {
                $line .= "->comment('".addslashes($field['comment'])."')";
            }

            $schema .= $line.";\n";
        }

        if (isset($fields[0]['timestamps']) && $fields[0]['timestamps'] === true) {
            $schema .= "            \$table->timestamps();\n";
        }

        $schema .= '        });';

        // Optionally, you can save the schema to a migration file
        // ...

        return $schema;
    }

    protected function generateModel($modelName, $fields): ?array
    {
        $modelName = Str::studly($modelName);
        $fillable = [];
        $relationships = [];
        $modelNamesapce = [];

        // Generate namespaces
        // Models
        $rootNamespace = getRootNamespace();
        $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);
        $modelsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Models');
        if (! $modelsDirectory) {
            mkdir($nameSpaceRootDirectory.'/Models', 0777, true);
            $modelsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Models');
        }
        $modelsDirectoryNamespace = str_replace('/', '\\', $modelsDirectory);

        // Add fillable fields
        foreach ($fields as $field) {
            $fillable[] = "'".$field['name']."'";
        }
        $modelCode = '    protected $fillable = ['.implode(', ', $fillable)."];\n";

        // Add factory
        if ($this->option('factory')) {
            $modelCode .= "\n    protected static function newFactory()\n    {\n";
            $modelCode .= '        return '.Str::studly($modelName)."Factory::new();\n";
            $modelCode .= "    }\n";

            $this->generateFactory($modelName, $fields);
        }

        // Add relationship methods
        foreach ($fields as $field) {
            if (isset($field['foreign'])) {
                $relationshipMethod = Str::camel(Str::singular($field['foreign']['table']));
                $modelCode .= "\n    public function {$relationshipMethod}()\n    {\n";
                $modelCode .= '        return $this->belongsTo('.Str::studly($field['foreign']['table'])."::class, '".$field['name']."');\n";
                $modelCode .= "    }\n";
                $relationships[] = $relationshipMethod;

                $modelNamesapce[] = [
                    'model' => $rootNamespace.$modelsDirectoryNamespace.'\\'.Str::studly($field['foreign']['table']),
                    'directory' => $nameSpaceRootDirectory.'Models/'.$modelName.'.php',
                ];
            }
        }

        return ['model_code' => $modelCode, 'model_namespace' => $modelNamesapce];
    }

    protected function generateFakeData($columnType)
    {
        $faker = Faker::create();

        switch ($columnType) {
            // Types numériques
            case 'bigIncrements':
            case 'increments':
            case 'tinyIncrements':
            case 'mediumIncrements':
                return $faker->unique()->numberBetween(1, 999999);

            case 'bigInteger':
            case 'integer':
            case 'mediumInteger':
            case 'smallInteger':
            case 'tinyInteger':
            case 'unsignedBigInteger':
            case 'unsignedInteger':
            case 'unsignedMediumInteger':
            case 'unsignedSmallInteger':
            case 'unsignedTinyInteger':
                return $faker->randomNumber();

                // Types booléens
            case 'boolean':
                return $faker->boolean;

                // Types chaîne de caractères
            case 'char':
            case 'string':
                return $faker->word;

                // Textes longs
            case 'text':
            case 'longText':
                return $faker->paragraph;

                // Décimal / Flottants
            case 'decimal':
            case 'float':
            case 'double':
                return $faker->randomFloat(2, 1, 100);

                // Types énumérés (à adapter selon tes options spécifiques)
            case 'enum':
                return $faker->randomElement(['option1', 'option2', 'option3']);

                // Dates et heures
            case 'date':
            case 'dateTime':
            case 'dateTimeTz':
                return $faker->dateTime;

            case 'time':
            case 'timeTz':
                return $faker->time;

            case 'timestamp':
            case 'timestampTz':
                return $faker->dateTime;

                // JSON et autres types complexes
            case 'json':
                return $faker->randomElement([json_encode(['key' => 'value']), json_encode(['foo' => 'bar'])]);

                // Identifiants uniques
            case 'uuid':
                return $faker->uuid;

            case 'macAddress':
                return $faker->macAddress;

            case 'ipAddress':
                return $faker->ipv4;

                // Géométrie (peut être complexe selon ton utilisation)
            case 'point':
            case 'polygon':
            case 'geometry':
            case 'geometryCollection':
            case 'multiLineString':
            case 'multiPoint':
            case 'multiPolygon':
                // Pour les géométries, utilise des valeurs génériques ou un générateur spécifique
                return 'POINT('.$faker->latitude.' '.$faker->longitude.')';

            case 'set':
                return $faker->randomElement(['a', 'b', 'c', 'd']); // Exemple de set

                // Champs personnalisés sans type spécifique
            case 'name':
                return $faker->name;

            case 'firstname':
                return $faker->firstName;

            case 'lastname':
                return $faker->lastName;

            case 'email':
                return $faker->unique()->safeEmail;

            case 'password':
                return bcrypt('password');

            case 'rememberToken':
                return Str::random(10);

                // Si le type est non reconnu, retourner null
            default:
                return null;
        }
    }

    protected function generateFactory($modelName, $fields)
    {
        $rootNamespace = getRootNamespace();
        $nameSpaceRootDirectory = getDirectoryFromNamespace($rootNamespace);

        $model = Str::studly($modelName);
        $factoriesDirectory = base_path('database/factories').'/'.$model.'Factory.php';

        // Models
        $modelsDirectory = findBasesDirectory($nameSpaceRootDirectory, 'Models');

        $modelsDirectoryNamespace = str_replace('/', '\\', $modelsDirectory);
        $modelNamespace = $rootNamespace.$modelsDirectoryNamespace.'\\'.$model;

        $definition = [];
        foreach ($fields as $field) {
            if ($field['name'] == 'email') {
                $type = 'email';
            } elseif ($field['name'] == 'password') {
                $type = 'password';
            } elseif ($field['name'] == 'lastname') {
                $type = 'lastname';
            } elseif ($field['name'] == 'firstname') {
                $type = 'firstname';
            } else {
                $type = $field['type'];
            }

            $definition[$field['name']] = $this->generateFakeData($type);
        }
        $definitionString = var_export($definition, true);

        $factory = <<<FACTORY
        <?php

            class {$model}Factory extends Factory
            {
                
                protected \$model = {$model}::class;

                /**
                * Define the model's default state.
                *
                * @return array<string, mixed>
                */
                public function definition(): array
                {
                    return $definitionString;
                }
            }
        FACTORY;

        if (! File::exists($factoriesDirectory)) {
            File::put($factoriesDirectory, ltrim($factory));
        }

        appendUseStatement($factoriesDirectory, $modelNamespace);
        appendUseStatement($factoriesDirectory, "Illuminate\Database\Eloquent\Factories\Factory");
        appendUseStatement($factoriesDirectory, "namespace Database\Factories", false);
    }

    protected function generateTest($modelName, $fields)
    {
        // Create tests/Datasets and tests/Feature directories if they don't exist
        $testsDirectory = base_path('tests');
        if (! File::exists($testsDirectory)) {
            File::makeDirectory($testsDirectory);
        }
        $testsDirectory = base_path('tests/Datasets');
        if (! File::exists($testsDirectory)) {
            File::makeDirectory($testsDirectory);
        }
        $testsDirectory = base_path('tests/Feature');
        if (! File::exists($testsDirectory)) {
            File::makeDirectory($testsDirectory);
        }

        // Generate tests/Datasets/{$modelName}s.php
        $this->generateDataset($modelName, $fields);

        // Generate tests/Feature/{$modelName}Test.php
        $this->generateTestFeature($modelName, $fields);
    }

    public function generateDataset($modelName, $fields)
    {
        $model = Str::studly($modelName);
        $datasetFile = base_path('tests/Datasets/'.$model.'s.php');
        $singularModel = Str::lower(Str::singular($model));

        $foreignkeysDatas = $this->getModelRelationsRecursive($model);
        $createRelations = $foreignkeysDatas['create'] ?? '';
        $makeRelations = $foreignkeysDatas['make'] ?? '';
        $nameSpaces = $foreignkeysDatas['namespaces'] ?? '';

        // generate foreign key Model
        $datasetString = <<<DATASET
        <?php

            {$nameSpaces};

           dataset('created {$singularModel}', [
                fn () => {$model}::factory()
                {$createRelations}
                ->createOne(),
            ]);

            dataset('created {$singularModel}s', [
                fn () => {$model}::factory()->count(30)
                {$createRelations}
                ->create(),
            ]);

            dataset('maked {$singularModel}', [
                fn () => {$model}::factory()
                {$makeRelations}
                ->makeOne(),
            ]);           
        DATASET;

        if (! File::exists($datasetFile)) {
            File::put($datasetFile, ltrim($datasetString));
        }
    }

    public function getModelRelationsRecursive($modelName, &$processedModels = [], &$notFoundModel = ['exist' => true, 'path' => ''])
    {
        $createRelation = '';
        $makeRelation = '';
        $allModelsNameSpace = '';

        $modelMethods = get_class_methods(Model::class);

        $model = Str::studly($modelName);
        $modelNamespace = getNameSpace('Models', $model);

        if (! class_exists($modelNamespace) || in_array($modelNamespace, $processedModels)) {
            return ['create' => '', 'make' => ''];
        }

        $allModelsNameSpace .= <<<NAMESAPCE
                                use {$modelNamespace};
                            NAMESAPCE.PHP_EOL;

        // Ajouter le modèle aux modèles traités pour éviter les boucles infinies
        $processedModels[] = $modelNamespace;

        $modelInstance = app()->make($modelNamespace);

        foreach (get_class_methods($modelInstance) as $method) {
            // Ignorer les méthodes de modèle par défaut de Laravel
            if (in_array($method, $modelMethods)) {
                continue;
            }

            $this->createModelClassIfNotExists(getNameSpace('Models', Str::studly($method)), $notFoundModel);
            $relation = $modelInstance->$method();

            if ($relation instanceof Relation) {
                $relatedModel = class_basename($relation->getRelated());

                // Appel récursif pour récupérer les relations imbriquées
                $this->getModelRelationsRecursive($relatedModel, $processedModels, $notFoundModel);

                $nameSpace = getNameSpace('Models', $relatedModel);

                $modelComment = $notFoundModel['exist'] ? '' : "//The {$relatedModel} model was not found in the project. Please create it.";

                $allModelsNameSpace .= <<<NAMESAPCE
                        use {$nameSpace};
                    NAMESAPCE.PHP_EOL;

                // Construction des relations
                $createRelation .= <<<RELATION
                    ->for(
                        {$relatedModel}::factory() {$modelComment}
                    )
                    RELATION.PHP_EOL;

                $makeRelation .= <<<RELATION
                    ->for(
                        {$relatedModel}::factory() {$modelComment}
                        ->createOne()
                    )
                    RELATION.PHP_EOL;

                // Delete the previously created not founded model
                if (! $notFoundModel['exist']) {
                    File::delete($notFoundModel['path']);
                }
            }
        }

        return ['create' => $createRelation, 'make' => $makeRelation, 'namespaces' => $allModelsNameSpace];
    }

    private function createModelClassIfNotExists($modelNamespace, &$notFoundModel)
    {
        $modelName = Str::studly(class_basename($modelNamespace));
        $modelPath = getPathFromNamespace('Models', Str::studly($modelName).'.php');
        if (file_exists($modelPath)) {
            return;
        }

        $modelTemplate = <<<PHP
        <?php

        namespace App\Models;

        use Illuminate\Database\Eloquent\Model;

        class {$modelName} extends Model
        {}
        PHP;

        if (! file_exists($modelPath)) {
            $notFoundModel['exist'] = false;
            $notFoundModel['path'] = $modelPath;
            file_put_contents($modelPath, $modelTemplate);
        }
    }

    public function generateTestFeature($modelName, $fields)
    {
        $modelNamespace = getNameSpace('Models', Str::studly($modelName));
        $modelInstance = app()->make($modelNamespace);

        $structure = array_merge([$modelInstance->getKeyName()], array_map(function ($field) {
            return $field['name'];
        }, $fields));

        $model = Str::studly($modelName);
        $path = base_path('tests/Feature/'.$model.'Test.php');

        // Generate tests/Feature/{$modelName}Test.php
        $storeTestDatas = $this->generateStoreTestDatas($model, $fields);
        $updateTestDatas = $this->generateUpdateTestDatas($model, $fields, $modelInstance);
        $showTestDatas = $this->generateShowTestDatas($model, $fields, $modelInstance);
        $deleteTestDatas = $this->generateDeleteTestDatas($model, $fields, $modelInstance);
        $listTestDatas = $this->generateIndexTestDatas($model, $fields, $modelInstance);
        $structureString = "[\n    '".implode("',\n    '", $structure)."'\n]";

        $testDatas = <<<TEST
            <?php

            \$structure = {$structureString};

            //tests/Feature/{$model}Test.php
            
            {$storeTestDatas}
            
            {$updateTestDatas}

            {$showTestDatas}

            {$deleteTestDatas}

            {$listTestDatas}
        TEST;

        app()->files->put($path, ltrim($testDatas));
    }

    private function generateValidation($lowerCaseModel, $fields, $method = 'post')
    {
        $validationDatas = '';
        $id = '';
        if ($method == 'put') {
            $id = '/\$created->{$modelKey}';
        }

        foreach ($fields as $field) {
            $type = $field['type'];
            $typeValue = generateOppositeValue($type);
            $name = $field['name'];
            $validationDatas .= <<< DESCRIBE
                //Validation {$name}
                 \$maked->{$name} = {$typeValue};
                 \$response = \$this->{$method}('/api/{$lowerCaseModel}s{$id}', \$maked->toArray());
                 \$response->assertBadRequest();
            DESCRIBE.PHP_EOL;
        }

        return $validationDatas;
    }

    private function generateStoreTestDatas($model, $fields)
    {
        $modelNamespace = getNameSpace('Models', Str::studly($model));
        $lowerCaseModel = Str::lower($model);
        $validationDatas = $this->generateValidation($lowerCaseModel, $fields);
        $describe = <<< DESCRIBE
        describe('Store {$model}', function () use (\$structure) {
            it('Store {$model} successfully', function (\$maked) use (\$structure) {
                \$response = \$this->post('/api/{$lowerCaseModel}s', \$maked->toArray());
                \$response->assertCreated();

                \$response->assertJsonStructure(['data' => \$structure]);
            })->with('maked {$lowerCaseModel}');   

            it('Store {$model} - should validate request datas', function (\$maked) {
                {$validationDatas}
            })->with('maked {$lowerCaseModel}');   

            it('Store {$model} - unauthorized access', function (\$maked) {
                //Create the User factory in your project if not exists
                \$response = \$this->actingAs(User::factory()->create())->put('/api/{$lowerCaseModel}s', \$maked->toArray());
                \$response->assertForbidden();
            })->with('maked {$lowerCaseModel}');

            it('Store {$model} - handle server error', function (\$maked) {
                \$this->mock({$modelNamespace}::class, function (\$mock) {
                \$mock->shouldReceive('someMethod')->andThrow(new \Exception('Internal Server Error', 500));
            });

                \$response = \$this->post('/api/{$lowerCaseModel}s', \$maked->toArray());
                \$response->assertStatus(500);
            })->with('maked {$lowerCaseModel}');
        });
        DESCRIBE.PHP_EOL;

        return $describe;
    }

    private function generateUpdateTestDatas($model, $fields, $modelInstance)
    {
        $lowerCaseModel = Str::lower($model);
        $validationDatas = $this->generateValidation($lowerCaseModel, $fields, 'put');
        $randomdigit = rand(1000, 9999);
        $modelKey = $modelInstance->getKeyName();
        $modelNamespace = get_class($modelInstance);
        $describe = <<< DESCRIBE
        describe('Update {$model}', function () {
            it('Update {$model} successfully', function (\$maked, \$created) {
                \$maked = \$maked();
                \$created = \$created();

                \$response = \$this->put('/api/{$lowerCaseModel}s/' . \$created->{$modelKey}, \$maked->toArray());
                \$response->assertOk();
            })->with('maked {$lowerCaseModel}')->with('created {$lowerCaseModel}');   

            it('Update {$model} - should validate request datas', function (\$maked, \$created) {
                \$maked = \$maked();
                \$created = \$created();

                {$validationDatas}
            })->with('maked {$lowerCaseModel}')->with('created {$lowerCaseModel}');   

            it('Update {$model} - not found', function (\$maked, \$created) {
                \$maked = \$maked();
                
                \$response = \$this->put('/api/{$lowerCaseModel}s/{$randomdigit}', \$maked->toArray());
                \$response->assertNotFound();
            })->with('maked {$lowerCaseModel}')->with('created {$lowerCaseModel}');   

             it('Update {$model} - unauthorized access', function (\$maked, \$created) {
             \$maked = \$maked();
             \$created = \$created();

             //Create the User factory in your project if not exists
             \$response = \$this->actingAs(User::factory()->create())->put('/api/{$lowerCaseModel}s/' . \$created->{$modelKey}, \$maked->toArray());
             \$response->assertForbidden();
            })->with('maked {$lowerCaseModel}')->with('created {$lowerCaseModel}');

            it('Update {$model} - handle server error', function (\$maked, \$created) {
            \$this->mock({$modelNamespace}::class, function (\$mock) {
                \$mock->shouldReceive('someMethod')->andThrow(new \Exception('Internal Server Error', 500));
            });

             \$maked = \$maked();
             \$created = \$created();

             \$response = \$this->put('/api/{$lowerCaseModel}s/' . \$created->{$modelKey}, \$maked->toArray());
             \$response->assertStatus(500);
            })->with('maked {$lowerCaseModel}')->with('created {$lowerCaseModel}');
        });
       DESCRIBE.PHP_EOL;

        return $describe;
    }

    private function generateShowTestDatas($model, $fields, $modelInstance)
    {
        $lowerCaseModel = Str::lower($model);
        $randomdigit = rand(1000, 9999);
        $modelKey = $modelInstance->getKeyName();
        $modelNamespace = get_class($modelInstance);
        $describe = <<< DESCRIBE
        describe('Show {$model}', function () use (\$structure) {
            it('Store {$model} detail successfully', function (\$created) use (\$structure) {
                \$response = \$this->get('/api/{$lowerCaseModel}s/' . \$created->{$modelKey});
                \$response->assertOk();
                \$response->assertJsonStructure(['data' => \$structure]);
            })->with('created {$lowerCaseModel}');   

            it('Show {$model} - not found', function (\$created) {
                
                \$response = \$this->get('/api/{$lowerCaseModel}s/{$randomdigit}');
                \$response->assertNotFound();
            })->with('created {$lowerCaseModel}');   

            it('Show {$model} - unauthorized access', function (\$created) {
                //Create the User factory in your project if not exists
                \$response = \$this->actingAs(User::factory()->create())->get('/api/{$lowerCaseModel}s/' . \$created->{$modelKey});
                \$response->assertForbidden();
            })->with('created {$lowerCaseModel}');

            it('Show {$model} - handle server error', function (\$created) {
                \$this->mock({$modelNamespace}::class, function (\$mock) {
                \$mock->shouldReceive('someMethod')->andThrow(new \Exception('Internal Server Error', 500));
                });
                \$response = \$this->get('/api/{$lowerCaseModel}s/' . \$created->{$modelKey});
                \$response->assertStatus(500);
            })->with('created {$lowerCaseModel}');
        });
       DESCRIBE.PHP_EOL;

        return $describe;
    }

    private function generateDeleteTestDatas($model, $fields, $modelInstance)
    {
        $lowerCaseModel = Str::lower($model);
        $modelKey = $modelInstance->getKeyName();
        $modelNamespace = get_class($modelInstance);
        $describe = <<< DESCRIBE
        describe('Delete {$model}', function () {
            it('Delete {$model} successfully', function (\$maked, \$created) {
                \$maked = \$maked();
                \$created = \$created();

                \$response = \$this->delete('/api/{$lowerCaseModel}s/' . \$created->{$modelKey});
                \$response->assertOk();
            })->with('maked {$lowerCaseModel}')->with('created {$lowerCaseModel}');   

             it('Delete {$model} - unauthorized access', function (\$maked, \$created) {
             \$maked = \$maked();
             \$created = \$created();

             //Create the User factory in your project if not exists
             \$response = \$this->actingAs(User::factory()->create())->delete('/api/{$lowerCaseModel}s/' . \$created->{$modelKey});
             \$response->assertForbidden();
            })->with('maked {$lowerCaseModel}')->with('created {$lowerCaseModel}');

            it('Delete {$model} - handle server error', function (\$maked, \$created) {
            \$this->mock({$modelNamespace}::class, function (\$mock) {
                \$mock->shouldReceive('someMethod')->andThrow(new \Exception('Internal Server Error', 500));
            });

             \$maked = \$maked();
             \$created = \$created();

             \$response = \$this->delete('/api/{$lowerCaseModel}s/' . \$created->{$modelKey});
             \$response->assertStatus(500);
            })->with('maked {$lowerCaseModel}')->with('created {$lowerCaseModel}');
        });
       DESCRIBE.PHP_EOL;

        return $describe;
    }

    private function generateIndexTestDatas($model, $fields)
    {
        $names = collect($fields)->pluck('name')->toArray();
        $lowerCaseModel = Str::lower($model);
        $sortingName = $names[array_rand(collect($fields)->pluck('name')->toArray())];
        $describe = <<< DESCRIBE
        describe('List {$model}', function () use (\$structure) {
            it('List {$model} all datas', function (\$created) use (\$structure) {
                \$response = \$this->get('/api/{$lowerCaseModel}s');
                \$response->assertOk();

                \$response->assertJsonStructure(['data' => [
                    '*' => \$structure
                ]]);

                \$response = json_decode(\$response->getContent(), true);
                expect(count(\$response['data']))->toBeInt()->toBe(10);
            })->with('created {$lowerCaseModel}s');   

            it('List {$model} by page', function (\$created) use (\$structure) {
                // page 1 with 15 items on 20
                \$response = \$this->get('/api/{$lowerCaseModel}s?limit=15&page=1');
                \$response->assertOk();
                \$response->assertJsonStructure(['data' => [
                    '*' => \$structure
                ]]);
                \$response = json_decode(\$response->getContent(), true);
                expect(count(\$response['data']))->toBeInt()->toBe(15);

                // page 2 with 15 items on 20
                \$response = \$this->get('/api/{$lowerCaseModel}s?limit=5&page=2');
                \$response->assertOk();
                \$response->assertJsonStructure(['data' => [
                    '*' => \$structure
                ]]);
                \$response = json_decode(\$response->getContent(), true);
                expect(count(\$response['data']))->toBeInt()->toBe(5);
            })->with('created {$lowerCaseModel}s');   

            //Uncomment this if you want to enable sort query
            /*
            it('List {$model} by sort', function (\$created) use (\$structure) {
                // Sort by {$sortingName} - asc
                \$response = \$this->get('/api/{$lowerCaseModel}s?sort[{$sortingName}]=asc&page=1&limit=15');
                \$response->assertOk();
                \$response->assertJsonStructure(['data' => [
                    '*' => \$structure
                ]]);
                \$response = json_decode(\$response->getContent(), true);
                expect(count(\$response['data']))->toBeInt()->toBe(15);

                for (\$i = 1; \$i < count(\$response['data']); \$i++) {
                    \$this->assertTrue(\$response[\$i - 1]['{$sortingName}'] <= \$response[\$i]['{$sortingName}']);
                }

                // Sort by {$sortingName} - desc
                \$response = \$this->get('/api/{$lowerCaseModel}s?sort[{$sortingName}]=desc&page=2&limit=5');
                \$response->assertOk();
                \$response->assertJsonStructure(['data' => [
                    '*' => \$structure
                ]]);
                \$response = json_decode(\$response->getContent(), true);
                expect(count(\$response['data']))->toBeInt()->toBe(5);

                for (\$i = 1; \$i < count(\$response['data']); \$i++) {
                    \$this->assertTrue(\$response[\$i - 1]['{$sortingName}'] >= \$response[\$i]['{$sortingName}']);
                }
            })->with('created {$lowerCaseModel}s');   
            */

            //Uncomment this if you want to enable search query
            /*
            it('List {$model} by search', function (\$created) use (\$structure) {
                // Save {$sortingName} to search for
                \${$sortingName} = \$created[0]['{$sortingName}'];
                \$response = \$this->get('/api/{$lowerCaseModel}s?search=\${$sortingName}');
                \$response->assertOk();
                \$response->assertJsonStructure(['data' => [
                    '*' => \$structure
                ]]);

                // Assert that {$sortingName} is in the response
                \$response->assertJsonFragment(['{$sortingName}' => \${$sortingName}]);

                // Count {$sortingName} to greater than 0 and equal to 1
                \$response = json_decode(\$response->getContent(), true);
                expect(count(\$response['data']))->toBeInt()->toBeGreaterThan(0)->toEqual(1);

                \$response->assertJsonFragment(['{$sortingName}' => \${$sortingName}]);
            })->with('created {$lowerCaseModel}s');   
            */

            //Uncomment this if you want to enable filter query
            /*
            it('List {$model} by filter', function (\$created) use (\$structure) {
                // Save {$sortingName} to search for
                \${$sortingName} = \$created[0]['{$sortingName}'];
                \$response = \$this->get('/api/{$lowerCaseModel}s?{$sortingName}=\${$sortingName}');
                \$response->assertOk();
                \$response->assertJsonStructure(['data' => [
                    '*' => \$structure
                ]]);
                \$response->assertJsonFragment(['{$sortingName}' => \${$sortingName}]);                
            })->with('created {$lowerCaseModel}s');   
            */
        });
        DESCRIBE.PHP_EOL;

        return $describe;
    }
}
