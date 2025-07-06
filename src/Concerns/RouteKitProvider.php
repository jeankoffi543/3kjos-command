<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Enums\Pattern;
use Illuminate\Support\Str;
use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Enums\EndpointType;

class RouteKitProvider
{
   private static array $config = [];
   private static KjosMakeRouteApiCommand $command;
   private static Path $path;

   public static function init(array $config, KjosMakeRouteApiCommand $command): void
   {
      self::$path = new Path();
      self::$config = $config;
      self::$command = $command;
   }

   public static function removePhpComments(string $code): string
   {
      // Supprimer les commentaires multilignes (/* ... */)
      $code = preg_replace(Pattern::COMMENT_MULTILINE->value, '', $code);

      // Supprimer les commentaires sur une seule ligne (// ...)
      $code = preg_replace(Pattern::COMMENT_ONELINE->value, '', $code);

      return $code;
   }

   public static function methodOrUrlExistsInRoute(string $prefix, array $existingRoute, array $requiredRoute): bool
   {
      $methodCondition = strtolower($existingRoute['method']) === strtolower($requiredRoute['method']);
      $urlCondition = rtrim($existingRoute['url'], '/') === rtrim($requiredRoute['url'], '/');

      $condition = $prefix === 'root' ? $urlCondition : $methodCondition && $urlCondition;

      return $condition;
   }

   public static function captureRouteGroup(string &$fileContent): array
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

   public static function captureRouteOutsideGroup(string $fileContent): array
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

   public static function rebuildRoutes(array $groups): string
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

   public static function buildControllerDefinition(string $name): ?array
   {
      $controllerNamespace = kjos_get_namespace(self::$path->getControllersPath()) . '\\' . Str::studly($name) . 'Controller';
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

      $prefix = self::getEndpointType() === EndpointType::GROUP ? $name : 'root';

      $missingRoutes["{$prefix}"] = self::getEndpointType() === EndpointType::GROUP ?
         $defaultRoute : (
            self::getEndpointType() === EndpointType::STANDALONE ?
            $defaultRoute
            : (
               self::getEndpointType() === EndpointType::RESOURCE ?
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

   public static function addRoutes(array &$allRoutes, array $requiredRoutes): void
   {
      $path = self::$path->getRouteApiPath();
      foreach ($requiredRoutes as $prefix => $requiredList) {
         foreach ($requiredList as $requiredRoute) {

            // 5. Vérifier si la route existe
            $alreadyExists = false;
            if (isset($allRoutes[$prefix]) || isset($allRoutes['root'])) {
               // Si le prefixe existe: on s'assure qu'on est sur la bonne route, le prefix root correspont aux routes qui ne sont pas dans un groupe
               $prefix = isset($allRoutes[$prefix]) ? $prefix : 'root';
               foreach ($allRoutes[$prefix] as $existing) {
                  // Comparaison stricte sur méthode ET URL selon le prefix root ou non
                  if (self::methodOrUrlExistsInRoute($prefix, $existing, $requiredRoute)) {
                     $alreadyExists = true;
                     self::$command->warn(
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

   public static function extracRouteGroup(string &$fileContent, string $name): string
   {
      // 1. Capturer les groupes
      $existingGroups = self::captureRouteGroup($fileContent);

      // 2. Capturer les routes hors groupe
      $outsideGroups = self::captureRouteOutsideGroup($fileContent);

      // 3. Fusionner les deux
      $allRoutes = array_merge_recursive($existingGroups, $outsideGroups);

      // 4. Routes à vérifier (définies automatiquement)
      $requiredRoutes = self::buildControllerDefinition($name); // [prefix => [routes]]

      self::addRoutes($allRoutes, $requiredRoutes, self::$command);

      // 5. Reconstruire toutes les routes
      $generatedCode = self::rebuildRoutes($allRoutes);

      return $generatedCode;
   }

   private static function getEndpointType(): EndpointType
   {
      $optionEndpointType = data_get(self::$command->options(), 'endpoint_type');
      $configEndpointType = data_get(self::$config['route'], 'endpoint_type');

      return $optionEndpointType ?
         EndpointType::tryFrom($optionEndpointType) : (
            $configEndpointType instanceof EndpointType ?
            $configEndpointType :
            EndpointType::tryFrom(
               $configEndpointType
            )
         );
   }
}
