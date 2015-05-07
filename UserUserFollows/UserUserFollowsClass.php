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
		if ( $this->checkUserUserFollow( $follower, $followee ) != false ){
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
	static function getFollowerCount ( $user ){
		$data = self::getFollowerCountCache( $user );
		if ( $data != '' ) {
			if ( $data == -1 ) {
				$data = 0;
			}
			$count = $data;
		} else {
			$count = self::getFollowerCountDB( $user );
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
	static function getFollowerCountDB( $user ) {
		global $wgMemc;

		wfDebug( "Got user followers count (user={$user}) from DB\n" );

		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_follower_count', $user->getName() );
		$dbr = wfGetDB( DB_SLAVE );
		$followerCount = 0;

		$s = $dbr->selectRow(
			'user_user_follow',
			array( 'COUNT(*) AS count' ),
			array(
				'f_target_user_id' => $user->getId()
			),
			__METHOD__
		);

		if ( $s != false ) {
			$followerCount = $s->count;
		}

		$wgMemc->set( $key, $followerCount );
		return $followerCount;
	}

	/**
	 * Get the amount of user following the current user from cache.
	 *
	 * @param $user User object: Whose follower count do you what
	 * @return Integer
	 */
	static function getFollowerCountCache( $user ) {
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_follower_count', $user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user follower count of $data ( user = {$user} ) from cache\n" );
			return $data;
		}
	}	/**
	 * Get the amount of users current user is following; first tries cache,
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

		wfDebug( "Got user following count (user={$user}) from DB\n" );

		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_following_count', $user->getName() );
		$dbr = wfGetDB( DB_SLAVE );
		$followingCount = 0;

		$s = $dbr->selectRow(
			'user_user_follow',
			array( 'COUNT(*) AS count' ),
			array(
				'f_user_id' => $user->getId()
			),
			__METHOD__
		);

		if ( $s != false ) {
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
	}
	/**
	* @param $user User Object
	* @param $huijiPrefix string: same as wgHuijiPrefix
	* @return Mixed: integer or boolean false
	*/
	public function checkUserUserFollow($follower, $followee){
		//TODO: We are not caching the result for now. 
		//But if we have a performance hit, this is where to go.
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow(			
			'user_user_follow',
			array( 'f_id' ),
			array( 'f_user_id' => $follower->getId(), 'f_target_user_id' => $followee->getId() ),
			__METHOD__
		);
		if ($s != false){
			return $s->f_id;
		}else {
			return false;
		}
	}

	/**
	 * Get the Follower or Following list for the current user.
	 *
	 * @param $type Integer: 1 for following, 2 (or anything else but 1) for followers
	 * @param $limit Integer: used as the LIMIT in the SQL query
	 * @param $page Integer: if greater than 0, will be used to calculate the
	 *                       OFFSET for the SQL query
	 * @return Array: array of follower/following information
	 */
	public function getFollowList( $user, $type = 0, $limit = 0, $page = 0 ) {
		$dbr = wfGetDB( DB_SLAVE );

		$where = array();
		$options = array();
		if ($type != 1) {
			$where['f_target_user_id'] = $user->getId();
		} else {
			$where['f_user_id'] = $user->getId();
		}
		
		if ( $limit > 0 ) {
			$limitvalue = 0;
			if ( $page ) {
				$limitvalue = $page * $limit - ( $limit );
			}
			$options['LIMIT'] = $limit;
			$options['OFFSET'] = $limitvalue;
		}
		$res = $dbr->select(
			'user_user_follow',
			array(
				'f_id', 'f_user_id', 'f_user_name', 'f_target_user_id',
				'f_target_user_name', 'f_date'
			),
			$where,
			__METHOD__,
			$options
		);

		$requests = array();
		foreach ( $res as $row ) {
			$requests[] = array(
				'id' => $row->f_id,
				'timestamp' => ( $row->f_date ),
				'user_id' => ( $type != 1? $row->f_user_id : $row->f_target_user_id),
				'user_name' => ( $type != 1? $row->f_user_name : $row->f_target_user_name),
				'type' => $type
			);
		}

		return $requests;
	}
	/**
	 * Increase the amount of following and followed count.
	 *
	 *  @param $follower User object: the user who initiates the follow
	 *  @param $followee User object: the user to be followed
	 */
	private function incFollowCount($follower, $followee){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_following_count', $follower->getName() );
		$wgMemc->incr( $key );
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_follower_count', $followee->getName() );
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
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_following_count', $follower->getName() );
		$wgMemc->decr( $key );
		$key = wfForeignMemcKey('huiji','', 'user_user_follow', 'user_follower_count', $followee->getName() );
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

