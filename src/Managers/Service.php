<?php

namespace Kjos\Command\Managers;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Service
{
    /** @var string */
    protected $model;

    /** @var string */
    protected $resource;

    /**
     * @param  mixed  $model
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function resource($model)
    {
        return $this->resource::make($model);
    }

    /**
     * @param  mixed  $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function resourcesCollection($resource)
    {
        return $this->resource::collection($resource);
    }


    public function index()
    {
        $limit = request()->integer('limit');
        $limit = $limit > 0 ? request()->integer('limit') : config('3kjos-command.route.pagination.limit', 10);
        $query = call_user_func([$this->model, 'query']);
        $query = $query->paginate($limit);
        return $this->resourcesCollection($query);
    }

    public function show($id)
    {
        $model = $this->model::findOrFail($id);
        return $this->resource($model);
    }

    public function store(array $data)
    {
        return $this->resource($this->model::create($data));
    }

    public function update(int $id, array $data)
    {
        $model = $this->model::findOrFail($id);
        if (count($data)) {
            $model->update($data);
        }
        return $this->resource($model);
    }

    public function destroy(int $id)
    {
        $model = call_user_func([$this->model, 'find'], (int) $id);

        if ($model) {
            $model->delete();
        }

        return response('success', Response::HTTP_OK);
    }

   public function saveFile($data, $path = null, $update = false, $fileKey = 'image', $model = null)
    {
        $file = data_get($data, $fileKey);

        if (! $file instanceof UploadedFile) {
            $data[$fileKey] = $file;
            return $data;
        }

        if ($update && $model) {
            Storage::delete($model->{$fileKey});
        }

        $path = $path ?? 'tenants/images'; // ← dossier relatif dans storage/app
        $fileName = Str::ulid() . '.' . $file->getClientOriginalExtension();
        $data[$fileKey] = $file->storeAs($path, $fileName);

        return $data;
    }
}
