<?php

namespace Database;

use Database\Connection;

class ConnectionManager
{
    private static $connections = [];

    public static function get($options, $key = 'default')
    {
        if (! isset(self::$connections[$key])) {
            self::add($key, $options);
        }
        return self::$connections[$key];

    }

    public static function add($key, $options)
    {
        if (! array_key_exists($key, $options) || ! is_array($options[$key])) {
            throw new Exception('ConnectionManager传入参数有误，无法创建新的连接');
        }
        self::$connections[$key] = new Connection($options[$key]);
    }

    /**
     * 删除一个数据库连接
     *
     * @param   string  $key  
     *
     * @return  boolean
     */
    public static function drop($key)
    {
        if (isset(self::$connections[$key])) {
            self::$connections[$key] = null;
        }
        return true;
    }
}
