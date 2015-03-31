CREATE TABLE /*_*/user_user_follow(
`f_id` int(11) PRIMARY KEY auto_increment,
`f_user_id` int(5) unsigned NOT NULL default '0',
`f_user_name` varchar(255) NOT NULL default '',
`f_target_user_id` varchar(255) NOT NULL default '',
`f_target_user_name` varchar(255) NOT NULL default '',
`f_date` datetime default NULL
)/*$wgDBTableOptions*/;
CREATE INDEX if_target_user_id ON /*_*/user_user_follow (`f_target_user_id`);
