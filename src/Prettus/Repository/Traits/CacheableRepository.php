<?php namespace Prettus\Repository\Traits;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;

/**
 * Class CacheableRepository
 * @package Prettus\Repository\Traits
 */
trait CacheableRepository {

    /**
     * @var int
     */
    protected $cacheMinutes = 30;

    /**
     * @var array
     */
    protected $cacheOnly = null;

    /**
     * @var array
     */
    protected $cacheExcept = null;

    /**
     * @var bool
     */
    protected $cacheSkip = false;

    /**
     * @var string
     */
    protected $cacheKeyPrefix = "";

    /**
     * @var CacheRepository
     */
    protected $cacheRepository = null;

    /**
     * @var Request
     */
    protected $request = null;

    /**
     * Set Cache Repository
     *
     * @param CacheRepository $repository
     * @return $this
     */
    public function setCacheRepository(CacheRepository $repository)
    {
        $this->cacheRepository = $repository;
        return $this;
    }

    /**
     * Return instance of Cache Repository
     *
     * @return CacheRepository
     */
    public function getCacheRepository()
    {
        if( is_null($this->cacheRepository) )
        {
            $this->cacheRepository = app('cache');
        }

        return $this->cacheRepository;
    }

    /**
     * Skip Cache
     *
     * @param bool $status
     * @return $this
     */
    public function skipCache($status = true)
    {
        $this->cacheSkip = $status;
        return $this;
    }

    /**
     * @param $method
     * @return bool
     */
    protected function allowedCache($method)
    {
        if( is_array($this->cacheOnly) && isset($this->cacheOnly[$method]) )
        {
            return true;
        }

        if( is_array($this->cacheExcept) && !in_array($method, $this->cacheExcept) )
        {
            return true;
        }

        if( is_null($this->cacheOnly) && is_null($this->cacheExcept) )
        {
            return true;
        }

        return false;
    }

    /**
     * Get Cache key for the method
     *
     * @param $method
     * @param $args
     * @return string
     */
    public function getCacheKey($method, $args = null){

        if( is_null($this->request) )
        {
            $this->request = app('Illuminate\Http\Request');
        }

        $args = serialize($args);
        $key  = md5($method.$args.$this->request->fullUrl());

        return $key;

    }

    /**
     * Get cache minutes
     *
     * @return int
     */
    public function getCacheMinutes(){

    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        if( !$this->allowedCache('all') || $this->cacheSkip ){
            return parent::all($columns);
        }

        $key     = $this->getCacheKey('all', func_get_args());
        $minutes = $this->getCacheMinutes();
        $value   = $this->getCacheRepository()->remember($key, $minutes, function() use($columns) {
            return parent::all($columns);
        });

        return $value;
    }

    /**
     * Retrieve all data of repository, paginated
     * @param null $limit
     * @param array $columns
     * @return mixed
     */
    public function paginate($limit = null, $columns = array('*'))
    {
        if( !$this->allowedCache('paginate') || $this->cacheSkip ){
            return parent::paginate($limit, $columns);
        }

        $key     = $this->getCacheKey('paginate', func_get_args());
        $minutes = $this->getCacheMinutes();
        $value   = $this->getCacheRepository()->remember($key, $minutes, function() use($limit, $columns) {
            return parent::paginate($limit, $columns);
        });

        return $value;
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
        if( !$this->allowedCache('find') || $this->cacheSkip ){
            return parent::find($id, $columns);
        }

        $key     = $this->getCacheKey('find', func_get_args());
        $minutes = $this->getCacheMinutes();
        $value   = $this->getCacheRepository()->remember($key, $minutes, function() use($id, $columns) {
            return parent::find($id, $columns);
        });

        return $value;
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
        if( !$this->allowedCache('findByField') || $this->cacheSkip ){
            return parent::findByField($field, $value, $columns);
        }

        $key     = $this->getCacheKey('findByField', func_get_args());
        $minutes = $this->getCacheMinutes();
        $value   = $this->getCacheRepository()->remember($key, $minutes, function() use($field, $value, $columns) {
            return parent::findByField($field, $value, $columns);
        });

        return $value;
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
        if( !$this->allowedCache('findWhere') || $this->cacheSkip ){
            return parent::findWhere($where, $columns);
        }

        $key     = $this->getCacheKey('findWhere', func_get_args());
        $minutes = $this->getCacheMinutes();
        $value   = $this->getCacheRepository()->remember($key, $minutes, function() use($where, $columns) {
            return parent::findWhere($where, $columns);
        });

        return $value;
    }

    /**
     * Find data by Criteria
     *
     * @param CriteriaInterface $criteria
     * @return mixed
     */
    public function getByCriteria(CriteriaInterface $criteria)
    {
        if( !$this->allowedCache('getByCriteria') || $this->cacheSkip ){
            return parent::getByCriteria($criteria);
        }

        $key     = $this->getCacheKey('getByCriteria', func_get_args());
        $minutes = $this->getCacheMinutes();
        $value   = $this->getCacheRepository()->remember($key, $minutes, function() use($criteria) {
            return parent::getByCriteria($criteria);
        });

        return $value;
    }
}