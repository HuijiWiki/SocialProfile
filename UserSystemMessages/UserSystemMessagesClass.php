<?php
/**
 * UserSystemMessage class
 * Used to send "You have advanced to level [fill in this]" messages
 * to users when User Levels is activated ($wgUserLevels is defined)
 *
 * @file
 * @ingroup Extensions
 */
class UserSystemMessage {

	/**
	 * Adds the message into the database
	 *
	 * @param $userName Mixed: the name of the user who's receiving the message
	 * @param $type Integer: 0 by default
	 * @param $message Mixed: message to be sent out
	 */
	public function addMessage( $userName, $type = 0, $message ) {
		$userId = User::idFromName( $userName );
		$dbw = wfGetDB( DB_MASTER );

		$dbw->insert(
			'user_system_messages',
			array(
				'um_user_id' => $userId,
				'um_user_name' => $userName,
				'um_type' => $type,
				'um_message' => $message,
				'um_date' => date( 'Y-m-d H:i:s' ),
			), __METHOD__
		);
	}

	/**
	 * Deletes a message from the user_system_messages table in the database
	 * @param $um_id Integer: internal ID number of the message to delete
	 */
	static function deleteMessage( $um_id ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'user_system_messages',
			array( 'um_id' => $um_id ),
			__METHOD__
		);
	}

	/**
	 * Gets a list of system messages for the current user from the database
	 *
	 * @param $type Integer: 0 by default
	 * @param $limit Integer: LIMIT for database queries, 0 by default
	 * @param $page Integer: 0 by default
	 * @return $requests
	 */
	public function getMessageList( $type, $limit = 0, $page = 0 ) {
		$dbr = wfGetDB( DB_SLAVE );

		if ( $limit > 0 ) {
			$limitvalue = 0;
			if ( $page ) {
				$limitvalue = $page * $limit - ( $limit );
			}
			$params['LIMIT'] = $limit;
			$params['OFFSET'] = $limitvalue;
		}

		$params['ORDER BY'] = 'ug_id DESC';
		$res = $dbr->select(
			array( 'user_gift', 'gift' ),
			array(
				'ug_id', 'ug_user_id_from', 'ug_user_name_from', 'ug_gift_id',
				'ug_date', 'ug_status', 'gift_name', 'gift_description',
				'gift_given_count'
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
				'gift_given_count' => $row->gift_given_count
			);
		}

		return $requests;
	}

	/**
	 * Sends out the "you have advanced to level [fill in this]" messages to the users
	 *
	 * @param $userIdTo Integer: user ID of the receiver
	 * @param $level Mixed: name of the level that the user advanced to
	 */
	public function sendAdvancementNotificationEmail( $userIdTo, $level ) {
		$user = User::newFromId( $userIdTo );
		$user->loadFromDatabase();

		// send echo notification
		$userpage = $user->getUserPage();
		EchoEvent::create( array(
		     'type' => 'advancement',
		     'extra' => array(
		         'advancement-user-id' => $userIdTo,  
		         'new-level' => $level,
		     ),
		     'title' => $userpage,
		) );
		// if ( $user->isEmailConfirmed() && $user->getIntOption( 'notifyhonorifics', 1 ) ) {
		// 	$updateProfileLink = SpecialPage::getTitleFor( 'UpdateProfile' );
		// 	$subject = wfMessage( 'level-advance-subject', $level )->text();
		// 	if ( trim( $user->getRealName() ) ) {
		// 		$name = $user->getRealName();
		// 	} else {
		// 		$name = $user->getName();
		// 	}
		// 	$body = wfMessage( 'level-advance-body',
		// 		$name,
		// 		$level,
		// 		$updateProfileLink->getFullURL()
		// 	)->text();

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

	/**
	* Used to pass Echo your definition for the notification category and the 
	* notification itself (as well as any custom icons).
	* 
    *
	*@see https://www.mediawiki.org/wiki/Echo_%28Notifications%29/Developer_guide
	*/
	public static function onBeforeCreateEchoEvent( &$notifications, &$notificationCategories, &$icons ) {
        $notificationCategories['advancement'] = array(
            'priority' => 3,
            'tooltip' => 'echo-pref-tooltip-advancement',
        );
        $notifications['advancement'] = array(
        	'category' => 'advancement',
        	'group' => 'positive',
        	'section' => 'alert',
        	'presentation-model' => 'EchoUserAdvancementPresentationModel',
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
	 		case 'advancement':
	 			$extra = $event->getExtra();
	 			if ( !$extra || !isset( $extra['advancement-user-id'] ) ) {
	 				break;
	 			}
	 			$recipientId = $extra['advancement-user-id'];
	 			$recipient = User::newFromId( $recipientId );
	 			$users[$recipientId] = $recipient;
	 			break;
	 	}
	 	return true;
	}

}

class EchoUserAdvancementPresentationModel extends EchoEventPresentationModel {
	public function canRender() {
		return (bool)$this->event->getTitle();
	}
	public function getIconType() {
		return 'thanks';
	}
	public function getHeaderMessage() {
		if ( $this->isBundled() ) {
			$msg = $this->msg( 'notification-bundle-header-advancement' );
			$msg->params( $this->getBundleCount() );
			return $msg;
		}
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getExtraParam('new-level'));
		return $msg;
	}
	public function getBodyMessage() {
		return false;
	}
	public function getPrimaryLink() {
		return [
			'url' => $this->event->getTitle()->getFullURL(),
			'label' => $this->msg( 'notification-view-user-page' )->text(),
		];
	}
	public function getSecondaryLinks() {
		return [];
	}
}
