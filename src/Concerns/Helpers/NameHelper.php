<?php

namespace Kjos\Command\Concerns\Helpers;

use Illuminate\Support\Str;
use Kjos\Command\Enums\NameArgument;

class NameHelper
{
    // Exemples de méthodes de transformation
    public static function toStudly(string $name): string
    {
        return Str::studly($name);
    }

    public static function toCamel(string $name): string
    {
        return Str::camel($name);
    }

    public static function toSnake(string $name): string
    {
        return Str::snake($name);
    }

    public static function toKebab(string $name): string
    {
        return Str::kebab($name);
    }

    public static function toTitle(string $name): string
    {
        return Str::title($name);
    }

    public static function toPascal(string $name): string
    {
        return self::toStudly($name);
    }

    public static function toUpper(string $name): string
    {
        return strtoupper($name);
    }

    public static function toLower(string $name): string
    {
        return strtolower($name);
    }

    public static function namePlural(string $name, NameArgument $case): string
    {
        return Str::plural(self::applyCase($name, $case));
    }

    public static function nameSingular(string $name, NameArgument $case): string
    {
        return Str::singular(self::applyCase($name, $case));
    }

    protected static function applyCase(string $name, NameArgument $case): string
    {
        $method = $case->value;

        if (!method_exists(static::class, $method)) {
            throw new \InvalidArgumentException("Method [$method] does not exist on " . static::class);
        }

        return static::$method($name);
    }
}
