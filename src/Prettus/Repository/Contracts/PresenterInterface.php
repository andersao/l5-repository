<?php
namespace Prettus\Repository\Contracts;

/**
 * Interface PresenterInterface
 * @package Prettus\Repository\Contracts
 */
interface PresenterInterface
{
    /**
     * Prepare data to present
     *
     * @param $data
     *
     * @return mixed
     */
    public function present($data);
}
