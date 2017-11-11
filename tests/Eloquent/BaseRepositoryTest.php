<?php
namespace Tests\Eloquent;

use Tests\TestCase;
use \Mockery as m;
use Tests\Stub\EloquentStub;
use Tests\Stub\RepositoryStub;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Tests\Factories\EloquentStubFactory;

class BaseRepositoryTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    private function generateEloquentStubCollection($count)
    {
        return EloquentStubFactory::makeCollection($count);
    }

    public function testAll()
    {
        $mockApplication = m::mock(Container::class);
        $eloquentStub = m::mock(EloquentStub::class);
        $countCollection = 5;
        $eloquentStubCollection = $this->generateEloquentStubCollection($countCollection);

        $eloquentStub->shouldReceive('all')
            ->andReturn($eloquentStubCollection);

        $mockApplication->shouldReceive('make')
            ->andReturn($eloquentStub);


        $baseRepository = new RepositoryStub($mockApplication);
        $resultAll = $baseRepository->all();

        $this->assertInstanceOf(Collection::class, $resultAll);
        $this->assertEquals($resultAll->count(), $countCollection);
        $this->assertContainsOnlyInstancesOf(EloquentStub::class, $resultAll);
        $this->assertTrue($mockApplication instanceof Container);
    }
}
