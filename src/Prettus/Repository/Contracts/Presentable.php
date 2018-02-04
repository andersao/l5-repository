<?php
namespace Prettus\Repository\Contracts;

/**
 * Interface Presentable
 * @package Prettus\Repository\Contracts
 * @author Anderson Andrade <contact@andersonandra.de>
 */
interface Presentable
{
    /**
     * @param PresenterInterface $presenter
     *
     * @return mixed
     */
    public function setPresenter(PresenterInterface $presenter);

    /**
     * @return mixed
     */
    public function presenter();
}
