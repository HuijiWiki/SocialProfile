-- ----------------------------
-- Table structure for `video_revision`
-- ----------------------------
-- DROP TABLE IF EXISTS /*_*/`video_revision`;
CREATE TABLE /*_*/video_revision (
  `rev_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '版本id',
  `rev_page_id` int(11) NOT NULL COMMENT '对应页面id',
  `rev_video_id` varbinary(255) NOT NULL COMMENT '视频唯一key',
  `rev_video_title` varbinary(255) NOT NULL COMMENT '视频标题',
  `rev_video_from` varbinary(255) NOT NULL COMMENT '视频来源网站',
  `rev_video_player_url` varbinary(255) NOT NULL COMMENT '视频源',
  `rev_video_tags` varbinary(255) NULL COMMENT '视频分类',
  `rev_video_duration` int(11) NOT NULL COMMENT '视频时长',
  `rev_upload_user` varbinary(255) NOT NULL COMMENT '上传用户',
  `rev_upload_date` datetime NOT NULL COMMENT '上传时间',
  PRIMARY KEY (`rev_id`)
)/*$wgDBTableOptions*/;