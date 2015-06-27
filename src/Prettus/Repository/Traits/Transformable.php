<?php namespace Prettus\Repository\Traits;

/**
 * Class Transformable
 * @package Prettus\Repository\Traits
 */
trait Transformable {

    /**
     * @return array
     */
    public function transform()
    {
        return $this->toArray();
    }

}