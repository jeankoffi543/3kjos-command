# 3kjos-command for Laravel

# INSTALLATION

```composer require kjos/command```

# USAGE

```php artisan kjos:make:api name```

### Tip

**name** is the name of the route to create, the name of the migration table, the name of the controller, model, form request, resource class and file name.

this command will create:

 - append api.php: add route for **name** (index, show, store, put, delete)
 - create a controller file **NameController** with all methods
 - create resource file **NameResource** with all values from setting fields
 - create model file **Name** with table name, fillable array, relations
 - create form request file **NameRequest** with all rules from setting fields
 - create migrations with all setting fields 

# OPTIONS

### --f|force
allows you to force the creation of files if they exist
```php artisan kjos:make:api name --force```

or

```php artisan kjos:make:api name -f```


### --er|errorhandler

Use a central method for all controller method to handle error

```php artisan kjos:make:api name --errorhandler```

or

```php artisan kjos:make:api name -er```

```php
 public function store(UserRequest $request)
{
    return $this->errorHandler(function () use ($request) {
      return new UserResource(User::create($request->validated()));
    });
}
```

**Error Handling with errorHandler Method**
The `errorHandler` method is a utility function designed to execute a callable while handling various exceptions that may occur. It provides robust error handling and ensures that different types of errors are appropriately managed.

- **ModelNotFoundException**: If a model is not found, the method returns a `404 Not Found` response.

- **QueryException**: For security, reasons catches database query errors and returns a 404 Not Found response.

- **General Exceptions**: The method checks the exception code and handles it as follows:
  - `404`: Returns a `404 Not Found` response.
  - `403`: Returns a `403 Forbidden` response with the error message.
  - `422`: Returns a `403 Forbidden` response with the error message.
  - `Other errors`: A generic `500 Internal Server Error` response is returned with the exception message.

This approach ensures that your application responds to errors with proper HTTP status codes, making error handling more predictable and user-friendly.




### --c|centralize

centralize contoller method actions

```php artisan kjos:make:api name --centralize```

or

```php artisan kjos:make:api name -c```

```php
 public function store(Request $request)
{
   return $this->errorHandler(function () use ($request) {
      return Central::store(User::class, UserResource::class, $request->validated());
  });
}
```

Centralized management of `index`, `show`, `store`, `update` and `delete` methods



### --factory

Generate relative model factory base on relative model attributes

```php artisan kjos:make:api name --gactory```

```php
public function definition(): array
{
   return [
      'client_id' => 11,
      'price' => 6765610,
      'partner_id' => 25,
   ];
}
```
