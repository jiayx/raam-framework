<?php

namespace Database\Connectors;


interface ConnectorInterface
{
    /**
     * 获取一个数据库连接对象
     * 
     * @param  array  $config
     */
    public function connect(array $config);

    /**
     * 获取表中的列
     *
     * @param   string  $table  表名
     */
    public function getColumns($table);
}
