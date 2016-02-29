-- ----------------------------
-- Table structure for `revision_binder`
-- ----------------------------
-- DROP TABLE IF EXISTS /*_*/`revision_binder`;
CREATE TABLE /*_*/revision_binder (
  `thum_sha1` varbinary(32) NOT NULL COMMENT '缩略图sha1',
  `video_revision` int(11) NOT NULL COMMENT '视频版本'
)/*$wgDBTableOptions*/;