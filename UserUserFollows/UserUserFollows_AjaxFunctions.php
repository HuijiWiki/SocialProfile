<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */
$wgAjaxExportList[] = 'wfUserUserFollowsResponse';
$wgAjaxExportList[] = 'wfUserUserUnfollowsResponse';
$wgAjaxExportList[] = 'wfUserFollowsInfoResponse';
function wfUserUserFollowsResponse( $follower, $followee ) {
	global $wgUser;
	$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_UNKNOWN);

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NOT_LOGGED_IN);
		return $out;
	}

	// No need to allow blocked users to access this page, they could abuse it, y'know.
	if ( $wgUser->isBlocked() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_BLOCKED);
		return $out;
	}

	// Database operations require write mode
	if ( wfReadOnly() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_READ_ONLY);
		return $out;
	}

	// Are we even allowed to do this?
	if ( !$wgUser->isAllowed( 'edit' ) ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NOT_ALLOWED);
		return $out;
	}

	$uuf = new UserUserFollow();
	if ( $follower === $wgUser->getName() && $followee !== $follower){
		if ($uuf->addUserUserFollow($wgUser, User::newFromName($followee)) !== false){
			$out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
		}
	}
		 //TODO: use wfMessage instead of hard code
	return $out;
}
function wfUserUserUnfollowsResponse( $follower, $followee ) {
	global $wgUser;

	$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_UNKNOWN);

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NOT_LOGGED_IN);
		return $out;
	}

	// No need to allow blocked users to access this page, they could abuse it, y'know.
	if ( $wgUser->isBlocked() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_BLOCKED);
		return $out;
	}
	// Database operations require write mode
	if ( wfReadOnly() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_READ_ONLY);
		return $out;
	}

	// Are we even allowed to do this?
	if ( !$wgUser->isAllowed( 'edit' ) ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NOT_ALLOWED);
		return $out;
	}

	$uuf = new UserUserFollow();
	if ( $follower === $wgUser->getName() && $followee !== $follower){
		if ($uuf->deleteUserUserFollow($wgUser, User::newFromName($followee))){
			$out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
		}
		 //TODO: use wfMessage instead of hard code
	}
	return $out;
}

function wfUserFollowsInfoResponse( $username ) {
	global $wgUser;
	$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_UNKNOWN);

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NOT_LOGGED_IN);
		return $out;
	}

	// No need to allow blocked users to access this page, they could abuse it, y'know.
	if ( $wgUser->isBlocked() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_BLOCKED);
		return $out;
	}

	// Database operations require write mode
	if ( wfReadOnly() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_READ_ONLY);
		return $out;
	}

	// Are we even allowed to do this?
	if ( !$wgUser->isAllowed( 'edit' ) ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NOT_ALLOWED);
		return $out;
	}
	$user = Usre::newFromName( $username );
	$ust = new UserStatus( $user );
		if ($ust->getUserAllInfoDB( ) !== false){
			$out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
		}
		 //TODO: use wfMessage instead of hard code
	return $out;
}