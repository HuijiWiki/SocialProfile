<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */
$wgAjaxExportList[] = 'wfUserSiteFollowsResponse';
$wgAjaxExportList[] = 'wfUserSiteUnfollowsResponse';
function wfUserSiteFollowsResponse( $username, $servername ) {
	global $wgUser, $wgServer, $wgHuijiPrefix;
	return 1;

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

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName() && $servername === $wgServer){
		if ($usf->addUserSiteFollow($wgUser, $wgHuijiPrefix) >= 0){
			$out = UserError::SUCCESS;
		}
	}
	return $out;
}
function wfUserSiteUnfollowsResponse( $username, $servername ) {
	global $wgUser, $wgSitename, $wgServer, $wgHuijiPrefix;
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

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName() && $servername === $wgServer){
		if ($usf->deleteUserSiteFollow($wgUser, $wgHuijiPrefix)){
			return UserError::SUCCESS;
		}
		 //TODO: use wfMessage instead of hard code
	}
	return $out;
}