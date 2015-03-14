# Laravel 5 Repositories

[![Build Status](https://travis-ci.org/andersao/l5-repository.svg)](https://travis-ci.org/andersao/l5-repository.svg)
[![Total Downloads](https://poser.pugx.org/prettus/l5-repository/downloads.svg)](https://packagist.org/packages/prettus/l5-repository)
[![Latest Stable Version](https://poser.pugx.org/prettus/l5-repository/v/stable.svg)](https://packagist.org/packages/prettus/l5-repository)
[![Latest Unstable Version](https://poser.pugx.org/prettus/l5-repository/v/unstable.svg)](https://packagist.org/packages/prettus/l5-repository)
[![License](https://poser.pugx.org/prettus/l5-repository/license.svg)](https://packagist.org/packages/prettus/l5-repository)

Laravel 5 Repositories is used to abstract the data layer, making our application more flexible to maintain.

## Installation

In your terminal run **composer require prettus/l5-repository.** This will grab the last release.

Or

Edit your composer.json like this:

```json
"require": {
    ...
    "prettus/l5-repository": "1.0.*"
}
```

Issue composer update

Add to app/config/app.php service provider array:

```php
    'Prettus\Repository\RepositoryServiceProvider',
```

Publish Configuration

```shell
php artisan vendor:publish --provider="Prettus\Repository\RepositoryServiceProvider"
```

## Methods

### Prettus\Repository\Contracts\Repository

- scopeReset()
- find($id, $columns = ['*'])
- findByField($field, $value, $columns = ['*'])
- all($columns = array('*'))
- paginate($limit = null, $columns = ['*'])
- create(array $attributes)
- update(array $attributes, $id)
- delete($id)
- getModel()
- with(array $relations);
- pushCriteria(Criteria $criteria)
- getCriteria()
- getByCriteria(Criteria $criteria)
- skipCriteria($status = true)
- getFieldsSearchable()
- pushMutatorBeforeAll(Mutator $mutator)
- pushMutatorBeforeSave(Mutator $mutator)
- pushMutatorBeforeUpdate(Mutator $mutator)
- getMutatorBeforeSave()
- getMutatorBeforeUpdate()
- skipMutators($status = true)

### Prettus\Repository\Contracts\Criteria

- apply($query, Repository $repository)

### Prettus\Repository\Contracts\Mutator

- transform(Model $model);

## Usage

### Create a Model

Create your model normally, but it is important to define the attributes that can be filled from the input form data.

```php
class Post extends Eloquent { // or Ardent, Or any other Model Class

    protected $fillable = [
        'title',
        'author',
        ...
     ];

     ...
}
```

### Create a Repository

```php
use Prettus\Repository\Eloquent\Repository;

class PostRepository extends Repository {

    public function __construct(Post $model)
    {
        parent::__construct($model);
    }   
    
}
```

### Use methods

```php
class PostsController extends BaseController {

    /**
     * @var PostRepository
     */
    protected $repository;

    public function __construct(PostRepository $repository){
        $this->repository = $repository;
    }
    
    ....
}
```

Find all results in Repository

```php
$posts = $this->repository->all();
```

Find all results in Repository with pagination

```php
$posts = $this->repository->paginate($limit = null, $columns = ['*']);
```

Find by result by id

```php
$post = $this->repository->find($id);
```

Find by result by field name

```php
$posts = $this->repository->findByField('country_id','15');
```

Create new entry in Repository

```php
$post = $this->repository->create( Input::all() );
```

Update entry in Repository

```php
$post = $this->repository->update( Input::all(), $id );
```

Delete entry in Repository

```php
$this->repository->delete($id)
```


### Create a Criteria

Criteria is a way to change the repository of the query by applying specific conditions according to their need. You can add multiple Criteria in your repository

```php

use Prettus\Repository\Contracts\Repository;

class MyCriteria implements \Prettus\Repository\Contracts\Criteria {

    public function apply($query, Repository $repository)
    {
        $query = $query->where('user_id','=', Auth::user()->id );
        return $query;
    }
}
```

### Using the Criteria in a Controller

```php

class PostsController extends BaseController {

    /**
     * @var PostRepository
     */
    protected $repository;

    public function __construct(PostRepository $repository){
        $this->repository = $repository;
    }


    public function index()
    {
        $this->repository->pushCriteria(new MyCriteria());
        $posts = $this->repository->all();
		...
    }

}
```

Getting results from Criteria

```php
$posts = $this->repository->getByCriteria(new MyCriteria());
```

Setting the default Criteria in Repository

```php
use Prettus\Repository\Eloquent\Repository;

class PostRepository extends Repository {

    public function __construct(Post $model)
    {
        parent::__construct($model);
    }
    
    public function boot(){
        $this->pushCriteria(new MyCriteria());
        $this->pushCriteria(new AnotherCriteria());
        ...
    }
    
}
```

### Skip criteria defined in the repository

Use *skipCriteria* before any method in the repository

```php

$posts = $this->repository->skipCriteria()->all();

```

### Using the RequestCriteria

RequestCriteria is a standard Criteria implementation. It enables filters to perform in the repository from parameters sent in the request.

You can perform a dynamic search, filter the data and customize the queries

To use the Criteria in your repository, you can add a new criteria in the boot method of your repository, or directly use in your controller, in order to filter out only a few requests

####Enabling in your Repository

```php
use Prettus\Repository\Eloquent\Repository;
use Prettus\Repository\Criteria\RequestCriteria;

class PostRepository extends Repository {

	/**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'email'
    ];

    public function __construct(Post $model)
    {
        parent::__construct($model);
    }
    
    public function boot(){
        $this->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        ...
    }
    
}
```

Remember, you need to define which fields from the model can be searchable.

In your repository set **$fieldSearchable** with the name of the fields to be searchable.

```php
protected $fieldSearchable = [
	'name',
	'email'
];
```

You can set the type of condition which will be used to perform the query, the default condition is "**=**"

```php
protected $fieldSearchable = [
	'name'=>'like',
	'email', // Default Condition "="
	'your_field'=>'condition'
];
```

####Enabling in your Controller

```php
	public function index()
    {
        $this->repository->pushCriteria(app('Prettus\Repository\Criteria\RequestCriteria'));
        $posts = $this->repository->all();
		...
    }
```

#### Example the Criteria

Request all data without filter by request

*http://prettus.local/users*

```json
[
    {
        "id": 1,
        "name": "Anderson Andrade",
        "email": "email@gmail.com",
        "created_at": "-0001-11-30 00:00:00",
        "updated_at": "-0001-11-30 00:00:00"
    },
    {
        "id": 2,
        "name": "Lorem Ipsum",
        "email": "lorem@ipsum.com",
        "created_at": "-0001-11-30 00:00:00",
        "updated_at": "-0001-11-30 00:00:00"
    },
    {
        "id": 3,
        "name": "Laravel",
        "email": "laravel@gmail.com",
        "created_at": "-0001-11-30 00:00:00",
        "updated_at": "-0001-11-30 00:00:00"
    }
]
```

Conducting research in the repository

*http://prettus.local/users?search=Anderson%20Andrade*

or

*http://prettus.local/users?search=Anderson&searchFields=name:like*

or

*http://prettus.local/users?search=email@gmail.com&searchFields=email:=*

```json
[
    {
        "id": 1,
        "name": "Anderson Andrade",
        "email": "email@gmail.com",
        "created_at": "-0001-11-30 00:00:00",
        "updated_at": "-0001-11-30 00:00:00"
    }
]
```

Filtering fields

*http://prettus.local/users?filter=id;name*

```json
[
    {
        "id": 1,
        "name": "Anderson Andrade"
    },
    {
        "id": 2,
        "name": "Lorem Ipsum"
    },
    {
        "id": 3,
        "name": "Laravel"
    }
]
```

Sorting the results

*http://prettus.local/users?filter=id;name&orderBy=id&sortedBy=desc*

```json
[
    {
        "id": 3,
        "name": "Laravel"
    },
    {
        "id": 2,
        "name": "Lorem Ipsum"
    },
    {
        "id": 1,
        "name": "Anderson Andrade"
    }
]
```

### Create a Mutator

Mutator gives the capacity to transform the Model attributes before saving

```php

use Prettus\Repository\Contracts\Mutator;

class UserAttributeMutator implements Mutator {

    public function transform(Model $model){
        $model->setAttribute('user_id',Auth::user()->id);
        return $model;
    }
}
```

####Enabling in your Repository

Transform the data before saving

```php
public function boot(){
    $this->pushMutatorBeforeSave(new UserAttributeMutator());
    ...
}
```

Transform the data before update

```php
public function boot(){
    $this->pushMutatorBeforeUpdate(new UserAttributeMutator());
    ...
}
```


Transform the data before saving and update

```php
public function boot(){
    $this->pushMutatorBeforeAll(new UserAttributeMutator());
    ...
}
```

### Using Presenter

Presenter to wrap and render objects. See more [robclancy/presenter](https://github.com/robclancy/presenter)

####Create Presenter

```php
use Robbo\Presenter\Presenter;

class PostPresenter extends Presenter {

	 public function url()
     {
         return Str::slug($this->title.'-'.$this->id);
     }
    
}
```

####Enable Presenter

```php
use Prettus\Repository\Eloquent\Repository;
use Robbo\Presenter\Presenter;

class PostRepository extends Repository {

	 /**
     * @var Robbo\Presenter\Presenter
     */
    protected $presenter = 'PostPresenter';
    
}
```

####Overwrite params name

You can change the name of the parameters in the configuration file **config/repository-criteria.php**

# Author

Anderson Andrade - <contato@andersonandra.de>
