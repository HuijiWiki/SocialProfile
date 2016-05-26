-- ----------------------------
-- Table structure for `user_donation`
-- ----------------------------
DROP TABLE IF EXISTS /*_*/`user_donation`;
CREATE TABLE /*_*/user_donation (
  `ud_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'user donation id',
  `user_name` varbinary(255) NOT NULL COMMENT '用户名',
  `site_prefix` varbinary(50) NOT NULL COMMENT '捐赠站点',
  `donation_value` float NOT NULL COMMENT '捐赠金额',
  `date` datetime NOT NUll COMMENT '捐赠时间',
  `month` varbinary(100) NOT NUll COMMENT '所在月份',
  PRIMARY KEY (`ud_id`)
)/*$wgDBTableOptions*/;