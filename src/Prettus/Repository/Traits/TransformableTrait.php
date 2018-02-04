<?php

namespace Prettus\Repository\Traits;

/**
 * Class TransformableTrait
 * @package Prettus\Repository\Traits
 * @author Anderson Andrade <contact@andersonandra.de>
 */
trait TransformableTrait
{
    /**
     * @return array
     */
    public function transform()
    {
        return $this->toArray();
    }
}
