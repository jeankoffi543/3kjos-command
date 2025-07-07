<?php

/**
 * @var $this
 *
 * */

use Kjos\Command\Enums\FakeEntity;
use Kjos\Command\Managers\TestQuestions;

test('it answers all field questions correctly', function () {
    $faker = \Faker\Factory::create('fr_FR');
    $name = $faker->randomElement(FakeEntity::cases());
    $question = test()->artisan("kjos:make:api {$name->value} --errorhandler --centralize --test --factory");
    $testQuestions = new TestQuestions($question, $faker);
    $fieldName = FakeEntity::attributs($name);


    foreach ($fieldName as $field) {
        $testQuestions->ask($field);
        // // Exécute manuellement
        // $exitCode = $question->run();

        // // // Affiche l'output
        // ray($question->output()); // ou dd(), dump(), etc.
    }
});
