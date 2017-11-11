<?php
namespace Tests\Eloquent;

use Tests\TestCase;
use \Mockery as m;
use Tests\Stub\EloquentStub;
use Tests\Stub\RepositoryStub;
use \Illuminate\Container\Container;
use Illuminate\Support\Collection;


class BaseRepositoryTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }
    public function testAll()
    {
        $mockApplication = m::mock(Container::class);
        $eloquentStub = m::mock(EloquentStub::class);

        $eloquentStub->shouldReceive('all')
                     ->andReturn(new Collection([]));

        $mockApplication->shouldReceive('make')
                        ->andReturn($eloquentStub);

        $baseRepository = new RepositoryStub($mockApplication);
        var_dump($baseRepository->all());


        $this->assertTrue($mockApplication instanceof Container);
    }
}
