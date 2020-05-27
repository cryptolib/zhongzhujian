<?php

namespace Library;

/**
 * Trade类库
 * @since 2019-07-19
 */
class Trader
{
	/**
	 * 静态实例
	 * @var Huobi
	 */
	protected static $instance = null;

	/**
	 * 精度（小数点后位数）
	 */
	protected $precision = 4;

	/**
	 * 错误信息
	 */
	protected $error = '';

	/**
	 * 构造函数
	 */
	protected function __construct()
	{
	}

	/**
	 * 获取静态实例
	 * @return Huobi
	 */
	public static function getInstance()
	{
		if(!isset(self::$instance) OR !self::$instance){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * 设置精度（小数点后位数）
	 * @param number $precision 精度（小数点后位数）
	 * @return void 
	 */
	public function set_precision($precision = 4)
	{
		if(is_numeric($precision) && $precision > 0){
			$this->precision = $precision;
			ini_set('trader.real_precision', $precision);
		}
	}

	/**
	 * 获取错误信息
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * 计算K线数据移动平均线
	 * @param array $kline K线数组
	 * @param array $period 移动平均周期
	 * @return array 
	 */
	public function ma($kline, $period = 5)
	{
		$close = [];
		foreach($kline as $val){
			$close[] = $val['close'];
		}
		$res = trader_ma($close, $period);
		if(is_array($res)){
			$ma_arr = array_values($res);
			foreach ($ma_arr as $key=>$ma) {
				$kline[$key]['ma'.$period] = $ma;
			}
		}
		return $kline;
	}

	/**
	 * 计算K线成交量移动平均线
	 * @param array $kline K线数组
	 * @param array $period 移动平均周期
	 * @return array 
	 */
	public function volma($kline, $period = 5)
	{
		$data = [];
		foreach($kline as $val){
			$data[] = $val['amount'];
		}
		$res = trader_ma($data, $period);
		if(is_array($res)){
			$ma_arr = array_values($res);
			foreach ($ma_arr as $key=>$ma) {
				$kline[$key]['volma'.$period] = $ma;
			}
		}
		return $kline;
	}

	/**
	 * 平滑异同移动平均线
	 * @param  array  $kline   [description]
	 * @param  integer $period  [description]
	 * @param  integer $period1 [description]
	 * @param  integer $period2 [description]
	 * @return array
	 */
	public function macd($kline, $period = 12, $period1 = 26, $period2 = 9)
	{
		$close = [];
		foreach($kline as $val){
			$close[] = $val['close'];
		}
		$res = trader_macd(array_reverse($close), $period, $period1, $period2);
		if(isset($res[2])){
			$macd_arr = array_values(array_reverse($res[2]));
			$dif_arr = array_values(array_reverse($res[0]));
			$dea_arr = array_values(array_reverse($res[1]));
			foreach ($macd_arr as $key=>$macd) {
				$kline[$key]['macd'] = $macd;
				$kline[$key]['dif'] = $dif_arr[$key];
				$kline[$key]['dea'] = $dea_arr[$key];
			}
		}
		return $kline;
	}

	public function rsi()
	{
		
	}
}
