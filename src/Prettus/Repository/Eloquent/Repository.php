<?php namespace Prettus\Repository\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\Mutator;
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
     * Collection of Mutator applied before save
     *
     * @var Collection
     */
    protected $mutatorsBeforeSave;

    /**
     * Collection of Mutator applied before update
     *
     * @var Collection
     */
    protected $mutatorsBeforeUpdate;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Model|Builder
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

    /**
     * @var bool
     */
    protected $skipMutator  = false;

    public function __construct(Model $model){
        $this->model = $model;
        $this->criteria = new Collection();
        $this->mutatorsBeforeSave = new Collection();
        $this->mutatorsBeforeUpdate = new Collection();
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
        $model = $this->query->newInstance($attributes);
        $model = $this->applyMutator("save", $model);
        $model->save();
        return $model;
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
        $model = $this->applyMutator("update", $model);
        $model->save();
        
        return $model;
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
     * @return $this
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
     * @param bool $status
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

    /**
     * Push mutator to be applied before saving and update
     *
     * @param Mutator $mutator
     * @return $this
     */
    public function pushMutatorBeforeAll(Mutator $mutator)
    {
        $this->pushMutatorBeforeSave($mutator);
        $this->pushMutatorBeforeUpdate($mutator);

        return $this;
    }

    /**
     * Push mutator to be applied before saving
     *
     * @param Mutator $mutator
     * @return $this
     */
    public function pushMutatorBeforeSave(Mutator $mutator)
    {
        $this->mutatorsBeforeSave->push($mutator);
        return $this;
    }

    /**
     * Push mutator to be applied before update
     *
     * @param Mutator $mutator
     * @return $this
     */
    public function pushMutatorBeforeUpdate(Mutator $mutator)
    {
        $this->mutatorsBeforeUpdate->push($mutator);
        return $this;
    }

    /**
     * Apply mutator for the action
     *
     * @param $action
     * @param Model $model
     * @return Model
     */
    protected function applyMutator($action, Model $model){

        if( $this->skipMutator === true )
            return  $model;

        switch($action){
            case "save"     : $mutators = $this->getMutatorBeforeSave(); break;
            case "update"   : $mutators = $this->getMutatorBeforeUpdate(); break;
            default         : $mutators = false; break;
        }

        if( $mutators ){
            foreach($mutators as $mutator){
                if( $mutator instanceof Mutator ){
                    $model = $mutator->transform($model);
                }
            }
        }

        return $model;
    }

    /**
     * Skip Mutators
     *
     * @param bool $status
     * @return $this
     */
    public function skipMutators($status = true){
        $this->skipMutator = $status;
        return $this;
    }

    /**
     * Get Collection of Mutator Before Save
     *
     * @return Collection
     */
    public function getMutatorBeforeSave()
    {
        return $this->mutatorsBeforeSave;
    }

    /**
     * Get Collection of Mutator Before Update
     *
     * @return Collection
     */
    public function getMutatorBeforeUpdate()
    {
        return $this->mutatorsBeforeUpdate;
    }
}