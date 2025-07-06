<?php

namespace Kjos\Command\Enums;


enum Pattern: string
{
   case SPACE_AT_START_OR_END_WITH_SEMICOLON = '^\s*|\s*;*\s*$';
   case SPACE_AT_START_OR_END_WITHOUT_SEMICOLON = '^\s*|\s*$';
   case SPACE_AT_START_ONLY = '^\s*';
   case SPACE_AT_END_ONLY = '\s*$';
   case ROUTE_GROUP_ITEM = '/Route::(get|post|put|delete|patch|options|match|resources?|apiResources?)\s*\((.*?)\);/s';
   case ROUTE_GROUP = '/Route::group\s*\(\s*(\[.*?\])\s*,\s*function\s*\(\s*\)\s*\{\s*(.*?)\s*\}\s*\);/ms';
   case COMMENT_ONELINE = '!//.*!';
   case COMMENT_MULTILINE = '!/\*.*?\*/!s';
   case ROUTE_PREFIX = "/'prefix'\s*=>\s*'([^']+)'/";
   case SLASH_COLLAPSE_PATTERN = '#/{2,}#';
   case FILE_CONTENT = '/((?:<\?php\s*)+)((?:namespace\s+[^;]+;\s*)?)((?:\s*use\s+[^;]+;\s*?)*)((?:\s*?class\s+[^\n;]+\s*?)*)((?:\s*?{\s*?)?)((?:\s*use\s+[^;]+;\s*?)*)/ms';
   case PROPERTY = '/^\s*(public|protected|private)\s+(static\s+)?(?:[\w\\\\]+\s+)?\$\w+(?:\s*=\s*[^;]+)?;/m';
   case METHOD = '/^\s*(public|protected|private)\s+(static\s+)?function\s+\w+\s*\([^)]*\)\s*{(?:[^{}]*|(?R))*}/m';
   case METHOD_MATCH = '/^\s*(public|protected|private)\s+(static\s+)?function\s+%s\s*\([^)]*\)\s*{(?:[^{}]*|(?R))*}/m';
   case METHOD_VALUE = '/(^\s*(public|protected|private)\s+(static\s+)?)(function\s+%s\s*\([^)]*\)\s*{(?:[^{}]*|(?R))*})/m';
   case STRING = '/^[a-zA-Z0-9 _-]+$/';

   public function match(string $value): string
   {
      return sprintf($this->value, $value);
   }
}
