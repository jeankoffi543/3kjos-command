<?php

namespace Kjos\Command\Managers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;

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

    protected function invokeWithCatching(\Closure $callable)
    {
        try {
            return $callable();
        } catch (ModelNotFoundException $e) {
            return response('not_found', Response::HTTP_NOT_FOUND);
        } catch (QueryException $e) {
            return response('not_found', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
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
    }
}
