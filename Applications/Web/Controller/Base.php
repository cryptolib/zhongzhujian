<?php

namespace Web\Controller;

use \Library\Db;
use \Library\Huobi;
use \Web\Common\Log;
use \Web\Common\Template;
use \Workerman\Connection\AsyncTcpConnection;
use \Workerman\Protocols\Http\Response;

class Base
{
    /**
     * 网关异步连接实例
     * @var array
     */
    protected static $gatewayConn = null;

    /**
     * 网关签名密钥
     * @var string
     */
    protected $gateway_sign;

    /**
     * connection
     * @var string
     */
    protected $connection = '';

    /**
     * request
     * @var \Workerman\Protocols\Http\Request
     */
    protected $request = '';

    /**
     * response
     * @var \Workerman\Protocols\Http\Response
     */
    protected $response = '';

    /**
     * sessionId
     * @var string
     */
    protected $sessionId = '';

    /**
     * 构造函数
     */
	public function __construct($connection,$request,$response)
	{
        $this->connection = $connection;
        $this->request = $request;
        $this->response = $response;
        $this->sessionId = $this->request->sessionId();
        if(!$this->isLogin() && !($this instanceof \Web\Controller\Auth)){
            $this->redirect('/login/');
        }
        $this->gateway_sign = \Config\Timer::$gateway_sign;
	}

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }
    
    public function setRequest($request)
    {
        $this->request = $request;
    }
    
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * redirect
     * @param  string $url
     * @return void
     */
    protected function redirect($url='')
    {
        $this->response->withStatus(302);
        $this->response->withHeaders(['Location' => $url]);
        $this->connection->send($this->response);
    }

    /**
     * [end description]
     * @return [type] [description]
     */
    protected function end()
    {
        //Http::end();
    }

    /**
     * json
     * @param  mixed $data
     * @return void
     */
    protected function json($data='')
    {
        $this->connection->send(json_encode($data));;
    }

    protected function success($data, $msg = '操作成功')
    {
        $this->json(['code'=>0,'msg'=>$msg,'data'=>$data]);
    }

    protected function error($msg = '', $code = 0, $data = [])
    {
        $this->json(['code'=>$code !== 0 ? : 99,'msg'=>$msg,'data'=>$data]);
    }

    /**
     * check user is login
     * @return boolean
     */
    protected function isLogin()
    {
        $skey = \Web\Config\Auth::$prefix . $this->sessionId;
        if (\Web\Config\Auth::$username != $this->gdata($skey)) {
            return false;
        }else{
            $this->gexpire($skey, \Web\Config\Auth::$expire);
            return true;
        }
    }

    /**
     * 数据库连接
     * @return \Library\DbConnection
     */
    protected function db()
    {
        return Db::instance(\Config\Database::$master);
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
     * 火币API类库
     * @var Library\Huobi
     */
    protected function huobi()
    {
        return Huobi::getInstance();
    }

    /**
     * 模板类库
     * @var Web\Common\Template
     */
    protected function view()
    {
        return Template::getInstance();
    }

    /**
     * 设置单个模板变量
     * @param string $key
     * @param string $value
     */
	protected function set($key, $value)
	{
		$this->view()->setVar($key, $value);
	}

    /**
     * 设置多个模板变量
     * @param array $vars
     */
	protected function sets($vars)
	{
		$this->view()->setVars($vars);
	}

    /**
     * 渲染模板输出
     * @param string $tpl
     */
	protected function render($tpl)
	{
		$this->view()->setFile(dirname(__DIR__).'/View/'.$tpl);
        $this->connection->send($this->view()->render());
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
     * 404页面
     */
    public function notfound()
    {
        $this->render('404.html');
    }

    /**
     * 请求业务处理
     * @param string $class  Business业务名,如果 Order\Timeout
     * @param string $method 方法
     * @param array $params 参数
     * @return boolean
     */
    protected function business($class, $method, $params)
    {
        // 请求业务处理参数
        $dataString = json_encode(array('class'=>$class,'method'=>$method,'params'=>$params,'client'=>'timer','sign'=>md5($class . $method . json_encode($params) . $this->gateway_sign)));
        // 判断业务是否正在处理中
        $call_id = md5($dataString);
        if(!$this->gadd($call_id, time())){
            return false;
        }
        // 发送数据
        $this->gateway()->send($dataString . "\n");
        // 异步获得结果
        $this->gateway()->onMessage = function ($conn, $result)
        {
            // 处理结果
            $res = explode("\n", trim($result));
            foreach($res as $re){
                $val = json_decode($re, true);
                $call_id = $val['call_id'];
                // 解除正在进行中的任务
                $this->gunset($call_id);
            }
        };
        return true;
    }
}