<?php

namespace Kjos\Command\Enums;

enum NameArgument: string
{
    case Camel = 'toCamel';
    case Snake = 'toSnake';
    case Studly = 'toStudly';
    case Kebab = 'toKebab';
    case Pascal = 'toPascal';
    case Title = 'toTitle';
    case Upper = 'toUpper';
    case Lower = 'toLower';
}
