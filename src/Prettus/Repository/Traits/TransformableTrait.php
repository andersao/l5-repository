<?php namespace Prettus\Repository\Traits;

/**
 * Class TransformableTrait
 * @package Prettus\Repository\Traits
 * @deprecated since version 2.0.15. Use the Transformable trait.
 */
trait TransformableTrait {

    /**
     * @return array
     */
    public function transform()
    {
        return $this->toArray();
    }

}