<?php
/**
 * A special page to generate the report of the users who earned the most
 * points during the past week or month. This is the only way to update the
 * points_winner_weekly and points_winner_monthly columns in the user_stats
 * table.
 *
 * This special page also creates a weekly report in the project namespace.
 * The name of that page is controlled by two system messages,
 * MediaWiki:User-stats-report-weekly-page-title and
 * MediaWiki:User-stats-report-monthly-page-title (depending on the type of the
 * report).
 *
 * @file
 * @ingroup Extensions
 */
class SpecialSendHiddenGift extends UnlistedSpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'SendHiddenGift', 'sendhiddengift' );
	}

	/**
	 * Show the special page
	 *
	 * @param $period String: either weekly or monthly
	 */
	public function execute( $award ) {
		global $wgContLang, $wgUser, $wgCentralServer;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

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

		$award = $request->getVal( 'award', $award );
		if (!$award || $award != 72){
			$this->getOutput()->redirect( $wgCentralServer.'/wiki/U_found_me' );
		} 
		$usg = new UserSystemGifts( $user->getName() );
    	if (HuijiFunctions::addLock( 'USG-72-'.$user->getId(), 1 ) ){
			$usg->sendSystemGift( 72 );
        	HuijiFunctions::releaseLock('USG-72-'.$user->getId());
    	}
    	$this->getOutput()->redirect( $wgCentralServer.'/wiki/U_found_me' );
    }
}
