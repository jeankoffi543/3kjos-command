<?php

namespace Kjos\Command\Managers;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

abstract class Controller extends \Illuminate\Routing\Controller
{
    public function __construct()
    {
        $this->bootDynamicServices();
    }

    protected function bootDynamicServices(): void
    {
        if (!method_exists($this, 'getServices')) {
            return;
        }

        $services = app()->call([$this, 'getServices']);

        foreach ($services as $key => $serviceClass) {
            // Création dynamique d'une propriété publique
            $this->{$key} = app()->make($serviceClass);
        }
    }

    protected function invokeWithCatching(\Closure $callable, ?Closure $callback = null): mixed
    {
        try {
            $c = $callable();
            if ($this->isApiRequest()) {
                return $c;
            }
            return $callback ? $callback($c) : $c;
        } catch (ModelNotFoundException $e) {
            dump($e);
            return response('not_found', Response::HTTP_NOT_FOUND);
        } catch (QueryException $e) {
            dump($e);
            return response('not_found', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            // dump($e);
            if ($this->isApiRequest()) {

                if ($e->getCode() === 404) {
                    return response('not_found', Response::HTTP_NOT_FOUND);
                }
                if ($e->getCode() === 403) {
                    return response($e->getMessage(), Response::HTTP_FORBIDDEN);
                }
                if ($e->getCode() === 422) {
                    return response($e->getMessage(), Response::HTTP_FORBIDDEN);
                }

                return response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            throw $e; // Très important pour que Inertia fonctionne
        }
    }

    protected function isApiRequest(): bool
    {
        $request = request();
        return $request->expectsJson()
            || $request->is('api/*')
            || str_starts_with(Route::currentRouteName() ?? '', 'api.');
    }
}
