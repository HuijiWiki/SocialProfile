<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */
$wgAjaxExportList[] = 'wfUserUserFollowsResponse';
$wgAjaxExportList[] = 'wfUserUserUnfollowsResponse';
function wfUserUserFollowsResponse( $follower, $followee ) {
	global $wgUser;

	$out = UserError::ERROR_UNKNOWN;

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = UserError::ERROR_NOT_LOGGED_IN;
		return $out;
	}

	// No need to allow blocked users to access this page, they could abuse it, y'know.
	if ( $wgUser->isBlocked() ) {
		$out = UserError::ERROR_BLOCKED;
		return $out;
	}

	// Database operations require write mode
	if ( wfReadOnly() ) {
		$out = UserError::ERROR_READ_ONLY;
		return $out;
	}

	// Are we even allowed to do this?
	if ( !$wgUser->isAllowed( 'edit' ) ) {
		$out = UserError::ERROR_NOT_ALLOWED;
		return $out;
	}

	$uuf = new UserUserFollow();
	if ( $follower === $wgUser->getName() && $followee !== $follower){
		if ($uuf->addUserUserFollow($wgUser, User::newFromName($followee)) !== false){
			$out = UserError::SUCCESS;
		}
	}
		 //TODO: use wfMessage instead of hard code
	return $out;
}
function wfUserUserUnfollowsResponse( $follower, $followee ) {
	global $wgUser;
	$out = UserError::ERROR_UNKNOWN;

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = UserError::ERROR_NOT_LOGGED_IN;
		return $out;
	}

	// No need to allow blocked users to access this page, they could abuse it, y'know.
	if ( $wgUser->isBlocked() ) {
		$out = UserError::ERROR_BLOCKED;
		return $out;
	}

	// Database operations require write mode
	if ( wfReadOnly() ) {
		$out = UserError::ERROR_READ_ONLY;
		return $out;
	}

	// Are we even allowed to do this?
	if ( !$wgUser->isAllowed( 'edit' ) ) {
		$out = UserError::ERROR_NOT_ALLOWED;
		return $out;
	}

	$uuf = new UserUserFollow();
	if ( $follower === $wgUser->getName() && $followee !== $follower){
		if ($uuf->deleteUserUserFollow($wgUser, User::newFromName($followee))){
			$out = UserError::SUCCESS;
		}
		 //TODO: use wfMessage instead of hard code
	}
	return $out;
}