<?php
namespace Tests\Stub;

use Prettus\Repository\Eloquent\BaseRepository;
use Tests\Eloquent\EloquentStub;

class RepositoryStub extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];
    /**
     * Configure the Model
     **/
    public function model()
    {
        return EloquentStub::class;
    }
}
