<?php

namespace Web\Controller;

use \Library\Http;

class Orders extends Base
{
	public function index()
	{
		$this->render('orders.html');
	}

	public function data($return = false)
	{
		$data = $this->db()
					->select('currency,amount,frozen,usdt_amount,usdt_price,now_amount,now_price,lasttime')
					->from('balance')
					->where('currency<>', 'usdt')
					->where('currency<>', 'btt')
					->query();
		if(is_array($data)){
			foreach ($data as $key => &$value) {
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
			}
		}
		$this->success($data);
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
				->where('currency', $currency)
				->query();
		}
		$this->success(['currency'=>$currency, 'price'=>$price]);
	}
}