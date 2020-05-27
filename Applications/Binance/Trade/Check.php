<?php

namespace Binance\Trade;

use Binance\Base;
use Library\Http;
use Library\Trader;

/**
 * 定时检查是否可交易
 * @author Minch<yeah@minch.me>
 * @since 2019-08-26
 */
class Check extends Base
{
    /**
     * 买入系数
     * @var integer
     */
    protected $buyrate = 0;

    /**
     * 卖出系数
     * @var integer
     */
    protected $sellrate = 0;

    public function run($params)
    {
        $symbol = $params['symbol'];
        $url = 'https://api.huobi.pro/market/history/kline?period=60min&size=300&symbol='.$symbol;
        $res = json_decode(Http::get($url), true);
        if(!isset($res['data']) || empty($res['data'])){
            return false;
        }
        $klines = $res['data'];

        $symbolInfo = $this->db()
                        ->select('*')
                        ->from('symbols')
                        ->where('symbol', $symbol)
                        ->row();
        if(!isset($symbolInfo['id'])){
           return false; 
        }

        $trader = Trader::getInstance();
        $trader->set_precision($symbolInfo['price_precision']);
        bcscale($symbolInfo['price_precision']);

        $klines = $trader->ma($klines, 7);
        $klines = $trader->ma($klines, 14);
        $klines = $trader->ma($klines, 26);
        $klines = $trader->volma($klines, 5);
        $klines = $trader->volma($klines, 10);
        $klines = $trader->macd($klines, 12, 26, 9);

        if($this->checkBuy($klines)){
            $price = bcmul($klines[0]['close'], 0.9975);
            $this->buy($symbol, $price);
        }elseif($this->checkSell($klines)){
            $price = bcmul($klines[0]['close'], 1.0025);
            $this->sell($symbol, $price);
        }
    }

    protected function checkBuy($klines)
    {
        if($klines[1]['macd'] > 0 &&
            $klines[2]['macd'] < 0 &&
            $klines[3]['macd'] < 0 &&
            $klines[4]['macd'] < 0 &&
            $klines[5]['macd'] < 0 
        ){
            return true;
        }
        return false;
    }

    protected function checkSell($klines)
    {
        if($klines[1]['macd'] < 0 && 
            $klines[2]['macd'] > 0 && 
            $klines[3]['macd'] > 0 && 
            $klines[4]['macd'] > 0 && 
            $klines[5]['macd'] > 0
        ){
            return true;
        }
        return false;
    }

    protected function buy($symbol, $price)
    {
        $order = [];
        $order['symbol'] = $symbol;
        $order['price'] = $price;
        $order['amount'] = 10;
        $order['type'] = 'buy';
        $order['dateline'] = time();
        $this->db()->insert('symbol_orders')->cols($order)->query();
    }

    protected function sell($symbol, $price)
    {
        $order = [];
        $order['symbol'] = $symbol;
        $order['price'] = $price;
        $order['amount'] = 10;
        $order['type'] = 'sell';
        $order['dateline'] = time();
        $this->db()->insert('symbol_orders')->cols($order)->query();
    }
}