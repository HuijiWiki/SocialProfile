<?php

/**
 * UserActivity class
 * step1: determine where clasue.
 * step2: read database
 * step3: set item data and item(grouped) data.
 * step4: prepare formated output
 * step5: build html
 */
class UserActivity {
	

	/**
	 * All member variables should be considered private
	 * Please use the accessor functions
	 */

	private $user_id;       # Text form (spaces not underscores) of the main part
	private $user_name;		# Text form (spaces not underscores) of the main part
	private $items;         # Text form (spaces not underscores) of the main part
	private $rel_type;
	private $show_following = false;
	private $show_current_user = false;
	private $show_all = false;
	private $show_following_sites = false;
	private $show_this_site = false;

	private $show_edits = 1;
	private $show_votes = 0;
	private $show_comments = 1;
	private $show_relationships = 1;
	private $show_gifts_sent = 0;
	private $show_gifts_rec = 1;
	private $show_system_gifts = 1;
	private $show_system_messages = 1;
	private $show_messages_sent = 1;
	private $show_network_updates = 0;
	private $show_user_user_follows = 1;
	private $show_user_site_follows = 1;
	private $show_user_update_status = 1;
	private $show_domain_creations = 1;
	private $show_image_uploads = 1;

	private $cached_where;
	private $cached_tables;
	private $templateParser;

	/**
	 * Constructor
	 *
	 * @param $username String: username (usually $wgUser's username)
	 * @param $filter String: passed to setFilter(); can be either 'user',
	 *                        'friends', 'foes' or 'all', depending on what
	 *                        kind of information is wanted
	 * @param $item_max Integer: maximum amount of items to display in the feed
	 */
	public function __construct( $username, $filter, $item_max ) {
		if ( $username ) {
			//$title1 = Title::newFromDBkey( $username );
			$this->user_name = $username;
			$this->user_id = User::idFromName( $this->user_name );
		}
		$this->setFilter( $filter );
		$this->item_max = $item_max;
		$this->sql_depth = $this->item_max*3;
		$this->now = time();
		$this->half_day_ago = $this->now - ( 60 * 60 * 12 );
		$this->half_a_day = ( 60 * 60 * 12 );
		$this->items_grouped = array();
		$this->cached_where = false;
		$this->cached_tables = false;
		$this->templateParser = new TemplateParser(  __DIR__ . '/html' );
	}

	private function setFilter( $filter ) {
		if ( strtoupper( $filter ) == 'USER' ) {
			$this->show_current_user = true;
		}
		if ( strtoupper( $filter ) == 'FRIENDS' ) {
			$this->rel_type = 1;
		}
		if ( strtoupper( $filter ) == 'FOES' ) {
			$this->rel_type = 2;
		}
		if ( strtoupper( $filter ) == 'FOLLOWING' ){
			$this->show_following = true;
		}
		if ( strtoupper( $filter ) == 'ALL' ) {
			$this->show_all = true;
		}
		if ( strtoupper( $filter ) == 'FOLLOWING_SITES' ) {
			$this->show_following_sites = true;
			$this->show_relationships = 0;
			$this->show_gifts_sent = 0;
			$this->show_gifts_rec = 0;
			$this->show_system_gifts = 0;
			$this->show_system_messages = 0;
			$this->show_messages_sent = 0;
			$this->show_network_updates = 0;
			$this->show_user_user_follows = 0;
			$this->show_user_site_follows = 0;
			$this->show_user_update_status = 0;
			$this->show_domain_creations = 0;
		}
		if ( strtoupper( $filter ) == 'THIS_SITE' ) {
			$this->show_this_site = true;
			$this->show_relationships = 0;
			$this->show_gifts_sent = 0;
			$this->show_gifts_rec = 0;
			$this->show_system_gifts = 0;
			$this->show_system_messages = 0;
			$this->show_messages_sent = 0;
			$this->show_network_updates = 0;
			$this->show_user_user_follows = 0;
			$this->show_user_site_follows = 0;
			$this->show_user_update_status = 0;
			$this->show_domain_creations = 0;			
		}
	}

	/**
	 * Sets the value of class member variable $name to $value.
	 */
	public function setActivityToggle( $name, $value ) {
		$this->$name = $value;
	}

	/**
	 * return a join argument for setEdits(). Preferably this should only return two or three wikis recently changed by a given set of users.
	 *
	 */
	private function getTables(){
		global $wgHuijiPrefix, $wgUser;
		$dbr = wfGetDB( DB_SLAVE );
		if ( !empty($this->cached_tables) ){
			return $this->cached_tables;
		}
		if ( !empty($this->show_this_site) ){
			$tables = array();
			$tables[] = $wgHuijiPrefix;
		} elseif ($this->show_following_sites){
			$values = $dbr->select(
				'user_site_follow',
				'f_wiki_domain',
				'f_user_id = '.$this->user_id,
				__METHOD__
			);
			// echo $values;
			// die(1);
			$tables = array();
			foreach( $values as $value ){
				$tables[] = $value->f_wiki_domain;
			}				
		} else {
			$values = $dbr->select(
				'domain',
				'domain_prefix',
				'domain_status = 0',
				__METHOD__
			);
			// echo $values;
			// die(1);
			$tables = array();
			foreach( $values as $value ){
				$tables[] = $value->domain_prefix;
			}			
		}
		$this->cached_tables = $tables;
		return $tables;
	}

	/**
	 * Based on the fileter, generate the where clause.
	 * @param $field the where clause field.
	 * @return array where clause for sql.
	 */
	private function where( $field ){
		$userArray = array();
		$where = array();
		$dbr = wfGetDB( DB_SLAVE );
		if (!empty($this->cached_where)){
			$userArray = $this->cached_where;
		} else {
			if ( !empty( $this->rel_type ) ) {
				$users = $dbr->select(
					'user_relationship',
					'r_user_id_relation',
					array(
						'r_user_id' => $this->user_id,
						'r_type' => $this->rel_type
					),
					__METHOD__
				);			
				foreach ( $users as $user ) {
					$userArray[] = $user->r_user_id_relation;
				}
			}

			if ( !empty( $this->show_current_user ) ) {
				$userArray[] = $this->user_id;
			}

			if ( !empty( $this->show_following )){
				$users = $dbr->select(
					'user_user_follow',
					'f_target_user_id',
					array(
						'f_user_id' => $this->user_id,
					),
					__METHOD__
				);
				foreach ( $users as $user ) {
					$userArray[] = $user->f_target_user_id;
				}
		
			}
			//cache it
			$this->cached_where = $userArray;
		}
		$userIDs = implode( ',', $userArray );
		if ( !empty( $userIDs ) ) {
			$where[] = "{$field} IN ($userIDs)";
		}	
		return $where;
	}

	/**
	 * Get recent edits from the recentchanges table and set them in the
	 * appropriate class member variables.
	 */
	private function setEdits() {
		global $wgDBprefix, $wgDBname, $isProduction, $wgHuijiPrefix;

		$dbr = wfGetDB( DB_SLAVE );
		$where = $this->where('rc_user');
		$sqls = array();

		$tables = $this->getTables();
		$oldDBprefix = $wgDBprefix;
		$oldDB = $wgDBname;
		$dbr->tablePrefix('');

		foreach ($tables as $table){
			if ( !$isProduction ){
				$dbr->selectDB('huiji_'.str_replace('.', '_', $table));
				$DBprefix = '';
				break;
			} elseif ( $table == 'www'){
				$dbr->selectDB('huiji_home');
				$DBprefix = '';
				continue;
			} else {
				$dbr->selectDB('huiji_sites');
				$DBprefix = str_replace('.', '_', $table);
			}

			$tableName = '`'.$DBprefix.'recentchanges'.'`';
			$fieldName = implode( ',', $dbr->fieldNamesWithAlias( 
				array('UNIX_TIMESTAMP(rc_timestamp) AS item_date', 'rc_title',
					'rc_user', 'rc_user_text', 'rc_comment', 'rc_id', 'rc_minor',
					'rc_new', 'rc_namespace', $dbr->addQuotes($table).' AS prefix',
					)
				) 
			);
			if (count($where) > 0){
				$conds = $dbr->makeList( $where, LIST_AND );
				$sql = "SELECT $fieldName FROM $tableName WHERE $conds";
			} else {
				$sql = "SELECT $fieldName FROM $tableName";
			}

			$sqls[] = $sql;

		} 
		// echo $dbr->unionQueries($sqls, true)." ORDER BY `rc_id` DESC LIMIT $this->item_max OFFSET 0";
		// die(1);
		if (count($sqls) > 0){
			$res = $dbr->query($dbr->unionQueries($sqls, true)." ORDER BY `item_date` DESC LIMIT $this->sql_depth OFFSET 0");

			foreach ( $res as $row ) {
				$row->item_date = strtotime('+8 hour', $row->item_date);
				// Special pages aren't editable, so ignore them
				// And blocking a vandal should not be counted as editing said
				// vandal's user page...
				if ( $row->rc_namespace == NS_SPECIAL || $row->rc_log_action != null ) {
					continue;
				}
				// Topics need some hack in title
				if ( $row->rc_namespace == NS_TOPIC){
					//TODO change something!
				}

				// Please aware that a project namespace in other wikis can not be localised as [[sitename:blahblah]].
				// We must add a prefix argument.
				if ($table != $wgHuijiPrefix){
					$title = Title::makeTitle( $row->rc_namespace, $row->rc_title, '', $row->prefix);
				} else {
					$title = Title::makeTitle( $row->rc_namespace, $row->rc_title, '');
				}
				
				
				$this->items_grouped['edit'][$title->getPrefixedText()]['users'][$row->rc_user_text][] = array(
					'id' => 0,
					'type' => 'edit',
					'timestamp' => $row->item_date,
					'pagetitle' => $row->rc_title,
					'namespace' => $row->rc_namespace,
					'username' => $row->rc_user_text,
					'userid' => $row->rc_user,
					'comment' => $this->fixItemComment( $row->rc_comment ),
					'minor' => $row->rc_minor,
					'new' => $row->rc_new,
					'prefix' => $row->prefix
				);

				// set last timestamp
				if (!isset($this->items_grouped['edit'][$title->getPrefixedText()]['timestamp'])){
					$this->items_grouped['edit'][$title->getPrefixedText()]['timestamp'] = $row->item_date;
				}
				$this->items[] = array(
					'id' => 0,
					'type' => 'edit',
					'timestamp' => ( $row->item_date ),
					'pagetitle' => $row->rc_title,
					'namespace' => $row->rc_namespace,
					'username' => $row->rc_user_text,
					'userid' => $row->rc_user,
					'comment' => $this->fixItemComment( $row->rc_comment ),
					'minor' => $row->rc_minor,
					'new' => $row->rc_new,
					'prefix' => $row->prefix
				);
				// set prefix
				$this->items_grouped['edit'][$title->getPrefixedText()]['prefix'][] = $row->prefix;
			}
		}
		$dbr->tablePrefix($oldDBprefix);
		$dbr->selectDB($oldDB);

	}


	/**
	 * Get users from user follow table and set them in the
	 * appropriate class member variables.
	 */
	private function setUserSiteFollows() {
		global $wgLang;
		$dbr = wfGetDB( DB_SLAVE );
		$where = $this->where('f_user_id');
		$res = $dbr->select(
			'user_site_follow',
			array(
				'UNIX_TIMESTAMP(f_date) AS item_date', 'f_id',
				'f_user_id', 'f_user_name', 'f_wiki_domain'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'f_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			)
		);

		foreach ( $res as $row ) {
			$user_name_short = $wgLang->truncate( $row->f_user_name, 25 );
			$this->items_grouped['user_site_follow'][$row->f_wiki_domain]['users'][$row->f_user_name][] = array(
				'id' => $row->f_id,
				'type' => 'user_site_follow',
				'timestamp' => $row->item_date,
				'pagetitle' => '',
				'namespace' => '',
				'username' => $user_name_short,
				'userid' => $row->f_user_id,
				'comment' => '',
				'prefix' => $row->f_wiki_domain,
				'minor' => 0,
				'new' => 0
			);
			// set last timestamp
			if (!isset($this->items_grouped['user_site_follow'][$row->f_wiki_domain]['timestamp']))
			{
				$this->items_grouped['user_site_follow'][$row->f_wiki_domain]['timestamp'] = $row->item_date;
			}
			$this->items[] = array(
				'id' => 0,
				'type' => 'user_site_follow',
				'timestamp' => ( $row->item_date ),
				'pagetitle' => '',
				'namespace' => '',
				'username' => $row->f_user_name,
				'userid' => $row->f_user_id,
				'comment' => '',
				'prefix' => $row->f_wiki_domain,
				'minor' => 0,
				'new' => '0'
			);
			$this->items_grouped['user_site_follow'][$row->f_wiki_domain]['prefix'][] = $row->f_wiki_domain;
		}
	}

	/**
	 * Get users from user follow table and set them in the
	 * appropriate class member variables.
	 */
	private function setUserUserFollows() {
		global $wgLang;
		$dbr = wfGetDB( DB_SLAVE );
		$where = $this->where('f_user_id');
		$res = $dbr->select(
			'user_user_follow',
			array(
				'UNIX_TIMESTAMP(f_date) AS item_date', 'f_id',
				'f_user_id', 'f_user_name', 'f_target_user_name'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'f_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			)
		);

		foreach ( $res as $row ) {
			$user_name_short = $wgLang->truncate( $row->f_user_name, 15 );
			$this->items_grouped['user_user_follow'][$row->f_target_user_name]['users'][$row->f_user_name][] = array(
				'id' => $row->f_id,
				'type' => 'user_user_follow',
				'timestamp' => $row->item_date,
				'pagetitle' => '',
				'namespace' => '',
				'username' => $user_name_short,
				'userid' => $row->f_user_id,
				'comment' => $row->f_target_user_name,
				'minor' => 0,
				'new' => 0
			);
			// set last timestamp
			if (!isset($this->items_grouped['user_user_follow'][$row->f_target_user_name]['timestamp'])){
				$this->items_grouped['user_user_follow'][$row->f_target_user_name]['timestamp'] = $row->item_date;
			}
			

			$this->items[] = array(
				'id' => 0,
				'type' => 'user_user_follow',
				'timestamp' => ( $row->item_date ),
				'pagetitle' => '',
				'namespace' => '',
				'username' => $row->f_user_name,
				'userid' => $row->f_user_id,
				'comment' => $row->f_target_user_name,
				'minor' => 0,
				'new' => '0'
			);
		}
	}
	/**
	 * Get recent votes from the Vote table (provided by VoteNY extension) and
	 * set them in the appropriate class member variables.
	 */
	private function setVotes() {
		$dbr = wfGetDB( DB_SLAVE );

		# Bail out if Vote table doesn't exist
		if ( !$dbr->tableExists( 'Vote' ) ) {
			return false;
		}
		$where = $this->where('vote_user_id');
		$where[] = 'vote_page_id = page_id';
		$res = $dbr->select(
			array( 'Vote', 'page' ),
			array(
				'UNIX_TIMESTAMP(vote_date) AS item_date', 'username',
				'page_title', 'vote_count', 'comment_count', 'vote_ip',
				'vote_user_id'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'vote_date DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			)
		);

		foreach ( $res as $row ) {
			$username = $row->username;
			$this->items[] = array(
				'id' => 0,
				'type' => 'vote',
				'timestamp' => $row->item_date,
				'pagetitle' => $row->page_title,
				'namespace' => $row->page_namespace,
				'username' => $username,
				'userid' => $row->vote_user_id,
				'comment' => '-',
				'new' => '0',
				'minor' => 0
			);
		}
	}
	/**
	 * Get recent uploads from the image table (provided by the Comments
	 * extension) and set them in the appropriate class member variables.
	 */
	private function setImageUploads() {
		global $wgDBprefix, $wgDBname, $isProduction;
		$dbr = wfGetDB( DB_SLAVE );

		$where = $this->where('img_user');
		$sqls = array();
		//$where[] = 'comment_page_id = page_id';

		$tables = $this->getTables();
		$oldDBprefix = $wgDBprefix;
		$oldDB = $wgDBname;
		$dbr->tablePrefix('');

		foreach ($tables as $table){
			if ( !$isProduction ){
				$dbr->selectDB('huiji_'.str_replace('.', '_', $table));
				$DBprefix = '';
				break;
			} elseif ( $table == 'www'){
				$dbr->selectDB('huiji_home');
				$DBprefix = '';
				continue;
			} else {
				$dbr->selectDB('huiji_sites');
				$DBprefix = str_replace('.', '_', $table);
			}
			$tableName = '`'.$DBprefix.'image'.'`';
			$fieldName = implode( ',', $dbr->fieldNamesWithAlias( 
				array('UNIX_TIMESTAMP(img_timestamp) AS item_date',
					'img_user_text', 'img_name', 'img_description',
					'img_user', 'img_sha1', $dbr->addQuotes($table).' AS prefix',
					)
				) 
			);
			if (count($where) > 0){
				$conds = $dbr->makeList( $where, LIST_AND );
				$sql = "SELECT $fieldName FROM $tableName WHERE $conds";
			} else {
				$sql = "SELECT $fieldName FROM $tableName";
			}
			$sqls[] = $sql;

		}
		if (count($sqls) > 0){
			
			$res = $dbr->query($dbr->unionQueries($sqls, true)." ORDER BY `item_date` DESC LIMIT $this->sql_depth OFFSET 0");

			foreach ( $res as $row ) {
				$row->item_date = strtotime('+8 hour', $row->item_date);
				$show_upload = true;

				// global $wgFilterComments;
				// if ( $wgFilterComments ) {
				// 	if ( $row->vote_count <= 4 ) {
				// 		$show_comment = false;
				// 	}
				// }

				if ( $show_upload ) {
					$title = Title::makeTitle( NS_FILE, $row->img_name);
					$this->items_grouped['image_upload'][$title->getPrefixedText()]['users'][$row->img_user_text][] = array(
						'id' => $row->img_sha1,
						'type' => 'image_upload',
						'timestamp' => $row->item_date,
						'pagetitle' => $row->img_name,
						'namespace' => NS_FILE,
						'username' => $row->img_user_text,
						'userid' => $row->img_user,
						'comment' => $row->img_description,
						'minor' => 0,
						'new' => 0,
						'prefix' => $row->prefix
					);

					// set last timestamp
					if (!isset($this->items_grouped['image_upload'][$title->getPrefixedText()]['timestamp'])){
						$this->items_grouped['image_upload'][$title->getPrefixedText()]['timestamp'] = $row->item_date;
					}
					
					//$username = $row->Comment_Username;
					$this->items[] = array(
						'id' => $row->img_sha1,
						'type' => 'image_upload',
						'timestamp' => $row->item_date,
						'pagetitle' => $row->img_name,
						'namespace' => NS_FILE,
						'username' => $row->img_user_text,
						'userid' => $row->img_user,
						'comment' => $row->img_description,
						'minor' => 0,
						'new' => 0,
						'prefix' => $row->prefix
					);
					// set prefix
					$this->items_grouped['image_upload'][$title->getPrefixedText()]['prefix'][] = $row->prefix;
				}
			}
		}
		
		$dbr->tablePrefix($oldDBprefix);
		$dbr->selectDB($oldDB);
	}

	/**
	 * Get recent comments from the Comments table (provided by the Comments
	 * extension) and set them in the appropriate class member variables.
	 */
	private function setComments() {
		global $wgDBprefix, $wgDBname, $isProduction, $wgHuijiPrefix;
		$dbr = wfGetDB( DB_SLAVE );

		# Bail out if Comments table doesn't exist
		if ( !$dbr->tableExists( 'Comments' ) ) {
			return false;
		}

		$where = $this->where('Comment_user_id');
		//$where[] = 'comment_page_id = page_id';
		$sqls = array();

		$tables = $this->getTables();
		$oldDBprefix = $wgDBprefix;
		$oldDB = $wgDBname;
		$dbr->tablePrefix('');

		foreach ($tables as $table){
			if ( !$isProduction ){
				$dbr->selectDB('huiji_'.str_replace('.', '_', $table));
				$DBprefix = '';
				break;
			} elseif ( $table == 'www'){
				$dbr->selectDB('huiji_home');
				$DBprefix = '';
				continue;
			} else {
				$dbr->selectDB('huiji_sites');
				$DBprefix = str_replace('.', '_', $table);
			}
			$tableName = '`'.$DBprefix.'Comments'.'`';
			$joinTableName =  '`'.$DBprefix.'page'.'`';
			$fieldName = implode( ',', $dbr->fieldNamesWithAlias( 
				array(
					'UNIX_TIMESTAMP(comment_date) AS item_date',
					'Comment_Username', 'page_title', 'Comment_Text',
					'Comment_user_id', 'page_namespace', 'CommentID', $dbr->addQuotes($table).' AS prefix',
					)
				)
			);
			if (count($where) > 0){
				$conds = $dbr->makeList( $where, LIST_AND );
				$sql = "SELECT $fieldName FROM $tableName INNER JOIN $joinTableName ON comment_page_id = page_id WHERE $conds";
			} else {
				$sql = "SELECT $fieldName FROM $tableName INNER JOIN $joinTableName ON comment_page_id = page_id";
			}
			$sqls[] = $sql;
		}
		if (count($sqls) > 0){
			$res = $dbr->query($dbr->unionQueries($sqls, true)." ORDER BY `item_date` DESC LIMIT $this->sql_depth OFFSET 0");
			foreach ( $res as $row ) {
				$show_comment = true;

				global $wgFilterComments;
				if ( $wgFilterComments ) {
					if ( $row->vote_count <= 4 ) {
						$show_comment = false;
					}
				}

				if ( $show_comment ) {
					if ($table != $wgHuijiPrefix){
						$title = Title::makeTitle( $row->page_namespace, $row->page_title, 'Comments-'.$row->CommentID, $row->prefix );
					} else {
						$title = Title::makeTitle( $row->page_namespace, $row->page_title, 'Comments-'.$row->CommentID );
					}
					$this->items_grouped['comment'][$title->getPrefixedText()]['users'][$row->Comment_Username][] = array(
						'id' => $row->CommentID,
						'type' => 'comment',
						'timestamp' => $row->item_date,
						'pagetitle' => $row->page_title,
						'namespace' => $row->page_namespace,
						'username' => $row->Comment_Username,
						'userid' => $row->Comment_user_id,
						'comment' => $this->fixItemComment( $row->Comment_Text ),
						'minor' => 0,
						'new' => 0,
						'prefix' => $row->prefix
					);

					// set last timestamp
					if (!isset($this->items_grouped['comment'][$title->getPrefixedText()]['timestamp'])){
						$this->items_grouped['comment'][$title->getPrefixedText()]['timestamp'] = $row->item_date;
					}

					$username = $row->Comment_Username;
					$this->items[] = array(
						'id' => $row->CommentID,
						'type' => 'comment',
						'timestamp' => $row->item_date,
						'pagetitle' => $row->page_title,
						'namespace' => $row->page_namespace,
						'username' => $username,
						'userid' => $row->Comment_user_id,
						'comment' => $this->fixItemComment( $row->Comment_Text ),
						'new' => '0',
						'minor' => 0,
						'prefix' => $row->prefix
					);
					// set prefix
					$this->items_grouped['comment'][$title->getPrefixedText()]['prefix'][] = $row->prefix;
				}
			}

		}
		$dbr->tablePrefix($oldDBprefix);
		$dbr->selectDB($oldDB);
	}

	/**
	 * Get recently sent user-to-user gifts from the user_gift and gift tables
	 * and set them in the appropriate class member variables.
	 */
	private function setGiftsSent() {
		$dbr = wfGetDB( DB_SLAVE );

		$where = $this->where('ug_user_id_from');

		$res = $dbr->select(
			array( 'user_gift', 'gift' ),
			array(
				'ug_id', 'ug_user_id_from', 'ug_user_name_from',
				'ug_user_id_to', 'ug_user_name_to',
				'UNIX_TIMESTAMP(ug_date) AS item_date', 'gift_name', 'gift_id'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'ug_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			),
			array( 'gift' => array( 'INNER JOIN', 'gift_id = ug_gift_id' ) )
		);

		foreach ( $res as $row ) {
			$this->items[] = array(
				'id' => $row->ug_id,
				'type' => 'gift-sent',
				'timestamp' => $row->item_date,
				'pagetitle' => $row->gift_name,
				'namespace' => $row->gift_id,
				'username' => $row->ug_user_name_from,
				'userid' => $row->ug_user_id_from,
				'comment' => $row->ug_user_name_to,
				'new' => '0',
				'minor' => 0
			);
		}
	}

	/**
	 * Get recently received user-to-user gifts from the user_gift and gift
	 * tables and set them in the appropriate class member variables.
	 */
	private function setGiftsRec() {
		global $wgLang;
		$dbr = wfGetDB( DB_SLAVE );

		$where = $this->where('ug_user_id_to');

		$res = $dbr->select(
			array( 'user_gift', 'gift' ),
			array(
				'ug_id', 'ug_user_id_from', 'ug_user_name_from',
				'ug_user_id_to', 'ug_user_name_to',
				'UNIX_TIMESTAMP(ug_date) AS item_date', 'gift_name', 'gift_id'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'ug_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			),
			array( 'gift' => array( 'INNER JOIN', 'gift_id = ug_gift_id' ) )
		);

		foreach ( $res as $row ) {
			global $wgUploadPath, $wgMemc;
			$key = wfForeignMemcKey('huiji', '', 'setGiftsRec', $row->ug_id);
			$html = $wgMemc->get($key);
			$timeago = CommentFunctions::getTimeAgo($row->item_date).'前';
			if ($html != ''){
				$html = $this->updateTime($html, $timeago);
			} else {
				$user_title = Title::makeTitle( NS_USER, $row->ug_user_name_to );
				$user_title_from = Title::makeTitle( NS_USER, $row->ug_user_name_from );

				$gift_image = '<img src="' . $wgUploadPath . '/awards/' .
					Gifts::getGiftImage( $row->gift_id, 'm' ) .
					'" border="0" alt="" />';
				$view_gift_link = SpecialPage::getTitleFor( 'ViewGift' );
				$avatar = new wAvatar($row->ug_user_id_to, 'l');
				$avatarUrl = $avatar->getAvatarURL();
				$user_name_short = $wgLang->truncate( $row->ug_user_name_to, 25 );
				// $timeago = CommentFunctions::getTimeAgo($row->item_date);
				/* build html */
				$html = $this->templateParser->processTemplate(
					'user-home-item',
					array(
						'userAvatar' => $avatarUrl,
						'userName'  => '<b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\">{$user_name_short}</a></b>",
						'timestamp' => $timeago,
						'description' => wfMessage( 'useractivity-gift',
										'<b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\">{$row->ug_user_name_to}</a></b>",
										'<a href="' . htmlspecialchars( $user_title_from->getFullURL() ) . "\">{$user_title_from->getText()}</a>"
									)->text(),
						'hasShowcase' => true,
						'showcase' =>  
									"<a href=\"" . htmlspecialchars( $view_gift_link->getFullURL( 'gift_id=' . $row->ug_id ) ) . "\" rel=\"nofollow\">
											{$gift_image}
											{$row->gift_name}
										</a>
									",
					)
				);
				$wgMemc->set($key, $html);
			}
			
			$this->activityLines[] = array(
				'type' => 'gift-rec',
				'timestamp' => $row->item_date,
				'data' => $html
			);
			// $this->activityLines[] = array(
			// 	'type' => 'gift-rec',
			// 	'timestamp' => $row->item_date,
			// 	'data' => ' ' . $html
			// );

			$this->items[] = array(
				'id' => $row->ug_id,
				'type' => 'gift-rec',
				'timestamp' => $row->item_date,
				'pagetitle' => $row->gift_name,
				'namespace' => $row->gift_id,
				'username' => $row->ug_user_name_to,
				'userid' => $row->ug_user_id_to,
				'comment' => $row->ug_user_name_from,
				'new' => '0',
				'minor' => 0
			);
		}
	}

	/**
	 * Get recently received system gifts (awards) from the user_system_gift
	 * and system_gift tables and set them in the appropriate class member
	 * variables.
	 */
	private function setSystemGiftsRec() {
		global $wgUploadPath, $wgLang;

		$dbr = wfGetDB( DB_SLAVE );

		$where = $this->where('sg_user_id');

		$res = $dbr->select(
			array( 'user_system_gift', 'system_gift' ),
			array(
				'sg_id', 'sg_user_id', 'sg_user_name',
				'UNIX_TIMESTAMP(sg_date) AS item_date', 'gift_name', 'gift_id'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'sg_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			),
			array( 'system_gift' => array( 'INNER JOIN', 'gift_id = sg_gift_id' ) )
		);

		foreach ( $res as $row ) {
			global $wgMemc;
			$key = wfForeignMemcKey('huiji', '', 'setSystemGiftsRec', $row->sg_id);
			$html = $wgMemc->get($key);
			$timeago = CommentFunctions::getTimeAgo($row->item_date).'前';
			if ($html != ''){
				$html = $this->updateTime($html, $timeago);
			} else {
				$user_title = Title::makeTitle( NS_USER, $row->sg_user_name );
				$system_gift_image = '<img src="' . $wgUploadPath . '/awards/' .
					SystemGifts::getGiftImage( $row->gift_id, 'm' ) .
					'" border="0" alt="" />';
				$system_gift_link = SpecialPage::getTitleFor( 'ViewSystemGift' );
				$user_name_short = $wgLang->truncate( $row->sg_user_name, 25 );
				$avatar = new wAvatar( $row->sg_user_id, 'l');
				$avatarUrl = $avatar->getAvatarURL();
				// $timeago = CommentFunctions::getTimeAgo($row->item_date).'前';
				/* build html */
				$html = $this->templateParser->processTemplate(
					'user-home-item',
					array(
						'userAvatar' => $avatarUrl,
						'userName'  => '<b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\">{$user_name_short}</a></b>",
						'timestamp' => $timeago,
						'description' => wfMessage(
											'useractivity-award',
											'<b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\">{$row->sg_user_name}</a></b>",
											$row->sg_user_name
										)->text(),
						'hasShowcase' => true,
						'showcase' =>  
									'<a href="' . htmlspecialchars( $system_gift_link->getFullURL( 'gift_id=' . $row->sg_id ) ) . "\" rel=\"nofollow\">
											{$system_gift_image}
											{$row->gift_name}
									</a>
									",
					)
				);
				$wgMemc->set($key, $html);
			}
			$this->activityLines[] = array(
				'type' => 'system_gift-rec',
				'timestamp' => $row->item_date,
				'data' => $html
			);

			// $html = wfMessage(
			// 	'useractivity-award',
			// 	'<b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\">{$row->sg_user_name}</a></b>",
			// 	$row->sg_user_name
			// )->text() .
			// '<div class="item">
			// 	<a href="' . htmlspecialchars( $system_gift_link->getFullURL( 'gift_id=' . $row->sg_id ) ) . "\" rel=\"nofollow\">
			// 		{$system_gift_image}
			// 		{$row->gift_name}
			// 	</a>
			// </div>";

			// $this->activityLines[] = array(
			// 	'type' => 'system_gift',
			// 	'timestamp' => $row->item_date,
			// 	'data' => ' ' . $html
			// );

			$this->items[] = array(
				'id' => $row->sg_id,
				'type' => 'system_gift',
				'timestamp' => $row->item_date,
				'pagetitle' => $row->gift_name,
				'namespace' => $row->gift_id,
				'username' => $row->sg_user_name,
				'userid' => $row->sg_user_id,
				'comment' => '-',
				'new' => '0',
				'minor' => 0
			);
		}
	}

	/**
	 * Get recent changes in user relationships from the user_relationship
	 * table and set them in the appropriate class member variables.
	 */
	private function setRelationships() {
		global $wgLang;

		$dbr = wfGetDB( DB_SLAVE );

		$where = $this->where('r_user_id');
		$res = $dbr->select(
			'user_relationship',
			array(
				'r_id', 'r_user_id', 'r_user_name', 'r_user_id_relation',
				'r_user_name_relation', 'r_type',
				'UNIX_TIMESTAMP(r_date) AS item_date'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'r_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			)
		);

		foreach ( $res as $row ) {
			if ( $row->r_type == 1 ) {
				$r_type = 'friend';
			} else {
				$r_type = 'foe';
			}

			$user_name_short = $wgLang->truncate( $row->r_user_name, 25 );

			$this->items_grouped[$r_type][$row->r_user_name_relation]['users'][$row->r_user_name][] = array(
				'id' => $row->r_id,
				'type' => $r_type,
				'timestamp' => $row->item_date,
				'pagetitle' => '',
				'namespace' => '',
				'username' => $user_name_short,
				'userid' => $row->r_user_id,
				'comment' => $row->r_user_name_relation,
				'minor' => 0,
				'new' => 0
			);

			// set last timestamp
			if (!isset($this->items_grouped[$r_type][$row->r_user_name_relation]['timestamp'])){
				$this->items_grouped[$r_type][$row->r_user_name_relation]['timestamp'] = $row->item_date;
			}


			$this->items[] = array(
				'id' => $row->r_id,
				'type' => $r_type,
				'timestamp' => $row->item_date,
				'pagetitle' => '',
				'namespace' => '',
				'username' => $row->r_user_name,
				'userid' => $row->r_user_id,
				'comment' => $row->r_user_name_relation,
				'new' => '0',
				'minor' => 0
			);
		}
	}

	/**
	 * Get recently sent public user board messages from the user_board table
	 * and set them in the appropriate class member variables.
	 */
	private function setMessagesSent() {

		$dbr = wfGetDB( DB_SLAVE );

		$where = $this->where('ub_user_id_from');
		// We do *not* want to display private messages...
		$where['ub_type'] = 0;
		$res = $dbr->select(
			'user_board',
			array(
				'ub_id', 'ub_user_id', 'ub_user_name', 'ub_user_id_from',
				'ub_user_name_from', 'UNIX_TIMESTAMP(ub_date) AS item_date',
				'ub_message'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'ub_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			)
		);

		foreach ( $res as $row ) {
			// Ignore nonexistent (for example, renamed) users
			$uid = $row->ub_user_id_from;
			if ( !$uid ) {
				continue;
			}
			$to = stripslashes( $row->ub_user_name );
			$from = stripslashes( $row->ub_user_name_from );
			$this->items_grouped['user_message'][$to]['users'][$from][] = array(
				'id' => $row->ub_id,
				'type' => 'user_message',
				'timestamp' => $row->item_date,
				'pagetitle' => '',
				'namespace' => '',
				'username' => $from,
				'userid' => $row->ub_user_id_from,
				'comment' => $to,
				'minor' => 0,
				'new' => 0
			);

			// set last timestamp
			if(!isset($this->items_grouped['user_message'][$to]['timestamp'])){
				$this->items_grouped['user_message'][$to]['timestamp'] = $row->item_date;
			}

			$this->items[] = array(
				'id' => $row->ub_id,
				'type' => 'user_message',
				'timestamp' => $row->item_date,
				'pagetitle' => '',
				'namespace' => $this->fixItemComment( $row->ub_message ),
				'username' => $from,
				'userid' => $row->ub_user_id_from,
				'comment' => $to,
				'new' => '0',
				'minor' => 0
			);
		}
	}

	/**
	 * Get recent system messages (i.e. "User Foo advanced to level Bar") from
	 * the user_system_messages table and set them in the appropriate class
	 * member variables.
	 */
	private function setSystemMessages() {
		global $wgLang;

		$dbr = wfGetDB( DB_SLAVE );

		$where = $this->where('um_user_id');

		$res = $dbr->select(
			'user_system_messages',
			array(
				'um_id', 'um_user_id', 'um_user_name', 'um_type', 'um_message',
				'UNIX_TIMESTAMP(um_date) AS item_date'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'um_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			)
		);

		foreach ( $res as $row ) {
			global $wgMemc;
			$key = wfForeignMemcKey('huiji', '', 'setSystemMessages', $row->um_id);
			$html = $wgMemc->get($key);
			$timeago = CommentFunctions::getTimeAgo($row->item_date).'前';
			if ($html != ''){
				$html = $this->updateTime($html, $timeago);
			} else {
				$user_title = Title::makeTitle( NS_USER, $row->um_user_name );
				$user_name_short = $wgLang->truncate( $row->um_user_name, 15 );
				$avatar = new wAvatar( $row->um_user_id, 'l');
				$avatarUrl = $avatar->getAvatarHtml();
				// $timeago = CommentFunctions::getTimeAgo($row->item_date).'前';
				/* build html */
				$html = $this->templateParser->processTemplate(
					'user-home-item',
					array(
						'userAvatar' => $avatarUrl,
						'userName'  => '<b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\">{$user_name_short}</a></b>",
						'timestamp' => $timeago,
						'description' => ' ' . '<b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\">{$user_name_short}</a></b> {$row->um_message}",
						'hasShowcase' => false,
					)
				);
				$html = $wgMemc->set($key, $html);
			}
			$this->activityLines[] = array(
				'type' => 'system_message',
				'timestamp' => $row->item_date,
				'data' => $html
			);
			// $this->activityLines[] = array(
			// 	'type' => 'system_message',
			// 	'timestamp' => $row->item_date,
			// 	'data' => ' ' . '<b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\">{$user_name_short}</a></b> {$row->um_message}"
			// );

			$this->items[] = array(
				'id' => $row->um_id,
				'type' => 'system_message',
				'timestamp' => $row->item_date,
				'pagetitle' => '',
				'namespace' => '',
				'username' => $row->um_user_name,
				'userid' => $row->um_user_id,
				'comment' => $row->um_message,
				'new' => '0',
				'minor' => 0
			);
		}
	}

	/**
	 * Get recent status updates (but only if the SportsTeams extension is
	 * installed) and set them in the appropriate class member variables.
	 */
	private function setNetworkUpdates() {
		global $wgLang;

		if ( !class_exists( 'SportsTeams' ) ) {
			return;
		}

		$dbr = wfGetDB( DB_SLAVE );

		$where = $this->where('us_user_id');
		$res = $dbr->select(
			'user_status',
			array(
				'us_id', 'us_user_id', 'us_user_name', 'us_text',
				'UNIX_TIMESTAMP(us_date) AS item_date', 'us_sport_id',
				'us_team_id'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'us_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			)
		);

		foreach ( $res as $row ) {
			if ( $row->us_team_id ) {
				$team = SportsTeams::getTeam( $row->us_team_id );
				$network_name = $team['name'];
			} else {
				$sport = SportsTeams::getSport( $row->us_sport_id );
				$network_name = $sport['name'];
			}

			$this->items[] = array(
				'id' => $row->us_id,
				'type' => 'network_update',
				'timestamp' => $row->item_date,
				'pagetitle' => '',
				'namespace' => '',
				'username' => $row->us_user_name,
				'userid' => $row->us_user_id,
				'comment' => $row->us_text,
				'sport_id' => $row->us_sport_id,
				'team_id' => $row->us_team_id,
				'network' => $network_name
			);

			$user_title = Title::makeTitle( NS_USER, $row->us_user_name );
			$user_name_short = $wgLang->truncate( $row->us_user_name, 15 );
			$page_link = '<a href="' . SportsTeams::getNetworkURL( $row->us_sport_id, $row->us_team_id ) .
				"\" rel=\"nofollow\">{$network_name}</a>";
			$network_image = SportsTeams::getLogo( $row->us_sport_id, $row->us_team_id, 's' );

			$html = wfMessage(
				'useractivity-network-thought',
				$row->us_user_name,
				$user_name_short,
				$page_link,
				htmlspecialchars( $user_title->getFullURL() )
			)->text() .
					'<div class="item">
						<a href="' . SportsTeams::getNetworkURL( $row->us_sport_id, $row->us_team_id ) . "\" rel=\"nofollow\">
							{$network_image}
							\"{$row->us_text}\"
						</a>
					</div>";

			$this->activityLines[] = array(
				'type' => 'network_update',
				'timestamp' => $row->item_date,
				'data' => $html,
			);
		}
	}
	/**
	 * Get recent wiki creations and set them in the appropriate class member variables.
	 */
	private function setDomainCreations() {
		global $wgLang;

		// if ( !class_exists( 'SportsTeams' ) ) {
		// 	return;
		// }

		$dbr = wfGetDB( DB_SLAVE );

		$where = $this->where('domain_founder_id');

		/* reading database */
		$res = $dbr->select(
			'domain',
			array(
				'domain_id', 'domain_prefix', 'domain_name', 'domain_dsp',
				'UNIX_TIMESTAMP(domain_date) AS item_date', 'domain_founder_id',
				'domain_founder_name'
			),
			$where,
			__METHOD__,
			array(
				'ORDER BY' => 'domain_id DESC',
				'LIMIT' => $this->item_max,
				'OFFSET' => 0
			)
		);

		foreach ( $res as $row ) {
			// if ( $row->us_team_id ) {
			// 	$team = SportsTeams::getTeam( $row->us_team_id );
			// 	$network_name = $team['name'];
			// } else {
			// 	$sport = SportsTeams::getSport( $row->us_sport_id );
			// 	$network_name = $sport['name'];
			// }

			/* set item data */
			$this->items[] = array(
				'id' => $row->domain_id,
				'type' => 'domain_creation',
				'timestamp' => $row->item_date,
				'pagetitle' => '',
				'namespace' => '',
				'username' => $row->domain_founder_name,
				'userid' => $row->domain_founder_id,
				'comment' => $row->domain_dsp,
				'prefix' => $row->domain_prefix,
				'domainname' => $row->domain_name
			);
			global $wgMemc;
			$key = wfForeignMemcKey('huiji', '', 'setDomainCreations', $row->domain_id);
			$html = $wgMemc->get($key);
			$timeago = CommentFunctions::getTimeAgo($row->item_date).'前';
			if ($html != ''){
				$html = $this->updateTime($html, $timeago);
			} else {
				/* prepare data */
				$domainUrl = HuijiPrefix::prefixToUrl($row->domain_prefix);
				$user_name_short = $wgLang->truncate( $row->domain_founder_name, 15 );
				$user_title = Title::makeTitle( NS_USER, $row->domain_founder_name );
				$founder_link = '<b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\">{$user_name_short}</a></b>";
				// $timeago = CommentFunctions::getTimeAgo($row->item_date).'前';
				$page_link = '<a href="' . $domainUrl .
					"\" rel=\"nofollow\">{$row->domain_name}</a>";
				$avatar = new wAvatar($row->domain_founder_id, 'l');
				$avatarUrl = $avatar->getAvatarURL();
				/* build html */
				$html = $this->templateParser->processTemplate(
					'user-home-item',
					array(
						'userAvatar' => $avatarUrl,
						'userName'  => $founder_link,
						'timestamp' => $timeago,
						'description' => wfMessage(
											'useractivity-domain-creation',
											$founder_link,
											$page_link
										)->text(),
						'hasShowcase' => strlen($row->domain_dsp) > 0,
						'showcase' => $row->domain_dsp
					)
				);
				$wgMemc->set($key, $html);
			}
			// $html = wfMessage(
			// 	'useractivity-domain-creation',
			// 	$founder_link,
			// 	$page_link
			// )->text() .
			// 		'<div class="item">
			// 			<a href="' . $domainUrl . "\" rel=\"nofollow\">
			// 				\"{$row->domain_dsp}\"
			// 			</a>
			// 		</div>";

			$this->activityLines[] = array(
				'type' => 'domain_creation',
				'timestamp' => $row->item_date,
				'data' => $html,
			);
		}
	}

	public function getEdits() {
		$this->setEdits();
		return $this->items;
	}

	public function getVotes() {
		$this->setVotes();
		return $this->items;
	}

	public function getComments() {
		$this->setComments();
		return $this->items;
	}

	public function getGiftsSent() {
		$this->setGiftsSent();
		return $this->items;
	}

	public function getGiftsRec() {
		$this->setGiftsRec();
		return $this->items;
	}

	public function getSystemGiftsRec() {
		$this->setSystemGiftsRec();
		return $this->items;
	}

	public function getRelationships() {
		$this->setRelationships();
		return $this->items;
	}

	public function getSystemMessages() {
		$this->setSystemMessages();
		return $this->items;
	}

	public function getMessagesSent() {
		$this->setMessagesSent();
		return $this->items;
	}

	public function getNetworkUpdates() {
		$this->setNetworkUpdates();
		return $this->items;
	}	

	public function getUserUserFollows() {
		$this->setUserUserFollows();
		return $this->items;
	}	

	public function getUserSiteFollows() {
		$this->setUserSiteFollows();
		return $this->items;
	}

	public function getDomainCreations() {
		$this->setDomainCreations();
		return $this->items;
	}

	public function getImageUploads() {
		$this->setImageUploads();
		return $this->items;
	}

	public function getActivityList() {
		if ( $this->show_edits ) {
			$this->setEdits();
		}
		if ( $this->show_votes ) {
			$this->setVotes();
		}
		if ( $this->show_comments ) {
			$this->setComments();
		}
		if ( $this->show_gifts_sent ) {
			$this->setGiftsSent();
		}
		if ( $this->show_gifts_rec ) {
			$this->setGiftsRec();
		}
		if ( $this->show_relationships ) {
			$this->setRelationships();
		}
		if ( $this->show_system_messages ) {
			$this->getSystemMessages();
		}
		if ( $this->show_system_gifts ) {
			$this->getSystemGiftsRec();
		}
		if ( $this->show_messages_sent ) {
			$this->getMessagesSent();
		}
		if ( $this->show_network_updates ) {
			$this->getNetworkUpdates();
		}		
		if ( $this->show_user_user_follows ) {
			$this->getUserUserFollows();
		}		
		if ( $this->show_user_site_follows ) {
			$this->getUserSiteFollows();
		}
		if ( $this->show_domain_creations ) {
			$this->getDomainCreations();
		}
		if ( $this->show_image_uploads ) {
			$this->getImageUploads();
		}
		if ( $this->items ) {
			usort( $this->items, array( 'UserActivity', 'sortItems' ) );
		}
		return $this->items;
	}

	public function getActivityListGrouped() {
		$this->getActivityList();

		if ( $this->show_edits ) {
			$this->simplifyPageActivity( 'edit' );
		}
		if ( $this->show_comments ) {
			$this->simplifyPageActivity( 'comment' );
		}
		if ( $this->show_relationships ) {
			$this->simplifyPageActivity( 'friend' );
		}
		if ( $this->show_relationships ) {
			$this->simplifyPageActivity( 'foe' );
		}
		if ( $this->show_messages_sent ) {
			$this->simplifyPageActivity( 'user_message' );
		}
		if ( $this->show_user_user_follows ) {
			$this->simplifyPageActivity( 'user_user_follow' );
		}
		if ( $this->show_user_site_follows ) {
			$this->simplifyPageActivity( 'user_site_follow' );
		}
		if ( $this->show_image_uploads ) {
			$this->simplifyPageActivity( 'image_upload' );
		}
		if ( !isset( $this->activityLines ) ) {
			$this->activityLines = array();
		}
		if ( isset( $this->activityLines ) && is_array( $this->activityLines ) ) {
			usort( $this->activityLines, array( 'UserActivity', 'sortItems' ) );
		}
		return $this->activityLines;
	}

	/**
	 * @param $type String: activity type, such as 'friend' or 'foe' or 'edit'
	 * @param $has_page Boolean: true by default
	 */
	function simplifyPageActivity( $type, $has_page = true ) {
		global $wgLang, $wgMemc;

		if ( !isset( $this->items_grouped[$type] ) || !is_array( $this->items_grouped[$type] ) ) {
			return '';
		}


		foreach ( $this->items_grouped[$type] as $page_name => $page_data ) {
			$timeago = CommentFunctions::getTimeAgo($page_data['timestamp']).'前';
			/* memcache checking */
			$key = wfForeignMemcKey('huiji', '', 'simplifyPageActivity', $type, $page_name, $page_data['timestamp']);
			$html = $wgMemc->get($key);
			if ($html != ''){
				$html = $this->updateTime($html, $timeago);
			} else {
				$users = '';
				$pages = '';

				if ( $type == 'friend' || $type == 'foe' || $type == 'user_message' || $type == 'user_user_follow') {
					$page_title = Title::newFromText( $page_name, NS_USER );
				} elseif ($type == 'user_site_follow'){
					$page_title = Title::newFromText( $page_name.':' );
				} elseif ($type == 'image_upload'){
					$page_title = Title::newFromText( $page_name, NS_FILE );
				} else {
					$page_title = Title::newFromText( $page_name );
				} 

				$count_users = count( $page_data['users'] );
				$user_index = 0;
				$pages_count = 0;

				// Init empty variable to be used later on for GENDER processing
				// if the event is only for one user.
				$userNameForGender = '';
				foreach ( $page_data['users'] as $user_name => $action ) {


					/* get rid of same actions more than 1/2 day ago */
					// if ( $page_data['timestamp'] < $this->half_day_ago ) {
					// 	continue;
					// }
					$count_actions = count( $action );

					if ( $has_page && !isset( $this->displayed[$type][$page_name] ) ) {
						$this->displayed[$type][$page_name] = 1;
						$pages.= $this->fixPageTitle($page_title, $page_data);
						/* get User Avatar for display */
						$avatar = new wAvatar(User::idFromName($user_name), 'l');
						$avatarUrl = $avatar->getAvatarHtml();
						
						if ( $count_users == 1 && $count_actions > 1 ) {
							$pages .= wfMessage( 'word-separator' )->text();
							$pages .= wfMessage( 'parentheses', wfMessage(
								"useractivity-group-{$type}",
								$count_actions,
								$user_name
							)->text() )->text();
						}
						$pages_count++;
					}

					// Single user on this action,
					// see if we can stack any other singles
					if ( $count_users == 1 ) {
						$userNameForGender = $user_name;
						foreach ( $this->items_grouped[$type] as $page_name2 => $page_data2 ) {

							// if we find singles for this type, not displayed and not co-worked.
							if ( !isset( $this->displayed[$type][$page_name2] ) &&
								count( $page_data2['users'] ) == 1 
							) {
								//change since sept.7: only group pages with same prefix.
								if (isset($page_data['prefix']) && $page_data['prefix'][0] != $page_data2['prefix'][0] ){
									continue;
								} 
								// don't stack the old ones.
								/* get rid of same actions more than 1/2 day ago */
								if ( $page_data2['timestamp'] < $page_data['timestamp'] - $this->half_a_day ) {
									continue;
								}
								foreach ( $page_data2['users'] as $user_name2 => $action2 ) {
									if ( $user_name2 == $user_name && $pages_count < $this->item_max ) {
										$count_actions2 = count( $action2 );

										if (
											$type == 'friend' ||
											$type == 'foe' ||
											$type == 'user_message' ||
											$type == 'user_user_follow'
										) {
											$page_title2 = Title::newFromText( $page_name2, NS_USER );
										}  elseif ($type == 'user_site_follow'){
											$page_title2 = Title::newFromText( $page_name2.':' );
										}  elseif ($type == 'image_upload'){
											$page_title2 = Title::newFromText( $page_name2, NS_FILE );
										}  else {
											$page_title2 = Title::newFromText( $page_name2 );
										}

										if ( $pages ) {
											$pages .= $page_title2->inNamespace( NS_FILE )?'':'，';
										}
										$pages .= $this->fixPageTitle($page_title2, $page_data2);						
										if ( $count_actions2 > 1 ) {
											$pages .= ' (' . wfMessage(
												"useractivity-group-{$type}", $count_actions2
											)->text() . ')';
										}
										$pages_count++;
										// if (isset($page_data['prefix'])){
										// 	$page_data['prefix'] = array_merge($page_data['prefix'], $page_data2['prefix']);
										// }
										$this->displayed[$type][$page_name2] = 1;
									}
								}
							}
						}
					}

					$user_index++;

					if ( $users && $count_users > 2 ) {
						$users .= wfMessage( 'comma-separator' )->text();
					}
					if ( $user_index ==  $count_users && $count_users > 1 ) {
						$users .= wfMessage( 'and' )->text();
					}

					$user_title = Title::makeTitle( NS_USER, $user_name );
					$user_name_short = $wgLang->truncate( $user_name, 15 );

					$safeTitle = htmlspecialchars( $user_title->getText() );
					$users .= ' <b><a href="' . htmlspecialchars( $user_title->getFullURL() ) . "\" title=\"{$safeTitle}\">{$user_name_short}</a></b>";
				}
				$prefixToName = '';
				if ( isset($page_data['prefix']) ){
					if ( is_array($page_data['prefix'])){
						$page_data['prefix'] = array_unique($page_data['prefix']);
						$prefixCount = count($page_data['prefix']);
						$i = 0;
						foreach($page_data['prefix'] as $prefix){
							$prefixToName .= HuijiPrefix::prefixToSiteNameAnchor($prefix);
							$i++;
							if ($i < $prefixCount - 1 ){
								$prefixToName .= wfMessage( 'comma-separator' )->text();
							}
							if ($i == $prefixCount-1 && $prefixCount > 1){
								$prefixToName .= wfMessage( 'and' )->text();
							}
						}
					}elseif (is_string($page_data['prefix'])){
						$prefixToName .= HuijiPrefix::prefixToSiteNameAnchor($prefix);
					}
				}
				/* prepare format */

				/* build html */
				$html = $this->templateParser->processTemplate(
					'user-home-item',
					array(
						'userAvatar' => $avatarUrl,
						'userName'  => $users,
						'timestamp' => $timeago,
						'description' => wfMessage(
											"useractivity-{$type}",
											$users, $count_users, $pages, $pages_count,
											$userNameForGender, $prefixToName
										)->text(),
						'hasShowcase' => false,
					)
				);
				$wgMemc->set($key, $html);
			} // end of if
			if ( $pages || $has_page == false ) {

				$this->activityLines[] = array(
					'type' => $type,
					'timestamp' => $page_data['timestamp'],
					'data' => $html
				);
			}

		}
	}

	/**
	 * Get the correct icon for the given activity type.
	 *
	 * @param $type String: activity type, such as 'edit' or 'friend' (etc.)
	 * @return String: image file name (images are located in SocialProfile's
	 *                 images/ directory)
	 */
	static function getTypeIcon( $type ) {
		switch( $type ) {
			case 'edit':
				return '<i class="fa fa-pencil"></i>';
			case 'vote':
				return '<i class="fa fa-bar-chart"></i>';
			case 'comment':
				return '<i class="fa fa-comment"></i>';
			case 'gift-sent':
				return '<i class="fa fa-gift"></i>';
			case 'gift-rec':
				return '<i class="fa fa-gift"></i>';
			case 'friend':
				return '<i class="fa fa-user"></i>';
			case 'foe':
				return '<i class="fa fa-user"></i>';
			case 'system_message':
				return '<i class="fa fa-level-up"></i>';
			case 'system_gift':
				return '<i class="fa fa-heart"></i>';
			case 'user_message':
				return '<i class="fa fa-comments-o"></i>';
			case 'network_update':
				return '<i class="fa fa-laptop"></i>';
			case 'user_user_follow':
				return '<i class="fa fa-paper-plane"></i>';
			case 'user_site_follow':
				return '<i class="fa fa-paper-plane-o"></i>';
			case 'domain_creation':
				return '<i class="fa fa-paper-plane-o"></i>';
			case 'user_update_status':
				return '<i class="fa fa-paper-plane-o"></i>';
			case 'image_upload':
				return '<i class="fa fa-paper-plane-o"></i>';
		}
	}

	/**
	 * Generate an array that can be used to construct foreignDBRepo.
	 * @param $prefix :string, the HuijiPrefix, with no dot (use '_')
	 * @return array
	 */
	private function streamlineForeignDBRepo( $prefix ){
		global $wgDBtype, $wgDBserver, $wgDBuser, $wgDBpassword, $isProduction;
		$lowDashPrefix = str_replace('.', '_', $prefix);
		$dotPrefix = str_replace('_', '.', $prefix);
		if ($isProduction){
			if ($prefix != 'www'){
				return array(
				    'class' => 'ForeignDBRepo',
				    'name' => $dotPrefix,
				    'url' => "http://cdn.huijiwiki.com/{$dotPrefix}/uploads",
				    'directory' => '/var/www/virutal/{$dotPrefix}/uploads',
				    'hashLevels' => 2, // This must be the same for the other family member
				    'dbType' => $wgDBtype,
				    'dbServer' => $wgDBserver,
				    'dbUser' => $wgDBuser,
				    'dbPassword' => $wgDBpassword,
				    'dbFlags' => DBO_DEFAULT,
				    'dbName' => "huiji_sites",
				    'tablePrefix' => $lowDashPrefix,
				    'hasSharedCache' => false,
				    'descBaseUrl' => "http://{$dotPrefix}.huiji.wiki/wiki/File:",
				    'fetchDescription' => false,
				    'backend' => 'local-backend'				
				);
			} else {
				return array(
				    'class' => 'ForeignDBRepo',
				    'name' => $dotPrefix,
				    'url' => "http://cdn.huijiwiki.com/{$dotPrefix}/uploads",
				    'directory' => '/var/www/virutal/{$dotPrefix}/uploads',
				    'hashLevels' => 2, // This must be the same for the other family member
				    'dbType' => $wgDBtype,
				    'dbServer' => $wgDBserver,
				    'dbUser' => $wgDBuser,
				    'dbPassword' => $wgDBpassword,
				    'dbFlags' => DBO_DEFAULT,
				    'dbName' => "huiji_home",
				    'tablePrefix' => '',
				    'hasSharedCache' => false,
				    'descBaseUrl' => "http://{$dotPrefix}.huiji.wiki/wiki/File:",
				    'fetchDescription' => false,
				    'backend' => 'local-backend'				
				);
			}

		}
		$dbname = 'huiji_'.$lowDashPrefix;
		return array(
		    'class' => 'ForeignDBRepo',
		    'name' => $dotPrefix,
		    'url' => "http://{$dotPrefix}.huiji.wiki/uploads",
		    'directory' => '/var/www/virutal/{$dotPrefix}/uploads',
		    'hashLevels' => 0, // This must be the same for the other family member
		    'dbType' => $wgDBtype,
		    'dbServer' => $wgDBserver,
		    'dbUser' => $wgDBuser,
		    'dbPassword' => $wgDBpassword,
		    'dbFlags' => DBO_DEFAULT,
		    'dbName' => $dbname,
		    'tablePrefix' => '',
		    'hasSharedCache' => false,
		    'descBaseUrl' => "http://{$dotPrefix}.huiji.wiki/wiki/File:",
		    'fetchDescription' => false,
		    'backend' => 'local-backend'
		);

	}
	private function updateTime($html, $timeago){
		$startPoint = '<p class="time-ago"><strong>';
		$endPoint = '</strong></p>';
		$html = preg_replace('#('.preg_quote($startPoint).')(.*)('.preg_quote($endPoint).')#usi', '$1'.$timeago.'$3', $html);
		return $html;
	}
	/**
	 * "Fixes" a comment (such as a recent changes edit summary) by converting
	 * certain characters (such as the ampersand) into their encoded
	 * equivalents and, if necessary, truncates the comment and finally applies
	 * stripslashes() to the comment.
	 *
	 * @param $comment String: comment to "fix"
	 * @return String: "fixed" comment
	 */
	function fixItemComment( $comment ) {
		global $wgLang;
		if ( !$comment ) {
			return '';
		} else {
			$comment = str_replace( '<', '&lt;', $comment );
			$comment = str_replace( '>', '&gt;', $comment );
			$comment = str_replace( '&', '%26', $comment );
			$comment = str_replace( '%26quot;', '"', $comment );
		}
		$preview = $wgLang->truncate( $comment, 75 );
		return stripslashes( $preview );
	}

	/**
	 * "Fixes" a site link. Don't display site name in project namespace.
	 *
	 * @param $comment String: link to "fix"
	 * @return String: "fixed" comment
	 */
	function fixPageTitle( $page_title, $page_data ) {
		if ($page_title instanceOf Title){
			if ($page_title->inNamespace( NS_FILE )){
				$repo = new ForeignDBRepo($this->streamlineForeignDBRepo($page_data['prefix'][0]));
				$f =  ForeignDBFile::newFromTitle($page_title, $repo);
				return ' <a href="'.$f->getDescriptionUrl().'"><img src="' .$f->getFullUrl(). '"></img></a>';
			} else {
				return ' <a href="' . htmlspecialchars( $page_title->getFullURL() ) . "\">{$page_title->getText()}</a>";

			}
		}
		return '';
	}

	/**
	 * Compares the timestamps of two given objects to decide how to sort them.
	 * Called by getActivityList() and getActivityListGrouped().
	 *
	 * @param $x Object
	 * @param $y Object
	 * @return Integer: 0 if the timestamps are the same, -1 if $x's timestamp
	 *                  is greater than $y's, else 1
	 */
	private static function sortItems( $x, $y ) {
		if( $x['timestamp'] == $y['timestamp'] ) {
			return 0;
		} elseif ( $x['timestamp'] > $y['timestamp'] ) {
			return -1;
		} else {
			return 1;
		}
	}
}
