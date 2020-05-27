<?php

use Workerman\Worker;

// composer autoload
require_once dirname(__DIR__) . '/loader.php';

$channel_server = new Channel\Server(Config\Channel::$address, Config\Channel::$port);

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
