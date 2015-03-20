<?php namespace Prettus\Repository\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use League\Fractal\Pagination\PaginatorInterface;

/**
 * Class IlluminatePaginatorAdapter
 * @package Prettus\Repository\Pagination
 */
class IlluminatePaginatorAdapter implements PaginatorInterface {

    /**
     * The paginator instance.
     *
     * @var \Illuminate\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new illuminate pagination adapter.
     *
     *
     * @param Paginator|LengthAwarePaginator $paginator
     * @throws \Exception
     */
    public function __construct($paginator)
    {
        if( !$paginator instanceof Paginator && !$paginator instanceof LengthAwarePaginator )
        {
            throw new \Exception(get_class($paginator)." is not valid Paginator");
        }

        $this->paginator = $paginator;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->paginator->currentPage();
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->paginator->lastItem();
    }

    /**
     * Get the total.
     *
     * @return int
     */
    public function getTotal()
    {
        if( $this->paginator instanceof LengthAwarePaginator ){
            return $this->paginator->total();
        }

        return $this->paginator->count();
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->paginator->count();
    }

    /**
     * Get the number per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->paginator->perPage();
    }

    /**
     * Get the url for the given page.
     *
     * @param int $page
     *
     * @return string
     */
    public function getUrl($page)
    {
        return $this->paginator->url($page);
    }
}