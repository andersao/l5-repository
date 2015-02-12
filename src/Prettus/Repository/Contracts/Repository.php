<?php namespace Prettus\Repository\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface Repository
 * @package Prettus\Repository\Contracts
 */
interface Repository {

    /**
     * Reset internal Query
     *
     * @return $this
     */
    public function scopeReset();

    /**
     * Find data by id
     *
     * @param $id
     * @param array $columns
     * @return Model|Collection
     */
    public function find($id, $columns = array('*'));

    /**
     * Find data by field and value
     *
     * @param $field
     * @param $value
     * @param array $columns
     * @return Model|Collection
     */
    public function findByField($field, $value, $columns = array('*'));

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return Collection
     */
    public function all($columns = array('*'));

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate($limit = null, $columns = array('*'));

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes);

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param $id
     * @return Model
     */
    public function update(array $attributes, $id);

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     * @return bool
     */
    public function delete($id);

    /**
     * Load relations
     *
     * @param array $relations
     * @return $this
     */
    public function with(array $relations);


    /**
     * Get repository model
     *
     * @return Model
     */
    public function getModel();

    /**
     * Push Criteria for filter the query
     *
     * @param Criteria $criteria
     * @return mixed
     */
    public function pushCriteria(Criteria $criteria);

    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function getCriteria();

    /**
     * Find data by Criteria
     *
     * @param Criteria $criteria
     * @return mixed
     */
    public function getByCriteria(Criteria $criteria);

    /**
     * Skip Criteria
     *
     * @return $this
     */
    public function skipCriteria();

    /**
     * Get Searchable Fields
     *
     * @return array
     */
    public function getFieldsSearchable();
}