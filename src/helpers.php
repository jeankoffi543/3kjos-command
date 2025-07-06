<?php

use Illuminate\Support\Facades\File;
use Kjos\Command\Enums\Pattern;

define('KJOS_COMMAND_MODULE_PATH', dirname(__DIR__, 1));

if (! function_exists('kjos_throw_file')) {
    function kjos_throw_file($path)
    {
        if (!File::exists($path)) {
            throw new \Exception("File {$path} does not exist. Please check the configuration in config/3kjos-command.php");
        }
    }
}

if (! function_exists('kjos_throw_directory')) {
    function kjos_throw_directory($path)
    {
        if (!File::isDirectory($path)) {
            throw new \Exception("File {$path} does not exist. Please check the configuration in config/3kjos-command.php");
        }
    }
}

if (! function_exists('kjos_create_file')) {
    function kjos_create_file($path): void
    {
        if (File::isDirectory(dirname($path)) && !File::exists($path)) {
            File::put($path, '');
        }
    }
}

if (! function_exists('kjos_get_file_content')) {
    function kjos_get_file_content($path): ?string
    {
        if (!File::exists($path)) {
            return null;
        }
        return File::get($path);
    }
}

if (! function_exists('kjos_ptrim')) {
    function kjos_ptrim(?string $string, Pattern $pattern = Pattern::SPACE_AT_START_OR_END_WITH_SEMICOLON): ?string
    {
        return $string ? preg_replace("/{$pattern->value}/m", "", $string) : null;
    }
}

if (! function_exists('kjos_parse_statment')) {
    function kjos_parse_statment(string $input, ?string $oldvalue = null, ?string $character = null): ?string
    {
        $inputs = preg_split('/\s*(?:,|\n)\s*/', kjos_ptrim($input));
        $inputs = (array_filter(array_map('trim', $inputs)));
        $newInput = [];
        foreach ($inputs as $input) {
            $input = preg_replace("/{$character}\s*/", '', $input);

            $usePattern = "/{$character}\s+" . preg_quote($input, '/') . '\s*;\s*/';
            if (!preg_match($usePattern, $oldvalue)) {
                $newInput[] = "{$character} $input";
            }
        }
        $newValue = count($newInput) > 0 ? implode(";\n", $newInput) . ";\n" : "";
        return $oldvalue . $newValue;
    }
}

if (!function_exists('kjos_get_namespace')) {
    function kjos_get_namespace(string $path): ?string
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $psr4 = $composer['autoload']['psr-4'] ?? [];

        $realPath = realpath($path);
        if (!$realPath) return null;

        $realPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $realPath);

        foreach ($psr4 as $namespace => $relativePath) {
            $namespaceBasePath = realpath(base_path($relativePath));
            if (!$namespaceBasePath) continue;

            $namespaceBasePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $namespaceBasePath);

            if (str_starts_with($realPath, $namespaceBasePath)) {
                $subPath = substr($realPath, strlen($namespaceBasePath));
                $subNamespace = trim(str_replace(DIRECTORY_SEPARATOR, '\\', $subPath), '\\');

                $n = rtrim($namespace, '\\') . ($subNamespace ? '\\' . $subNamespace : '');
                return rtrim($n, '.php');
            }
        }

        return null;
    }
}

if (!function_exists('kjos_in_array')) {
    function kjos_in_array(string $needle, array $haystack, ?string $key = null): bool
    {
        foreach ($haystack as $h) {
            if (is_array($h)) {
                $r = kjos_in_array($needle, $h, $key);
                if ($key) $h = $h[$key];
                return $r;
            } else {
                dump($h);
                if ($needle === $h) {
                    return true;
                }
            }
        }
        return false;
    }
}


if (!function_exists('kjos_get_config')) {
    function kjos_get_config(): array
    {
        return config('3kjos-command');
    }
}

if (!function_exists('kjos_is_string')) {
    function kjos_is_string(string $value): bool
    {
        return preg_match(Pattern::STRING->value, $value);
    }
}
