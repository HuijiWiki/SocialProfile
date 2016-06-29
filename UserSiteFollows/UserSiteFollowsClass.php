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
	static function getFollowerCount ( $huijiPrefix ){
		$data = self::getFollowerCountCache( $huijiPrefix );
		if ( $data != '' ) {
			if ( $data == -1 ) {
				$data = 0;
			}
			$count = $data;
		} else {
			$count = self::getFollowerCountDB( $huijiPrefix );
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
	static function getFollowerCountDB( $huijiPrefix ) {
		global $wgMemc;

		// wfDebug( "Got site followers count (prefix={$huijiPrefix}) from DB\n" );

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
	static function getFollowerCountCache( $huijiPrefix ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'follow_count', $huijiPrefix );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			// wfDebug( "Got site count of $data ( prefix = {$huijiPrefix} ) from cache\n" );
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
	static function getFollowingCount ( $user ){
		$data = self::getFollowingCountCache( $user );
		if ( $data != '' ) {
			if ( $data == -1 ) {
				$data = 0;
			}
			$count = $data;
		} else {
			$count = self::getFollowingCountDB( $user );
		}

		return $count;
	}
	/**
	 * Get the amount of site followed by a given user from the
	 * database and cache it.
	 *
	 * @param $user User Object:
	 * @return Integer
	 */
	static function getFollowingCountDB( $user ) {
		global $wgMemc;

		// wfDebug( "Got user followed sites count (prefix={$user}) from DB\n" );

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
	static function getFollowingCountCache( $user ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'user_count', $user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			// wfDebug( "Got user count of $data ( User = {$user} ) from cache\n" );
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
			// wfDebug( "Checkout ( User = {$user} ) from cache\n" );
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
		$wgMemc->delete( wfForeignMemcKey('huiji','', 'user_site_follow', 'all_sites_user_following', $user->getName() ) );
		$wgMemc->delete( wfForeignMemcKey('huiji','', 'user_site_follow', 'site_followed_list', $huijiPrefix ) );


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
		$wgMemc->delete( wfForeignMemcKey('huiji','', 'user_site_follow', 'all_sites_user_following', $user->getName() ) );	
		$wgMemc->delete( wfForeignMemcKey('huiji','', 'user_site_follow', 'site_followed_list', $huijiPrefix ) );
	}
		

	/**
	 * Get 3 most-edited following wiki site.
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

		// wfDebug( "Got user followed sites count (prefix={$user}) from DB\n" );

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
			$topFollowed[] = $prefix;
		}
		// $result = sortFollowedSite($topFollowed);
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
			//wfDebug( "Got top followed $data ( User = {$user} ) from cache\n" );
			return $data;
		}
	}
	public static function getTopFollowedSitesWithDetails( $user_id, $target_user_id ){
		$usf = new UserSiteFollow();
		$tuser = User::newFromId($target_user_id);
		$followedByTargetUser = $usf->getFullFollowedSites($tuser);
		$user = User::newFromId($user_id);
		$followedByCurrentUser = $usf->getFullFollowedSites($user);
		return self::sortFollowedSiteWithDetails($tuser, $followedByTargetUser, $followedByCurrentUser, 5);		
	}
	/**
	 * Get full list of followed sites from the
	 * database and cache it.
	 *
	 * @param $user: vist user id;$target_user_id:visted id
	 * @return array
	 */
	public static function getFullFollowedSitesWithDetails( $user_id,$target_user_id ) {
		$tuser = User::newFromId($target_user_id);
		$followedByTargetUser = self::getFullFollowedSites($tuser);
		$user = User::newFromId($user_id);
		$followedByCurrentUser = self::getFullFollowedSites($user);
		return self::sortFollowedSiteWithDetails($tuser, $followedByTargetUser, $followedByCurrentUser);
	}
	/**
	 * Sort The User Followed Sites by edits
	 *
	 *
	 * @param $followedByTargetUser: array, sites that are followed by the target user
	 * @param $followedByCurrentUser: array, Sites that are followed by the current user (wgUser).
	 *        defaut to null.
	 * @return array
	 */
	public static function sortFollowedSiteWithDetails($targetUser, $followedByTargetUser, $followedByCurrentUser = null, $limit = null){
		$fs = array();
		if (!empty ($followedByCurrentUser)){
			foreach ($followedByCurrentUser as $value) {
				$fs[] = $value;
			}
		}
		$followed = array();
		foreach( $followedByTargetUser as $row ){
			$temp = array();
			$domain = $row;
			$siteName = HuijiPrefix::prefixToSiteName($domain);
			$temp['count'] = UserStats::getSiteEditsCount($targetUser,$domain);
			$temp['key'] = $domain;
			$temp['val'] = $siteName;
			if(in_array($domain, $fs)){
				$is_follow = 'Y';
			}else{
				$is_follow = 'N';
			}
			$temp['is'] = $is_follow;
			$siteAvatar = new wSiteAvatar($domain, 'm');
			$temp['avatar'] =  $siteAvatar->getAvatarImage();
			$followed[] = $temp; 
		}
		$count = array();
		foreach ($followed as $key => $value) {
			$count[$key] = $value['count'];
		}
		array_multisort($count, SORT_DESC, $followed); 
		if (!empty($limit)){
			$followed = array_slice($followed, 0, $limit);
		}
		return $followed;
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
		$user = User::newFromId($user_id);
		$ures = self::getFullFollowedSites($user);
		$tuser = User::newFromId($target_user_id);
		$tres = self::getFullFollowedSites($tuser);
		foreach ($tres as $tval) {
			foreach ($ures as $uval) {
				if ($tval == $uval) {
					$coninterest[] = $tval;
				}
			}
		}
		return $coninterest;
	}
	/**
	 * Get site followed users 
	 *
	 * @param $user:current username; $site_name:servername
	 * @return array
	 */
	public static function getSiteFollowersWithDetails( $site_name ){
		// return '';
		$dbr = wfGetDB( DB_SLAVE );
		$request = array();
			// $follower = UserUserFollow::getFollowedByUser( $user->getName() );
			$res = self::getSiteFollowers($site_name);
			foreach ($res as $value) {
				$u_name = $value['user_name'];
				$temp['user'] = $u_name;
				// $temp['user'] = User::getEffectiveGroups($user);
				$userPage = Title::makeTitle( NS_USER, $u_name );
				$userPageURL = htmlspecialchars( $userPage->getFullURL() );
				$temp['userUrl'] = $userPageURL;
				$user_id = User::idFromName($u_name);
				$stats = new UserStats( $user_id, $u_name );
				$stats_data = $stats->getUserStats();
				$user_level = new UserLevel( $stats_data['points'] );
				$temp['level'] = $user_level->getLevelName();
				$avatar = new wAvatar( $user_id, 'm' );
				$temp['url'] = $avatar->getAvatarURL();
				$tuser = User::newFromName($u_name);
				$temp['count'] = UserStats::getSiteEditsCount($tuser,$site_name);

				// if(in_array($u_name, $follower)){
				// 	$is_follow = 'Y';
				// }else{
				// 	$is_follow = 'N';
				// }
				// $temp['is_follow'] = $is_follow;
				
				$request[] = $temp;
	 		}
	 		foreach ($request as $key => $value) {
					$count[$key] = $value['count'];
				}
			array_multisort($count, SORT_DESC, $request); 
			return $request;

	}
	/**
	 * Get user's list of his/her following sites.
	 *
	 * @param $user:current object
	 * @return array list of sites
	 */	
	public static function getFullFollowedSites( $user ){
		$data = self::getFullFollowedSitesCache( $user );
		if ( $data != '' ) {
			return $data;
		} else {
			return self::getFullFollowedSitesDB( $user );
		}
	}
	public static function getFullFollowedSitesCache( $user ){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'all_sites_user_following', $user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			// wfDebug( "Got top followed data ( User = {$user->getName()} ) from cache\n" );
			return $data;
		}		
	}
	public static function getFullFollowedSitesDB( $user ){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'all_sites_user_following', $user->getName() );
		$dbr = wfGetDB( DB_SLAVE );
		$result = array();
		$res = $dbr->select(
			'user_site_follow',
			array('f_wiki_domain'),
			array(
				'f_user_id' => $user->getId()
			),
			__METHOD__,
			array(
				'ORDER BY' => 'f_date DESC',
			)
		);
		if($res != false){
			foreach ($res as $value) {
				$result[] = $value->f_wiki_domain;
			}
			$wgMemc->set( $key, $result );
			return $result; 
		}

	}
	/**
	 * Get site followed users 
	 *
	 * @param $sitename:site's name
	 * @return array list of user
	 */	
	public static function getSiteFollowers( $sitename ) {
		$data = self::getSiteFollowersCache( $sitename );
		if ( $data != '' ) {
			return $data;
		}else {
			return self::getSiteFollowersDB( $sitename );
		}
	}
	public static function getSiteFollowersCache( $sitename ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'site_followed_list', $sitename );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			return $data;
		}
	}
	public static function getSiteFollowersDB( $sitename ){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'site_followed_list', $sitename );
		$dbr = wfGetDB( DB_SLAVE );
		$res = array();
		$res = $dbr->select(
			'user_site_follow',
			array(
				'f_user_name',
				'f_date'
			),
			array(
				'f_wiki_domain' => $sitename
			),
			__METHOD__,
			array( 
				'ORDER BY' => 'f_date DESC'
			)
		);
		// return $res;	
		if($res == true){
			$data = array();
			foreach ($res as $value) {
				$ruser = User::newFromName($value->f_user_name);
				$group = $ruser->getEffectiveGroups();
				$req['user_name'] = $value->f_user_name;
				$req['follow_date'] = $value->f_date;
				$data[] = $req;
			}
			$wgMemc->set( $key, $data );
			return $data;
		}
	}
	/**
	 * get new site followers in a given day
	 */
	static function getFollowerCountOneday ( $huijiPrefix, $date ){
		$data = self::getFollowerCountOnedayCache( $huijiPrefix, $date );
		if ( $data != '' ) {
			if ( $data == -1 ) {
				$data = 0;
			}
			$count = $data;
		} else {
			$count = self::getFollowerCountOnedayDB( $huijiPrefix, $date );
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
	static function getFollowerCountOnedayDB( $huijiPrefix, $date ) {
		global $wgMemc;

		// wfDebug( "Got site followers count (prefix={$huijiPrefix}) from DB in day:{$date}\n" );

		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'follow_count', $huijiPrefix.$date );
		$dbr = wfGetDB( DB_SLAVE );
		$siteCount = 0;

		$s = $dbr->selectRow(
			'user_site_follow',
			array( 'COUNT(*) AS count' ),
			"f_wiki_domain='".$huijiPrefix."' and f_date like '".$date."%'",
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
	static function getFollowerCountOnedayCache( $huijiPrefix, $date ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_site_follow', 'follow_count', $huijiPrefix.$date );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			// wfDebug( "Got site count of ( prefix = {$huijiPrefix} ) from cache in day{$date}\n" );
			return $data;
		}
	}

}
