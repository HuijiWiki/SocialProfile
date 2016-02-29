-- ----------------------------
-- Table structure for `video_archive`
-- ----------------------------
-- DROP TABLE IF EXISTS /*_*/`video_archive`;
CREATE TABLE /*_*/video_archive (
  `ar_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '档案id',
  `ar_rev_id` int(11) NOT NULL COMMENT '版本id',
  `ar_page_id` int(11) NOT NULL COMMENT '对应页面id',
  `ar_video_id` varbinary(255) NOT NULL COMMENT '视频唯一key',
  `ar_video_title` varbinary(255) NOT NULL COMMENT '视频标题',
  `ar_video_from` varbinary(255) NOT NULL COMMENT '视频来源网站',
  `ar_video_player_url` varbinary(255) NOT NULL COMMENT '视频源',
  `ar_video_tags` varbinary(255) NULL COMMENT '视频分类',
  `ar_video_duration` int(11) NOT NULL COMMENT '视频时长',
  `ar_upload_user` varbinary(255) NOT NULL COMMENT '上传用户',
  `ar_upload_date` datetime NOT NULL COMMENT '上传时间',
  `ar_date` datetime NOT NULL COMMENT '删除时间',
  PRIMARY KEY (`ar_id`)
)/*$wgDBTableOptions*/;