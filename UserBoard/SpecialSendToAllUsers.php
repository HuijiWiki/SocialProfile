<?php
/**
 * A special page to allow users to send a mass board message to all users
 *
 * @file
 * @ingroup Extensions
 * @author slx
 */

class SpecialsendToAllUsers extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		set_time_limit(0);
		parent::__construct( 'sendToAllUsers' );
	}

	/**
	 * Show the special page
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		// If the user doesn't have the required 'sendToAllUsers' permission, display an error
		if ( !$user->isAllowed( 'SendToAllUsers' ) ) {
			$out->permissionRequired( 'SendToAllUsers' );
			return;
		}

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// This feature is available only to logged-in users.
		if ( !$user->isLoggedIn() ) {
			$out->setPageTitle( $this->msg( 'boardblastlogintitle' )->plain() );
			$out->addWikiMsg( 'boardblastlogintext' );
			return '';
		}

		// Is the database locked?
		if ( wfReadOnly() ) {
			$out->readOnlyPage();
			return false;
		}

		// Blocked through Special:Block? No access for you!
		if ( $user->isBlocked() ) {
			$out->blockedPage( false );
			return false;
		}

		// Add CSS & JS
		$out->addModuleStyles( 'ext.socialprofile.userboard.boardblast.css' );
		$out->addModules( 'ext.socialprofile.userboard.boardblast.js' );

		$output = '';

		if ( $request->wasPosted() ) {
			$out->setPageTitle( $this->msg( 'messagesenttitle' )->plain() );
			$message = $request->getVal( 'message' );
			$user_ids_to = explode( ',', $request->getVal( 'ids' ) );
			$jobParams = array( 'user_ids_to' => $user_ids_to, 'message' => $message, 'sender' => $user->getId() );
			$job = new BoardBlastJobs($this->getTitle(), $jobParams);
			JobQueueGroup::singleton()->push( $job );
			$output .= $this->msg( 'messagesentsuccess' )->plain();
		} else {
			$out->setPageTitle( $this->msg( 'boardblasttitle' )->plain() );
			$output .= $this->displayForm();
		}

		$out->addHTML( $output );
	}

	/**
	 * Displays the form for sending board blasts
	 */
	function displayForm() {
		global $wgHuijiPrefix;
		$user = $this->getUser();
		$output = '<div class="board-blast-message-form">
				<h2>' . $this->msg( 'boardblaststep1' )->escaped() . '</h2>
				<form method="post" name="blast" action="">
					<input type="hidden" name="ids" id="ids" />
					<div class="blast-message-text">'
						. $this->msg( 'boardblastprivatenote' )->escaped() .
					'</div>
					<textarea name="message" id="message" cols="63" rows="4"></textarea>
				</form>
		</div>
		<div class="blast-nav">
				<h2>' . $this->msg( 'boardblaststep2' )->escaped() . '</h2>
				<div class="blast-nav-links">
					<a href="javascript:void(0);" class="blast-select-all-link">' .
						$this->msg( 'boardlinkselectall' )->escaped() . '</a> -
					<a href="javascript:void(0);" class="blast-unselect-all-link">' .
						$this->msg( 'boardlinkunselectall' )->escaped() . '</a> ';

		$output .= '</div>
		</div>';

		$us = new UserStats();
		$follows = $us->getAllUser( );

		$output .= '<div id="blast-friends-list" class="blast-friends-list">';

		$x = 1;
		$per_row = 3;
		if ( count( $follows ) > 0 ) {
			foreach ( $follows as $follow ) {
				if ( $follow['type'] == 1 ) {
					$class = 'friend';
				} else {
					$class = 'foe';
				}
				if ( $follow['user_name'] !== $user->getName() ) {
					$id = $follow['user_id'];
					$output .= '<div class="blast-' . $class . "-unselected\" id=\"user-{$id}\">
							".$follow['user_name']."
						</div>";
					if ( $x == count( $follows ) || $x != 1 && $x % $per_row == 0 ) {
						$output .= '<div class="cleared"></div>';
					}
				}
				$x++;
			}
		} else {
			$output .= '<div>' . $this->msg( 'boardnofriends' )->escaped() . '</div>';
		}

		$output .= '</div>

			<div class="cleared"></div>';

		$output .= '<div class="blast-message-box-button">
			<input type="button" value="' . $this->msg( 'boardsendbutton' )->escaped() . '" class="site-button" />
		</div>';

		return $output;
	}

}
