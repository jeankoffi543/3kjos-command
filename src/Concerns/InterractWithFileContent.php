<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Concerns\Helpers\MethodExtractor;
use Kjos\Command\Concerns\Helpers\PropertyExtractor;
use Kjos\Command\Concerns\Helpers\TraitExtractor;
use Kjos\Command\Enums\EndpointType;
use Kjos\Command\Enums\Pattern;
use Illuminate\Support\Str;

trait InterractWithFileContent
{
   public function parseContent(): array
   {
      preg_match(Pattern::FILE_CONTENT->value, $this->fileContent, $content);
      $file = [];
      if (count($content) > 0) {
         $file = [
            'main' => $content[0],
            'php' => $content[1],
            'namespace' => $content[2],
            'use_statments' => $content[3],
            'declaration' => $content[4],
            'traits' => $content[6],
         ];
      } else {
         $file = [
            'main' => '',
            'php' => '',
            'namespace' => '',
            'use_statments' => '',
            'declaration' => '',
            'traits' => '',
         ];
      }

      return [
         ...$file,
         'properties' => $this->extractProperties(),
         'properties_to_array' => $this->extractPropertiesToArray(),
         'traits_to_array' => $this->extractTraitsToArray(),
         'traits' => $this->extractTraits(),
         'methods' => $this->extractMethods(),
         'full_methods' => $this->extractFullMethods(),
         // 'routes' => RouteKitProvider::extracRouteGroup($this->fileContent, $this->name),
      ];
   }

   public function methodOrUrlExistsInRoute(string $prefix, array $existingRoute, array $requiredRoute): bool
   {
      $methodCondition = strtolower($existingRoute['method']) === strtolower($requiredRoute['method']);
      $urlCondition = rtrim($existingRoute['url'], '/') === rtrim($requiredRoute['url'], '/');

      $condition = $prefix === 'root' ? $urlCondition : $methodCondition && $urlCondition;

      return $condition;
   }

   public function extractMethods(): array
   {
      $extractor = new MethodExtractor();
      $methods = $extractor->extractFromFile($this->factory->path);
      return $methods;
   }

   public function extractFullMethods(): string
   {
      $extractor = new MethodExtractor();
      $methods = $extractor->extractFullMethodsFromFile($this->factory->path);
      return implode("\n", $methods);
   }

   public function buildControllerDefinition(string $name): ?array
   {
      $controllerNamespace = kjos_get_namespace($this->factory->getControllersPath()) . '\\' . Str::studly($name) . 'Controller';
      $defaultRoute = [
         [
            'method' => 'get',
            'definition' => "'/', [{$controllerNamespace}::class, 'index']",
            'url' => "/{$name}/",
         ],
         [
            'method' => 'post',
            'definition' => "'/', [{$controllerNamespace}::class, 'store']",
            'url' => "/{$name}/",
         ],
         [
            'method' => 'get',
            'definition' => "'/{id}', [{$controllerNamespace}::class, 'show']",
            'url' => "/{$name}/{id}",
         ],
         [
            'method' => 'put',
            'definition' => "'/{id}', [{$controllerNamespace}::class, 'update']",
            'url' => "/{$name}/{id}",
         ],
         [
            'method' => 'delete',
            'definition' => "'/{id}', [{$controllerNamespace}::class, 'destroy']",
            'url' => "/{$name}/{id}",
         ]
      ];

      $prefix = $this->getEndpointType() === EndpointType::GROUP ? $name : 'root';

      $missingRoutes["{$prefix}"] = $this->getEndpointType() === EndpointType::GROUP ?
         $defaultRoute : (
            $this->getEndpointType() === EndpointType::STANDALONE ?
            $defaultRoute
            : (
               $this->getEndpointType() === EndpointType::RESOURCE ?
               [
                  [
                     'method' => 'resource',
                     'definition' => "'{$name}', [{$controllerNamespace}::class]",
                     'url' => "/{$name}/",
                  ],
               ] :
               [
                  [
                     'method' => 'apiResource',
                     'definition' => "'{$name}', [{$controllerNamespace}::class]",
                     'url' => "/{$name}/",
                  ],
               ]
            )
         );

      return $missingRoutes;
   }


   public function extractProperties(): string
   {
      // preg_match_all(Pattern::PROPERTY->value, $this->fileContent, $properties);
      // return implode($properties[0]);
      $extractor = new PropertyExtractor();
      $properties = $extractor->extractFromFile($this->factory->path);
      $toString = [];
      foreach ($properties as $prop) {
         $default = $prop['default'] === null ? '' : " = {$prop['default']}";
         $visibility = $prop['visibility'] === null ? '' : $prop['visibility'];
         $type = $prop['type'] === null ? '' : $prop['type'];
         $toString[] = "{$visibility} {$type} \${$prop['name']}{$default};\n";
      }
      return implode("\n", $toString);
   }

   public function extractPropertiesToArray(): ?array
   {
      // preg_match_all(Pattern::PROPERTY->value, $this->fileContent, $properties);
      // return implode($properties[0]);
      $extractor = new PropertyExtractor();
      return $extractor->extractFromFile($this->factory->path);
   }

   public function extractTraits(): ?string
   {
      $extractor = new TraitExtractor();
      return implode("\n", $extractor->extractFromFile($this->factory->path));
   }

   public function extractTraitsToArray(): ?array
   {
      $extractor = new TraitExtractor();
      return $extractor->extractFromFile($this->factory->path);
   }

   private function splitFileContent(): void
   {
      $splitContent = $this->parseContent();
      $this->addNamespace($splitContent['namespace']);
      $this->addUseStatements($splitContent['use_statments']);
      if ($this->phpBodyFactory !== null) {
         $this->phpBodyFactory
            ->addClassDeclaration($splitContent['declaration'])
            ->addTraits($splitContent['traits'])
            ->addProperties($splitContent['properties'])
            ->addMethods($splitContent['methods']);
         // ->addRouteGroup($splitContent['routes']);
      }
   }

   public function parseRouteGroup(string $name): string
   {
      $name = Str::plural($name);
      return  $this->extracRouteGroup($this->fileContent, $name);
   }

   public function get(): string
   {
      return <<<PHP
      <?php

      {$this->getNamespace()}

      {$this->getUseStatements()}

      {$this->getBody()}
      PHP;
   }

   public function extracRouteGroup(string &$fileContent, string $name): string
   {

      // 1. Capturer les groupes
      $existingGroups = $this->captureRouteGroup($fileContent);

      // 2. Capturer les routes hors groupe
      $outsideGroups = $this->captureRouteOutsideGroup($fileContent);

      // 3. Fusionner les deux
      $allRoutes = array_merge_recursive($existingGroups, $outsideGroups);

      // 4. Routes à vérifier (définies automatiquement)
      $requiredRoutes = $this->buildControllerDefinition($name); // [prefix => [routes]]

      $this->addRoutes($allRoutes, $requiredRoutes, $this->factory->command);

      // 5. Reconstruire toutes les routes
      $generatedCode = $this->rebuildRoutes($allRoutes);

      return $generatedCode;
   }

   public function captureRouteGroup(string &$fileContent): array
   {
      $groups = [];

      preg_match_all(Pattern::ROUTE_GROUP->value, $fileContent, $matches, PREG_SET_ORDER);

      $groupedRouteContent = []; // Pour exclusion

      foreach ($matches as $match) {
         $fullMatch = $match[0];          // Le code complet du groupe
         $rawOptions = $match[1];         // Le tableau d'options
         $groupBody = $match[2];          // Le contenu du groupe

         // On enregistre ce groupe pour l’exclure après
         $groupedRouteContent[] = $fullMatch;

         // Extraire le prefix
         preg_match(Pattern::ROUTE_PREFIX->value, $rawOptions, $prefixMatch);
         $prefix = $prefixMatch[1] ?? 'root';

         // Extraire les routes dans le groupe
         preg_match_all(Pattern::ROUTE_GROUP_ITEM->value, $groupBody, $routeMatches, PREG_SET_ORDER);

         foreach ($routeMatches as $route) {
            $method = $route[1];
            $definition = trim($route[2]);
            $segments = explode(',', $definition);
            $endpoint = trim(trim($segments[0] ?? '', "'\""));
            $url = '/' . trim($prefix, '/') . '/' . trim($endpoint, '/');
            $url = preg_replace(Pattern::SLASH_COLLAPSE_PATTERN->value, '/', $url);

            $groups[$prefix][] = [
               'method' => $method,
               'definition' => $definition,
               'url' => $url,
            ];
         }
      }

      // 2. Nettoyer les groupes du contenu initial pour éviter les doublons
      foreach ($groupedRouteContent as $groupBlock) {
         $fileContent = str_replace($groupBlock, '', $fileContent);
      }

      return $groups;
   }

   public function captureRouteOutsideGroup(string $fileContent): array
   {
      $groups = [];
      preg_match_all(Pattern::ROUTE_GROUP_ITEM->value, $fileContent, $standaloneMatches, PREG_SET_ORDER);

      foreach ($standaloneMatches as $route) {
         $method = $route[1];
         $definition = trim($route[2]);
         $segments = explode(',', $definition);
         $endpoint = trim(trim($segments[0] ?? '', "'\""));
         $url = '/' . trim($endpoint, '/');

         $groups['root'][] = [
            'method' => $method,
            'definition' => $definition,
            'url' => $url,
         ];
      }

      return $groups;
   }


   private function getEndpointType(): EndpointType
   {
      $optionEndpointType = data_get($this->factory->command->options(), 'endpoint_type');
      $configEndpointType = data_get($this->factory->config['route'], 'endpoint_type');

      return $optionEndpointType ?
         EndpointType::tryFrom($optionEndpointType) : (
            $configEndpointType instanceof EndpointType ?
            $configEndpointType :
            EndpointType::tryFrom(
               $configEndpointType
            )
         );
   }

   public function removePhpComments(string $code): string
   {
      // Supprimer les commentaires multilignes (/* ... */)
      $code = preg_replace(Pattern::COMMENT_MULTILINE->value, '', $code);

      // Supprimer les commentaires sur une seule ligne (// ...)
      $code = preg_replace(Pattern::COMMENT_ONELINE->value, '', $code);

      return $code;
   }

   public function rebuildRoutes(array $groups): string
   {
      $finaleRoutes = "";
      // Et on reconstruit tout ce qui manque
      foreach ($groups as $prefix => $routes) {
         $lines = [];

         if ($prefix === 'root') {
            foreach ($routes as $route) {
               $lines[] = "Route::{$route['method']}({$route['definition']});";
            }
         } else {
            $lines[] = "Route::group(['prefix' => '{$prefix}'], function () {";
            foreach ($routes as $route) {
               $lines[] = "    Route::{$route['method']}({$route['definition']});";
            }
            $lines[] = "});";
         }

         $finaleRoutes .= implode("\n", $lines) . "\n\n";

         // Tu peux écrire dans le fichier ici si tu veux
         // file_put_contents(...);
      }
      return $finaleRoutes;
   }


   public function addRoutes(array &$allRoutes, array $requiredRoutes): void
   {
      $path = $this->factory->getRouteApiPath();
      foreach ($requiredRoutes as $prefix => $requiredList) {
         foreach ($requiredList as $requiredRoute) {

            // 5. Vérifier si la route existe
            $alreadyExists = false;
            if (isset($allRoutes[$prefix]) || isset($allRoutes['root'])) {
               // Si le prefixe existe: on s'assure qu'on est sur la bonne route, le prefix root correspont aux routes qui ne sont pas dans un groupe
               $prefix = isset($allRoutes[$prefix]) ? $prefix : 'root';
               foreach ($allRoutes[$prefix] as $existing) {
                  // Comparaison stricte sur méthode ET URL selon le prefix root ou non
                  if ($this->methodOrUrlExistsInRoute($prefix, $existing, $requiredRoute)) {
                     $alreadyExists = true;
                     $this->factory->command->warn(
                        "[Warning] Route {$requiredRoute['method']}: {$requiredRoute['url']} already exists in {$path} <fg=red>[skipped]</>"
                     );
                     break;
                  }
               }
            }

            if (! $alreadyExists) {
               $allRoutes[$prefix][] = [
                  'method' => $requiredRoute['method'],
                  'definition' => $requiredRoute['definition'],
                  'url' => $requiredRoute['url'],
               ];
            }
            $alreadyExists = false;
            if ($prefix === 'root') break 2;
         }
      }
   }
}
