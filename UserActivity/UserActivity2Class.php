<?php
use MediaWiki\Logger\LoggerFactory;

class UserActivity2  {
	private $user_id;       # Text form (spaces not underscores) of the main part
	private $user_name;		# Text form (spaces not underscores) of the main part
	private $items;         # Text form (spaces not underscores) of the main part
	private $scoreThreshold = 40;

	private $show_following = false;
	private $show_current_user = false;
	private $show_all = false;
	private $show_following_sites = false;
	private $show_this_site = false;

	private $show_edits = 1;
	private $show_votes = 0;
	private $show_comments = 0;
	private $show_relationships = 0;
	private $show_gifts_sent = 0;
	private $show_gifts_rec = 0;
	private $show_system_gifts = 0;
	private $show_system_messages = 0;
	private $show_messages_sent = 0;
	private $show_network_updates = 0;
	private $show_user_user_follows = 0;
	private $show_user_site_follows = 0;
	private $show_user_update_status = 0;
	private $show_domain_creations = 0;
	private $show_image_uploads = 0;
	private $show_polls = 0;
	private $hide_bot = 0;

	private $templateParser;
	private $cached_where;
	private $cached_tables;

	const EXTRACT_LENGTH = 500;
	const PAGEIMAGE_WIDTH = 120;

	const REASON_USER_EDIT = 100;
	const REASON_SITE_EDIT = 200;
	public function __construct( $username, $filter, $item_max, $earlierThan = null ) {
		if ( $username ) {
			//$title1 = Title::newFromDBkey( $username );
			$this->user_name = $username;
			$this->user_id = User::idFromName( $this->user_name );
		}
		$this->setFilter( $filter );

		$this->item_max = $item_max;
		$this->sql_depth = $this->item_max * 5;
		$this->now = time();
		$this->half_day_ago = $this->now - ( 60 * 60 * 12 );
		$this->half_a_day = ( 60 * 60 * 12 );
		$this->items_grouped = array();
		$this->cached_where = false;
		$this->cached_tables = false;
		$this->templateParser = new TemplateParser(  __DIR__ . '/html' );
		$this->earlierThan = $earlierThan;		
		$this->logger = MediaWiki\Logger\LoggerFactory::getInstance( 'feed' );
	}
	private function setFilter( $filter ) {
		if ( strtoupper( $filter ) == 'USER' ) {
			$this->show_current_user = true;
			$this->scoreThreshold = 0;
		}
		if ( strtoupper( $filter ) == 'FOLLOWING' ){
			$this->show_following = true;
			$this->show_following_sites = true;
		}
		if ( strtoupper( $filter ) == 'ALL' ) {
			$this->show_all = true;
			$this->scoreThreshold = 80;
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
			$this->show_polls = 0;
			$this->hide_bot = 1;			
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
			$this->show_polls = 0;	
			$this->hide_bot = 1;		
		}
	}
	/**
	 * Based on the fileter, generate the where clause.
	 * @param $field the where clause field.
	 * @return array where clause for sql.
	 */
	private function where(){
		$userArray = array();
		$dbr = wfGetDB( DB_SLAVE );
		if (!empty($this->cached_where)){
			$userArray = $this->cached_where;
		} else {
			// if ( !empty( $this->rel_type ) ) {
			// 	$users = $dbr->select(
			// 		'user_relationship',
			// 		'r_user_id_relation',
			// 		array(
			// 			'r_user_id' => $this->user_id,
			// 			'r_type' => $this->rel_type
			// 		),
			// 		__METHOD__
			// 	);			
			// 	foreach ( $users as $user ) {
			// 		$userArray[] = $user->r_user_id_relation;
			// 	}
			// }

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
			if ( !empty( $this->hide_bot ) ){
				for ( $i = 0; $i < count($userArray); $i++ ){
					if (User::newFromId($userArray[$i])->isAllowed('bot')){
						unset($userArray[$i]);
					};
				}
				/* normalize userArray */
				$userArray = array_values($userArray);
			}
			//cache it
			$this->cached_where = $userArray;
		}
		return $this->cached_where;
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
			$tables = Huiji::getInstance()->getSitePrefixes(false);	
		}
		$this->cached_tables = $tables;
		return $tables;
	}
	private function setEdits(){
		global $wgContentNamespaces;
		$tables = $this->getTables();
		$where = $this->where();
		if (count($where) > 0){
			$siteFeed = FeedProvider::getFeed(
				'edit', 
				[], 
				$where,
				[],
				$this->scoreThreshold, 
				$this->earlierThan ? wfTimestamp(TS_ISO_8601, $this->earlierThan - 28800 ): null, 
				null 
			);		
			foreach ($siteFeed->message as $item){
				$this->items_grouped['page'][$item->site->prefix.":".$item->page->title][$item->user->name]['feed'] = $item;
				$this->items_grouped['page'][$item->site->prefix.":".$item->page->title][$item->user->name]['reason'][self::REASON_USER_EDIT]++;
				$this->items[] = array(
					'feed' => $item,
					'timestamp' => wfTimestamp(TS_UNIX, $item->timestamp) + 28800,
				);
			}	
		} 
		if (count($tables) > 0){
			$userFeed = FeedProvider::getFeed(
				'edit', 
				$tables, 
				[],
				[],
				$this->scoreThreshold, 
				$this->earlierThan ? wfTimestamp(TS_ISO_8601, $this->earlierThan - 28800 ): null,
				null 
			);	
			foreach ($userFeed->message as $item){
				$this->items_grouped['page'][$item->site->prefix.":".$item->page->title][$item->user->name]['feed'] = $item;
				$this->items_grouped['page'][$item->site->prefix.":".$item->page->title][$item->user->name]['reason'][self::REASON_SITE_EDIT]++;
				$this->items[]['feed'] = $item;
				$this->items[] = array(
					'feed' => $item,
					'timestamp' => wfTimestamp(TS_UNIX,$item->timestamp)+ 28800,
				);
			}	
		}
	}
	/**
	 * Sets the value of class member variable $name to $value.
	 */
	public function setActivityToggle( $name, $value ) {
		$this->$name = $value;
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
		if ( $this->show_polls ) {
			$this->setPolls();
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
			usort( $this->items, array( 'UserActivity2', 'sortItems' ) );
		}
		return $this->items;
	}
	public function getActivityListGrouped() {
		$this->getActivityList();

		if ( $this->show_edits ) {
			$this->simplifyPageActivity( 'page' );
		}
		if ( $this->show_votes ) {
			$this->simplifyPageActivity( 'vote' );
		}
		if ( $this->show_polls ) {
			$this->simplifyPageActivity( 'poll' );
		}
		if ( $this->show_comments ) {
			$this->simplifyPageActivity( 'comment' );
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
			usort( $this->activityLines, array( 'UserActivity2', 'sortItems' ) );
		}
		return $this->activityLines;
	}
	/**
	 * @param $type String: activity type, such as 'friend' or 'foe' or 'edit'
	 * @param $has_page Boolean: true by default
	 */
	function simplifyPageActivity( $type, $has_page = true ) {
		if ( !isset( $this->items_grouped[$type] ) || !is_array( $this->items_grouped[$type] ) ) {
			return '';
		}
		$this->logger->info('simplify page activity starts here');
		foreach($this->items_grouped[$type] as $pageName => $pageData){
			// $userCount = count($pageData);
			// if ($userCount > 3){
			// 	$params = array_keys($pageData);
			// 	array_unshift($params, $userCount);
			// 	$note = wfMessage('useractivity2-note-edit-morethan3',
			// 		$params
			// 	)->parse();
			// } else {
			// 	$this->logger->debug('params',['params'=>array_keys($pageData)]);
			// 	$note = wfMessage("useractivity2-note-edit-$userCount",
			// 		array_keys($pageData)
			// 	)->parse();
			// }
			// $this->logger->debug('note',['note'=>$note]);
			foreach($pageData as $userName => $detailData){

				$title = Title::makeTitle($detailData['feed']->page->ns, $detailData['feed']->page->title, '', $detailData['feed']->site->prefix);
				$this->logger->debug('title',['title' => $title]);
				$extract = $this->getExtract($title, self::EXTRACT_LENGTH);
				$this->logger->debug('extract', ['extract' => $extract]);
				$image = $this->getPageImage($detailData['feed']->site->prefix, $detailData['feed']->page->id, self::PAGEIMAGE_WIDTH);
				if ($image != ''){
					$hasImage = true;
				} else {
					$hasImage = false;
				}
				$this->logger->debug('image',['image' => $image]);

				if ($detailData['reason'][self::REASON_USER_EDIT] > 0 ){
					$reason = wfMessage(
						'useractivity2-reason-user-edit',
						$userName, 
						$detailData['feed']->site->prefix,
						$detailData['feed']->site->name,
						$detailData['reason'][self::REASON_USER_EDIT]
					)->parse();
					$avatarUrl = HuijiUser::newFromName($userName)->getAvatar('ml')->getAvatarHtml();
				} else if ($detailData['reason'][self::REASON_SITE_EDIT] > 0 ){
					$reason = wfMessage(
						'useractivity2-reason-site-edit',
						$detailData['feed']->site->prefix,
						$detailData['feed']->site->name,
						$detailData['reason'][self::REASON_SITE_EDIT]
					)->parse();
					$avatarUrl = WikiSite::newFromPrefix($detailData['feed']->site->prefix)->getAvatar('ml')->getAvatarHtml();
				}
				$this->logger->debug('reason',['reason' => $reason]);

				$timestamp = wfTimestamp(TS_UNIX, $detailData['feed']->timestamp )+28800 );
				//Now it is time to format real html.
				/* build html */
				$html = $this->templateParser->processTemplate(
					'user-home-item2',
					array(
						'userAvatar' => $avatarUrl,
						'reason'  => $reason,
						'timestamp' => HuijiFunctions::getTimeAgo($timestamp),
						'title' => Linker::LinkKnown($title, $title->getText()),
						'image' => $image,
						'hasImage' => $hasImage,
						'description' => $extract,
						'hasShowcase' => false,
						'editUrl' => $title->getFullURL(['veaction'=>'edit']),
						'sourceUrl' => $title->getFullURL(['action' => 'edit']),
					)
				);
				$this->activityLines[] = array(
					'type' => $type,
					'timestamp' => $timestamp,
					'data' => $html
				);
				$this->logger->info('done', ['html'=>$html, 'time'=>$timestamp]);
				break;
			}
			//Now it is time to format real html.
			/* build html */
			// $avatarUrl = HuijiUser::newFromName($userName)->getAvatar('ml')->getAvatarHtml();

			
			
		}
	}
	private function getExtract($title, $length){
		global $wgParser;
		$cache = wfGetCache(CACHE_ANYTHING);
		$key = wfMemcKey('UserActivity2', 'getextract',$title->getFullText() );
		$data = $cache->get($key);
		if ($data != ''){
			return $data;
		}
		$text = $wgParser->interwikiTransclude($title, 'render');
		$extract = new TextExtracts\ExtractFormatter($text, false, MediaWiki\MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'textextracts' ));
		$wikitext = $extract->getText();
		$result = $this->getFirstSection($wikitext, false);
		$cache->set($key, $result, 60*60*24);
		return $result;
	}
	private function getFirstSection( $text, $plainText ) {
		if ( $plainText ) {
			$regexp = '/^(.*?)(?=' . TextExtracts\ExtractFormatter::SECTION_MARKER_START . ')/s';
		} else {
			$regexp = '/^(.*?)(?=<h[1-6]\b)/s';
		}
		if ( preg_match( $regexp, $text, $matches ) ) {
			$text = $matches[0];
		}
		return $text;
	}
	// private function cacheKey( WikiPage $page, $introOnly ) {
	// 	return wfMemcKey( 'textextracts', $page->getId(), $page->getTouched(),
	// 		$page->getTitle()->getPageLanguage()->getPreferredVariant(),
	// 		$this->params['plaintext'], $introOnly
	// 	);
	// }
	// private function getFromCache( WikiPage $page, $introOnly ) {
	// 	global $wgMemc;
	// 	$key = $this->cacheKey( $page, $introOnly );
	// 	return $wgMemc->get( $key );
	// }
	// private function setCache( WikiPage $page, $text ) {
	// 	global $wgMemc;
	// 	$key = $this->cacheKey( $page, $this->params['intro'] );
	// 	$wgMemc->set( $key, $text );
	// }
	private function getPageImage($prefix, $id, $width){
		global $wgThumbLimits;
		$dbr = wfGetDB( DB_SLAVE );
		$cache = wfGetCache( CACHE_ANYTHING );
		$key = wfMemcKey('UserActivity2', 'getpageimage', $prefix, $id );
		$data = $cache->get($key);
		if ($data != ''){
			return $data;
		}

		$dbr->tablePrefix(WikiSite::tableNameFromPrefix($prefix));
		$dbr->selectDB('huiji_sites');
		$name = $dbr->selectField( 'page_props',
			'pp_value',
			[ 'pp_page' => $id, 'pp_propname' => PageImages::PROP_NAME ],
			__METHOD__
		);
		$imgUrl = '';
		if ($name){
			$imgUrl = "http://cdn.huijiwiki.com/$prefix/thumb.php?f=$name&width=$width";
		}
		$cache->set($key, $imgUrl, 60*60*24);
		return $imgUrl;
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
		if (!isset($x['timestamp']) || !isset($y['timestamp'])){
			return 0;
		}
		if( $x['timestamp'] == $y['timestamp'] ) {
			return 0;
		} elseif ( $x['timestamp'] > $y['timestamp'] ) {
			return -1;
		} else {
			return 1;
		}
	}
}