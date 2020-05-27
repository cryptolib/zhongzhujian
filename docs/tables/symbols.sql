
DROP TABLE IF EXISTS `symbols`;

CREATE TABLE `symbols` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `symbol` varchar(20) NOT NULL DEFAULT '' COMMENT '交易对名称',
  `base_currency` varchar(10) DEFAULT NULL COMMENT '交易对中的基础币种',
  `quote_currency` varchar(10) DEFAULT NULL COMMENT '交易对中的报价币种',
  `price_precision` tinyint(4) DEFAULT NULL COMMENT '交易对报价的精度（小数点后位数）',
  `amount_precision` tinyint(4) DEFAULT NULL COMMENT '交易对基础币种计数精度（小数点后位数）',
  `symbol_partition` varchar(20) DEFAULT NULL COMMENT '交易区，可能值: [main，innovation]',
  `state` enum('online','offline','suspend') DEFAULT NULL COMMENT '交易对状态；可能值: [online，offline,suspend] online - 已上线；offline - 交易对已下线，不可交易；suspend -- 交易暂停',
  `value_precision` tinyint(4) DEFAULT NULL COMMENT '交易对交易金额的精度（小数点后位数）',
  `min_order_amt` decimal(20,8) DEFAULT NULL COMMENT '交易对最小下单量 (下单量指当订单类型为限价单或sell-market时，下单接口传的''amount'')',
  `max_order_amt` decimal(20,8) DEFAULT NULL COMMENT '交易对最大下单量',
  `min_order_value` decimal(20,8) DEFAULT NULL COMMENT '最小下单金额 （下单金额指当订单类型为限价单时，下单接口传入的(amount * price)。当订单类型为buy-market时，下单接口传的''amount''）',
  `price` decimal(20,10) DEFAULT NULL COMMENT '当前价格',
  `tradeable` tinyint(1) DEFAULT NULL COMMENT '交易状态',
  `last_time` int(10) unsigned DEFAULT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `symbol` (`symbol`),
  KEY `base_quote` (`base_currency`,`quote_currency`),
  KEY `tradeable` (`tradeable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
