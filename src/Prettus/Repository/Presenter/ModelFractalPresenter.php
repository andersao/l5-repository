<?php namespace Prettus\Repository\Presenter;
use Prettus\Repository\Transformer\ModelTransformer;

/**
 * Class ModelFractalPresenter
 * @package Prettus\Repository\Presenter
 */
class ModelFractalPresenter extends FractalPresenter {

    /**
     * Transformer
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new ModelTransformer();
    }
}