-- ----------------------------
-- Table structure for `template_fork`
-- ----------------------------
--DROP TABLE IF EXISTS `template_fork`;
CREATE TABLE /*_*/`template_fork` (
  `fork_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '搬运id',
  `template_id` varbinary(255) NOT NULL COMMENT '模板/词条id',
  `fork_from` varbinary(255) NOT NULL COMMENT '模板/词条来源',
  `fork_user` varbinary(255) NOT NULL COMMENT '搬运用户',
  `fork_data` date NOT NUll COMMENT '搬运时间',
  PRIMARY KEY (`fork_id`)
)/*$wgDBTableOptions*/;