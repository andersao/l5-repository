<?php
namespace Prettus\Repository\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class RequestCriteria
 * @package Prettus\Repository\Criteria
 * @author Anderson Andrade <contato@andersonandra.de>
 */
class RequestCriteria implements CriteriaInterface
{
    protected $operatorMap = [
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'like' => 'like',
        'in' => 'in'
    ];

    public function apply($model, RepositoryInterface $repository)
    {
        /*
         * -------------
         *  filtering
         * -------------
         */
        $filterSetting = $this->parseSetting($repository->getFieldsSearchable());

        $requestsQuery = $this->request->query();

        foreach ($requestsQuery as $key => $value){
            $queryName = $this->parseQueryName($key);
            $operator = $this->parseOperator($key);

            // is query enable?
            if(!array_key_exists($queryName, $filterSetting)) {
                continue;
            }

            $setting = $filterSetting[$queryName];

            // is operator enable?
            if(isset($setting['operators']) && !isset($setting['operators'][$operator])) {
                continue;
            }
            $primaryTable = $model->getModel()->getTable();
            $column = $setting['column'] ?? $queryName;
            $value = ($operator == 'like') ? "%{$value}%" : $value;

            $model = $model->where(function($query)use($primaryTable, $column, $operator, $value, $setting){
                /** @var Builder $query */

                if(isset($setting['relation'])){
                    $relation = $setting['relation'];
                    $query->whereHas($relation , function($query) use ($column, $operator, $value){
                        $this->applyWhere($query,$column,$operator,$value);
                    });

                } else {
                    $this->applyWhere($query,$primaryTable.'.'.$column,$operator,$value);
                }
            });
        }

        /*
         * -------------
         *  sorting
         * -------------
         */

        $orderBy = $this->request->query('order_by');
        $sortSetting = $repository->getSortable();

        if($orderBy && $sortSetting){
            $sortSetting = $this->parseSetting($sortSetting);

            // is query enable?
            if(array_key_exists($orderBy, $sortSetting)) {
                $setting = $sortSetting[$orderBy];

                $primaryTable = $model->getModel()->getTable();
                if(isset($setting['join'])){
                    $relations = $setting['join'];
                    $relationExplode = explode('.',$relations);

                    $firstTable = null;
                    foreach ($relationExplode as $index => $secondTable)
                    {

                        $firstTable = ($firstTable == null) ? $primaryTable : $relationExplode[$index-1];
                        $joinScope = ($firstTable == $primaryTable) ? 'leftJoin' : 'join';
                        if(\Schema::hasColumn($firstTable, str_singular($secondTable).'_id')) {
                            $model = $model->{$joinScope}($secondTable, "$firstTable.".str_singular($secondTable)."_id", '=',"$secondTable.id");
                        }else if(\Schema::hasColumn($secondTable, str_singular($firstTable).'_id')) {
                            $model = $model->{$joinScope}($secondTable, "$firstTable.id", '=', "$secondTable.".str_singular($firstTable)."_id");
                        }
                    }
                }

                $dir = $this->request->query('order_dir','asc');
                $column = $setting['column'] ?? $orderBy;
                $model = $model->orderBy($column, $dir)
                    ->addSelect("$primaryTable.*");
            }
        }


        return $model;
    }

    protected function applyWhere($query, $column, $operator, $value, $boolean = 'and')
    {
        if($value === "null"){
            $value = null;
        }
        if($operator == 'in') {
            $query->whereIn($column, array_wrap($value), $boolean);
        }else{
            $query->where($column, $operator, $value, $boolean);
        }
    }

    protected function parseSetting(array $original)
    {
        $setting = [];

        foreach ($original as $key => $value){
            if(is_string($key)){
                $setting[$key] = $value;
            }else{
                $setting[$value] = [];
            }
        }

        return $setting;
    }

    /**
     * 'abc_123_gte' => 'abc_123'
     * @param $queryName
     * @return string
     */
    protected function parseQueryName($originalQueryName)
    {
        $operator = last(explode('_',$originalQueryName));
        if(isset($this->operatorMap[$operator])){
            $array = explode('_',$originalQueryName);
            array_pop($array);
            return implode($array,'_');
        }
        return $originalQueryName;
    }

    protected function parseOperator($queryName)
    {
        $operator = last(explode('_',$queryName));
        return $this->operatorMap[$operator] ?? '=';
    }
}
