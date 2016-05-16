<?php
namespace Raam\Support\Facades;

class Config extends Facade
{
    public static function getFacadeAccessor()
    {
        static::$app->setSingleton('Config', ['class' => 'Raam\Config', 'configPath' => ROOT_PATH . CONFIG_FOLDER]);
        return 'Config';
    }
}
