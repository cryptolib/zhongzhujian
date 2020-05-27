
DROP TABLE IF EXISTS `cnylog`;

CREATE TABLE `cnylog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ts` int(10) unsigned DEFAULT NULL COMMENT '时间',
  `currency` varchar(5) DEFAULT NULL COMMENT '币种',
  `cny` decimal(10,2) DEFAULT NULL COMMENT '人民币数量',
  `amount` decimal(20,8) DEFAULT NULL COMMENT '代币数量',
  `price` decimal(10,2) unsigned DEFAULT NULL COMMENT '操作价格',
  `type` enum('buy','sell') DEFAULT NULL COMMENT '操作类型：买入,卖出',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
