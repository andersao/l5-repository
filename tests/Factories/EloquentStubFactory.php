<?php
namespace Tests\Factories;

use Tests\Interfaces\FactoryInterface;
use Tests\Stub\EloquentStub;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Collection;

final class EloquentStubFactory implements FactoryInterface
{
    /**
     *   Make a Eloquent with values using Faker
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public static function make()
    {
        $faker = Faker::create('pt_BR');
        $eloquentStub = new EloquentStub();
        $eloquentStub->name = $faker->name;
        $eloquentStub->bar = $faker->word;

        return $eloquentStub;
    }

    public static function makeCollection($count = 1)
    {
        $collection = new Collection();

        for ($i=0; $i < $count; $i++) {
            $eloquentStub = self::make();
            $collection->push($eloquentStub);
        }
        return $collection;
    }
}
