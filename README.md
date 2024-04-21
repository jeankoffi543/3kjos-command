# 3kjos-command

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

