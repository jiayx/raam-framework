<?php

namespace Database\Connectors;

use PDO;

class MySqlConnector extends Connector implements ConnectorInterface
{
    private $pdo;

    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);
        $options = $this->getOptions($config);
        // print_r($options);die;
        return $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    }

    /**
     * 获取全部的列
     *
     * @return  array 列
     */
    public function getColumns($table)
    {
        $stmt = $this->pdo->prepare("DESCRIBE {$table}");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $columns;
    }
}
