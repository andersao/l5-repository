<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Criteria\RequestCriteria;

class ApiRequestCriteria extends RequestCriteria
{
    public function apply($model, RepositoryInterface $repository)
    {
        $fieldsSearchable = $repository->getFieldsSearchable();
        $search = $this->request->query(config('repository.criteria.params.search', 'search'), null);
        $searchFields = $this->request->query(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $filter = $this->request->query(config('repository.criteria.params.filter', 'filter'), null);
        $orderBy = $this->request->query(config('repository.criteria.params.orderBy', 'orderBy'), 'created_at');
        $sortedBy = $this->request->query(config('repository.criteria.params.sortedBy', 'sortedBy'), 'desc');
        $with = $this->request->query(config('repository.criteria.params.with', 'with'), null);
        $withCount = $this->request->query(config('repository.criteria.params.withCount', 'withCount'), null);
        $searchJoin = $this->request->query(config('repository.criteria.params.searchJoin', 'searchJoin'), '');
        $scopes = $this->request->query(config('repository.criteria.params.scopes', 'scopes'), null);

        if ($search && is_array($fieldsSearchable) && count($fieldsSearchable)) {

            $searchFields = is_array($searchFields) || is_null($searchFields) ? $searchFields : array_filter(explode(';', $searchFields));
            $isFirstField = true;
            $searchData = $this->parserSearchData($search);
            $fields = $this->parserFieldsSearch($fieldsSearchable, $searchFields, array_keys($searchData));
            $search = $this->parserSearchValue($search);
            $modelForceAndWhere = strtolower($searchJoin) === 'and';

            $model = $model->where(function ($query) use ($fields, $search, $searchData, $isFirstField, $modelForceAndWhere) {
                /** @var Builder $query */
                foreach ($fields as $field => $condition) {

                    if (is_numeric($field)) {
                        $field = $condition;
                        $condition = '=';
                    }

                    $value = null;

                    $condition = trim(strtolower($condition));

                    if (isset($searchData[$field])) {
                        $value = ($condition == 'like' || $condition == 'ilike') ? "%{$searchData[$field]}%" : $searchData[$field];
                    } else {
                        if (! is_null($search) && ! in_array($condition, ['in', 'between'])) {
                            $value = ($condition == 'like' || $condition == 'ilike') ? "%{$search}%" : $search;
                        }
                    }

                    $relation = null;
                    if (stripos($field, '.')) {
                        $explode = array_filter(explode('.', $field));
                        $field = array_pop($explode);
                        $relation = implode('.', $explode);
                    }
                    if ($condition === 'in') {
                        $value = array_filter(explode(',', $value));
                        if (trim($value[0]) === '' || $field == $value[0]) {
                            $value = null;
                        }
                    }
                    if ($condition === 'between') {
                        $value = array_filter(explode(',', $value));
                        if (count($value) < 2) {
                            $value = null;
                        }
                    }
                    if (in_array($condition, ['null', 'not_null'])) {
                        $value = '';
                    }
                    $modelTableName = $query->getModel()->getTable();
                    if ($isFirstField || $modelForceAndWhere) {
                        if (! is_null($value)) {
                            if (! is_null($relation)) {
                                $query->whereHas($relation, function ($query) use ($field, $condition, $value) {
                                    if ($condition === 'in') {
                                        $query->whereIn($field, $value);
                                    } elseif ($condition === 'between') {
                                        $query->whereBetween($field, $value);
                                    } elseif ($condition === 'null') {
                                        $query->whereNull($field);
                                    } elseif ($condition === 'not_null') {
                                        $query->whereNotNull($field);
                                    } else {
                                        $query->where($field, $condition, $value);
                                    }
                                });
                            } else {
                                if ($condition === 'in') {
                                    $query->whereIn($modelTableName.'.'.$field, $value);
                                } elseif ($condition === 'between') {
                                    $query->whereBetween($modelTableName.'.'.$field, $value);
                                } elseif ($condition === 'null') {
                                    $query->whereNull($modelTableName.'.'.$field);
                                } elseif ($condition === 'not_null') {
                                    $query->whereNotNull($modelTableName.'.'.$field);
                                } else {
                                    $query->where($modelTableName.'.'.$field, $condition, $value);
                                }
                            }
                            $isFirstField = false;
                        }
                    } else {
                        if (! is_null($value)) {
                            if (! is_null($relation)) {
                                $query->orWhereHas($relation, function ($query) use ($field, $condition, $value) {
                                    if ($condition === 'in') {
                                        $query->whereIn($field, $value);
                                    } elseif ($condition === 'between') {
                                        $query->whereBetween($field, $value);
                                    } elseif ($condition === 'null') {
                                        $query->whereNull($field);
                                    } elseif ($condition === 'not_null') {
                                        $query->whereNotNull($field);
                                    } else {
                                        $query->where($field, $condition, $value);
                                    }
                                });
                            } else {
                                if ($condition === 'in') {
                                    $query->orWhereIn($modelTableName.'.'.$field, $value);
                                } elseif ($condition === 'between') {
                                    $query->whereBetween($modelTableName.'.'.$field, $value);
                                } elseif ($condition === 'null') {
                                    $query->whereNull($modelTableName.'.'.$field);
                                } elseif ($condition === 'not_null') {
                                    $query->whereNotNull($modelTableName.'.'.$field);
                                } else {
                                    $query->orWhere($modelTableName.'.'.$field, $condition, $value);
                                }
                            }
                        }
                    }
                }
            });
        }

        if (isset($orderBy) && ! empty($orderBy)) {
            $orderBySplit = array_filter(explode(';', $orderBy));
            if (count($orderBySplit) > 1) {
                $sortedBySplit = array_filter(explode(';', $sortedBy));
                foreach ($orderBySplit as $orderBySplitItemKey => $orderBySplitItem) {
                    $sortedBy = isset($sortedBySplit[$orderBySplitItemKey]) ? $sortedBySplit[$orderBySplitItemKey] : $sortedBySplit[0];
                    $model = $this->parserFieldsOrderBy($model, $orderBySplitItem, $sortedBy);
                }
            } else {
                $model = $this->parserFieldsOrderBy($model, $orderBySplit[0], $sortedBy);
            }
        }

        if (isset($filter) && ! empty($filter)) {
            if (is_string($filter)) {
                $filter = array_filter(explode(';', $filter));
            }

            $model = $model->select($filter);
        }

        if ($with) {
            $with = array_filter(explode(';', $with));
            $model = $model->with($with);
        }

        if ($withCount) {
            $withCount = array_filter(explode(';', $withCount));
            $model = $model->withCount($withCount);
        }

        if ($scopes) {
            $scopes = array_filter(explode(';', $scopes));
            $model = $model->scopes($scopes);
        }

        return $model;
    }
}
