-- ----------------------------
-- Table structure for `video_page`
-- ----------------------------
-- DROP TABLE IF EXISTS /*_*/`video_page`;
CREATE TABLE /*_*/video_page (
  `page_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '页面id',
  `revision_id` int(11) NOT NULL COMMENT '对应版本id',
  PRIMARY KEY (`page_id`)
)/*$wgDBTableOptions*/;