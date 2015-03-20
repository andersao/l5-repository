<?php namespace Prettus\Repository\Eloquent;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\PresenterInterface;
use Prettus\Repository\Contracts\RepositoryCriteriaInterface;
use Prettus\Repository\Exceptions\PresenterException;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Repository\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Collection;

/**
 * Class BaseRepository
 * @package Prettus\Repository\Eloquent
 */
abstract class BaseRepository implements RepositoryInterface, RepositoryCriteriaInterface {

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
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeModel();
        $this->makePresenter();
        $this->boot();
    }

    /**
     *
     */
    public function boot(){}

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract public function model();

    /**
     * Specify Presenter class name
     *
     * @return mixed
     */
    public function presenter(){
        return null;
    }

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model)
        {
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }


    /**
     * @return PresenterInterface
     * @throws RepositoryException
     */
    public function makePresenter()
    {
        if( !is_null($this->presenter()) )
        {
            $presenter = $this->app->make($this->presenter());

            if (!$presenter instanceof PresenterInterface )
            {
                throw new RepositoryException("Class {$this->presenter()} must be an instance of Artesaos\\Warehouse\\Contracts\\PresenterInterface");
            }

            return $this->presenter = $presenter;
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
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        $this->applyCriteria();

        if( $this->model instanceof \Illuminate\Database\Eloquent\Builder ){
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }

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
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $results = $this->model->paginate($limit, $columns);
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
        $model = $this->model->find($id, $columns);
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
    public function findByField($field, $value, $columns = array('*'))
    {
        $this->applyCriteria();
        $model = $this->model->where($field,'=',$value)->get();
        return $this->parserResult($model);
    }

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $model = $this->model->newInstance($attributes);
        $model->save();

        return $this->parserResult($model);
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param $id
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $model = $this->find($id);
        $model->fill($attributes);
        $model->save();

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
        $model = $this->find($id);
        return $model->delete();
    }

    /**
     * Load relations
     *
     * @param array $relations
     * @return $this
     */
    public function with(array $relations)
    {
        $this->model = $this->model->with($relations);
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
     * Apply criteria in current Query
     *
     * @return $this
     */
    protected function applyCriteria()
    {

        if( $this->skipCriteria === true )
        {
            return  $this;
        }

        $criteria = $this->getCriteria();

        if( $criteria )
        {
            foreach($criteria as $c)
            {
                if( $c instanceof CriteriaInterface )
                {
                    $this->model = $c->apply($this->model, $this);
                }
            }
        }

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
        if( $this->presenter instanceof PresenterInterface )
        {
            return $this->presenter->present($result);
        }

        return $result;
    }
}