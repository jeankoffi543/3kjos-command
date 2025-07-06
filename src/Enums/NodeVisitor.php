<?php

namespace Kjos\Command\Enums;


Enum NodeVisitor: string
{
    
   case NAMESAPCE = 'namespace';
   case CLASSNAME = 'class';
   case ATTRIBUTE = 'attribute';
   case IMPORT = 'import';
   case API = 'api';
}
