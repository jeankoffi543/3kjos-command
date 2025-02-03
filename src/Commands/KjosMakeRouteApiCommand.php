<?php

namespace Kjos\Command\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\GeneratorCommand;
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
    {--factory : Generate factory for model}';

    private ?array $runtimeDatas = [];

    public function handle()
    {
        $prefix = $this->argument('name');
        $apiRoutePath = base_path('routes/api.php');
        $force = $this->option('force');
        $errorHandler = $this->option('errorhandler');
        $centralize = $this->option('centralize');
        $factory = $this->option('factory');

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
        format(base_path('database/factories'));

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
}
