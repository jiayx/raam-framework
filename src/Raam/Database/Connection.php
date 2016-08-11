<?php

namespace Database;

use Database\Connectors\MySqlConnector;
use PDO;
use Closure;

class Connection
{
    /**
     * 创建一个 Connection 对象
     *
     * @param  array  $config
     * @return void
     */
    public function __construct(array $config)
    {
        if (! isset($config['driver'])) {
            throw new Exception('请指定数据库驱动.');
        }

        $this->config = $config;
        $this->driver = $config['driver'];
        $this->tablePrefix = $config['prefix'];

        $this->connector = $this->createConnector();
        $this->pdo = $this->createConnection();
    }

    /**
     * 创建一个连接器
     * 
     * @return \Database\Connectors\ConnectorInterface
     *
     * @throws Exception
     */
    public function createConnector()
    {
        switch ($this->driver) {
            case 'mysql':
                return new MySqlConnector();
                break;
        }

        throw new Exception("不支持的驱动 [{$this->driver}].");
    }

    /**
     * 创建 Connection
     *
     * @return  PDO
     */
    public function createConnection()
    {
        return $this->connector->connect($this->config);
    }

    public function table($table)
    {
        $query = new QueryBuilder($this);
        return $query->from($table);
    }

    public function fetch($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($self, $query, $bindings) {
            $stmt = $self->getPdo()->prepare($query);
            $stmt->execute($bindings);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        });
    }

    public function insert($query, $bindings, $getId = false)
    {
        $count = $this->query($query, $bindings);
        if ($getId) {
            return $this->getPdo()->lastInsertId();
        } else {
            return $count;
        }
    }

    public function update($query, $bindings)
    {
        return $this->query($query, $bindings);
    }

    public function delete($query, $bindings) 
    {
        return $this->query($query, $bindings);
    }

    /**
     * 获取 pdo 对象
     *
     * @return  PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * 获取 Connector 对象
     *
     * @return \Database\Connectors\ConnectorInterface
     */
    public function getConnector()
    {
        return $this->connector;
    }

    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    public function query($query, $bindings)
    {
        return $this->run($query, $bindings, function ($self, $query, $bindings) {
            $stmt = $self->getPdo()->prepare($query);
            $stmt->execute($bindings);
            return $stmt->rowCount();
        });
    }

    public function run($query, $bindings, Closure $callback)
    {
        $start = microtime(true);

        $result = $callback($this, $query, $bindings);

        // 计算查询时间
        $elapsedTime = $this->getElapsedTime($start);

        // TODO log记录

        return $result;
    }

    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }
}
