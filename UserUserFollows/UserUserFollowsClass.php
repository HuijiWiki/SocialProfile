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

		if ($follower == null || $followee == null ){
			return false;
		}
		if ($follower == $followee){
			return false;
		}
		if ( $this->checkUserUserFollow( $follower, $followee ) !== false ){
			return 0;
		}
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			'user_user_follow',
			array(
				'f_user_id' => $follower->getId(),
				'f_user_name' => $follower->getName(),
				'f_target_user_id' => $followee->getId(),
				'f_target_user_name' => $followee->getName(),
				'f_date' => date( 'Y-m-d H:i:s' )
			), __METHOD__
		);
		$followId = $dbw->insertId();
		$this->incFollowCount( $follower, $followee );
		$stats = new UserStatsTrack( $follower->getId(), $follower->getName() );
		$stats->incStatField( 'friend' ); //use friend record to count the number of people followed.
		$stats = new UserStatsTrack( $followee->getId(), $followee->getName() );
		$stats->incStatField( 'foe' ); // use foe record to count the number of people following.
		// TODO: Notify the followee?
		return $followId;

	}

	/**
	 * Remove a follower from followee
	 *
	 * @param $user1 User object: user to be removed
	 * @param $user2 string: site prefix
	 * @return bool: true if successfully deleted
	 */
	public function deleteUserUserFollow($follower, $followee){
		if ($follower == null || $followee == null ){
			return false;
		}
		// if ( $this->checkUserUserFollow( $follower, $followee ) == false ){
		// 	return true;
		// }

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'user_user_follow',
			array( 'f_user_id' => $follower->getId(), 'f_target_user_id' => $followee->getId() ),
			__METHOD__
		);
		$this->decFollowCount( $follower, $followee );
		$stats = new UserStatsTrack( $follower->getId(), $follower->getName() );
		$stats->decStatField( 'friend' ); //use friend record to count the number of people followed.
		$stats = new UserStatsTrack( $followee->getId(), $followee->getName() );
		$stats->decStatField( 'foe' ); // use foe record to count the number of people following.
		return true;

	}

	/**
	 * Get the amount of followers of a certain user; first tries cache,
	 * and if that fails, fetches the count from the database.
	 *
	 * @param $user User object: Whose follower count do you what
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
	 * Get the amount of users following current user from the
	 * database and cache it.
	 *
	 * @param $user User object: Whose follower count do you what
	 * @return Integer
	 */
	static function getFollowingCountDB( $user ) {
		global $wgMemc;

		wfDebug( "Got user followers count (user={$user}) from DB\n" );

		$key = wfMemcKey( 'user_user_follow', 'user_following_count', $user->getName() );
		$dbr = wfGetDB( DB_SLAVE );
		$followingCount = 0;

		$s = $dbr->selectRow(
			'user_user_follow',
			array( 'COUNT(*) AS count' ),
			array(
				'f_user_target_id' => $user->getId()
			),
			__METHOD__
		);

		if ( $s !== false ) {
			$followingCount = $s->count;
		}

		$wgMemc->set( $key, $followingCount );
		return $followingCount;
	}

	/**
	 * Get the amount of user following the current user from cache.
	 *
	 * @param $user User object: Whose follower count do you what
	 * @return Integer
	 */
	static function getfollowingCountCache( $user ) {
		global $wgMemc;
		$key = wfMemcKey( 'user_user_follow', 'user_following_count', $user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got site count of $data ( user = {$user->getName()} ) from cache\n" );
			return $data;
		}
	}
	/**
	* @param $user User Object
	* @param $huijiPrefix string: same as wgHuijiPrefix
	* @return Mixed: integer or boolean false
	*/
	public function checkUserUserFollow($follower, $followee){
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow(			
			'user_user_follow',
			array( 'f_id' ),
			array( 'f_user_id' => $follower->getId(), 'f_target_user_id' => $followee->getId() ),
			__METHOD__
		);
		if ($s !== false){
			return $s->f_id;
		}else {
			return false;
		}
	}
	/**
	 * Increase the amount of following and followed count.
	 *
	 *  @param $follower User object: the user who initiates the follow
	 *  @param $followee User object: the user to be followed
	 */
	private function incFollowCount($follower, $followee){
		global $wgMemc;
		$key = wfMemcKey( 'user_user_follow', 'user_following_count', $followee->getName() );
		$wgMemc->incr( $key );
		$key = wfMemcKey( 'user_user_follow', 'user_followed_count', $follower->getName() );
		$wgMemc->incr( $key );
	}
	/**
	 * Decrease the amount of follewers for the site.
	 *
	 *  @param $follower User object: the user who initiates the follow
	 *  @param $followee User object: the user to be followed
	 */
	private function decFollowCount($follower, $followee){
		global $wgMemc;
		$key = wfMemcKey( 'user_user_follow', 'user_following_count', $followee->getName() );
		$wgMemc->decr( $key );
		$key = wfMemcKey( 'user_user_follow', 'user_followed_count', $follower->getName() );
		$wgMemc->decr( $key );
	}
	

}
