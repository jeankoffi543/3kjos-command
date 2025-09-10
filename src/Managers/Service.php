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

    protected function model(mixed $mixed = null): string
    {
        return '';
    }

    protected function resource(mixed $mixed = null): string
    {
        return '';
    }

    /** @var array<string, mixed> $dispatchEvents */
    protected $dispatchEvents = [];

    /**
     * Triggered after a resource is deleted.
     *
     * @param mixed $model The model that was deleted.
     *
     * @return mixed
     */
    protected function deleted($model): mixed
    {
        return '';
    }


    /**
     * @param  mixed $model
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function resources($model)
    {
        return ($this->resource ?? $this->resource())::make($model);
    }

    /**
     * @param  mixed  $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function resourcesCollection($resource)
    {
        return ($this->resource ?? $this->resource())::collection($resource);
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
        $query = call_user_func([($this->model ?? $this->model()), 'query']);
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
        $model = ($this->model ?? $this->model())::findOrFail($id);
        return $this->resources($model);
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
        $model = ($this->model ?? $this->model())::create($this->saveFile($data));
        if (!empty($this->dispatchEvents) && $event = data_get($this->dispatchEvents, 'created', null)) {
            $event::dispatch($model);
        }
        return $this->resources($model);
    }

    public function update(int $id, array $data): \Illuminate\Http\Resources\Json\JsonResource
    {
        $model = ($this->model ?? $this->model())::findOrFail($id);
        if (count($data)) {
            $model->update($this->saveFile($data, true, $model));
            if (!empty($this->dispatchEvents) && $event = data_get($this->dispatchEvents, 'updated', null)) {
                $event::dispatch($model);
            }
        }
        return $this->resources($model);
    }

    /**
     * Delete a model by ID.
     *
     * @param int $id the ID of the model to delete
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id): \Illuminate\Http\Response
    {
        $model = call_user_func([($this->model ?? $this->model()), 'find'], (int) $id);

        if ($model) {
            if ($model->{$this->fileKey()}) {
                Storage::delete($model->{$this->fileKey()});
            }
            $model->delete();
            if (!empty($this->dispatchEvents) && $event = data_get($this->dispatchEvents, 'deleted', null)) {
                $event::dispatch($id, $this->deleted($model));
            }
        }

        return response('success', Response::HTTP_OK);
    }

    /**
     * @param array $data
     * @param null $path
     * @param bool $update
     * @param string $this->fileKey()
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
     * The key of the file in the $data array is determined by the $this->fileKey() parameter.
     * If the $this->fileKey() parameter is not given, the key will be 'image'.
     */
    public function saveFile($data, $update = false, $model = null): array
    {
        $file = data_get($data, $this->fileKey());

        if (! $file instanceof UploadedFile) {
            $data[$this->fileKey()] = $file;
            return $data;
        }

        if ($update && $model) {
            Storage::delete($model->{$this->fileKey()});
        }

        $fileName = Str::ulid() . '.' . $file->getClientOriginalExtension();
        $data[$this->fileKey()] = $file->storeAs($this->filePath(), $fileName);

        return $data;
    }

    /**
     * Return the key of the file in the $data array.
     *
     * If not overridden, the key will be 'image'.
     *
     * @return string The key of the file in the $data array
     */
    public function fileKey(): string
    {
        return 'image';
    }

    /**
     * Return the path where the file will be saved.
     *
     * @return string The path where the file will be saved
     */
    public function filePath(): string
    {
        return 'image';
    }
}
