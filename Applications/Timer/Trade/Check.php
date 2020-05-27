<?php

namespace Timer\Trade;

use Timer\Base;

/**
 * 定时检查是否可交易
 * @author Minch<yeah@minch.me>
 * @since 2019-08-26
 */
class Check extends Base
{
    /**
     * 定时检查是否可交易
     */
    public function trigger()
    {
        if(!$this->getlock()){
            return false;
        }
        $now = time();
        $interval = intval(\Config\Timer::$modules['Trade\Check']);

        // get account orders every 1hour
        if( ($now % 3600) < $interval){
            foreach (\Config\Huobi::$symbols as $symbol) {
                $this->call('Business\Trade\Check', array('symbol'=>$symbol));
            }
        }

        unset($now,$interval,$symbol);

        $this->unlock();
        $this->wait();
    }
}