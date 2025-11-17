<?php

namespace Kjos\ParameterMapper\Middleware;

use Closure;
use Kjos\ParameterMapper\Support\ParameterMapper;

class MapRequestParameters
{
    public function handle($request, Closure $next)
    {
        // GET parameters
        $query = ParameterMapper::apply($request->query());
        $request->query->replace($query);

        // POST/PUT/PATCH parameters
        $body = ParameterMapper::apply($request->all());
        $request->replace($body);

        return $next($request);
    }
}
