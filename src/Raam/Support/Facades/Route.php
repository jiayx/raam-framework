<?php
namespace Raam\Support\Facades;

class Route extends Facade
{
    public static function getFacadeAccessor()
    {
        static::$app->setSingleton('Route', 'Raam\Route');
        return 'Route';
    }
}
