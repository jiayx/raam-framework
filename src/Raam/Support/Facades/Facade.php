<?php
namespace Raam\Support\Facades;

use Raam\Exceptions\RuntimeException;

class Facade
{
    public static $app;
    public static $facadeInstances = [];

    public static function getFacadeAccessor()
    {
        throw new RuntimeException('继承自facade的类必须要重写 getFacadeAccessor 才可以使用');
    }

    public static function getFacadeInstance()
    {
        $mixed = static::getFacadeAccessor();
        if (is_callable($mixed)) {
            return $mixed();
        } elseif (is_object($mixed)) {
            return $mixed;
        } elseif (is_string($mixed)) {
            // facade设置为单例
            if (! isset(static::$facadeInstances[$mixed])) {
                static::$facadeInstances[$mixed] = static::$app->get($mixed);
            }
            return static::$facadeInstances[$mixed];
        } else {
            throw new RuntimeException('getFacadeAccessor方法返回类型错误');
        }
    }

    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeInstance();

        if (! $instance) {
            throw new RuntimeException('没有这个声明这个类');
        }

        switch (count($args)) {
            case 0:
                return $instance->$method();

            case 1:
                return $instance->$method($args[0]);

            case 2:
                return $instance->$method($args[0], $args[1]);

            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);

            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);

            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }
}
