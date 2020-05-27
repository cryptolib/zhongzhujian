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

// composer autoload
require_once dirname(__DIR__) . '/loader.php';

// WebServer
$web = new Worker('http://'.Config\Web::$address.':'.Config\Web::$port);
// WebServer数量
$web->count = 2;

$web->onMessage = function($connection, $data)
{
    // 向浏览器发送hello world
    $connection->send('hello world');
};


// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}

