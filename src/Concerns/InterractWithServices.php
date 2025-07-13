<?php

namespace Kjos\Command\Concerns;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

trait InterractWithServices
{
    /**
     * Index a relation model
     *
     * @param int $relation_id
     * @param string $relation
     * @return AnonymousResourceCollection
     */
    public function indexWithRelation(int $id, string $relation): AnonymousResourceCollection
    {
        $limit = \request()->integer('limit');
        $limit = $limit > 0 ? $limit : \config('3kjos-command.route.pagination.limit', 10);
        $query = \call_user_func([
           $this->model::findOrFail($id)->$relation(),
           'query',
        ]);
        $query = $query->paginate($limit);

        return $this->resourcesCollection($query);
    }

    /**
     * Retrieve a relation model by its ID
     *
     * @param int $id
     * @param int $relation_id
     * @param string $relation
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function showWithRelation(int $id, int $relation_id, string $relation): \Illuminate\Http\Resources\Json\JsonResource
    {
        $model = $this->model::findOrFail($id)
           ->$relation()
           ->findOrFail($relation_id);

        return $this->resource($model);
    }

    /**
     * Store a new relation model
     *
     * @param int $id
     * @param array $data
     * @param string $relation
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function storeWithRelation(int $id, array $data, string $relation): \Illuminate\Http\Resources\Json\JsonResource
    {
        $model = $this->model::findOrFail($id);

        return $this->resource($this->model::create($model->$relation()->create($data)));
    }

    /**
     * Update a relation model
     *
     * @param int $id
     * @param int $relation_id
     * @param array $data
     * @param string $relation
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function updateWithRelation(int $id, int $relation_id, array $data, string $relation): \Illuminate\Http\Resources\Json\JsonResource
    {
        $model = $this->model::findOrFail($id);
        $model = $model->$relation()->findOrFail($relation_id);
        if (\count($data)) {
            $model->update($data);
        }

        return $this->resource($model);
    }

    /**
     * Destroy a relation model by its ID
     *
     * @param int $id
     * @param int $relation_id
     * @param string $relation
     * @return \Illuminate\Http\Response
     */
    public function destroyWithRelation(int $id, int $relation_id, string $relation): Response
    {
        $model = \call_user_func([$this->model->findOrFail($id)->$relation(), 'find'], (int) $relation_id);

        if ($model) {
            $model->delete();
        }

        return \response('success', Response::HTTP_OK);
    }

    /**
     * Assign a role or permission to a model
     *
     * @param int $id
     * @param array|string $items
     * @param string $relation
     * @return \Illuminate\Http\Response
     * @throws \InvalidArgumentException
     */
     public function assignWithRoleBased(int $id, array|string $items, string $relation): Response
    {
        $model = $this->model::findOrFail($id);
        $items = \is_array($items) ? $items : [$items];

        // assignRole ou givePermissionTo
        if ($relation === 'roles') {
            $model->assignRole($items);
        } elseif ($relation === 'permissions') {
            $model->givePermissionTo($items);
        } else {
            throw new \InvalidArgumentException("Relation [$relation] non prise en charge pour l’assignation.");
        }

        return new Response('succes', Response::HTTP_OK);
    }

    /**
     * Sync a role or permission from a model
     *
     * @param int $id
     * @param array<string, mixed>|string $items
     * @param string $relation
     * @return \Illuminate\Http\Response
     * @throws \InvalidArgumentException
     */
    public function syncWithRoleBased(int $id, array|string $items, string $relation): Response
    {
        $model = $this->model::findOrFail($id);
        $items = \is_array($items) ? $items : [$items];

        if ($relation === 'roles') {
            $model->syncRoles($items);
        } elseif ($relation === 'permissions') {
            $model->syncPermissions($items);
        } else {
            throw new \InvalidArgumentException("Relation [$relation] non prise en charge pour la synchronisation.");
        }

        return new Response('succes', Response::HTTP_OK);
    }

    /**
     * Revoke a role or permission from a model
     *
     * @param int $id
     * @param array<string, mixed>|string $items
     * @param string $relation
     * @return \Illuminate\Http\Response
     * @throws \InvalidArgumentException
     */
    public function revokeWithRoleBased(int $id, array|string $items, string $relation): Response
    {
        $model = $this->model::findOrFail($id);
        $items = \is_array($items) ? $items : [$items];

        if ($relation === 'roles') {
            foreach ($items as $item) {
                $model->removeRole($item);
            }
        } elseif ($relation === 'permissions') {
            foreach ($items as $item) {
                $model->revokePermissionTo($item);
            }
        } else {
            throw new \InvalidArgumentException("Relation [$relation] non prise en charge pour la révocation.");
        }

        return new Response('succes', Response::HTTP_OK);
    }

    /**
     * Update a role or permission model
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @param string $relation
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function updateRoleModelWithRoleBased(int $id, array $data, string $relation): \Illuminate\Http\Resources\Json\JsonResource
    {
        $modelClass = match ($relation) {
            'roles'       => \Spatie\Permission\Models\Role::class,
            'permissions' => \Spatie\Permission\Models\Permission::class,
            default       => throw new \InvalidArgumentException("Relation [$relation] non prise en charge pour update."),
        };

        $model = $modelClass::findOrFail($id);
        $model->update($data);

        return $this->resource($model);
    }

    /**
     * Dynamically handle calls to the service. This is used to route calls made to the service
     * to the correct method on the model.
     *
     * @param string $method
     * @param array $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        foreach ($this->relations as $relation) {
            $studly = \ucfirst($relation);

            if ($method === "index{$studly}") {
                return $this->indexWithRelation($parameters[0], $relation);
            }

            if ($method === "store{$studly}") {
                return $this->storeWithRelation($parameters[0], $parameters[1], $relation);
            }

            if ($method === "show{$studly}") {
                return $this->showWithRelation($parameters[0], $parameters[1], $relation);
            }

            if ($method === "update{$studly}") {
                return $this->updateWithRelation($parameters[0], $parameters[1], $parameters[2], $relation);
            }

            if ($method === "destroy{$studly}") {
                return $this->destroyWithRelation($parameters[0], $parameters[1], $relation);
            }

            // roles
            if ($method === "assign{$studly}") {
                return $this->assignWithRoleBased($parameters[0], $parameters[1], $relation);
            }

            if ($method === "sync{$studly}") {
                return $this->syncWithRoleBased($parameters[0], $parameters[1], $relation);
            }

            if ($method === "revoke{$studly}") {
                return $this->revokeWithRoleBased($parameters[0], $parameters[1], $relation);
            }

            if ($method === "update{$studly}") {
                return $this->updateRoleModelWithRoleBased($parameters[0], $parameters[1], $relation);
            }
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}
