-- ----------------------------
-- Table structure for `template_fork_count`
-- ----------------------------
DROP TABLE IF EXISTS /*_*/`template_fork_count`;
CREATE TABLE /*_*/template_fork_count (
  `template_id` int(11) NOT NULL COMMENT '模板/词条id',
  `fork_count` int(11)	NOT NULL DEFAULT 0 COMMENT '搬运次数',
  PRIMARY KEY (`template_id`)
)/*$wgDBTableOptions*/;