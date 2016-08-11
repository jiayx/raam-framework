<?php

namespace Database;


class QueryBuilder
{
    protected $connection;

    protected $distinct;
    protected $select = ['*'];
    protected $from;
    protected $joins;    
    protected $wheres;
    protected $groups;
    protected $havings;
    protected $orders;
    protected $limit;
    protected $offset;
    // protected $unions;
    // protected $lock;

    protected $selectComponents = [
        'select',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
    ];

    /**
     * 数据绑定数组
     *
     * @var  array
     */
    protected $bindings;

    protected $tablePrefix;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->tablePrefix = $connection->getTablePrefix();
    }

    public function from($table)
    {
        $this->from = $table;

        return $this;
    }

    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * 要查询的列
     *
     * @param   array   $columns  
     *
     * @return  QueryBuilder
     */
    public function select($columns = ['*'])
    {
        $this->select = (is_array($columns)) ? $columns : func_get_args();

        return $this;
    }

    /**
     * 添加一个where查询
     * 
     * @param   string  $column    列
     * @param   string  $operator  操作符 > < = 等
     * @param   string  $value     值
     * @param   string  $boolean   与前一语句的关系 AND OR  
     *
     * @return  QueryBuilder $this 
     */
    public function where($column, $operator, $value = null, $boolean = 'AND')
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }

        $where = compact('column', 'operator', 'value', 'boolean');
        $this->wheres[] = $where;

        $this->addBinding('wheres', $value);

        return $this;
    }

    /**
     * 添加一个orWhere查询
     * 
     * @return  QueryBuilder
     */
    public function orWhere($column, $operator, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    // TODO
    public function join($table)
    {
        
    }

    public function groupBy()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $this->groups = array_merge((array) $this->groups, $arg);
            } else {
                $this->groups[] = $arg;
            }
        }

        return $this;
    }

    public function having($column, $operator, $value = null, $boolean = 'AND')
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }

        $having = compact('column', 'operator', 'value', 'boolean');
        $this->wheres[] = $having;

        $this->addBinding('havings', $value);

        return $this;
    }

    public function order($column, $type = 'ASC')
    {
        $type = strtoupper($type);
        if (! in_array($type, ['ASC', 'DESC'])) {
            $type = 'ASC';
        }
        $this->orders[] = $this->wrapTable($column).' '.$type;

        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    private function addBinding($key, $value)
    {
        $this->bindings[$key][] = $value;
    }

    /**
     * 生成查询sql 
     *
     * @return  
     */
    public function compileFetch()
    {
        $sql = [];

        foreach ($this->selectComponents as $component) {
            if (! is_null($this->$component)) {
                $method = 'compile'.ucfirst($component);

                $sql[] = $this->$method();
            }
        }

        return implode(' ', $sql);
    }

    public function compileSelect()
    {
        $select = 'SELECT ';
        if (! is_null($this->distinct)) {
            $select .= 'DISTINCT ';
        }
        return $select.implode(', ', $this->select);
    }

    public function compileFrom()
    {
        return 'FROM '.$this->prefixTable($this->from);
    }

    public function compileGroups()
    {
        return 'GROUP BY '.implode(', ', array_map(function ($group) {
            return $this->wrapTable($group);
        }, $this->groups));
    }

    public function compileOrders()
    {
        return 'ORDER BY'.implode(', ', $this->orders);
    }

    public function compileHavings()
    {
        if (is_null($this->havings)) {
            return '';
        }
        $havings = [];

        foreach ($this->havings as $having) {
            if (count($havings) == 0) {
                $havings[] = $this->wrapTable($having['column'])
                    .$having['operator']
                    .'?';
            } else {
                $havings[] = $having['boolean'].' '
                    .$this->wrapTable($having['column'])
                    .$having['operator']
                    .'?';
            }
        }

        return 'WHERE '.implode(' ', $havings);
    }

    public function compileLimit()
    {
        return 'LIMIT '.(int) $this->limit;
    }

    public function compileOffset()
    {
        return 'OFFSET '.(int) $this->offset;
    }

    public function compileWheres()
    {
        if (is_null($this->wheres)) {
            return '';
        }
        $sql = [];

        foreach ($this->wheres as $where) {
            if (count($sql) == 0) {
                $sql[] = $this->wrapTable($where['column'])
                    .$where['operator']
                    .'?';
            } else {
                $sql[] = $where['boolean'].' '
                    .$this->wrapTable($where['column'])
                    .$where['operator']
                    .'?';
            }
        }

        return 'WHERE '.implode(' ', $sql);
    }

    public function compileInsert($values)
    {
        $count = count($values);
        $table = $this->prefixTable($this->from);
        $columns = $this->columnize($values);
        $params = [];
        foreach ($values as $value) {
            $params[] = '('.$this->parameterize($value).')';
        }

        $params = implode(', ', $params);

        return "INSERT INTO {$table} ({$columns}) VALUES {$params}";
    }

    public function compileUpdate($values)
    {
        $table = $this->prefixTable($this->from);
        // $pk = $this->getPks();
        $update = [];
        foreach ($values as $column => $value) {
            // if (!in_array($column, $pk)) {
                $update[] = $this->wrapTable($column).'=?';
                $this->addBinding('update', $value);
            // }

        }
        $update = implode(', ', $update);

        $where = $this->compileWheres();

        return "UPDATE {$table} SET {$update} {$where}";
    }

    public function compileDelete($values)
    {
        $table = $this->prefixTable($this->from);

        if (is_array($values)) {
            $operator = '=';
            $count = count($values);
            if ($count == 2) {
                list($key, $value) = $values;            
            } elseif ($count > 2) {
                list($key, $operator, $value) = $values;
            }
            $this->where($key, $operator, $value);
        } elseif (is_numeric($values)) {
            $this->where('id', '=', $values);
        }

        $where = $this->compileWheres();

        return "DELETE FROM {$table} {$where}";
    }

    /**
     * 给表名加前缀 顺便加反引号
     *
     * @param   string  $table  表名
     *
     * @return  string
     */
    public function prefixTable($table)
    {
        return $this->wrapTable($this->tablePrefix.$table);
    }

    public function wrapValue($value)
    {
        if (is_int($value)) {
            return $value;
        }
        return '"'.$value.'"';
    }

    public function wrapTable($table)
    {
        if ($table == '*') {
            return $table;
        }

        return '`'.$table.'`';
    }

    // 根据数组生成数据库列
    public function columnize(array $values)
    {
        $columns = array_keys(reset($values));
        return implode(', ', array_map([$this, 'wrapTable'], $columns));
    }

    // 根据数组生成问号
    public function parameterize(array $values)
    {
        $count = count($values);
        return rtrim(str_repeat('?, ', $count), ', ');
    }

    // 获取主键
    public function getPk()
    {
        $connector = $this->connection->getConnector();
        $columns = $connector->getColumns($this->prefixTable($this->from));

        $primary = [];
        foreach ($columns as $column) {
            if ($column['Key'] === 'PRI') {
                $primary[] = $column['Field'];
            }
        }

        return $primary;
    }

    /**
     * 执行查询语句 - 返回结果的前N条
     *
     * @param   integer  $limit  取查询结果的前n条
     *
     * @return  array $result 查询结果
     */
    public function fetch($limit = 0)
    {
        $bindings = isset($this->bindings['wheres']) ? $this->bindings['wheres'] : [];
        $result = $this->connection->fetch($this->compileFetch(), $bindings);
        if ($limit > 0) {
            $result = array_slice($result, 0, $limit);
        }

        return $result;
    }

    /**
     * 执行查询并且只取查询结果的第一条
     *
     * @return  array 
     */
    public function frist()
    {
        $result = $this->fetch();

        return isset($result[0]) ? $result[0] : [];
    }

    // 插入一条数据并返回影响的行数
    public function insert(array $values, $getId = false)
    {
        if (empty($values)) {
            return true;
        }

        // 每次插入操作都当做批量插入来做
        if (! is_array(reset($values))) {
            $values = [$values];
        } else {
            // 保证每个数组的数据顺序
            $values = array_map(function ($value) {
                ksort($value);
                return $value;
            }, $values);
        }

        $sql = $this->compileInsert($values);

        $bindings = [];
        foreach ($values as $value) {
            foreach ($value as $v) {
                $bindings[] = $v;
            }
        }

        return $this->connection->insert($sql, $bindings, $getId);
    }

    // 插入一条数据并获取最后插入的id
    public function insertGetId(array $values)
    {
        return $this->insert($values, true);
    }

    // 更新操作
    public function update(array $values)
    {
        $sql = $this->compileUpdate($values);
        $bindings = array_merge($this->bindings['update'], $this->bindings['wheres']);
        return $this->connection->update($sql, $bindings);
    }

    public function delete($values = null)
    {
        $sql = $this->compileDelete($values);
        return $this->connection->update($sql, $this->bindings['wheres']);
    }

    public function matchInsert($values)
    {

    }
}
