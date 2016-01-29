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
		$ug_gift_id = $dbw->insertId();
		$this->incGiftGivenCount( $gift_id );
		$this->sendGiftNotificationEmail( $user_id_to, $this->user_name, $ug_gift_id, $type );

		// Add to new gift count cache for receiving user
		$this->incNewGiftCount( $user_id_to );

		$stats = new UserStatsTrack( $user_id_to, $user_to );
		$stats->incStatField( 'gift_rec' );

		$stats = new UserStatsTrack( $this->user_id, $this->user_name );
		$stats->incStatField( 'gift_sent' );
		return $ug_gift_id;
	}

	/**
	 * Sends the (echo) notification about a new gift to the user who received the
	 * gift, if the user wants notifications about new gifts and their e-mail
	 * is confirmed.
	 *
	 * @param $user_id_to Integer: user ID of the receiver of the gift
	 * @param $user_from Mixed: name of the user who sent the gift
	 * @param $gift_id Integer: ID number of the given gift
	 * @param $type Integer: gift type; unused
	 */
	public function sendGiftNotificationEmail( $user_id_to, $user_from, $gift_id, $type ) {
		$gift = Gifts::getGift( $gift_id );
		$user = User::newFromId( $user_id_to );
		$user->loadFromDatabase();

		//send an echo notification
		$agent = User::newFromName($user_from);
		$giftsLink = SpecialPage::getTitleFor( 'ViewGift' );
		EchoEvent::create( array(
		     'type' => 'gift-receive',
		     'extra' => array(
		         'gift-user-id' => $user_id_to,  
		         'gift-id' => $gift_id,
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
				'ug_gift_id' => $ug_id,
				'ug_user_id_to' => $user_id
			 ),
			__METHOD__
		);
		if ( $s !== false ) {
			return true;
		}
		return false;
	}

	/**
	 * Deletes a gift from the user_gift table.
	 *
	 * @param $ug_id Integer: ID number of the gift to delete
	 */
	static function deleteGift( $ug_id ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( 'user_gift', array( 'ug_id' => $ug_id ), __METHOD__ );
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
				'gift_given_count'
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
			wfDebug( "Got new gift count of $data for id $user_id from cache\n" );
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
		wfDebug( "Got new gift count for id $user_id from DB\n" );

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
        	'primary-link' => array('message' => 'notification-link-text-respond-to-user', 'destination' => 'gift-page'),
            'category' => 'gift-receive',
            'group' => 'positive',
            'formatter-class' => 'EchoGiftFormatter',
            'title-message' => 'notification-gift',
            'title-params' => array( 'agent', 'giftview', 'main-title-text' ),
            'flyout-message' => 'notification-gift-flyout',
            'flyout-params' => array( 'agent', 'giftview', 'main-title-text' ),
            'payload' => array( 'summary' ),
            'email-subject-message' => 'notification-gift-email-subject',
            'email-subject-params' => array( 'agent' ),
            'email-body-message' => 'notification-gift-email-body',
            'email-body-params' => array( 'agent', 'giftview', 'main-title-text', 'email-footer' ),
            'email-body-batch-message' => 'notification-system-gift-email-batch-body',
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

}
class EchoGiftFormatter extends EchoCommentFormatter {
	/**
	 * Helper function for getLink()
	 *
	 * @param \EchoEvent $event
	 * @param \User $user The user receiving the notification
	 * @param string $destination The destination type for the link
	 * @return array including target and query parameters
	 * @throws FlowException
	 */
	protected function getLinkParams( $event, $user, $destination ) {
		// Set up link parameters based on the destination (or pass to parent)
		switch ( $destination ) {
			case 'gift-page':
				$titleData = $event->getTitle();
				$eventData = $event->getExtra();
	            if ( !isset( $eventData['gift-id'])  ) {
	                return array($titleData, array());
	            } else {
        			return array($titleData, array('fromnotif' => 1, 'gift_id' => $eventData['gift-id']));
        		}
			default:
				return parent::getLinkParams( $event, $user, $destination );
		}
	}
   /**
     * @param $event EchoEvent
     * @param $param
     * @param $message Message
     * @param $user User
     */
    protected function processParam( $event, $param, $message, $user ) {
        if ( $param === 'giftview' ) {
            $eventData = $event->getExtra();
            if ( !isset( $eventData['gift-id']) ) {
                $message->params( '' );
                return;
            }
            $this->setTitleLink(
                $event,
                $message,
                array(
                    'class' => 'mw-echo-gift-view',
                    'linkText' => wfMessage( 'notification-gift-view-link' )->text(),
                    'param' => array(
                        'gift_id' => $eventData['gift-id'],
                    )
                )
            );
        } else {
            parent::processParam( $event, $param, $message, $user );
        }
    }
}

