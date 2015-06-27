<?php
namespace Prettus\Repository\Criteria;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class RequestCriteria
 * @package Prettus\Repository\Criteria
 */
class RequestCriteria implements CriteriaInterface
{
    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /**
     * Apply criteria in query repository
     *
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $fieldsSearchable   = $repository->getFieldsSearchable();
        $search             = $this->request->get( config('repository.criteria.params.search','search') , null);
        $searchFields       = $this->request->get( config('repository.criteria.params.searchFields','searchFields') , null);
        $filter             = $this->request->get( config('repository.criteria.params.filter','filter') , null);
        $orderBy            = $this->request->get( config('repository.criteria.params.orderBy','orderBy') , null);
        $sortedBy           = $this->request->get( config('repository.criteria.params.sortedBy','sortedBy') , 'asc');
        $sortedBy           = !empty($sortedBy) ? $sortedBy : 'asc';
        
        if ( $search && is_array($fieldsSearchable) && count($fieldsSearchable) )
        {

            $searchFields       = is_array($searchFields) || is_null($searchFields) ? $searchFields : explode(';',$searchFields);
            $fields             = $this->parserFieldsSearch($fieldsSearchable, $searchFields);
            $isFirstField       = true;
            $searchData         = $this->parserSearchData($search);
            $search             = $this->parserSearchValue($search);
            $modelForceAndWhere = false;

            foreach ($fields as $field=>$condition) {

                if (is_numeric($field)){
                    $field = $condition;
                    $condition = "=";
                }

                $value = null;

                $condition  = trim(strtolower($condition));

                if ( isset($searchData[$field]) ) {
                    $value = $condition == "like" ? "%{$searchData[$field]}%" : $searchData[$field];
                } else {
                    if ( !is_null($search) ) {
                        $value = $condition == "like" ? "%{$search}%" : $search;
                    }
                }

                if ( $isFirstField || $modelForceAndWhere ) {
                    if (!is_null($value)) {
                        $model = $model->where($field,$condition,$value);
                        $isFirstField = false;
                    }
                } else {
                    if (!is_null($value)) {
                        $model = $model->orWhere($field,$condition,$value);
                    }
                }
            }
        }

        if ( isset($orderBy) && !empty($orderBy) ) {
            $model = $model->orderBy($orderBy, $sortedBy);
        }

        if ( isset($filter) && !empty($filter) ) {
            if ( is_string($filter) ) {
                $filter = explode(';', $filter);
            }

            $model = $model->select($filter);
        }

        return $model;
    }

    /**
     * @param $search
     * @return array
     */
    protected function parserSearchData($search)
    {
        $searchData = [];

        if ( stripos($search,':') ) {
            $fields = explode(';', $search);

            foreach ($fields as $row) {
                try {
                    list($field, $value) = explode(':', $row);
                    $searchData[$field] = $value;
                } catch (\Exception $e) {
                    //Surround offset error
                }
            }
        }

        return $searchData;
    }

    /**
     * @param $search
     * @return null
     */
    protected function parserSearchValue($search)
    {

        if ( stripos($search,';') || stripos($search,':') ) {
            $values = explode(';', $search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if ( count($s) == 1 ) {
                    return $s[0];
                }
            }

            return null;
        }

        return $search;
    }


    protected function parserFieldsSearch(array $fields = array(), array $searchFields =  null)
    {
        if ( !is_null($searchFields) && count($searchFields) ) {
            $acceptedConditions = config('repository.criteria.acceptedConditions', array('=','like') );
            $originalFields     = $fields;
            $fields = [];

            foreach ($searchFields as $index => $field) {
                $field_parts = explode(':', $field);
                $_index = array_search($field_parts[0], $originalFields);

                if ( count($field_parts) == 2 ) {
                    if ( in_array($field_parts[1],$acceptedConditions) ) {
                        unset($originalFields[$_index]);
                        $field                  = $field_parts[0];
                        $condition              = $field_parts[1];
                        $originalFields[$field] = $condition;
                        $searchFields[$index]   = $field;
                    }
                }
            }

            foreach ($originalFields as $field=>$condition) {
                if (is_numeric($field)){
                    $field = $condition;
                    $condition = "=";
                }
                if ( in_array($field, $searchFields) )
                {
                    $fields[$field] = $condition;
                }
            }

            if ( count($fields) == 0 ){
                throw new \Exception( trans('repository::criteria.fields_not_accepted', array('field'=>implode(',', $searchFields))) );
            }

        }

        return $fields;
    }
}