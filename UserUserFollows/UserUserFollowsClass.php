<?php
/**
 * This Class manages the User and Site follows.
 */
class UserUserFollow{
	function __construct( ) {

	}

	/** add a user follow site action to the database.
	 *
	 *  @param $follower User object: the user who initiates the follow
	 *  @param $followee User object: the user to be followed
	 *	@return mixed: false if unsuccessful, id if successful
	 */
	public function addUserUserFollow($follower, $followee){
		if ( $this->checkUserUserFollow( $user, $huijiPrefix ) !== false ){
			return 0;
		}
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			'user_user_follow',
			array(
				'f_user_id' => $user->getId(),
				'f_user_name' => $user->getName(),
				'f_wiki_domain' => $huijiPrefix,
				'f_date' => date( 'Y-m-d H:i:s' )
			), __METHOD__
		);
		$followId = $dbw->insertId();
		$this->incFollowCount( $huijiPrefix );
		$stats = new UserStatsTrack( $user->getId(), $user->getName() );
		$stats->incStatField( 'friend' );
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

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'user_site_follow',
			array( 'f_user_id' => $user->getId(), 'f_wiki_domain' => $huijiPrefix ),
			__METHOD__
		);
		$stats = new UserStatsTrack( $user->getId(), $user->getName() );
		$stats->decStatField( 'friend' );
		$this->decFollowCount( $huijiPrefix );
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

		$key = wfMemcKey( 'user_site_follow', 'follow_count', $huijiPrefix );
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

		if ( $s !== false ) {
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
		$key = wfMemcKey( 'user_site_follow', 'follow_count', $huijiPrefix );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got site count of $data ( prefix = {$huijiPrefix} ) from cache\n" );
			return $data;
		}
	}
	/**
	* @param $user User Object
	* @param $huijiPrefix string: same as wgHuijiPrefix
	* @return Mixed: integer or boolean false
	*/
	public function checkUserSiteFollow($user, $huijiPrefix){
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow(			
			'user_site_follow',
			array( 'f_id' ),
			array( 'f_user_id' => $user->getId(), 'f_wiki_domain' => $huijiPrefix ),
			__METHOD__
		);
		if ($s !== false){
			return $s->f_id;
		}else {
			return false;
		}
	}
	/**
	 * Increase the amount of follewers for the site.
	 *
	 * @param $huijiPrefix string: which site
	 */
	private function incFollowCount($huijiPrefix){
		global $wgMemc;
		$key = wfMemcKey( 'user_site_follow', 'follow_count', $huijiPrefix );
		$wgMemc->incr( $key );
	}
	/**
	 * Decrease the amount of follewers for the site.
	 *
	 * @param $huijiPrefix string: which site
	 */
	private function decFollowCount($huijiPrefix){
		global $wgMemc;
		$key = wfMemcKey( 'user_site_follow', 'follow_count', $huijiPrefix );
		$wgMemc->decr( $key );
	}
	

}
