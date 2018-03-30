<?php

namespace Prettus\Repository\Helpers;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RelationsHelper
 * @package Prettus\Repository\Helpers
 */
class RelationsHelper
{

    /**
     * @param Builder|Model $model
     * @param string $requestedRelations
     * @return array
     */
    public static function filterRelations($model, string $requestedRelations)
    {
        $model = $model instanceof Builder ? $model->getModel() : $model;
        $with = explode(';', $requestedRelations);
        foreach ($with as $key => $relation) {
            if (!method_exists($model, $relation)) {
                unset($with[$key]);
            }
        }
        return $with;
    }
}