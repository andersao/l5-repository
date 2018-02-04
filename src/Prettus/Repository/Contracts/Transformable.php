<?php
namespace Prettus\Repository\Contracts;

/**
 * Interface Transformable
 * @package Prettus\Repository\Contracts
 * @author Anderson Andrade <contato@andersonandra.de>
 */
interface Transformable
{
    /**
     * @return array
     */
    public function transform();
}
