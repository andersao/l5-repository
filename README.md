# Laravel 5 Repositories

Laravel 5 Repositories is used to abstract the data layer, making our application more flexible to maintain.

## Installation

Add this line "prettus/l5-repository": "1.0.*" in your composer.json.

```json
"require": {
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

### Repository

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
- skipCriteria()

### Criteria

- apply($query)

## Utilisation

### Create a Repository

```php
use Prettus\Repository\Eloquent\Repository;

class PostRepository extends RepositoryBase {

    public function __construct(Post $model)
    {
        parent::__construct($model);
    }   
    
}
```

### Using the Repository in a Controller

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
        $posts = $this->repository->all();
		...
    }


    public function show($id)
    {
        $post = $this->repository->find($id);
		...
    }

    public function store()
    {

        $post = $this->repository->create( Input::all() );
        ...
    }

    public function update($id)
    {
    	$post = $this->repository->update( Input::all(), $id );
    	...
    }

    public function destroy($id){
        $this->repository->delete($id)
        ...
    }
}
```

### Create a Criteria

```php
class MyCriteria implements \Prettus\Repository\Contracts\Criteria {

    public function apply($query)
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

Getting results from criteria

```php
$posts = $this->repository->getByCriteria(new MyCriteria());
```

Setting Criteria default in Repository

```php
use Prettus\Repository\Eloquent\Repository;

class PostRepository extends RepositoryBase {

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

### Using the RequestCriteria

RequestCriteria is a standard Criteria implementation. It enables filters perform in the repository from parameters sent in the request.

You can perform a dynamic search , filtering the data and customize queries

To use the Criteria in your repository , you can add a new criteria in the boot method of your repository , or directly use in your controller , in order to filter out only a few requests

####Enabling in your Repository

```php
use Prettus\Repository\Eloquent\Repository;
use Prettus\Repository\Criteria\RequestCriteria;

class PostRepository extends RepositoryBase {

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
        $this->pushCriteria(new RequestCriteria(app('request')));
        ...
    }
    
}
```

Remember, you need to define which fields from the model can are searchable.

In your repository set **$fieldSearchable** with their fields searchable.

```php
protected $fieldSearchable = [
	'name',
	'email'
];
```

You can set the type of condition will be used to perform the query , the default condition is "**=**"

```php
protected $fieldSearchable = [
	'name'=>'like',
	'email', // Default Condition "**=**"
	'your_field'=>'condition'
];
```

####Enabling in your Controller

```php
	public function index()
    {
        $this->repository->pushCriteria(new RequestCriteria(app('request')));
        $posts = $this->repository->all();
		...
    }
```

#### Example the Crirteria

Request all data without filter by request

*http://prettus.local/users*

```json
[
{
id: 1,
name: "Anderson Andrade",
email: "email@gmail.com",
created_at: "-0001-11-30 00:00:00",
updated_at: "-0001-11-30 00:00:00"
},
{
id: 2,
name: "Lorem Ipsum",
email: "lorem@ipsum.com",
created_at: "-0001-11-30 00:00:00",
updated_at: "-0001-11-30 00:00:00"
},
{
id: 3,
name: "Laravel",
email: "laravel@gmail.com",
created_at: "-0001-11-30 00:00:00",
updated_at: "-0001-11-30 00:00:00"
}
]
``

Conducting research in the repository

*http://prettus.local/users?search=Anderson%20Andrade*

or

*http://prettus.local/users?search=Anderson&searchFields=name:like*

or

*http://prettus.local/users?search=email@gmail.com&searchFields=email:=*

```json
[
{
id: 1,
name: "Anderson Andrade",
email: "email@gmail.com",
created_at: "-0001-11-30 00:00:00",
updated_at: "-0001-11-30 00:00:00"
}
]
```

Filtering fields

*http://prettus.local/users?filter=id;name*

```json
[
{
id: 1,
name: "Anderson Andrade"
},
{
id: 2,
name: "Lorem Ipsum"
},
{
id: 3,
name: "Laravel"
}
]
```

Sorting the results

*http://prettus.local/users?filter=id;name&orderBy=id&sortedBy=desc*

```json
[
{
id: 3,
name: "Laravel"
},
{
id: 2,
name: "Lorem Ipsum"
},
{
id: 1,
name: "Anderson Andrade"
}
]
```

####Overwrite params name

You can change the name of the parameters in the configuration file **config/repository-criteria.php**

# Author

Anderson Andrade - <contato@andersonandra.de>