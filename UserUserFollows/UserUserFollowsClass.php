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
		EchoEvent::create( array(
			'type' => 'follow-msg',
			'extra' => array(
					'followee-user-id' => $followee->getId(),
					'agent-page' => $follower->getUserPage(),
				),
			'agent' => $follower,
			'title' => $followee->getUserPage()
		));
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
	 * @param $user User object: Whose follower count
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

		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_following_count', $user->getName() );
		$dbr = wfGetDB( DB_SLAVE );
		$followingCount = 0;

		$s = $dbr->selectRow(
			'user_user_follow',
			array( 'COUNT(*) AS count' ),
			array(
				'f_target_user_id' => $user->getId()
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
	static function getFollowingCountCache( $user ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_following_count', $user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user following count of $data ( user = {$user} ) from cache\n" );
			return $data;
		}
	}	/**
	 * Get the amount of followers of a certain user; first tries cache,
	 * and if that fails, fetches the count from the database.
	 *
	 * @param $user User object: Whose follower count
	 * @return Integer
	 */
	static function getFollowedCount ( $user ){
		$data = self::getFollowedCountCache( $user );
		if ( $data != '' ) {
			if ( $data == -1 ) {
				$data = 0;
			}
			$count = $data;
		} else {
			$count = self::getFollowedCountDB( $user );
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
	static function getFollowedCountDB( $user ) {
		global $wgMemc;

		wfDebug( "Got user followed count (user={$user}) from DB\n" );

		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_followed_count', $user->getName() );
		$dbr = wfGetDB( DB_SLAVE );
		$followedCount = 0;

		$s = $dbr->selectRow(
			'user_user_follow',
			array( 'COUNT(*) AS count' ),
			array(
				'f_user_id' => $user->getId()
			),
			__METHOD__
		);

		if ( $s !== false ) {
			$followedCount = $s->count;
		}

		$wgMemc->set( $key, $followedCount );
		return $followedCount;
	}

	/**
	 * Get the amount of user following the current user from cache.
	 *
	 * @param $user User object: Whose follower count do you what
	 * @return Integer
	 */
	static function getFollowedCountCache( $user ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_followed_count', $user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user followed count of $data ( user = {$user} ) from cache\n" );
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
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_following_count', $followee->getName() );
		$wgMemc->incr( $key );
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_followed_count', $follower->getName() );
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
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_following_count', $followee->getName() );
		$wgMemc->decr( $key );
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_followed_count', $follower->getName() );
		$wgMemc->decr( $key );
	}
	
	/**
	* Used to pass Echo your definition for the notification category and the 
	* notification itself (as well as any custom icons).
	* 
    *
	*@see https://www.mediawiki.org/wiki/Echo_%28Notifications%29/Developer_guide
	*/
	public static function onBeforeCreateEchoEvent( &$notifications, &$notificationCategories, &$icons ) {
        $notificationCategories['follow-msg'] = array(
            'priority' => 3,
            'tooltip' => 'echo-pref-tooltip-follow-msg',
        );
        $notifications['follow-msg'] = array(
            'category' => 'follow-msg',
            'group' => 'positive',
            'formatter-class' => 'EchoFollowFormatter',
            'title-message' => 'notification-follow',
            'title-params' => array( 'agent', 'agent-link', 'follow', 'main-title-text' ),
            'flyout-message' => 'notification-follow-flyout',
            'flyout-params' => array( 'agent', 'agent-link', 'follow', 'main-title-text' ),
            'payload' => array( 'summary' ),
            'email-subject-message' => 'notification-follow-email-subject',
            'email-subject-params' => array( 'agent' ),
            'email-body-message' => 'notification-follow-email-body',
            'email-body-params' => array( 'agent', 'follow', 'main-title-text', 'email-footer' ),
            'email-body-batch-message' => 'notification-follow-email-batch-body',
            'email-body-batch-params' => array( 'agent', 'main-title-text' ),
            'icon' => 'gratitude',
        );
        return true;
    }


	/**
	* Used to define who gets the notifications (for example, the user who performed the edit)
	* 
    *
	*@see https://www.mediawiki.org/wiki/Echo_%28Notifications%29/Developer_guide
	*/
	public static function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
	 	switch ( $event->getType() ) {
	 		case 'follow-msg':
	 			$extra = $event->getExtra();
	 			if ( !$extra || !isset( $extra['followee-user-id'] ) ) {
	 				break;
	 			}
	 			$recipientId = $extra['followee-user-id'];
	 			$recipient = User::newFromId( $recipientId );
	 			$users[$recipientId] = $recipient;
	 			break;
	 	}
	 	return true;
	}

}
class EchoFollowFormatter extends EchoCommentFormatter {
   /**
     * @param $event EchoEvent
     * @param $param
     * @param $message Message
     * @param $user User
     */
    protected function processParam( $event, $param, $message, $user ) {
        if ( $param === 'follow' ) {
            $this->setTitleLink(
                $event,
                $message,
                array(
                    'class' => 'mw-echo-follow-msg',
                    'linkText' => wfMessage('notification-follow-msg-link')->text(),
                )
            );
        } elseif ( $param === 'agent-link') {
        	$eventData = $event->getExtra();
            if ( !isset( $eventData['agent-page']) ) {
                $message->params( '' );
                return;
            }
            $link = $this->buildLinkParam(
                $eventData['agent-page'],
                array(
                    'class' => 'mw-echo-follow-msg',
                    'linkText' => $eventData['agent-page']->getText(),
                )
            );
            $message->params( $link );
        } else {
            parent::processParam( $event, $param, $message, $user );
        }
    }
}

