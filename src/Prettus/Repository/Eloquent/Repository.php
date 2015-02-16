<?php namespace Prettus\Repository\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Prettus\Repository\Contracts\Repository as RepositoryInterface;
use Prettus\Repository\Contracts\Criteria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class Repository
 * @package Prettus\Repository\Contracts
 */
class Repository implements RepositoryInterface {

    /**
     * Collection of Criteria
     *
     * @var Collection
     */
    protected $criteria;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var $query
     */
    protected $query;

    /**
     * @var array
     */
    protected $fieldSearchable = array();

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    public function __construct(Model $model){
        $this->model = $model;
        $this->criteria = new Collection();
        $this->boot();
        $this->scopeReset();
    }

    /**
     * Reset internal Query
     *
     * @return $this
     */
    public function scopeReset()
    {
        $this->query =  $this->model;
        $this->skipCriteria(false);
        return $this;
    }

    /**
     *
     */
    public function boot(){}

    /**
     * Find data by id
     *
     * @param $id
     * @param array $columns
     * @return Model|Collection
     */
    public function find($id, $columns = array('*'))
    {
        $this->applyCriteria();
        return $this->query->find($id, $columns);
    }

    /**
     * Find data by field and value
     *
     * @param $field
     * @param $value
     * @param array $columns
     * @return Model|Collection
     */
    public function findByField($field, $value, $columns = array('*'))
    {
        $this->applyCriteria();
        return $this->query->where($field,'=',$value)->first();
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return Collection
     */
    public function all($columns = array('*'))
    {
        $this->applyCriteria();

        if( $this->query instanceof \Illuminate\Database\Eloquent\Builder ){
            return $this->query->get($columns);
        }

        return $this->query->all($columns);
    }

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate($limit = null, $columns = array('*'))
    {
        $this->applyCriteria();
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        return $this->query->paginate($limit, $columns);
    }

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes)
    {
        return $this->query->create($attributes);
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param $id
     * @return Model
     */
    public function update(array $attributes, $id)
    {
        $model = $this->find($id);
        $model->fill($attributes);
        return $model->save();
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
        $this->query = $this->query->with($relations);
        return $this;
    }

    /**
     * Get repository model
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Push Criteria for filter the query
     *
     * @param Criteria $criteria
     * @return mixed
     */
    public function pushCriteria(Criteria $criteria)
    {
        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function getCriteria(){
        return $this->criteria;
    }

    /**
     * Find data by Criteria
     *
     * @param Criteria $criteria
     * @return mixed
     */
    public function getByCriteria(Criteria $criteria)
    {
        $this->query = $criteria->apply($this->query, $this);
        return $this->query->get();
    }

    /**
     * Skip Criteria
     *
     * @return $this
     */
    public function skipCriteria($status = true){
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * Apply criteria in current Query
     *
     * @return $this
     */
    protected function applyCriteria(){

        if( $this->skipCriteria === true )
            return  $this;

        $criteria = $this->getCriteria();

        foreach($criteria as $c){
            if( $c instanceof Criteria ){
                $this->query = $c->apply($this->query, $this);
            }
        }

        return $this;
    }

    /**
     * Get Searchable Fields
     *
     * @return array
     */
    public function getFieldsSearchable(){
        return $this->fieldSearchable;
    }
}