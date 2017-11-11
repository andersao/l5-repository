<?php
namespace Tests\Interfaces;

/**
 *   All factories must implements this interface
 *
 * @package Tests\Interfaces
 */
interface FactoryInterface
{
    public static function make();
    public static function makeCollection($count);
}
