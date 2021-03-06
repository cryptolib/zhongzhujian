<?php

namespace Binance\Account;

use Binance\Base;

/**
 * 查询所有交易订单并记录到数据库
 * @author Minch<yeah@minch.me>
 * @since 2019-05-27
 */
class Orders extends Base
{
    /**
     * 定时获取所有订单
     */
    public function run($params)
    {
        $symbol = $params['symbol'];
        $start = isset($params['start']) ? $params['start'] : '';
        $end = isset($params['end']) ? $params['end'] : '';
        try{
            if(!$start || !$end){
                $start = date("Y-m-d", strtotime('-1 day'));
                $end = date("Y-m-d");
            }
            $datas = $this->huobi()->get_order_orders($symbol, $start, $end);
            if(empty($datas)){
                return false;
            }
            $this->save($datas);
            unset($datas, $symbol, $params);
        }catch(\Exception $e){
            return false;
        }
        return true;
    }

    private function save($datas)
    {
        foreach($datas as $data){
            $cols = [];
            $cols['oid'] = $data['id'];
            $cols['symbol'] = $data['symbol'];
            $cols['source'] = $data['source'];
            $cols['base_currency'] = '';
            $cols['quote_currency'] = '';
            $cols['price'] = $data['price'];
            $cols['amount'] = $data['amount'];
            $cols['type'] = $data['type'];
            $cols['direction'] = $this->direction($data['type']);
            $cols['account_id'] = $data['account-id'];
            $cols['created_at'] = $data['created-at'];
            $cols['finished_at'] = $data['finished-at'];
            $cols['canceled_at'] = $data['canceled-at'];
            $cols['state'] = $data['state'];
            $cols['field_amount'] = $data['field-amount'];
            $cols['field_cash_amount'] = $data['field-cash-amount'];
            $cols['field_fees'] = $data['field-fees'];
            $row = $this->db()->select('id,oid')->from('orders')->where('oid', $data['id'])->row();
            if(!isset($row['id'])){
                $this->db()->insert('orders')->cols($cols)->query();
            }else{
                $this->db()->update('orders')->setCols($cols)->where('oid', $data['id'])->query();
            }
        }
        $this->db()->query('UPDATE orders o,symbols s SET o.base_currency=s.base_currency,o.quote_currency=s.quote_currency WHERE o.symbol=s.symbol;');
        unset($datas, $data, $cols);
    }

    private function direction($type)
    {
        $tmp = explode('-', $type);
        return array_shift($tmp);
    }
}