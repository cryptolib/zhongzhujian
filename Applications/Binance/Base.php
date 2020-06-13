<?php

namespace Binance;

use \Library\Log;

/**
 * 业务处理基类
 * @author Minch<yeah@minch.me>
 * @since 2019-01-27
 */
abstract class Base extends \Common\Base 
{
    /**
     * 业务处理实例
     * @var array
     */
    protected static $instance = array();
    
    /**
     * 网关链接
     * @var AsyncTcpConnection
     */
    protected static $gateway_conn = null;
    
    /**
     * 错误代码
     * @var number
     */
    protected $errCode = 0;
    
    /**
     * 错误信息
     * @var string
     */
    protected $errMsg = '';
    
    /**
     * 异步请求 array(call_id=>array(callback,params))
     * @var array
     */
    protected static $async_calling = array();
    
    /**
     * 构造函数
     */
    public function __construct()
    {
    }

    abstract public function run($params);

    /**
     * 业务处理器之间相互调用（同步）
     * @param string $class 类名
     * @param string $method 方法名
     * @param array $params 参数
     * @return boolean
     */
    protected static function call($class, $method, $params)
    {
        $res = call_user_func(array(self::getInstance($class),$method), $params);
        unset($class, $method, $params);
        return $res;
    }

    /**
     * 获取下次触发时间间隔
     * @param number $error_num 错误次数
     * @return number
     */
    protected function getNextTriggerTime($error_num = 0)
    {
        $maps = array('0'=>30,'1'=>180,'2'=>600,'3'=>1800);
        return isset($maps[$error_num]) ? $maps[$error_num] : 0;
    }

    /**
     * 异步请求业务处理
     * @param string $class 业务类名(带命名空间，如：Hulianpay\Webservice\User\GetMoney)
     * @param string $method 业务方法名
     * @param array $params 请求参数
     * @param mix $callback 成功后回调函数
     */
    protected function asyncCall($class, $method, $params, $callback = null)
    {
        $sign = md5($class.$method.json_encode($params).\Config\Gateway::$client_sign['business']);
        // 请求业务处理参数
        $dataString = json_encode(array('class'=>$class, 'method'=>$method,'params'=>$params,'client'=>'business','sign'=>$sign ));
        
        // 判断业务是否正在处理中
        $call_id = md5($dataString);
        if(array_key_exists($call_id, self::$async_calling)){
            return false;
        }
        if( $callback != null ){
            // 添加到业务处理列表
            self::$async_calling[$call_id] = array('callback'=>$callback, 'params'=>$params);
        }
        // 发送数据
        $this->gateway()->send($dataString . "\n");
        // 异步获得结果
        $this->gateway()->onMessage = function ($conn, $result)
        {
            // 处理结果
            $res = explode("\n", trim($result));
            foreach ($res as $re){
                $val = json_decode($re, true);
                if (!isset($val['call_id']) || !isset(self::$async_calling[$val['call_id']])) continue;
                call_user_func(self::$async_calling[$val['call_id']]['callback'], self::$async_calling[$val['call_id']]['params'], $val);
                // 解除正在进行中的任务
                unset(self::$async_calling[$val['call_id']], $val);
            }
            // 解除正在进行中的任务
            unset($result,$res);
        };
        unset($dataString, $service, $params, $call_id, $conn, $callback);
        return true;
    }

    /**
     * 记录日志
     * @param string $msg
     * @return void
     */
    protected function log($msg = '')
    {
        return Log::add($msg);
    }

    /**
     * 设置错误信息
     * @param number $errCode 错误编码(1000:数据不存在或状态异常,2000:数据更新失败)
     * @param string $errMsg 错误信息
     * @return boolean false
     */
    protected function error($errCode = 0, $errMsg = '')
    {
        $this->errCode = $errCode;
        $this->errMsg = $errMsg;
        Log::add('ERROR[' . $errCode . '] ' . $errMsg);
        return false;
    }

    /**
     * 获取错误代码
     * @return number
     */
    public function getErrorCode()
    {
        return $this->errCode;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->errMsg;
    }
}