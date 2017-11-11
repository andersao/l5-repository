<?php
namespace Tests\Stub;

use Tests\Eloquent\EloquentStub;
use Prettus\Repository\Eloquent\BaseRepository;

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
