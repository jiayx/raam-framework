<?php

use Raam\Di\Container;

if (! function_exists('is_associative')) {
    // 是否索引数组
    function is_associative($array)
    {
        if (! is_array($array) || empty($array)) {
            return false;
        }
        foreach ($array as $key => $value) {
            if (! is_string($key)) {
                return false;
            }
        }
        return true;
    }
}

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string  $make
     * @param  array   $parameters
     * @return mixed|\Illuminate\Foundation\Application
     */
    function app($make = null, $parameters = [])
    {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make, $parameters);
    }
}
