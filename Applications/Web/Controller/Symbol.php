<?php

namespace Web\Controller;

use \Library\Http;

class Symbol extends Base
{
	public function index()
	{
		$this->set('data', json_encode(array_values($this->globaldata->symbols)));
		$this->render('symbol.html');
	}

	public function price($symbol, $return = false)
	{
		$price = 0.00;
		$url = 'https://api.huobi.pro/market/history/kline?period=5min&size=1&symbol='.$symbol;
		$res = json_decode(Http::get($url), true);
		if(isset($res['data']) && isset($res['data'][0]) && isset($res['data'][0]['close'])){
			$price = $res['data'][0]['close'];
		}
		if ($return === true) {
			return $price;
		}
		$this->json(['symbol'=>$symbol, 'price'=>$price]);
	}
}