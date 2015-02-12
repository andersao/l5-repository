<?php namespace Prettus\Repository\Test\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Criteria;
use Prettus\Repository\Contracts\Repository;
use \Mockery as m;

/**
 * Class RepositoryTest
 * @package Prettus\Repository\Test\Eloquent
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Mockery\Mock;
     */
    protected $mock;

    /**
     * @var Repository
     */
    protected $repository;

    public function setUp(){
        $this->mock = m::mock('Illuminate\Database\Eloquent\Model');
    }

    public function createRepository($model){
        return new RepositoryEloquentDumb($model);
    }

    public function testGetModelReturnModel(){
        $this->repository = $this->createRepository($this->mock);
        $this->assertEquals($this->mock, $this->repository->getModel());
    }

    public function testFindById(){

        $this->mock->shouldReceive('find')
            ->with(1, array('*'))
            ->once()
            ->andReturn('foo');

        $this->repository = $this->createRepository($this->mock);
        $this->assertEquals('foo', $this->repository->find(1));
    }

    public function testFindByField(){

        $this->mock->shouldReceive('where')
            ->with('name','=','foo')
            ->once()
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('first')
            ->once()
            ->andReturn('foo');

        $this->repository = $this->createRepository($this->mock);
        $this->assertEquals('foo', $this->repository->findByField('name','foo'));

    }

    public function testGetAllWithoutCriteria(){

        $this->mock->shouldReceive('all')
            ->with(array('*'))
            ->once()
            ->andReturn('foo');

        $this->repository = $this->createRepository($this->mock);
        $this->assertEquals('foo', $this->repository->all());
    }

    public function testGetPaginator(){

        $this->mock->shouldReceive('paginate')
            ->with(10, array('*'))
            ->once()
            ->andReturn('foo');

        $this->repository = $this->createRepository($this->mock);
        $this->assertEquals('foo', $this->repository->paginate(10));
    }

    public function testCreate(){

        $attributes = array('name'=>'Anderson');
        $this->mock->shouldReceive('create')
            ->once()
            ->with($attributes)
            ->andReturn('foo');

        $this->repository = $this->createRepository($this->mock);
        $this->assertEquals('foo', $this->repository->create($attributes));

    }

    public function testUpdate(){

        $attributes = array('name'=>'Anderson');
        $this->mock
            ->shouldReceive('find')
            ->once()
            ->with(1, array('*'))
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('fill')
            ->once()
            ->with($attributes)
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('save')
            ->andReturn('foo');

        $this->repository = $this->createRepository($this->mock);
        $this->assertEquals('foo', $this->repository->update($attributes, 1));

    }

    public function testDelete(){

        $this->mock
            ->shouldReceive('find')
            ->once()
            ->with(1, array('*'))
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('delete')
            ->once()
            ->andReturn(1);

        $this->repository = $this->createRepository($this->mock);
        $this->assertEquals(1, $this->repository->delete(1));

    }

    public function testeSetRelationshipWith(){

        $this->mock
            ->shouldReceive('with')
            ->once()
            ->with(array('foo'))
            ->andReturnSelf();

        $this->repository = $this->createRepository($this->mock);
        $this->assertEquals($this->repository, $this->repository->with(array('foo')));

    }

}

class RepositoryEloquentDumb extends \Prettus\Repository\Eloquent\Repository {

}

class CriteriaDumb implements \Prettus\Repository\Contracts\Criteria {


    /**
     * Apply criteria in query repository
     *
     * @param $query
     * @param Repository $repository
     * @return mixed
     */
    public function apply($query, Repository $repository)
    {
        $query->where('1',1,1);
        return $query;
    }
}