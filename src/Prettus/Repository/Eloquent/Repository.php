<?php namespace Prettus\Repository\Eloquent;

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Contracts\Pagination\Paginator as PaginatorInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Prettus\Repository\Contracts\Mutator;
use Prettus\Repository\Contracts\Repository as RepositoryInterface;
use Prettus\Repository\Contracts\Criteria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Robbo\Presenter\Presenter;
use Exception;

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
     * @var Presenter
     */
    protected $presenter = null;

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
        $result = $this->query->findOrFail($id, $columns);
        return $this->parserResult( $result );
    }

    /**
     * Find data by field and value
     *
     * @param $field
     * @param $value
     * @param array $columns
     * @return Collection
     */
    public function findByField($field, $value, $columns = array('*'))
    {
        $this->applyCriteria();
        $result = $this->query->where($field,'=',$value)->get();
        return $this->parserResult( $result );
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

        $results = $this->query->all($columns);

        return $this->parserResult( $results );
    }

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($limit = null, $columns = array('*'))
    {
        $this->applyCriteria();
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $results = $this->query->paginate($limit, $columns);
        return $this->parserResult( $results );

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

        return $this->parserResult( $model );
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

        return $this->parserResult( $model );
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
        $results = $this->query->get();
        return $this->parserResult( $results );
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

    /**
     * @param $result
     * @return mixed
     */
    protected function parserResult($result){

        if( $result instanceof Model )
        {
            return $this->wrapperModelPresenter($result);
        }
        elseif( $result instanceof Collection )
        {
            $that = $this;
            $result->transform(function($item) use($that){
                return $that->wrapperModelPresenter($item);
            });

        }
        elseif( $result instanceof AbstractPaginator )
        {
            $collection = $result->getCollection();
            $that = $this;
            $collection->transform(function($item) use($that){
                return $that->wrapperModelPresenter($item);
            });

            $page = Paginator::resolveCurrentPage();
            $perPage = $result->perPage();

            if( $result instanceof LengthAwarePaginatorInterface )
            {
                return new LengthAwarePaginator($collection, $result->total(), $result->perPage(), $page, [
                    'path' => Paginator::resolveCurrentPath()
                ]);
            }
            elseif( $result instanceof PaginatorInterface )
            {
                return new Paginator($collection, $perPage, $page, [
                    'path' => Paginator::resolveCurrentPath()
                ]);
            }
        }

        return $result;
    }

    /**
     *
     * @param Model $model
     * @return Model|Presenter
     * @throws Exception
     */
    protected function wrapperModelPresenter(Model $model){

        if( is_null($this->presenter) ){
            return $model;
        }

        if( !empty($this->presenter) && class_exists($this->presenter) ){
            return new $this->presenter($model);
        }

        throw new Exception("Class {$this->presenter} not found or not a Presenter valid");
    }
}
