<?php
namespace Prettus\Repository\Contracts;

/**
 * Interface CriteriaInterface
 * @package Prettus\Repository\Contracts
 * @author Anderson Andrade <contact@andersonandra.de>
 */
interface CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @param                     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository);
}
