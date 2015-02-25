<?php namespace Prettus\Repository\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface Mutator
 * @package Prettus\Repository\Contracts]
 */
interface Mutator {

    /**
     * Transform model data
     *
     * @param Model $model
     * @return Model
     */
    public function transform(Model $model);

}