
DROP TABLE IF EXISTS `klines`;

CREATE TABLE `klines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kid` int(10) unsigned DEFAULT NULL COMMENT 'K线ID',
  `ktime` datetime DEFAULT NULL COMMENT '时间',
  `symbol` varchar(20) NOT NULL DEFAULT '' COMMENT '交易对名称',
  `period` varchar(10) NOT NULL DEFAULT '' COMMENT 'K线周期',
  `open` decimal(20,8) DEFAULT NULL COMMENT '开盘价',
  `close` decimal(20,8) DEFAULT NULL COMMENT '收盘价',
  `low` decimal(20,8) DEFAULT NULL COMMENT '最低价',
  `high` decimal(20,8) DEFAULT NULL COMMENT '最高价',
  `amount` decimal(20,6) DEFAULT NULL COMMENT '以基础币种计量的交易量',
  `vol` decimal(20,6) DEFAULT NULL COMMENT '以报价币种计量的交易量',
  `count` decimal(20,6) DEFAULT NULL COMMENT '交易次数',
  `ma5` decimal(20,6) DEFAULT NULL COMMENT '5日移动平均线',
  `ma10` decimal(20,6) DEFAULT NULL COMMENT '10日移动平均线',
  `ma30` decimal(20,6) DEFAULT NULL COMMENT '30日移动平均线',
  `dif` decimal(20,10) DEFAULT NULL COMMENT 'MACD(12,26,9)-DIF',
  `dea` decimal(20,10) DEFAULT NULL COMMENT 'MACD(12,26,9)-DEA',
  `macd` decimal(20,10) DEFAULT NULL COMMENT 'MACD(12,26,9)-MACD',
  `k` decimal(10,6) DEFAULT NULL COMMENT 'KDJ指标K值',
  `d` decimal(10,6) DEFAULT NULL COMMENT 'KDJ指标D值',
  `j` decimal(10,6) DEFAULT NULL COMMENT 'KDJ指标J值',
  `type` enum('up','down') DEFAULT NULL COMMENT '类型：可能值: [up，down] up - 阳线；down - 阴线',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ksp` (`kid`,`symbol`,`period`) USING BTREE,
  KEY `kid` (`kid`),
  KEY `sp` (`symbol`,`period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
