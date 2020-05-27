<?php

namespace Web\Controller;

use \Library\Http;

class Balance extends Base
{
	public function index()
	{
		$this->render('balance.html');
	}

	public function data()
	{
		$data = $this->db()
					->select('currency,amount,frozen,usdt_amount,usdt_price,now_amount,now_price,lasttime')
					->from('balance')
					->where('currency<>', 'usdt')
					->where('currency<>', 'btt')
					->order('lasttime DESC')
					->query();
		if(is_array($data)){
			foreach ($data as $key => &$value) {
				$value = $this->formatValue($value);
			}
		}
		$this->success($data);
	}

	private function formatValue($value='')
	{
		$value['amount'] = $this->format(bcadd($value['amount'], $value['frozen'], 8));
		$value['unfrozen'] = $this->format($value['amount']);
		$value['usdt_amount'] = $this->format($value['usdt_amount']);
		$value['usdt_price'] = $this->format($value['usdt_price']);
		$value['now_amount'] = $this->format($value['now_amount']);
		$value['now_price'] = $this->format($value['now_price']);
		$value['gainloss'] = $this->format(bcsub($value['now_amount'], $value['usdt_amount'],8));
		$value['gainloss_rate'] = $value['usdt_amount']==0 ? 
			'0.00%' : 
			bcmul(bcsub(bcdiv($value['now_price'], $value['usdt_price'],6), 1, 6), 100,2).'%';
		$value['symbol'] = $value['currency'].'usdt';

		return $value;
	}

	private function format($val)
	{
		$tmp = rtrim($val, '0');
		if(substr($tmp, -1) == '.'){
			$tmp .= '00';
		}
		return $tmp;
	}

	public function price($currency)
	{
		$symbol = $currency.'usdt';
		$price = 0.00;
		$url = 'https://api.huobi.pro/market/history/kline?period=5min&size=1&symbol='.$symbol;
		$res = json_decode(Http::get($url), true);
		if(isset($res['data']) && isset($res['data'][0]) && isset($res['data'][0]['close'])){
			$price = $res['data'][0]['close'];
			$this->db()
				->update('balance')
				->set('now_amount', ['raw'=>'`amount`*'.$price])
				->set('now_price', $price)
				->set('lasttime', time())
				->where('currency', $currency)
				->query();

			$value = $this->db()
					->select('currency,amount,frozen,usdt_amount,usdt_price,now_amount,now_price,lasttime')
					->from('balance')
					->where('currency', $currency)
					->row();
			$this->success($this->formatValue($value));
		}
		$this->error('操作失败');
	}

	public function reprice($currency)
    {
		$symbol = $currency.'usdt';
		$this->orders($symbol);
        $balance = $this->db()
        				->select('id,currency,reset_order_oid')
        				->from('balance')
        				->where('currency', $currency)
        				->row();

        $this->db()->update('balance')
                ->setCols(['usdt_amount'=>0,'usdt_price'=>0])
                ->where('currency', $currency)->query();

        $fields = 'symbol,base_currency,quote_currency,
                SUM(IF(direction=\'buy\',field_amount-field_fees,-field_amount)) amount,
                SUM(IF(direction=\'buy\',field_cash_amount,-field_cash_amount)) cash_amount';
        $row = $this->db()
        			->select($fields)
        			->from('orders')
                    ->where('symbol', $symbol)
                    ->where('oid>', intval($balance['reset_order_oid']))
                    ->where('state', 'filled')
                    ->where('account_id', '9156386')
                    ->row();
        unset($fields, $balance);
        if(is_array($row) && !empty($row['symbol'])){
            $symbolInfo = $this->db()
            				->select('price_precision,amount_precision')
            				->from('symbols')
            				->where('symbol', $symbol)
            				->row();
            $data = [
            	'usdt_amount'=>$row['cash_amount'],
            	'usdt_price'=>bcdiv($row['cash_amount'], $row['amount'], $symbolInfo['price_precision']),
            	'lasttime'=>time()
            ];
            $this->db()
        		->update('balance')
                ->setCols($data)
                ->where('currency', $currency)
                ->query();
            unset($symbolInfo,$data);
        }
		$value = $this->db()
				->select('currency,amount,frozen,usdt_amount,usdt_price,now_amount,now_price,lasttime')
				->from('balance')
				->where('currency', $currency)
				->row();
		$this->success($this->formatValue($value));
    }

    private function orders($symbol)
    {
        $start = date("Y-m-d", strtotime('-1 day'));
        $end = date("Y-m-d");
        $datas = $this->huobi()->get_order_orders($symbol, $start, $end);
        if(empty($datas)){
            return false;
        }
        $this->save($datas);
        unset($datas, $symbol, $params);
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
            $row = $this->db->select('id,oid')->from('orders')->where('oid', $data['id'])->row();
            if(!isset($row['id'])){
                $this->db->insert('orders')->cols($cols)->query();
            }else{
                $this->db->update('orders')->setCols($cols)->where('oid', $data['id'])->query();
            }
        }
        $this->db->query('UPDATE orders o,symbols s SET o.base_currency=s.base_currency,o.quote_currency=s.quote_currency WHERE o.symbol=s.symbol;');
        unset($datas, $data, $cols);
    }

    private function direction($type)
    {
        $tmp = explode('-', $type);
        return array_shift($tmp);
    }
}