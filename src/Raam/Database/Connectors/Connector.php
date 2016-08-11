<?php

namespace Database\Connectors;

use PDO;

abstract class Connector
{
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * @param  array  $config 数据库连接配置
     * @return string         dsn
     */
    protected function getDsn(array $config)
    {
        $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['dbname']};";
        return $dsn;
    }

    protected function getOptions(array $config)
    {
        $options = array_get($config, 'options', []);
        return $options + $this->options;
    }
}
