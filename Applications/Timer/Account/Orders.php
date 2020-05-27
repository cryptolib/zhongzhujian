<?php

namespace Timer\Account;

use Timer\Base;

/**
 * 查询所有交易订单并记录到数据库
 * @author Minch<yeah@minch.me>
 * @since 2019-05-27
 */
class Orders extends Base
{
    private $start = '2019-07-01';
    private $end = '2019-07-02';
    private $start_key = '';
    private $end_key = '';

    /**
     * 定时获取所有订单
     */
    public function trigger()
    {
        if(!$this->getlock()){
            return false;
        }
        $now = time();
        $interval = intval(\Config\Timer::$modules['Account\Orders']);
        $symbols = \Config\Huobi::$symbols;
        // get account orders every 4hour
        if( ($now % 14400) < $interval){
            foreach ($symbols as $symbol) {
                $this->call('Business\Account\Orders', ['symbol'=>$symbol]);
            }
        }
        $this->unlock();
        $this->wait();
    }
}