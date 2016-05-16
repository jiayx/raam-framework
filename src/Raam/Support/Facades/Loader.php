<?php
namespace Raam\Support\Facades;

class Loader extends Facade
{
    public static function getFacadeAccessor()
    {
        static::$app->setSingleton('Raam\Loader');
        return 'Raam\Loader';
    }
}