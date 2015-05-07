<?php
/**
 * This Class manages the User and Site follows.
 */
class UserSiteFollow{
	function __construct( ) {

	}

	/** add a user follow site action to the database.
	 *
	 *  @param $user User object: the user_name who initiates the follow
	 *  @param $huijiPrefix string: the wiki to be followed, use prefix as identifier.
	 *	@return bool: true if successfully followed
	 */
	public function addUserSiteFollow($user, $huijiPrefix){
		global $wgMemc;
		if ( $this->checkUserSiteFollow( $user, $huijiPrefix ) != false ){
			return 0;
		}
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			'user_site_follow',
			array(
				'f_user_id' => $user->getId(),
				'f_user_name' => $user->getName(),
				'f_wiki_domain' => $huijiPrefix,
				'f_date' => date( 'Y-m-d H:i:s' )
			), __METHOD__
		);
		$followId = $dbw->insertId();
		$this->incFollowCount( $user, $huijiPrefix );
		$stats = new UserStatsTrack( $user->getId(), $user->getName() );
		$stats->incStatField( 'friend' );

		//store result in cache
		if ($followId > 0){
			$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'check_follow', $user->getName(), $huijiPrefix );
			$wgMemc->set($key, true);			
		}
		// Notify Siteadmin maybe?
		return $followId;

	}

	/**
	 * Remove a follower from site and clear caches afterwards.
	 *
	 * @param $user1 User object: user to be removed
	 * @param $user2 string: site prefix
	 */
	public function deleteUserSiteFollow($user, $huijiPrefix){
		global $wgMemc;

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'user_site_follow',
			array( 'f_user_id' => $user->getId(), 'f_wiki_domain' => $huijiPrefix ),
			__METHOD__
		);
		$stats = new UserStatsTrack( $user->getId(), $user->getName() );
		$stats->decStatField( 'friend' );
		$this->decFollowCount( $user, $huijiPrefix );

		//store result in cache
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'check_follow', $user->getName(), $huijiPrefix );
		$wgMemc->set($key, false);
		return true;

	}

	/**
	 * Get the amount of site; first tries cache,
	 * and if that fails, fetches the count from the database.
	 *
	 * @param $huijiPrefix String: the site
	 * @return Integer
	 */
	static function getSiteCount ( $huijiPrefix ){
		$data = self::getSiteCountCache( $huijiPrefix );
		if ( $data != '' ) {
			if ( $data == -1 ) {
				$data = 0;
			}
			$count = $data;
		} else {
			$count = self::getSiteCountDB( $huijiPrefix );
		}

		return $count;
	}
	/**
	 * Get the amount of site followers from the
	 * database and cache it.
	 *
	 * @param $HuijiPrefix String:
	 * @return Integer
	 */
	static function getSiteCountDB( $huijiPrefix ) {
		global $wgMemc;

		wfDebug( "Got site followers count (prefix={$huijiPrefix}) from DB\n" );

		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'follow_count', $huijiPrefix );
		$dbr = wfGetDB( DB_SLAVE );
		$siteCount = 0;

		$s = $dbr->selectRow(
			'user_site_follow',
			array( 'COUNT(*) AS count' ),
			array(
				'f_wiki_domain' => $huijiPrefix
			),
			__METHOD__
		);

		if ( $s != false ) {
			$siteCount = $s->count;
		}

		$wgMemc->set( $key, $siteCount );
		return $siteCount;
	}

	/**
	 * Get the amount of site followers from cache.
	 *
	 * @param $huijiPrefix string: 
	 * 
	 * @return Integer
	 */
	static function getSiteCountCache( $huijiPrefix ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'follow_count', $huijiPrefix );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got site count of $data ( prefix = {$huijiPrefix} ) from cache\n" );
			return $data;
		}
	}

	/**
	 * Get the amount of users following the site; first tries cache,
	 * and if that fails, fetches the count from the database.
	 *
	 * @param $user User:object
	 * @return Integer
	 */
	static function getUserCount ( $user ){
		$data = self::getUserCountCache( $user );
		if ( $data != '' ) {
			if ( $data == -1 ) {
				$data = 0;
			}
			$count = $data;
		} else {
			$count = self::getUserCountDB( $user );
		}

		return $count;
	}
	/**
	 * Get the amount of site followers from the
	 * database and cache it.
	 *
	 * @param $user User Object:
	 * @return Integer
	 */
	static function getUserCountDB( $user ) {
		global $wgMemc;

		wfDebug( "Got user followed sites count (prefix={$user}) from DB\n" );

		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'user_count', $user->getName() );
		$dbr = wfGetDB( DB_SLAVE );
		$userCount = 0;

		$s = $dbr->selectRow(
			'user_site_follow',
			array( 'COUNT(*) AS count' ),
			array(
				'f_user_id' => $user->getId()
			),
			__METHOD__
		);

		if ( $s != false ) {
			$userCount = $s->count;
		}

		$wgMemc->set( $key, $userCount );
		return $userCount;
	}

	/**
	 * Get the amount of site followers from cache.
	 *
	 * @param $user User Object: 
	 * 
	 * @return Integer
	 */
	static function getUserCountCache( $user ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'user_count', $user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user count of $data ( User = {$user} ) from cache\n" );
			return $data;
		}
	}

	/**
	* @param $user User Object
	* @param $huijiPrefix string: same as wgHuijiPrefix
	* @return bool: true if they are indeed paired.
	*/
	public function checkUserSiteFollow($user, $huijiPrefix){

		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'check_follow', $user->getName(), $huijiPrefix );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Checkout ( User = {$user} ) from cache\n" );
			return $data;
		} else{
			$dbr = wfGetDB( DB_SLAVE );
			$s = $dbr->selectRow(			
				'user_site_follow',
				array( 'f_id' ),
				array( 'f_user_id' => $user->getId(), 'f_wiki_domain' => $huijiPrefix ),
				__METHOD__
			);
			if ($s != false){
				$wgMemc->set($key, true);
				return true;
			}else {
				$wgMemc->set($key, false);
				return false;
			}

		}

	}
	/**
	 * Increase the amount of follewers for the site.
	 *
	 * @param $huijiPrefix string: which site
	 * @param $user User object: which user
	 */
	private function incFollowCount($user, $huijiPrefix){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'follow_count', $huijiPrefix );
		$wgMemc->incr( $key );
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'user_count', $user->getName() );
		$wgMemc->incr( $key );
		$wgMemc->delete( wfForeignMemcKey('huiji','', 'user_site_follow', 'top_followed', $user->getName() ) );
	}
	/**
	 * Decrease the amount of follewers for the site.
	 *
	 * @param $huijiPrefix string: which site
	 */
	private function decFollowCount($user, $huijiPrefix){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'follow_count', $huijiPrefix );
		$wgMemc->decr( $key );
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'user_count', $user->getName() );
		$wgMemc->decr( $key );	
		$wgMemc->delete( wfForeignMemcKey('huiji','', 'user_site_follow', 'top_followed', $user->getName() ) );
	}
		

	/**
	 * Get 3 recently followed wiki site.
	 * 
	 * @param $user User object, whose info we want.
	 * @return array the array of the top followed site.
	 */
	public function getTopFollowedSites( $user ){
		$data = self::getTopFollowedSitesCache( $user );
		if ( $data != '' ) {
			return $data;
		} else {
			return self::getTopFollowedSitesDB( $user );
		}
		
	}
	/**
	 * Get the top followed site from the
	 * database and cache it.
	 *
	 * @param $user User Object:
	 * @return array
	 */
	static function getTopFollowedSitesDB( $user ) {
		global $wgMemc;

		wfDebug( "Got user followed sites count (prefix={$user}) from DB\n" );

		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'top_followed', $user->getName() );
		$dbr = wfGetDB( DB_SLAVE );
		$topFollowed = array();

		$s = $dbr->select(
			'user_site_follow',
			array( 'f_wiki_domain' ),
			array(
				'f_user_id' => $user->getId()
			),
			__METHOD__,
			array( 
				'ORDER BY' => 'f_date DESC',
				'LIMIT' => '3'
			)
		);
		foreach( $s as $row ){
			$prefix = $row->f_wiki_domain;
			$siteName = HuijiPrefix::prefixToSiteName($prefix);
			$topFollowed[$prefix] = $siteName;
		}

		$wgMemc->set( $key, $topFollowed );
		return $topFollowed;
	}
	/**
	 * Get top followed site from cache.
	 *
	 * @param $user User Object: 
	 * 
	 * @return array
	 */
	static function getTopFollowedSitesCache( $user ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'top_followed', $user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got top followed $data ( User = {$user} ) from cache\n" );
			return $data;
		}
	}
	/**
	 * Get full list of followed sites from the
	 * database and cache it.
	 *
	 * @param $user: vist user id;$target_user_id:visted id
	 * @return array
	 */
	public static function getFullFollowedSitesDB( $user_id,$target_user_id ) {

		$dbr = wfGetDB( DB_SLAVE );
		$followed = array();
		$fs = array();
		$res = $dbr->select(
			'user_site_follow',
			array('f_wiki_domain'),
			array(
				'f_user_id' => $target_user_id
			),
			__METHOD__,
			array(
				'ORDER BY' => 'f_date DESC',
			)
		);
		$s = $dbr->select(
			'user_site_follow',
			array( 'f_wiki_domain' ),
			array(
				'f_user_id' => $user_id
			),
			__METHOD__,
			array( 
				'ORDER BY' => 'f_date DESC',
			)
		);
		foreach ($s as$value) {
			$fs[] = $value->f_wiki_domain;
		}
		foreach( $res as $row ){
			$temp = array();
			$domain = $row->f_wiki_domain;
			$siteName = HuijiPrefix::prefixToSiteName($domain);
			$temp['key'] = $domain;
			$temp['val'] = $siteName;
			if(in_array($domain, $fs)){
				$is_follow = 'Y';
			}else{
				$is_follow = 'N';
			}
			$temp['is'] = $is_follow;
			$followed[] = $temp; 

		}
		return $followed;
	}
	/**
	 * Get user's followed from the
	 * database and show it.
	 *
	 * @param $username:current user
	 * @return array
	 */
	public static function getFollowedByUser( $username ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = array();
		$res = $dbr->select(
			'user_user_follow',
			array(
				'f_target_user_name'
			),
			array(
				'f_user_name' => $username
			),
			__METHOD__
		);
		foreach ($res as $value) {
			$req[] = $value->f_target_user_name;
		}
		$res = $req;
		return $res;
	}
	/**
	 * Get the common interests users of followed sites from the
	 * database and show it.
	 *
	 * @param $user_id:current user; $huijiPrefix:common interests
	 * @return array
	 */
	public static function getCommonInterestUser( $user_id,$huijiPrefix ){
		$dbr = wfGetDB( DB_SLAVE );
		$follow = self::getFollowedByUser($user_id);
		$common = array();
		$res = $dbr->select(
				'user_site_follow',
				array(
					'f_user_id',
				),
			    array(
			    	'f_wiki_domain' => $huijiPrefix,
			    ),
			    __METHOD__
		);
		foreach ($res as $resval) {
			$uid = $resval->f_user_id;
			foreach ($follow as $folval) {
				if($uid == $folval->$f_target_user_id){
					$common[] = $uid;
				}
			}
		}
		return $common;
	}
	/**
	 * Get common interests with the user you are watching
	 *
	 * @param $user_id:current user; $target_user_id:his id
	 * @return array
	 */
	public static function getCommonInterest( $user_id,$target_user_id ){
		$dbr = wfGetDB( DB_SLAVE );
		$coninterest = array();
		$ures = $dbr->select(
			'user_site_follow',
			array(
				'f_wiki_domain',
			),
			array(
				'f_user_id' => $user_id,
			),
			__METHOD__
		);
		$tres = $dbr->select(
			'user_site_follow',
			array(
				'f_wiki_domain',
			),
			array(
				'f_user_id' => $target_user_id,
			),
			__METHOD__
		);
		foreach ($tres as $tval) {
			$tdomain = $tval->f_wiki_domain;
			foreach ($ures as $uval) {
				if ($tdomain == $uval->f_wiki_domain) {
					$coninterest[] = $tdomain;
				}
			}
		}
		return $coninterest;
	}
	/**
	 * Get site follows from DB 
	 *
	 * @param $user:current username; $site_name:servername
	 * @return array
	 */
	public static function getUserFollowSite( $user,$site_name ){
		$dbr = wfGetDB( DB_SLAVE );
		$request = array();
		$follower = self::getFollowedByUser($user);
		$res = $dbr->select(
			'user_site_follow',
			array(
				'f_user_name'
			),
			array(
				'f_wiki_domain' => $site_name
			),
			__METHOD__
		);
		
		foreach ($res as $value) {
			$u_name = $value->f_user_name;
			$temp['user'] = $u_name;
			$userPage = Title::makeTitle( NS_USER, $u_name );
			$userPageURL = htmlspecialchars( $userPage->getFullURL() );
			$temp['userUrl'] = $userPageURL;
			$user_id = User::idFromName($u_name);
			$avatar = new wAvatar( $user_id, 'm' );
			$temp['url'] = $avatar->getAvatarURL();
			$req = $dbr->select(
				'user_stats',
				array(
					'stats_edit_count'
				),
				array(
					'stats_user_name' => $u_name
				),
				__METHOD__
			);
			foreach ($req as $value) {
				$temp['count'] = $value->stats_edit_count;
			}
			
			if(in_array($u_name, $follower)){
				$is_follow = 'Y';
			}else{
				$is_follow = 'N';
			}
			$temp['is_follow'] = $is_follow;
			$request[] = $temp;
 		}
		return $request;

	}

}
