<?php

namespace Kjs\ParameterMapper;

use Illuminate\Support\ServiceProvider;
use Kjos\ParameterMapper\Middleware\MapRequestParameters;

class ParameterMapperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/parametermap.php' => config_path('parametermap.php'),
        ]);

        $this->app['router']->aliasMiddleware('parametermap', MapRequestParameters::class);
    }
}
