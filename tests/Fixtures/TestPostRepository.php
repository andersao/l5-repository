<?php

namespace Prettus\Repository\Tests\Fixtures;

use Prettus\Repository\Eloquent\BaseRepository;

class TestPostRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title' => 'like',
        'body'  => 'like',
    ];

    public function model()
    {
        return TestPost::class;
    }
}
