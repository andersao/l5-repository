<?php

namespace Prettus\Repository\Traits;
use Prettus\Repository\Contracts\PresenterInterface;

/**
 * Class PresentableRepository
 * @package Prettus\Repository\Traits
 */
trait Presentable {

    /**
     * @var PresenterInterface
     */
    protected $presenter = null;

    /**
     * @param \Prettus\Repository\Contracts\PresenterInterface $presenter
     * @return $this
     */
    public function setPresenter(PresenterInterface $presenter){
        $this->presenter = $presenter;
        return $this;
    }

    /**
     * @return $this|mixed
     */
    public function presentable()
    {
        if( isset($this->presenter) && $this->presenter instanceof PresenterInterface )
        {
            return $this->presenter->present($this);
        }

        return $this;
    }

}