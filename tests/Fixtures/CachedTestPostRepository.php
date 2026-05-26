<?php

namespace Prettus\Repository\Tests\Fixtures;

use Prettus\Repository\Contracts\CacheableInterface;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Traits\CacheableRepository;

class CachedTestPostRepository extends BaseRepository implements CacheableInterface
{
    use CacheableRepository;

    protected $fieldSearchable = [
        'title' => 'like',
    ];

    public function model()
    {
        return TestPost::class;
    }
}
