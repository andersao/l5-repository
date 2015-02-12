<?php namespace Prettus\Repository\Contracts;

/**
 * Interface Criteria
 * @package Prettus\Repository\Contracts
 */
interface Criteria {

    /**
     * Apply criteria in query repository
     *
     * @param $query
     * @param Repository $repository
     * @return mixed
     */
    public function apply($query, Repository $repository);

}