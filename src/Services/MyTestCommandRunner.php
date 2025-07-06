<?php

namespace Kjos\Command\Services;

use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class MyTestCommandRunner
{
    public static function runWithAnswers(Command $command, array $answers)
    {
        $input = new ArrayInput([]);
        $output = new OutputStyle($input, new BufferedOutput());

        // Simule les questions avec des réponses prédéfinies
        $command->setLaravel(app());
        $command->setInput($input);
        $command->setOutput($output);

        // Injecte les réponses automatiquement dans les prompts
        $command->setHelperSet(new \Symfony\Component\Console\Helper\HelperSet([
            new \Symfony\Component\Console\Helper\QuestionHelper()
        ]));

        // Remplace la méthode ask, anticipate, confirm, etc.
        foreach ($answers as $method => $valueList) {
            $command->macro($method, function ($self, ...$params) use (&$valueList) {
                return array_shift($valueList);
            });
        }

        $command->handle();

        return $output;
    }
}
