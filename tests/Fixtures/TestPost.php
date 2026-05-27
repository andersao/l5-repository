<?php

namespace Prettus\Repository\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestPost extends Model
{
    protected $table = 'posts';

    protected $guarded = [];

    public $timestamps = false;
}
