<?php
/*
|--------------------------------------------------------------------------
| Prettus Repository Config
|--------------------------------------------------------------------------
|
|
*/
return [

    /*
    |--------------------------------------------------------------------------
    | Repository Pagination Limit Default
    |--------------------------------------------------------------------------
    |
    */
    'pagination' => [
        'limit' => 15
    ],

    /*
    |--------------------------------------------------------------------------
    | Fractal Presenter Config
    |--------------------------------------------------------------------------
    |

    Available serializers:
    ArraySerializer
    DataArraySerializer
    JsonApiSerializer

    */
    'fractal'    => [
        'params'     => [
            'include' => 'include'
        ],
        'serializer' => League\Fractal\Serializer\DataArraySerializer::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Config
    |--------------------------------------------------------------------------
    |
    */
    'cache'      => [
        /*
         |--------------------------------------------------------------------------
         | Cache Status
         |--------------------------------------------------------------------------
         |
         | Enable or disable cache
         |
         */
        'enabled'    => false,

        /*
         |--------------------------------------------------------------------------
         | Cache Minutes
         |--------------------------------------------------------------------------
         |
         | Time of expiration cache
         |
         */
        'minutes'    => 30,

        /*
         |--------------------------------------------------------------------------
         | Cache Repository
         |--------------------------------------------------------------------------
         |
         | Instance of Illuminate\Contracts\Cache\Repository
         |
         */
        'repository' => 'cache',

        /*
          |--------------------------------------------------------------------------
          | Cache Clean Listener
          |--------------------------------------------------------------------------
          |
          |
          |
          */
        'clean'      => [

            /*
              |--------------------------------------------------------------------------
              | Enable clear cache on repository changes
              |--------------------------------------------------------------------------
              |
              */
            'enabled' => true,

            /*
              |--------------------------------------------------------------------------
              | Actions in Repository
              |--------------------------------------------------------------------------
              |
              | create : Clear Cache on create Entry in repository
              | update : Clear Cache on update Entry in repository
              | delete : Clear Cache on delete Entry in repository
              |
              */
            'on'      => [
                'create' => true,
                'update' => true,
                'delete' => true,
            ]
        ],

        'params'     => [
            /*
            |--------------------------------------------------------------------------
            | Skip Cache Params
            |--------------------------------------------------------------------------
            |
            |
            | Ex: http://prettus.local/?search=lorem&skipCache=true
            |
            */
            'skipCache' => 'skipCache'
        ],

        /*
       |--------------------------------------------------------------------------
       | Methods Allowed
       |--------------------------------------------------------------------------
       |
       | methods cacheable : all, paginate, find, findByField, findWhere, getByCriteria
       |
       | Ex:
       |
       | 'only'  =>['all','paginate'],
       |
       | or
       |
       | 'except'  =>['find'],
       */
        'allowed'    => [
            'only'   => null,
            'except' => null
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Criteria Config
    |--------------------------------------------------------------------------
    |
    | Settings of request parameters names that will be used by Criteria
    |
    */
    'criteria'   => [
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
        'acceptedConditions' => [
            '=',
            'like',
            'in'
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
        |   Ex: http://prettus.local/?search=lorem
        |
        | - searchFields : Fields in which research should be carried out
        |   Ex:
        |    http://prettus.local/?search=lorem&searchFields=name;email
        |    http://prettus.local/?search=lorem&searchFields=name:like;email
        |    http://prettus.local/?search=lorem&searchFields=name:like
        |
        | - filter : Fields that must be returned to the response object
        |   Ex:
        |   http://prettus.local/?search=lorem&filter=id,name
        |
        | - orderBy : Order By
        |   Ex:
        |   http://prettus.local/?search=lorem&orderBy=id
        |
        | - sortedBy : Sort
        |   Ex:
        |   http://prettus.local/?search=lorem&orderBy=id&sortedBy=asc
        |   http://prettus.local/?search=lorem&orderBy=id&sortedBy=desc
        |
        | - searchJoin: Specifies the search method (AND / OR), by default the
        |               application searches each parameter with OR
        |   EX:
        |   http://prettus.local/?search=lorem&searchJoin=and
        |   http://prettus.local/?search=lorem&searchJoin=or
        |
        */
        'params'             => [
            'search'       => 'search',
            'searchFields' => 'searchFields',
            'filter'       => 'filter',
            'orderBy'      => 'orderBy',
            'sortedBy'     => 'sortedBy',
            'with'         => 'with',
            'searchJoin'   => 'searchJoin',
            'withCount'    => 'withCount'
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | Generator Config: for default structure
    |--------------------------------------------------------------------------
    |
    */
    'generator'  => [
        'structure'     => 'default',   // modular, default
        'modules'       => [],
        'basePath'      => app()->path(),
        'rootNamespace' => 'App\\',
        'stubsOverridePath' => app()->path(),
        'paths'         => [
            'models'       => 'Entities',
            'repositories' => 'Repositories',
            'interfaces'   => 'Repositories',
            'transformers' => 'Transformers',
            'presenters'   => 'Presenters',
            'validators'   => 'Validators',
            'controllers'  => 'Http/Controllers',
            'provider'     => 'RepositoryServiceProvider',
            'criteria'     => 'Criteria'
        ]
    ],
    /*
    |--------------------------------------------------------------------------
    | Generator Config: for modular structure
    |--------------------------------------------------------------------------
    |   IF THE TYPE IS = 'default' THE MODULES VALUE IS AN EMPTY ARRAY,
    |   IF THE TYPE IS = 'modular' YOU CAN ADD LIST OF THE MODULES AS AN ARRAY
    |
    */
//    'generator'  => [
//        'structure'     => 'modular',   // modular, default
//        'package'       => 'nwidart',   // if you use the nwidart or another package, add the name of the package and the package's commands.
//        'modules'       => [
//            'Core',
//            'User',
//        ],
//        'packageCommands' => [
//            'nwidart' => [
//                'controllers' => 'module:make-controller',
//                'requests'   => 'module:make-request',
//                'models' => 'module:make-model',
//            ]
//        ],
//        'ORM'   => 'eloquent',
//
//        'basePath'      => base_path('Modules'),
//        'rootNamespace' => 'Modules\\',
//        'moduleNamespace' => 'Modules\\',           // if you use the modular structure you should add this attribute
//        'stubsOverridePath' => app()->path(),
//        'provider'      => app()->path() . "/Providers/",
//        'paths'         => [
//            'models'       => 'Entities',
//            'repositories' => 'Repositories',
//            'interfaces'   => 'Repositories',
//            'transformers' => 'Transformers',
//            'presenters'   => 'Presenters',
//            'validators'   => 'Validators',
//            'controllers'  => 'Http/Controllers',
//            'criteria'     => 'Criteria',
//            'migrations'   => '/Database/Migrations/',     // when you have all the migration files in default path, remove this
//        ],
//    ],

];
