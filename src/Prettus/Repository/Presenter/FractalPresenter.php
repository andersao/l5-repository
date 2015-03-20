<?php namespace Prettus\Repository\Presenter;

use Prettus\Repository\Contracts\PresenterInterface;
use Prettus\Repository\Pagination\IlluminatePaginatorAdapter;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

/**
 * Class FractalPresenter
 * @package Prettus\Repository\Presenter
 */
abstract class FractalPresenter implements PresenterInterface {

    /**
     * @var string
     */
    protected $resourceKeyItem = null;

    /**
     * @var string
     */
    protected $resourceKeyCollection = null;

    /**
     * @var \League\Fractal\Manager
     */
    protected $fractal = null;

    /**
     * @var \League\Fractal\Resource\Collection
     */
    protected $resource = null;

    /**
     *
     */
    public function __construct(){
        $this->fractal  = new Manager();
        $this->parseIncludes();
        $this->setupSerializer();
    }

    /**
     * @return $this
     */
    protected function setupSerializer()
    {
        $serializer = $this->serializer();

        if( $serializer instanceof SerializerAbstract ){
            $this->fractal->setSerializer($serializer);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function parseIncludes()
    {

        $request        = app('Illuminate\Http\Request');
        $paramIncludes  = config('repository.fractal.params.include','include');

        if ( $request->has( $paramIncludes ) )
        {
            $this->fractal->parseIncludes( $request->get( $paramIncludes ) );
        }

        return $this;
    }

    /**
     * Get Serializer
     *
     * @return SerializerAbstract
     */
    public function serializer()
    {
        return null;
    }

    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    abstract public function getTransformer();

    /**
     * Prepare data to present
     *
     * @param $data
     * @return mixed
     */
    public function present($data)
    {
        if( $data instanceof EloquentCollection )
        {
            $this->resource = $this->transformCollection($data);
        }
        elseif( $data instanceof AbstractPaginator )
        {
            $this->resource = $this->transformPaginator($data);
        }
        else
        {
            $this->resource = $this->transformItem($data);
        }

        return $this->fractal->createData($this->resource)->toArray();
    }

    /**
     * @param $data
     * @return Item
     */
    protected function transformItem($data)
    {
        return new Item($data, $this->getTransformer(), $this->resourceKeyItem);
    }

    /**
     * @param $data
     * @return \League\Fractal\Resource\Collection
     */
    protected function transformCollection($data)
    {
        return new Collection($data, $this->getTransformer(), $this->resourceKeyCollection);
    }

    /**
     * @param AbstractPaginator|LengthAwarePaginator|Paginator $paginator
     * @return \League\Fractal\Resource\Collection
     */
    protected function transformPaginator($paginator)
    {
        $collection = $paginator->getCollection();
        $resource = new Collection($collection, $this->getTransformer(), $this->resourceKeyCollection);
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        return $resource;
    }
}