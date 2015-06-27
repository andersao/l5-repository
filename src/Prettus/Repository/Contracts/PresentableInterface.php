<?php
namespace Prettus\Repository\Contracts;

/**
 * Interface PresentableInterface
 * @package Prettus\Repository\Contracts
 */
interface PresentableInterface
{
    /**
     * @param PresenterInterface $presenter
     * @return mixed
     */
    public function setPresenter(PresenterInterface $presenter);

    /**
     * @return mixed
     */
    public function presentable();
}