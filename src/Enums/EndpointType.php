<?php

namespace Kjos\Command\Enums;

enum EndpointType: string
{
    case GROUP = 'group';
    case STANDALONE = 'standalone';
    case API_RESOURCE = 'apiResource';
    case RESOURCE = 'resource';

    public function label(): string
    {
        return match ($this) {
            self::GROUP => 'Groupe de routes',
            self::STANDALONE => 'Route indépendante',
            self::API_RESOURCE => 'Route API resource',
            self::RESOURCE => 'Route resource',
        };
    }
}
