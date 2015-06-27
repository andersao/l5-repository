<?php
namespace Prettus\Repository\Contracts;

/**
 * Interface Transformable
 * @package Prettus\Repository\Contracts
 */
interface Transformable
{
    /**
     * @return array
     */
    public function transform();
}