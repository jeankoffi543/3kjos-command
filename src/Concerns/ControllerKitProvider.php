<?php

namespace Kjos\Command\Concerns;

class ControllerKitProvider
{
   use InterraxtWithController;

   public static function getServiceProperty(string $namespaces): string
   {
      if (
         (
            self::$command->option('centralize') &&
            self::$command->option('errorhandler')
         ) ||
         (
            self::$command->option('centralize')
         )
      ) {
         $nameStudySingular = self::$nameStudySingular;

         return "/** @var \\{$namespaces}\\{$nameStudySingular}Services */ \n protected \$service;";
      } else {
         return '';
      }
   }

   public static function getServices(string $namespace): string
   {
      if (
         (
            self::$command->option('centralize')
           
         ) ||
         (
            self::$command->option('centralize')
         )
      ) {
         $nameStudySingular = self::$nameStudySingular;

         return <<<SERVICE
            public function getServices(): array
            {
               return [
                  'service' => \\{$namespace}\\{$nameStudySingular}Service::class,
               ];
            }
            SERVICE;
      } else {
         return '';
      }
   }

   public static function index(): string
   {
      if (
         !self::$command->option('centralize') &&
         !self::$command->option('errorhandler')
      ) {
         return self::defaultIndex();
      } else if (
         self::$command->option('centralize') &&
         self::$command->option('errorhandler')
      ) {
         return self::centralizeAndErrorHandlerIndex();
      } else if (self::$command->option('centralize')) {
         return self::centralizeIndex();
      } else if (self::$command->option('errorhandler')) {
         return self::errorHandlerIndex();
      }
      return self::defaultIndex();
   }

   public static function show(): string
   {
      if (
         !self::$command->option('centralize') &&
         !self::$command->option('errorhandler')
      ) {
         return self::defaultShow();
      } else if (
         self::$command->option('centralize') &&
         self::$command->option('errorhandler')
      ) {
         return self::centralizeAndErrorHandlerShow();
      } else if (self::$command->option('centralize')) {
         return self::centralizeShow();
      } else if (self::$command->option('errorhandler')) {
         return self::errorHandlerShow();
      }
      return self::defaultShow();
   }

   public static function store(): string
   {
      if (
         !self::$command->option('centralize') &&
         !self::$command->option('errorhandler')
      ) {
         return self::defaultStore();
      } else if (
         self::$command->option('centralize') &&
         self::$command->option('errorhandler')
      ) {
         return self::centralizeAndErrorHandlerStore();
      } else if (self::$command->option('centralize')) {
         return self::centralizeStore();
      } else if (self::$command->option('errorhandler')) {
         return self::errorHandlerStore();
      }
      return self::defaultStore();
   }

   public static function update(): string
   {
      if (
         !self::$command->option('centralize') &&
         !self::$command->option('errorhandler')
      ) {
         return self::defaultUpdate();
      } else if (
         self::$command->option('centralize') &&
         self::$command->option('errorhandler')
      ) {
         return self::centralizeAndErrorHandlerUpdate();
      } else if (self::$command->option('centralize')) {
         return self::centralizeUpdate();
      } else if (self::$command->option('errorhandler')) {
         return self::errorHandlerUpdate();
      }
      return self::defaultUpdate();
   }

   public static function destroy(): string
   {
      if (
         !self::$command->option('centralize') &&
         !self::$command->option('errorhandler')
      ) {
         return self::defaultDestroy();
      } else if (
         self::$command->option('centralize') &&
         self::$command->option('errorhandler')
      ) {
         return self::centralizeAndErrorHandlerDestroy();
      } else if (self::$command->option('centralize')) {
         return self::centralizeDestroy();
      } else if (self::$command->option('errorhandler')) {
         return self::errorHandlerDestroy();
      }
      return self::defaultDestroy();
   }
}
