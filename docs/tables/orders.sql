
DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `oid` bigint(20) unsigned DEFAULT NULL,
  `symbol` varchar(20) DEFAULT NULL,
  `source` varchar(20) DEFAULT NULL,
  `base_asset` varchar(10) DEFAULT NULL COMMENT '交易对中的基础币种',
  `quote_asset` varchar(10) DEFAULT NULL COMMENT '交易对中的报价币种',
  `price` decimal(30,18) unsigned DEFAULT NULL,
  `amount` decimal(30,18) unsigned DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL COMMENT 'buy-market：市价买, sell-market：市价卖, buy-limit：限价买, sell-limit：限价卖, buy-ioc：IOC买单, sell-ioc：IOC卖单',
  `direction` varchar(5) DEFAULT NULL,
  `account_id` int(10) unsigned DEFAULT NULL,
  `created_at` bigint(20) DEFAULT NULL,
  `finished_at` bigint(20) DEFAULT NULL COMMENT '订单变为终结态的时间，不是成交时间，包含“已撤单”状态',
  `canceled_at` bigint(20) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL COMMENT '  submitting , submitted 已提交, partial-filled 部分成交, partial-canceled 部分成交撤销, filled 完全成交, canceled 已撤销',
  `field_amount` decimal(30,18) DEFAULT NULL,
  `field_cash_amount` decimal(30,18) DEFAULT NULL,
  `field_fees` decimal(30,18) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oid` (`oid`),
  KEY `symbols` (`symbol`),
  KEY `base_asset` (`base_asset`),
  KEY `quote_asset` (`quote_asset`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

