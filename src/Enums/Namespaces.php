<?php

namespace Kjos\Command\Enums;

enum Namespaces: string
{
    case CONTROLLERS = 'controllers';
    case REQUESTS = 'requests';
    case RESOURCES = 'resources';
    case MODELS = 'models';
    case SERVICES = 'services';
    case FACTORIES = 'factories';
    case MIGRATIONS = 'migrations';
    case SEEDERS = 'seeders';    
}
