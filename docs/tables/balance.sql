
DROP TABLE IF EXISTS `balance`;

CREATE TABLE `balance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `currency` varchar(10) NOT NULL DEFAULT '' COMMENT '币种',
  `amount` decimal(20,8) unsigned DEFAULT '0.00000000' COMMENT '持有数',
  `frozen` decimal(20,8) unsigned DEFAULT '0.00000000' COMMENT '冻结数',
  `usdt_amount` decimal(20,10) unsigned DEFAULT '0.0000000000' COMMENT 'USDT成本值',
  `usdt_price` decimal(20,10) unsigned DEFAULT '0.0000000000' COMMENT 'USDT成本价',
  `now_amount` decimal(20,10) unsigned DEFAULT '0.0000000000' COMMENT '现USDT值',
  `now_price` decimal(20,10) unsigned DEFAULT '0.0000000000' COMMENT '现USDT价',
  `lasttime` int(10) unsigned DEFAULT '0' COMMENT '最后更新时间',
  `reset_order_oid` int(10) unsigned DEFAULT '0' COMMENT '最后订单编号',
  PRIMARY KEY (`id`),
  UNIQUE KEY `currencys` (`currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

