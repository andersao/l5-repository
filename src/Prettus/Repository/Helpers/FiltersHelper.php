<?php

namespace Prettus\Repository\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FiltersHelper
{
    /**
     * @param Builder|Model $model
     * @param $filtersList
     * @return array
     */
    public static function checkAvailableAttributes($model, $filtersList)
    {
        $model = $model instanceof Builder ? $model->getModel() : $model;
        $filter = explode(';', $filtersList);
        $attributes = $model->getAttributes();
        foreach ($filter as $key => $attr) {
            if (!array_key_exists($attr, $attributes)) {
                unset($filter[$key]);
            }
        }
        return $filter;
    }
}