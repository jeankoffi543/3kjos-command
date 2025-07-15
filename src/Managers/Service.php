<?php

namespace Kjos\Command\Managers;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kjos\Command\Concerns\InterractWithServices;

class Service
{
    use InterractWithServices;

    /** @var string */
    protected $model;

    /** @var string */
    protected $resource;

    /** @var array<string> $relations */
    protected $relations = [];

    /**
     * @param  mixed $model
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


    /**
     * Return a list of resources.
     *
     * This method will return a paginated list of resources.
     * The pagination limit is determined by the `limit` parameter in the request.
     * If the `limit` parameter is not given, the default limit is 10.
     * The default limit can be changed by setting the value of
     * `3kjos-command.route.pagination.limit` in the `config` file.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $limit = request()->integer('limit');
        $limit = $limit > 0 ? request()->integer('limit') : config('3kjos-command.route.pagination.limit', 10);
        $query = call_user_func([$this->model, 'query']);
        $query = $query->paginate($limit);
        return $this->resourcesCollection($query);
    }

    /**
     * Retrieve a model by ID.
     *
     * @param int $id the ID of the model to retrieve
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource The retrieved model
     */
    public function show($id): \Illuminate\Http\Resources\Json\JsonResource
    {
        $model = $this->model::findOrFail($id);
        return $this->resource($model);
    }

    /**
     * Create a new model
     *
     * @param array $data The data to create a new model
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource The created model
     */
    public function store(array $data): \Illuminate\Http\Resources\Json\JsonResource
    {
        return $this->resource($this->model::create($data));
    }

    public function update(int $id, array $data): \Illuminate\Http\Resources\Json\JsonResource
    {
        $model = $this->model::findOrFail($id);
        if (count($data)) {
            $model->update($data);
        }
        return $this->resource($model);
    }

    /**
     * Delete a model by ID.
     *
     * @param int $id the ID of the model to delete
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id): \Illuminate\Http\Response
    {
        $model = call_user_func([$this->model, 'find'], (int) $id);

        if ($model) {
            $model->delete();
        }

        return response('success', Response::HTTP_OK);
    }

    /**
     * @param array $data
     * @param null $path
     * @param bool $update
     * @param string $fileKey
     * @param null $model
     * @return array<mixed>
     */
    /**
     * Save a file in the storage/app directory.
     *
     * If the file is an instance of UploadedFile, it will be saved in the given path.
     * If the path is not given, it will be saved in the 'tenants/images' directory.
     *
     * If the $update parameter is true and the $model parameter is given, the file will be updated.
     * If the $update parameter is false, a new file will be created.
     *
     * The key of the file in the $data array is determined by the $fileKey parameter.
     * If the $fileKey parameter is not given, the key will be 'image'.
     */
   public function saveFile($data, $path = null, $update = false, $fileKey = 'image', $model = null): array
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
