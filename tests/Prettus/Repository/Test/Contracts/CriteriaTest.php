<?php namespace Prettus\Repository\Test\Contracts;

use Prettus\Repository\Contracts\Criteria;
use \Mockery as m;
use Prettus\Repository\Contracts\Repository;

/**
 * Class CriteriaTest
 * @package Prettus\Repository\Test\Contracts
 */

class CriteriaTest extends \PHPUnit_Framework_TestCase {

    public function testCriteiraApply(){

        $repository = m::mock('Prettus\Repository\Contracts\Repository');
        $query      = m::mock('stdClass');
        $query->shouldReceive('where')
            ->once()
            ->with('name','=','bar')
            ->andReturnSelf();

        $criteria = new CriteriaDumb($query);

        $this->assertEquals($query, $criteria->apply($query, $repository));
    }
}

class CriteriaDumb implements Criteria {

    /**
     * Apply criteria in query repository
     *
     * @param $query
     * @param Repository $repository
     * @return mixed
     */
    public function apply($query, Repository $repository)
    {
        $query = $query->where('name','=','bar');
        return $query;
    }
}