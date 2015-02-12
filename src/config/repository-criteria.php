<?php

/*
|--------------------------------------------------------------------------
| Prettus Repository Criteria Config
|--------------------------------------------------------------------------
|
| Settings of request parameters names that will be used by Criteria
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Accepted Conditions
    |--------------------------------------------------------------------------
    |
    | Conditions accepted in consultations where the Criteria
    |
    | Ex:
    |
    | 'acceptedConditions'=>['=','like']
    |
    | $query->where('foo','=','bar')
    | $query->where('foo','like','bar')
    |
    */
    'acceptedConditions'=>[
        '=','like'
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Params
    |--------------------------------------------------------------------------
    |
    | Request parameters that will be used to filter the query in the repository
    |
    | Params :
    |
    | - search : Searched value
    |   Ex: http://localhost/?search=lorem
    |
    | - searchFields : Fields in which research should be carried out
    |   Ex:
    |    http://localhost/?search=lorem&searchFields=name;email
    |    http://localhost/?search=lorem&searchFields=name:like;email
    |    http://localhost/?search=lorem&searchFields=name:like
    |
    | - filter : Fields that must be returned to the response object
    |   Ex:
    |   http://localhost/?search=lorem&filter=id,name
    |
    | - orderBy : Order By
    |   Ex:
    |   http://localhost/?search=lorem&orderBy=id
    |
    | - sortedBy : Sort
    |   Ex:
    |   http://localhost/?search=lorem&orderBy=id&sortedBy=asc
    |   http://localhost/?search=lorem&orderBy=id&sortedBy=desc
    |
    */
    'params'=>[
        'search'        =>'search',
        'searchFields'  =>'searchFields',
        'filter'        =>'filter',
        'orderBy'       =>'orderBy',
        'sortedBy'      =>'sortedBy'
    ]
];