<?php

namespace Aries\Database;

use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\DriverManager;
use Aries\Core\Config\Loader;

class Connection
{
    /**
     * 数据库连接
     */
    protected $connection;

    /**
     * 配置加载器
     */
    protected $config;

    /**
     * 构造函数
     */
    public function __construct(Loader $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * 连接数据库
     */
    protected function connect()
    {
        $params = [
            'driver' => $this->config->get('database.driver', 'pdo_mysql'),
            'host' => $this->config->get('database.host', 'localhost'),
            'port' => $this->config->get('database.port', 3306),
            'dbname' => $this->config->get('database.database'),
            'user' => $this->config->get('database.username'),
            'password' => $this->config->get('database.password'),
            'charset' => $this->config->get('database.charset', 'utf8mb4'),
        ];

        $this->connection = DriverManager::getConnection($params);
    }

    /**
     * 执行查询
     */
    public function query(string $sql, array $params = [])
    {
        return $this->connection->executeQuery($sql, $params);
    }

    /**
     * 执行更新
     */
    public function execute(string $sql, array $params = [])
    {
        return $this->connection->executeStatement($sql, $params);
    }

    /**
     * 开始事务
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * 回滚事务
     */
    public function rollBack()
    {
        $this->connection->rollBack();
    }

    /**
     * 获取查询构建器
     */
    public function createQueryBuilder()
    {
        return $this->connection->createQueryBuilder();
    }

    /**
     * 获取最后插入的ID
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * 获取数据库连接
     */
    public function getConnection()
    {
        return $this->connection;
    }
} 