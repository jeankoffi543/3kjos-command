<?php

namespace Kjos\Command\Concerns;

use Kjos\Command\Commands\KjosMakeRouteApiCommand;
use Kjos\Command\Concerns\Helpers\MethodExtractor;
use Kjos\Command\Concerns\Helpers\PropertyExtractor;
use Kjos\Command\Concerns\Helpers\TraitExtractor;
use Kjos\Command\Enums\Pattern;

trait InterractWithFileContent
{

   public function initConfig(array $config, KjosMakeRouteApiCommand $command): void
   {
      RouteKitProvider::init($config, $command);
   }

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


   public function extractMethods(): array
   {
      $extractor = new MethodExtractor();
      $methods = $extractor->extractFromFile($this->path);
      return $methods;
   }

   public function extractFullMethods(): string
   {
      $extractor = new MethodExtractor();
      $methods = $extractor->extractFullMethodsFromFile($this->path);
      return implode("\n", $methods);
   }

   public function extractProperties(): string
   {
      // preg_match_all(Pattern::PROPERTY->value, $this->fileContent, $properties);
      // return implode($properties[0]);
      $extractor = new PropertyExtractor();
      $properties = $extractor->extractFromFile($this->path);
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
      return $extractor->extractFromFile($this->path);
   }

   public function extractTraits(): ?string
   {
      $extractor = new TraitExtractor();
      return implode("\n", $extractor->extractFromFile($this->path));
   }

   public function extractTraitsToArray(): ?array
   {
      $extractor = new TraitExtractor();
      return $extractor->extractFromFile($this->path);
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
      return  RouteKitProvider::extracRouteGroup($this->fileContent, $name);
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
}
