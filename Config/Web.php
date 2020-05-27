<?php

namespace Config;

class Web
{
    /**
     * 网关监听的ip地址，分布式部署时使用内网ip
     * @var string
     */
    public static $address = '0.0.0.0';
    
    /**
     * 网关监听端口
     * @var number
     */
    public static $port = 8899;

    /**
     * 网关监听端口
     * @var number
     */
    public static $domain = 'localhost';
}
