<?php
namespace Prettus\Repository\Eloquent;

use Closure;
use Exception;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\Presentable;
use Prettus\Repository\Contracts\PresentableInterface;
use Prettus\Repository\Contracts\PresenterInterface;
use Prettus\Repository\Contracts\RepositoryCriteriaInterface;
use Prettus\Repository\Events\RepositoryEntityCreated;
use Prettus\Repository\Events\RepositoryEntityDeleted;
use Prettus\Repository\Events\RepositoryEntityUpdated;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Repository\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Collection;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class BaseRepository
 * @package Prettus\Repository\Eloquent
 */
abstract class BaseRepository implements RepositoryInterface, RepositoryCriteriaInterface
{

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $fieldSearchable = array();

    /**
     * @var PresenterInterface
     */
    protected $presenter;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Validation Rules
     *
     * @var array
     */
    protected $rules = null;

    /**
     * Collection of Criteria
     *
     * @var Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * @var bool
     */
    protected $skipPresenter = false;

    /**
     * @var \Closure
     */
    protected $scopeQuery = null;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeModel();
        $this->makePresenter();
        $this->makeValidator();
        $this->boot();
    }

    /**
     *
     */
    public function boot()
    {

    }

    /**
     * @throws RepositoryException
     */
    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model();

    /**
     * Specify Presenter class name
     *
     * @return string
     */
    public function presenter()
    {
        return null;
    }

    /**
     * Specify Validator class name of Prettus\Validator\Contracts\ValidatorInterface
     *
     * @return null
     * @throws Exception
     */
    public function validator()
    {

        if ( isset($this->rules) && ! is_null($this->rules) && is_array($this->rules) && !empty($this->rules) ) {
            if ( class_exists('Prettus\Validator\LaravelValidator') ) {
                $validator = app('Prettus\Validator\LaravelValidator');
                if ($validator instanceof ValidatorInterface) {
                    $validator->setRules($this->rules);
                    return $validator;
                }
            } else {
                throw new Exception( trans('repository::packages.prettus_laravel_validation_required') );
            }
        }

        return null;
    }

    /**
     * Set Presenter
     *
     * @param $presenter
     * @return $this
     */
    public function setPresenter($presenter)
    {
        $this->makePresenter($presenter);
        return $this;
    }

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * @param null $presenter
     * @return PresenterInterface
     * @throws RepositoryException
     */
    public function makePresenter($presenter = null)
    {
        $presenter = !is_null($presenter) ? $presenter : $this->presenter();

        if ( !is_null($presenter) ) {
            $this->presenter = is_string($presenter) ? $this->app->make($presenter) : $presenter;

            if (!$this->presenter instanceof PresenterInterface ) {
                throw new RepositoryException("Class {$presenter} must be an instance of Prettus\\Repository\\Contracts\\PresenterInterface");
            }

            return $this->presenter;
        }

        return null;
    }

    /**
     * @param null $validator
     * @return null|ValidatorInterface
     * @throws RepositoryException
     */
    public function makeValidator($validator = null)
    {
        $validator = !is_null($validator) ? $validator : $this->validator();

        if ( !is_null($validator) ) {
            $this->validator = is_string($validator) ? $this->app->make($validator) : $validator;

            if (!$this->validator instanceof ValidatorInterface ) {
                throw new RepositoryException("Class {$validator} must be an instance of Prettus\\Validator\\Contracts\\ValidatorInterface");
            }

            return $this->validator;
        }

        return null;
    }

    /**
     * Get Searchable Fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Query Scope
     *
     * @param \Closure $scope
     * @return $this
     */
    public function scopeQuery(\Closure $scope){
        $this->scopeQuery = $scope;
        return $this;
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        $this->applyCriteria();
        $this->applyScope();

        if ( $this->model instanceof \Illuminate\Database\Eloquent\Builder ){
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }

        $this->resetModel();

        return $this->parserResult($results);
    }

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return mixed
     */
    public function paginate($limit = null, $columns = array('*'))
    {
        $this->applyCriteria();
        $this->applyScope();
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $results = $this->model->paginate($limit, $columns);
        $this->resetModel();
        return $this->parserResult($results);
    }

    /**
     * Find data by id
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->findOrFail($id, $columns);
        $this->resetModel();
        return $this->parserResult($model);
    }

    /**
     * Find data by field and value
     *
     * @param $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findByField($field, $value = null, $columns = array('*'))
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->where($field,'=',$value)->get($columns);
        $this->resetModel();
        return $this->parserResult($model);
    }

    /**
     * Find data by multiple fields
     *
     * @param array $where
     * @param array $columns
     * @return mixed
     */
    public function findWhere( array $where , $columns = array('*'))
    {
        $this->applyCriteria();
        $this->applyScope();

        foreach ($where as $field => $value) {
            if ( is_array($value) ) {
                list($field, $condition, $val) = $value;
                $this->model = $this->model->where($field,$condition,$val);
            } else {
                $this->model = $this->model->where($field,'=',$value);
            }
        }

        $model = $this->model->get($columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Save a new entity in repository
     *
     * @throws ValidatorException
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        if ( !is_null($this->validator) ) {
            $this->validator->with($attributes)
                ->passesOrFail( ValidatorInterface::RULE_CREATE );
        }

        $model = $this->model->newInstance($attributes);
        $model->save();
        $this->resetModel();

        event(new RepositoryEntityCreated($this, $model));

        return $this->parserResult($model);
    }

    /**
     * Update a entity in repository by id
     *
     * @throws ValidatorException
     * @param array $attributes
     * @param $id
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $this->applyScope();

        if ( !is_null($this->validator) ) {
            $this->validator->with($attributes)
                ->setId($id)
                ->passesOrFail( ValidatorInterface::RULE_UPDATE );
        }

        $_skipPresenter = $this->skipPresenter;

        $this->skipPresenter(true);

        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        $this->skipPresenter($_skipPresenter);
        $this->resetModel();

        event(new RepositoryEntityUpdated($this, $model));

        return $this->parserResult($model);
    }

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     * @return int
     */
    public function delete($id)
    {
        $this->applyScope();

        $_skipPresenter = $this->skipPresenter;
        $this->skipPresenter(true);

        $model = $this->find($id);
        $originalModel = clone $model;

        $this->skipPresenter($_skipPresenter);
        $this->resetModel();

        $deleted = $model->delete();

        event(new RepositoryEntityDeleted($this, $originalModel));

        return $deleted;
    }

    /**
     * Load relations
     *
     * @param array|string $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);
        return $this;
    }

    /**
     * Set hidden fields
     *
     * @param array $fields
     * @return $this
     */
    public function hidden(array $fields)
    {
        $this->model->setHidden($fields);
        return $this;
    }

    /**
     * Set visible fields
     *
     * @param array $fields
     * @return $this
     */
    public function visible(array $fields)
    {
        $this->model->setVisible($fields);
        return $this;
    }

    /**
     * Push Criteria for filter the query
     *
     * @param CriteriaInterface $criteria
     * @return $this
     */
    public function pushCriteria(CriteriaInterface $criteria)
    {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Find data by Criteria
     *
     * @param CriteriaInterface $criteria
     * @return mixed
     */
    public function getByCriteria(CriteriaInterface $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);
        $results = $this->model->get();
        $this->resetModel();
        return $this->parserResult( $results );
    }

    /**
     * Skip Criteria
     *
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * Apply scope in current Query
     *
     * @return $this
     */
    protected function applyScope()
    {
        if ( isset($this->scopeQuery) && is_callable($this->scopeQuery) ) {
            $callback = $this->scopeQuery;
            $this->model = $callback($this->model);
        }

        return  $this;
    }

    /**
     * Apply criteria in current Query
     *
     * @return $this
     */
    protected function applyCriteria()
    {

        if ( $this->skipCriteria === true ) {
            return  $this;
        }

        $criteria = $this->getCriteria();

        if ( $criteria ) {
            foreach ($criteria as $c) {
                if ( $c instanceof CriteriaInterface ) {
                    $this->model = $c->apply($this->model, $this);
                }
            }
        }

        return $this;
    }

    /**
     * Skip Presenter Wrapper
     *
     * @param bool $status
     * @return $this
     */
    public function skipPresenter($status = true)
    {
        $this->skipPresenter = $status;
        return $this;
    }

    /**
     * Wrapper result data
     *
     * @param mixed $result
     * @return mixed
     */
    public function parserResult($result)
    {
        if ( $this->presenter instanceof PresenterInterface ) {

            if( $result instanceof Collection ){
                $result = $result->each(function($model){
                    if( $model instanceof Presentable ){
                        $model->setPresenter($this->presenter);
                    }
                    return $model;
                });
            } elseif ( $result instanceof Presentable ) {
                $result = $result->setPresenter($this->presenter);
            }

            if( !$this->skipPresenter){
                return $this->presenter->present($result);
            }
        }

        return $result;
    }
}