<?php

namespace Timer\Account;

use Timer\Base;

/**
 * 查询账户余额
 * @author Minch<yeah@minch.me>
 * @since 2019-05-27
 */
class Balance extends Base
{
    /**
     * 定时更新账户余额
     */
    public function trigger()
    {
        if(!$this->getlock()){
            return false;
        }
        $now = time();
        $interval = intval(\Config\Timer::$modules['Account\Balance']);
        // update account balance every 4hours
        if( ($now % 14400) < $interval){
            $this->call('Business\Account\Balance', array());
        }
        $this->unlock();
        $this->wait();
    }
}