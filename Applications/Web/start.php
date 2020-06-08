<?php 
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
use \Workerman\Worker;
use \Web\Common\Router;
use \Workerman\Protocols\Http\Response;

// WebServer
$web = new Worker('http://'.Config\Web::$address.':'.Config\Web::$port);
// WebServer数量
$web->count = 2;

$web->onMessage = function($connection, $request)
{
    $file = __DIR__.DIRECTORY_SEPARATOR.'Root'.$request->path();
    if(file_exists($file) && is_dir($file)){
        $file .= DIRECTORY_SEPARATOR.'index.html';
    }
    if(file_exists($file) && is_file($file)){
        if (!empty($if_modified_since = $request->header('if-modified-since'))) {
            $modified_time = date('D, d M Y H:i:s',  filemtime($file)) . ' ' . \date_default_timezone_get();
            // 文件未修改则返回304
            if ($modified_time === $if_modified_since) {
                $connection->send(new Response(304));
                return;
            }
        }
        // 文件修改过或者没有if-modified-since头则发送文件
        $response = (new Response())->withFile($file);
        $connection->send($response);
        return;
    }

    $router = Router::getInstance();
    $router->setBasePath('/');
    $router->setConnection($connection);
    $router->setRequest($request);
    $router->setResponse(new Response());
    $router->setNamespace('\Web\Controller');

    $router->set404('Auth@notfound');

    array_walk(\Web\Config\Router::$get, function($value, $key) use($router){
        $router->get($key, $value);
    });

    array_walk(\Web\Config\Router::$post, function($value, $key) use($router){
        $router->post($key, $value);
    });

    $router->run();
};


// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

