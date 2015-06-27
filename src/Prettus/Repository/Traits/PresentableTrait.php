<?php

namespace Prettus\Repository\Traits;
use Prettus\Repository\Contracts\PresenterInterface;

/**
 * Class PresentableTrait
 * @package Prettus\Repository\Traits
 */
trait PresentableTrait {

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
    public function presenter()
    {
        if( isset($this->presenter) && $this->presenter instanceof PresenterInterface )
        {
            return $this->presenter->present($this);
        }

        return $this;
    }

}