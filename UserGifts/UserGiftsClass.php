<?php
/**
 * UserGifts class
 * @todo document
 */
class UserGifts {

	public $user_id; # Text form (spaces not underscores) of the main part
	public $user_name; # Text form (spaces not underscores) of the main part

	/**
	 * Constructor
	 */
	public function __construct( $username ) {
		$title1 = Title::newFromDBkey( $username );
		$this->user_name = $title1->getText();
		$this->user_id = User::idFromName( $this->user_name );
	}

	/**
	 * Sends a gift to the specified user.
	 *
	 * @param $user_to Integer: user ID of the recipient
	 * @param $gift_id Integer: gift ID number
	 * @param $type Integer: gift type
	 * @param $message Mixed: message as supplied by the sender
	 */
	public function sendGift( $user_to, $gift_id, $type, $message ) {
		$user_id_to = User::idFromName( $user_to );
		$dbw = wfGetDB( DB_MASTER );

		$dbw->insert(
			'user_gift',
			array(
				'ug_gift_id' => $gift_id,
				'ug_user_id_from' => $this->user_id,
				'ug_user_name_from' => $this->user_name,
				'ug_user_id_to' => $user_id_to,
				'ug_user_name_to' => $user_to,
				'ug_type' => $type,
				'ug_status' => 1,
				'ug_message' => $message,
				'ug_date' => date( 'Y-m-d H:i:s' ),
			), __METHOD__
		);

		$ug_id = $dbw->insertId();
		$this->incGiftGivenCount( $gift_id );
		$this->sendGiftNotificationEmail( $user_id_to, $this->user_name, $gift_id, $message );

		// Add to new gift count cache for receiving user
		$this->incNewGiftCount( $user_id_to );

		$stats = new UserStatsTrack( $user_id_to, $user_to );
		$stats->incStatField( 'gift_rec' );

		$stats = new UserStatsTrack( $this->user_id, $this->user_name );
		$stats->incStatField( 'gift_sent' );
		Hooks::run('SocialProfile::giftSend', [$this->user_id_to, $this->user_id, $gift_id, $ug_id, $message]);
		return $ug_id;
	}

	/**
	 * Sends the (echo) notification about a new gift to the user who received the
	 * gift, if the user wants notifications about new gifts and their e-mail
	 * is confirmed.
	 *
	 * @param $user_id_to Integer: user ID of the receiver of the gift
	 * @param $user_from Mixed: name of the user who sent the gift
	 * @param $gift_id Integer: ID number of the given gift in user gift table
	 * @param $message  Mixed: message as supplied by the sender
	 */
	public function sendGiftNotificationEmail( $user_id_to, $user_from, $gift_id, $message ) {

		$user = User::newFromId( $user_id_to );
		$user->loadFromDatabase();

		//send an echo notification
		$agent = User::newFromName($user_from);
		$giftsLink = SpecialPage::getTitleFor( 'ViewGift' );
		EchoEvent::create( array(
		     'type' => 'gift-receive',
		     'extra' => array(
		     	'gift-user-name-from' => $user_from,
		     	'gift_description' => $message,
		        'gift-user-id' => $user_id_to,  
		        'gift-id' => $gift_id,
		        'user' => $user->getName()
		     ),
		     'agent' => $agent,
		     'title' => $giftsLink,
		) );

		// if ( $user->isEmailConfirmed() && $user->getIntOption( 'notifygift', 1 ) ) {
		// 	$giftsLink = SpecialPage::getTitleFor( 'ViewGifts' );
		// 	$updateProfileLink = SpecialPage::getTitleFor( 'UpdateProfile' );

		// 	if ( trim( $user->getRealName() ) ) {
		// 		$name = $user->getRealName();
		// 	} else {
		// 		$name = $user->getName();
		// 	}

		// 	$subject = wfMessage( 'gift_received_subject',
		// 		$user_from,
		// 		$gift['gift_name']
		// 	)->parse();
		// 	$body = wfMessage( 'gift_received_body',
		// 		$name,
		// 		$user_from,
		// 		$gift['gift_name'],
		// 		$giftsLink->getFullURL(),
		// 		$updateProfileLink->getFullURL()
		// 	)->parse();

		// 	// The email contains HTML, so actually send it out as such, too.
		// 	// That's why this no longer uses User::sendMail().
		// 	// @see https://bugzilla.wikimedia.org/show_bug.cgi?id=68045
		// 	global $wgPasswordSender;
		// 	$sender = new MailAddress( $wgPasswordSender,
		// 		wfMessage( 'emailsender' )->inContentLanguage()->text() );
		// 	$to = new MailAddress( $user );
		// 	UserMailer::send( $to, $sender, $subject, $body, null, 'text/html; charset=UTF-8' );
		// }
	}

	public function clearAllUserGiftStatus() {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->update( 'user_gift',
			/* SET */array( 'ug_status' => 0 ),
			/* WHERE */array( 'ug_user_id_to' => $this->user_id ),
			__METHOD__
		);
		$this->clearNewGiftCountCache( $this->user_id );
	}

	static function clearUserGiftStatus( $id ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->update( 'user_gift',
			/* SET */array( 'ug_status' => 0 ),
			/* WHERE */array( 'ug_id' => $id ),
			__METHOD__
		);
	}

	/**
	 * Checks if a given user owns the gift, which is specified by its ID.
	 *
	 * @param $user_id Integer: user ID of the given user
	 * @param $ug_id Integer: ID number of the gift that we're checking
	 * @return Boolean: true if the user owns the gift, otherwise false
	 */
	public function doesUserOwnGift( $user_id, $ug_id ) {
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow(
			'user_gift',
			array( 'ug_user_id_to' ),
			array( 
				'ug_id' => $ug_id,
				'ug_user_id_to' => $user_id
			 ),
			__METHOD__
		);
		if ( $s !== false ) {
			return true;
		}
		return false;		

	}

	public function doesUserHaveGiftOfTheSameGiftType( $user_id, $us_gfit_id ){
		$dbr = wfGetDB( DB_SLAVE );
		$s = $dbr->selectRow(
			'user_gift',
			array( 'ug_user_id_to' ),
			array( 
				'ug_gift_id' => $ug_gift_id,
				'ug_user_id_to' => $user_id
			 ),
			__METHOD__
		);
		if ( $s !== false ) {
			return true;
		}
		return false;		

	}
	public function getMyGift( $ug_id ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'user_gift', 'gift' ),
			array(
				'ug_id', 'ug_user_id_from', 'ug_user_name_from',
				'ug_user_id_to', 'ug_user_name_to', 'ug_message', 'gift_id',
				'ug_date', 'ug_status', 'gift_name', 'gift_description',
				'gift_given_count','designation'
			),
			array( "ug_id = {$ug_id} AND ug_user_name_to='$this->user_name'" ),
			__METHOD__,
			$params,
			array( 'gift' => array( 'INNER JOIN', 'ug_gift_id = gift_id' ) )
		);
		if ( $res ) {
			foreach ($res as $value) {
				$gift['id'] = $value->ug_id;
				$gift['user_id_from'] = $value->ug_user_id_from;
				$gift['user_name_from'] = $value->ug_user_name_from;
				$gift['user_id_to'] = $value->ug_user_id_to;
				$gift['user_name_to'] = $value->ug_user_name_to;
				$gift['message'] = $value->ug_message;
				$gift['gift_count'] = $value->gift_given_count;
				$gift['timestamp'] = $value->ug_date;
				$gift['gift_id'] = $value->gift_id;
				$gift['name'] = $value->gift_name;
				$gift['description'] = $value->gift_description;
				$gift['status'] = $value->ug_status;
				$gift['designation'] = $value->designation;
			}
			return $gift;
		}		
	}

	/**
	 * Deletes a gift from the user_gift table.
	 *
	 * @param $ug_id Integer: ID number of the gift to delete
	 */
	static function deleteGift( $ug_id ) {
		$dbw = wfGetDB( DB_MASTER );
		$id = $dbw->selectField( 'user_gift', 'ug_gift_id', array( 'ug_id' => $ug_id ), __METHOD__ );
		$count = $dbw->selectField( 'user_gift', 'COUNT(*)', array( 'ug_gift_id' => $id ), __METHOD__ );
		$dbw->delete( 'user_gift', array( 'ug_id' => $ug_id ), __METHOD__ );
		if ($count == 1){
			$dbw->delete(
				'user_title',
				array('gift_id' => $id),
				__METHOD__
			);			
		}
	}

	/**
	 * Gets the user gift with the ID = $id.
	 *
	 * @param $id Integer: gift ID number
	 * @return Array: array containing gift info, such as its ID, sender, etc.
	 */
	static function getUserGift( $user_name, $id, $limit ) {
		if ( !is_numeric( $id ) ) {
			return '';
		}
		$params = $gift = $result = array();
		if( $limit != 0  ){
			$params = array('LIMIT' => $limit, 'OFFSET' => 0 );
		}
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'user_gift', 'gift' ),
			array(
				'ug_id', 'ug_user_id_from', 'ug_user_name_from',
				'ug_user_id_to', 'ug_user_name_to', 'ug_message', 'gift_id',
				'ug_date', 'ug_status', 'gift_name', 'gift_description',
				'gift_given_count','designation'
			),
			array( "ug_gift_id = {$id} AND ug_user_name_to='$user_name'" ),
			__METHOD__,
			$params,
			array( 'gift' => array( 'INNER JOIN', 'ug_gift_id = gift_id' ) )
		);
		if ( $res ) {
			foreach ($res as $value) {
				$gift['id'] = $value->ug_id;
				$gift['user_id_from'] = $value->ug_user_id_from;
				$gift['user_name_from'] = $value->ug_user_name_from;
				$gift['user_id_to'] = $value->ug_user_id_to;
				$gift['user_name_to'] = $value->ug_user_name_to;
				$gift['message'] = $value->ug_message;
				$gift['gift_count'] = $value->gift_given_count;
				$gift['timestamp'] = $value->ug_date;
				$gift['gift_id'] = $value->gift_id;
				$gift['name'] = $value->gift_name;
				$gift['description'] = $value->gift_description;
				$gift['status'] = $value->ug_status;
				$gift['designation'] = $value->designation;
				$result[] = $gift;
			}
			if( $result ){
				return $result;
			}
		}
		// $row = $dbr->fetchObject( $res );
		// if ( $row ) {
			// $gift['id'] = $row->ug_id;
			// $gift['user_id_from'] = $row->ug_user_id_from;
			// $gift['user_name_from'] = $row->ug_user_name_from;
			// $gift['user_id_to'] = $row->ug_user_id_to;
			// $gift['user_name_to'] = $row->ug_user_name_to;
			// $gift['message'] = $row->ug_message;
			// $gift['gift_count'] = $row->gift_given_count;
			// $gift['timestamp'] = $row->ug_date;
			// $gift['gift_id'] = $row->gift_id;
			// $gift['name'] = $row->gift_name;
			// $gift['description'] = $row->gift_description;
			// $gift['status'] = $row->ug_status;
		// }

		return $gift;
	}

	/**
	 * Increase the amount of new gifts for the user with ID = $user_id.
	 *
	 * @param $user_id Integer: user ID for the user whose gift count we're
	 *							going to increase.
	 */
	public function incNewGiftCount( $user_id ) {
		global $wgMemc;
		$key = wfForeignMemcKey( 'huiji', '', 'user_gifts', 'new_count', $user_id );
		$wgMemc->incr( $key );
	}

	/**
	 * Decrease the amount of new gifts for the user with ID = $user_id.
	 *
	 * @param $user_id Integer: user ID for the user whose gift count we're
	 *							going to decrease.
	 */
	public function decNewGiftCount( $user_id ) {
		global $wgMemc;
		$key = wfForeignMemcKey( 'huiji', '', 'user_gifts', 'new_count', $user_id );
		$wgMemc->decr( $key );
	}

	/**
	 * Clear the new gift counter for the user with ID = $user_id.
	 * This is done by setting the value of the memcached key to 0.
	 */
	public function clearNewGiftCountCache() {
		global $wgMemc;
		$key = wfForeignMemcKey( 'huiji', '', 'user_gifts', 'new_count', $this->user_id );
		$wgMemc->set( $key, 0 );
	}

	/**
	 * Get the amount of new gifts for the user with ID = $user_id
	 * from memcached. If successful, returns the amount of new gifts.
	 *
	 * @param $user_id Integer: user ID for the user whose gifts we're going to
	 *							fetch.
	 * @return Integer: amount of new gifts
	 */
	static function getNewGiftCountCache( $user_id ) {
		global $wgMemc;
		$key = wfForeignMemcKey( 'huiji', '', 'user_gifts', 'new_count', $user_id );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			// wfDebug( "Got new gift count of $data for id $user_id from cache\n" );
			return $data;
		}
	}

	/**
	 * Get the amount of new gifts for the user with ID = $user_id.
	 * First tries cache (memcached) and if that succeeds, returns the cached
	 * data. If that fails, the count is fetched from the database.
	 * UserWelcome.php calls this function.
	 *
	 * @param $user_id Integer: user ID for the user whose gifts we're going to
	 *							fetch.
	 * @return Integer: amount of new gifts
	 */
	static function getNewGiftCount( $user_id ) {
		$data = self::getNewGiftCountCache( $user_id );

		if ( $data != '' ) {
			$count = $data;
		} else {
			$count = self::getNewGiftCountDB( $user_id );
		}
		return $count;
	}

	/**
	 * Get the amount of new gifts for the user with ID = $user_id from the
	 * database and stores it in memcached.
	 *
	 * @param $user_id Integer: user ID for the user whose gifts we're going to
	 *							fetch.
	 * @return Integer: amount of new gifts
	 */
	static function getNewGiftCountDB( $user_id ) {
		// wfDebug( "Got new gift count for id $user_id from DB\n" );

		global $wgMemc;
		$key = wfForeignMemcKey( 'huiji', '', 'user_gifts', 'new_count', $user_id );
		$dbr = wfGetDB( DB_SLAVE );
		$newGiftCount = 0;
		$s = $dbr->selectRow(
			'user_gift',
			array( 'COUNT(*) AS count' ),
			array( 'ug_user_id_to' => $user_id, 'ug_status' => 1 ),
			__METHOD__
		);
		if ( $s !== false ) {
			$newGiftCount = $s->count;
		}

		$wgMemc->set( $key, $newGiftCount );

		return $newGiftCount;
	}

	public function getUserGiftList( $type ) {
		$dbr = wfGetDB( DB_SLAVE );
		$params = array();

		// if ( $limit > 0 ) {
		// 	$limitvalue = 0;
		// 	if ( $page ) {
		// 		$limitvalue = $page * $limit - ( $limit );
		// 	}
		// 	$params['LIMIT'] = $limit;
		// 	$params['OFFSET'] = $limitvalue;
		// }

		$params['ORDER BY'] = 'ug_date DESC';
		$res = $dbr->select(
			array( 'user_gift', 'gift' ),
			array(
				'ug_id', 'ug_user_id_from', 'ug_user_name_from', 'ug_gift_id',
				'ug_date', 'ug_status', 'gift_name', 'gift_description',
				'gift_given_count', 'UNIX_TIMESTAMP(ug_date) AS unix_time'
			),
			array( "ug_user_id_to = {$this->user_id}" ),
			__METHOD__,
			$params,
			array( 'gift' => array( 'INNER JOIN', 'ug_gift_id = gift_id' ) )
		);

		$requests = array();
		foreach ( $res as $row ) {
			$requests[] = array(
				'id' => $row->ug_id,
				'gift_id' => $row->ug_gift_id,
				'timestamp' => ( $row->ug_date ),
				'status' => $row->ug_status,
				'user_id_from' => $row->ug_user_id_from,
				'user_name_from' => $row->ug_user_name_from,
				'gift_name' => $row->gift_name,
				'gift_description' => $row->gift_description,
				'gift_given_count' => $row->gift_given_count,
				'unix_timestamp' => $row->unix_time
			);
		}

		return $requests;
	}

	public function getAllGiftList( $limit = 10, $page = 0 ) {
		$dbr = wfGetDB( DB_SLAVE );
		$params = array();

		$params['ORDER BY'] = 'ug_id DESC';
		if ( $limit > 0 ) {
			$limitvalue = 0;
			if ( $page ) {
				$limitvalue = $page * $limit - ( $limit );
			}
			$params['LIMIT'] = $limit;
			$params['OFFSET'] = $limitvalue;
		}

		$res = $dbr->select(
			array( 'user_gift', 'gift' ),
			array(
				'ug_id', 'ug_user_id_from', 'ug_user_name_from', 'ug_gift_id',
				'ug_date', 'ug_status', 'gift_name', 'gift_description',
				'gift_given_count', 'UNIX_TIMESTAMP(ug_date) AS unix_time'
			),
			array(),
			__METHOD__,
			$params,
			array( 'gift' => array( 'INNER JOIN', 'ug_gift_id = gift_id' ) )
		);

		$requests = array();
		foreach ( $res as $row ) {
			$requests[] = array(
				'id' => $row->ug_id,
				'gift_id' => $row->ug_gift_id,
				'timestamp' => ( $row->ug_date ),
				'status' => $row->ug_status,
				'user_id_from' => $row->ug_user_id_from,
				'user_name_from' => $row->ug_user_name_from,
				'gift_name' => $row->gift_name,
				'gift_description' => $row->gift_description,
				'gift_given_count' => $row->gift_given_count,
				'unix_timestamp' => $row->unix_time
			);
		}

		return $requests;
	}

	/**
	 * Update the counter that tracks how many times a gift has been given out.
	 *
	 * @param $gift_id Integer: ID number of the gift that we're tracking
	 */
	private function incGiftGivenCount( $gift_id ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->update(
			'gift',
			array( 'gift_given_count=gift_given_count+1' ),
			array( 'gift_id' => $gift_id ),
			__METHOD__
		);
	}

	/**
	 * Gets the amount of gifts a user has.
	 *
	 * @param $userName Mixed: username whose gift count we're looking up
	 * @return Integer: amount of gifts the specified user has
	 */
	static function getGiftCountByUsername( $userName ) {
		$dbr = wfGetDB( DB_SLAVE );
		$userId = User::idFromName( $userName );

		$res = $dbr->select(
			'user_gift',
			'COUNT(*) AS count',
			array( "ug_user_id_to = {$userId}" ),
			__METHOD__,
			array( 'LIMIT' => 1, 'OFFSET' => 0 )
		);

		$row = $dbr->fetchObject( $res );
		$giftCount = 0;

		if ( $row ) {
			$giftCount = $row->count;
		}

		return $giftCount;
	}

	/**
	* Used to pass Echo your definition for the notification category and the 
	* notification itself (as well as any custom icons).
	* 
    *
	*@see https://www.mediawiki.org/wiki/Echo_%28Notifications%29/Developer_guide
	*/
	public static function onBeforeCreateEchoEvent( &$notifications, &$notificationCategories, &$icons ) {
        $notificationCategories['gift-receive'] = array(
            'priority' => 3,
            'tooltip' => 'echo-pref-tooltip-gift-receive',
        );
        $notifications['gift-receive'] = array(
        	'category' => 'gift-receive',
        	'group' => 'positive',
        	'section' => 'alert',
        	'presentation-model' => 'EchoUserGiftPresentationModel',
        	'bundle' => [
        		'web' => true,
        		'expandable' => true,
        	]
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
	 		case 'gift-receive':
	 			$extra = $event->getExtra();
	 			if ( !$extra || !isset( $extra['gift-user-id'] ) ) {
	 				break;
	 			}
	 			$recipientId = $extra['gift-user-id'];
	 			$recipient = User::newFromId( $recipientId );
	 			$users[$recipientId] = $recipient;
	 			break;
	 	}
	 	return true;
	}

	/**
	 * addUserGiftInviteInfo when user send invitation gift to someone ,insert into ug_invite
	 * @param int $user_gift_id    usergift_id
	 */
	public function addUserGiftInviteInfo( $user_gift_id ){
		require_once ('/var/www/html/Invitation.php');
        require_once ('/var/www/html/InvitationDB.php');
        Invitation::generateInvCode(1);
        //code = $invite[0]
        $invite = InvitationDB::getInv(1);
        $invite_code = empty($invite[0]) ? '' : $invite[0];
		$dbw = wfGetDB( DB_MASTER );
        $dbw->insert(
        	'ug_invite',
        	array(
        		'ug_id' => $user_gift_id,
        		'invitation_code' => $invite_code
        	),
        	__METHOD__
        );
        if ( $dbw->insertId() ) {
        	return $dbw->insertId();
        }else{
        	return 0;
        }

	}

	/**
	 * addCustomInvitationCode
	 * @param int $user_gift_id    usergift_id
	 * @param string $source where does this code come from
	 * @return int id
	 */
	public function addCustomInvitationCode( $user_gift_id, $source ){
		if ($source == 'MaskedShooter'){
			$dbw = wfGetDB(DB_MASTER);
			if($dbw->lock('masked_shooter','addCustomInvitationCode')){
				$res = $dbw->selectRow(
					'masked_shooter',
					array('code'),
					array('status' => 1),
					__METHOD__
				);
				if($res == ''){
					return 0;
				}
				$invite_code = $res->code;
				$dbw->update(
					'masked_shooter',
					array('status' => 0),
					array('code' => $invite_code ),
					__METHOD__
				);
				$dbw->unlock('masked_shooter','addCustomInvitationCode');
				$dbw->insert(
	        		'ug_invite',
	        		array(
	        			'ug_id' => $user_gift_id,
	        			'invitation_code' => $invite_code
	        		),
	        		__METHOD__
	        	);

	        	if ( $dbw->insertId() ) {
		        	return $dbw->insertId();
		        }else{
		        	return 0;
		        }		
			}
			//throw an exception
		}
	}

	/**
	 * if this gift comes with an invitation code, return the code
	 * @param $ug_id int user_gift_id
	 * @return string invitationCode or empty string
 	 */
	public static function fetchInvitationCode( $user_gift_id ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
				'ug_invite',
				array(
					'invitation_code'
				),
				array(
					'ug_id' => $user_gift_id
				),
				__METHOD__
			);
		$invite_code = '';
		if ($res) {
			foreach ($res as $key => $value) {
				$invite_code = $value->invitation_code;
			}
		}
		return $invite_code;
	}

	/**
	 * add Gift Title Info
	 * @param int $gift_id    gift_id from tatble gift
	 * @param int $user_to_id user get the gift
	 */
	public function addUserGiftTitleInfo( $gift_id, $user_to_id, $title_content, $title_from ){
		$dbw = wfGetDB( DB_MASTER );
        $dbw->insert(
        	'user_title',
        	array(
        		'gift_id' => $gift_id,
        		'title_content' => $title_content,
        		'user_to_id' => $user_to_id,
        		'is_open' => 1,
        		'title_from' => $title_from,
        	),
        	__METHOD__
        );
        return $dbw->insertId();
	}

	/**
	 * get designation based on gift id
	 * @param  int $gift_id    gift id from gift table
	 * @param  int $user_to_id user who get the gift
	 * @return string designation
	 */
	static function getGiftDesignation( $gift_id){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
				'gift',
				array(
					'designation'
				),
				array(
					'gift_id' => $gift_id
				),
				__METHOD__
			);
		$user_title = '';
		if ($res) {
			foreach ($res as $key => $value) {
				$user_title = $value->designation;
			}
		}
		return $user_title;
	}

	/**
	 * cleraAllGiftTitle
	 * @param  string $title_from  giftTitle or systemGiftTitle
	 * @return boolen 
	 */
	static function clearAllGiftTitle( $title_from, $user_to_id ){
		global $wgMemc;
		$dbw = wfGetDB( DB_MASTER );
		$dbw->update(
			'user_title',
			array(
				'is_open' => '1'
			),
			array( 
				'title_from' => $title_from,
				'user_to_id' => $user_to_id
			),
			__METHOD__
		);

		$key = wfForeignMemcKey('huiji', '', 'user_title', $title_from, $user_to_id);
		$wgMemc->delete($key);
	}

}
class EchoUserGiftPresentationModel extends EchoEventPresentationModel {
	public function canRender() {
		return (bool)$this->event->getTitle();
	}
	public function getIconType() {
		return 'thanks';
	}
	public function getHeaderMessage() {
		if ( $this->isBundled() ) {
			$msg = $this->msg( 'notification-bundle-header-gift-receive' );
			$msg->params( $this->getBundleCount() );
			return $msg;
		} else {
			$msg = parent::getHeaderMessage();
			return $msg;
		}
	}
	public function getCompactHeaderMessage() {
		$msg = parent::getCompactHeaderMessage();
		$msg->params( $this->getViewingUserForGender() );
		return $msg;
	}
	public function getBodyMessage() {
		$excerpt = $this->event->getExtraParam( 'gift-description' );
		if ( $excerpt ) {
			$msg = new RawMessage( '$1' );
			$msg->plaintextParams( $excerpt );
			return $msg;
		}
	}
	public function getPrimaryLink() {
		$title = $this->event->getTitle();
		// Make a link to #flow-post-{postid}
		$title = Title::makeTitle(
			$title->getNamespace(),
			$title->getDBKey()
		);
		$p1 = $this->event->getExtraParam('gift-user-name-from');
		return [
			'url' => $title->getFullURL( [
                        'gift_id' => $this->event->getExtraParam('gift-id'),
                        'user' => $this->event->getExtraParam('user'),
			] ),
			'label' => $this->msg( 'notification-view-gift' )->text(),
		];
	}
	public function getSecondaryLinks() {
		return [ $this->getAgentLink(), array(
		    'url' => SpecialPage::getTitleFor('ViewGifts')->getFullURL(),
		    'label' => $this->msg('notification-view-all-gifts')->text(),
		    // 'description' => $this->msg('notification-view-all-gifts')->text(),
		    'icon' => false,
		    'prioritized' => false
		) ];
	}
}