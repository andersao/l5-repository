<?php namespace Prettus\Repository\Criteria;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\Criteria;
use Prettus\Repository\Contracts\Repository;

/**
 * Class RequestCriteria
 * @package Prettus\Repository\Criteria
 */
class RequestCriteria implements Criteria {

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param $query
     * @param Repository $repository
     * @return mixed
     */
    public function apply($query, Repository $repository)
    {
        $fieldsSearchable   = $repository->getFieldsSearchable();
        $search             = $this->request->get( config('repository-criteria.params.search','search') , null);
        $searchFields       = $this->request->get( config('repository-criteria.params.searchFields','searchFields') , null);
        $filter             = $this->request->get( config('repository-criteria.params.filter','filter') , null);
        $orderBy            = $this->request->get( config('repository-criteria.params.orderBy','orderBy') , null);
        $sortedBy           = $this->request->get( config('repository-criteria.params.sortedBy','sortedBy') , 'asc');
        $sortedBy           = !empty($sortedBy) ? $sortedBy : 'asc';

        if( $search && is_array($fieldsSearchable) && count($fieldsSearchable) ) {

            $searchFields       = is_array($searchFields) || is_null($searchFields) ? $searchFields : explode(';',$searchFields);
            $fields             = $this->parserFieldsSearch($fieldsSearchable, $searchFields);
            $isFirstField       = true;
            $searchData         = array();
            $queryForceAndWhere = false;

            foreach($fields as $field=>$condition)
            {
                if(is_numeric($field)){
                    $field = $condition;
                    $condition = "=";
                }

                $condition  = trim(strtolower($condition));

                if( isset($searchData[$field]) )
                {
                    $value = $condition == "like" ? "%{$searchData[$field]}%" : $searchData[$field];
                }
                else
                {
                    $value = $condition == "like" ? "%{$search}%" : $search;
                }

                if( $isFirstField || $queryForceAndWhere )
                {
                    $query = $query->where($field,$condition,$value);
                    $isFirstField = false;
                }
                else
                {
                    $query = $query->orWhere($field,$condition,$value);
                }
            }
        }

        if( isset($orderBy) && !empty($orderBy) )
        {
            $query = $query->orderBy($orderBy, $sortedBy);
        }

        if( isset($filter) && !empty($filter) )
        {
            if( is_string($filter) ){
                $filter = explode(';', $filter);
            }
            $query = $query->select($filter);
        }

        return $query;
    }

    protected function parserFieldsSearch(array $fields = array(), array $searchFields =  null){

        if( !is_null($searchFields) && count($searchFields) ){

            $acceptedConditions = config('repository-criteria.acceptedConditions', array('=','like') );
            $originalFields     = $fields;
            $fields = [];

            foreach($searchFields as $index => $field){

                $field_parts = explode(':', $field);
                $_index = array_search($field_parts[0], $originalFields);

                if( count($field_parts) == 2 )
                {
                    if( in_array($field_parts[1],$acceptedConditions) )
                    {
                        unset($originalFields[$_index]);
                        $field                  = $field_parts[0];
                        $condition              = $field_parts[1];
                        $originalFields[$field] = $condition;
                        $searchFields[$index]   = $field;
                    }
                }

            }

            foreach($originalFields as $field=>$condition)
            {
                if(is_numeric($field)){
                    $field = $condition;
                    $condition = "=";
                }

                if( in_array($field, $searchFields) )
                {
                    $fields[$field] = $condition;
                }
            }

            if( count($fields) == 0 ){
                throw new \Exception( trans('prettus-repository::criteria.fields_not_accepted', array('field'=>implode(',', $searchFields))) );
            }
        }

        return $fields;
    }
}