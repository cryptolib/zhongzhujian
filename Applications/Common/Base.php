<?php

namespace Common;

use Library\Db;
use Library\DbConnection;
use Workerman\Connection\AsyncTcpConnection;

abstract class Base
{
    /**
     * 静态实例
     * @var array
     */
    protected static $instance = array();

    /**
     * 网关异步连接实例
     * @var Workerman\Connection\AsyncTcpConnection
     */
    protected static $gatewayConn = null;

    /**
     * 构造函数
     */
	public function __construct(){}

    /**
     * 获取业务处理器实例
     * @param string $name
     * @return multitype:
     */
    public static function getInstance($name)
    {
        if(!isset(self::$instance[$name]) OR !self::$instance[$name]){
            self::$instance[$name] = new $name();
        }
        return self::$instance[$name];
    }

    /**
     * 数据库连接
     * @param $conf string \Config\Database属性名
     * @return \Library\DbConnection
     */
    protected function db($conf = 'default')
    {
        if (!isset(\Config\Database::${$conf})) {
            return false;
        }
        return Db::instance(\Config\Database::${$conf});
    }

    /**
     * 网关异步连接实例
     * @return AsyncTcpConnection
     */
    protected function gateway()
    {
        if(!isset(self::$gatewayConn) OR !self::$gatewayConn){
            self::$gatewayConn = new AsyncTcpConnection('tcp://' . \Config\Gateway::$address . ':' . \Config\Gateway::$port);
            self::$gatewayConn->connect();
        }
        return self::$gatewayConn;
    }

    /**
     * 变量共享组件
     * @return GlobalData\Client
     */
    protected function globaldata()
    {
        return \GlobalData\Client::getInstance(\Config\GlobalData::$address . ':' . \Config\GlobalData::$port);
    }

    /**
     * 获取共享数据变量
     * @param string $key 
     */
    protected function gdata($key)
    {
        return $this->globaldata()->$key;
    }

    /**
     * 设置共享数据变量
     * @param string $key 
     * @param mixed $value 
     * @param mixed $expire 
     * @return mixed 
     * @throws \Exception
     */
    protected function gadd($key, $value, $expire = 0)
    {
        return $this->globaldata()->add($key, $value, $expire);
    }

    /**
     * 共享数据变量原子操作
     * @param string $key
     * @param mixed $old_value
     * @param mixed $new_value
     */
    protected function gcas($key, $old_value, $new_value)
    {
        return $this->globaldata()->cas($key, $old_value, $new_value);
    }

    /**
     * 设置共享数据变量过期时间
     * @param string $key 
     * @param mixed $expire 
     * @return mixed 
     */
    protected function gexpire($key, $expire = 0)
    {
        return $this->globaldata()->expire($key, $expire);
    }

    /**
     * 设置共享数据变量
     * @param string $key 
     * @param mixed $value 
     */
    protected function gset($key, $value)
    {
        $this->globaldata()->$key = $value;
    }

    /**
     * 销毁共享数据变量
     * @param string $key
     */
    protected function gunset($key)
    {
        unset($this->globaldata()->$key);
    }

}