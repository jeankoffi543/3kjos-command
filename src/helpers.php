<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Kjos\Command\Enums\Pattern;

define('KJOS_COMMAND_MODULE_PATH', dirname(__DIR__, 1));

if (! function_exists('kjos_create_test_directory')) {
    function kjos_create_test_directory()
    {
        Artisan::call('vendor:publish', ['--tag' => '3kjos-command', '--force' => true]);
        collect(config('3kjos-command.paths'))
            ->map(function ($path, $key) {
                match ($key) {
                    'routes' => collect($path)->map(fn($p) => !File::exists(base_path($p)) && File::put(base_path($p), '')),
                    default => File::ensureDirectoryExists(base_path($path)),
                };
            });
    }
}


if (! function_exists('kjos_remove_directory_content')) {
    function kjos_remove_directory_content(string $dir)
    {
        $files = glob($dir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}

// Remove test created files
if (! function_exists('kjos_remove_test_directory')) {
    function kjos_remove_test_directory()
    {
        collect(config('3kjos-command.paths'))
            ->map(function ($path, $key) {
                match ($key) {
                    'routes' => collect($path)->map(fn($p) => File::exists(base_path($p)) && File::put(base_path($p), '')),
                    default => File::isDirectory(base_path($path)) && kjos_remove_directory_content(base_path($path)),
                };
            });
        Artisan::call('kjos:vendor:cleanup');
    }
}

if (! function_exists('kjos_get_root_namespace')) {
    /**
     * Gets the root namespace of the application.
     *
     * @return string
     */
    function kjos_get_root_namespace()
    {
        // Get the RouteServiceProvider instance from the container
        return app()->getNamespace();
    }
}


if (! function_exists('kjos_get_directory_from_namespace')) {
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
    function kjos_get_directory_from_namespace($namespace)
    {
        // Ensure namespace is correctly formatted with trailing backslashes
        $namespace = trim($namespace, '\\') . '\\';

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
                    return base_path($path . $relativePath);
                }
            }
        }

        // Return null if no matching directory is found
        return null;
    }
}

if (! function_exists('find_bases_directory')) {
    /**
     * Finds the path to a directory with a given name, relative to a start directory.
     *
     * @param  string  $startDirectory  The directory to start searching from
     * @param  string  $name  The name of the directory to search for
     * @return string|null The relative path to the directory, or null if it is not found
     */
    function find_bases_directory($startDirectory, $name = null)
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
