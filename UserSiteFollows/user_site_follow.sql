CREATE TABLE /*_*/user_site_follow{
`f_id` int(11) PRIMARY KEY auto_increment,
`f_user_id` int(5) unsigned NOT NULL default '0',
`f_user_name` varchar(255) NOT NULL default '',
`f_wiki_domain` varchar(255) NOT NULL default '',
`f_date` datetime default NULL
}/*$wgDBTableOptions*/;
CREATE INDEX /*i*/f_wiki_domain ON /*_*/user_site_follow_table (`f_wiki_domain`);
CREATE TABLE /*_*/site_follow_count{
`f_wiki_domain` varchar(255) NOT NULL default '',
`f_wiki_count` int(11) NOT NULL default '0'
}/*$wgDBTableOptions*/;
CREATE INDEX /*i*/f_wiki_domain ON /*_*/site_follow_count (`f_wiki_domain`);