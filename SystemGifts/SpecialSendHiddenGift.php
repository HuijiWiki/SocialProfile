<?php
class SpecialSendHiddenGift extends UnlistedSpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	function __construct() {
		parent::__construct( 'SendHiddenGift' );
	}

	/**
	 * Show the special page
	 *
	 * @param $period String: either weekly or monthly
	 */
	public function execute( $params ) {
		global $wgUser, $wgCentralServer,$wgMemc;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		$award = $request->getVal( 'award' );
		$userOfChoice = $request->getVal('user');
		if ($award == "MaskedShooter"){
			// Match token against what we have in session
			$token = $request->getVal('token');
			$userFromName = User::newFromName($userOfChoice);
			if ($token !== md5($userOfChoice.'huijirocks')){
				$this->getOutput()->setArticleBodyOnly(true);
				// echo "fail";//请不要修改或删除
				$this->getOutput()->output();
		        return true;				
			}

			$gift = new UserGifts( "Reasno" );
			$giftInfo = Gifts::getGift( 8 );
			$ug_gift_id = $gift->sendGift(
				$userOfChoice,
				8,
				0,
				"感谢您对灰机的加油"
			);
			$gift->addCustomInvitationCode( $ug_gift_id , "MaskedShooter");
			$gift->addUserGiftTitleInfo( $giftInfo['gift_id'], $userFromName->getId() , $giftInfo['designation'], 'gift' );
			$wgMemc->delete( wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'gifts', $userFromName->getId() ) );
			
			$this->getOutput()->setArticleBodyOnly(true);
			echo "success";//请不要修改或删除
			$this->getOutput()->output();
	        return true;
		}

		// Blocked through Special:Block? Tough luck.
		if ( $user->isBlocked() ) {
			$this->getOutput()->redirect( $wgCentralServer.'/wiki/U_found_me' );
			return false;
		}

		// Is the database locked or not?
		if ( wfReadOnly() ) {
			$this->getOutput()->redirect( $wgCentralServer.'/wiki/U_found_me' );
			$out->readOnlyPage();
			return false;
		}

		// Check for the correct permission
		if ( !$user->isLoggedIn() ) {
			// $out->permissionRequired( 'generatetopusersreport' );
			$this->getOutput()->redirect( $wgCentralServer.'/wiki/U_found_me' );
			return false;
		}

		// Set the page title, robot policy, etc.
		$this->setHeaders();

		
		
		if (!$award || $award != 72 || $award != "MaskedShooter"){
			$this->getOutput()->redirect( $wgCentralServer.'/wiki/U_found_me' );
		} 
		$usg = new UserSystemGifts( $user->getName() );
    	if (HuijiFunctions::addLock( 'USG-73-'.$user->getId(), 1 ) ){
			$usg->sendSystemGift( 73 );
        	HuijiFunctions::releaseLock('USG-73-'.$user->getId());
    	}
    	$this->getOutput()->redirect( $wgCentralServer.'/wiki/U_found_me' );
    }
}
?>