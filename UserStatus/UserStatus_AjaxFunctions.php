<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */
$wgAjaxExportList[] = 'wfUpdateUserStatus';
$wgAjaxExportList[] = 'wfGetUserAvatar';
function wfUpdateUserStatus( $username, $field, $value ) {
	global $wgUser;
	// Sanitizer::escapeHtmlAllowEntities($html);
	$value = trim($value);
	$field = trim($field);
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

	if ( $username === $wgUser->getName() ){
		$us = new UserStatus($wgUser);

		// if ($us->setAll($gender, $province, $city, $birthday, $status)){
		// 	$out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
		// }
		if ($us->setInfo($field, $value)){
			// $out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
			$ret = array('success'=> true, 'result'=>array($field => $value ));
			$out = json_encode($ret);
			return $out;
		}
	}
	return $out;
}
function wfGetUserAvatar( $username ){
	if( $username==true ){
		$user_id = User::idFromName( $username );
		$avatar = new wAvatar( $user_id, 'm' );
    	$useravatar = $avatar->getAvatarURL();
	    $ret = array('success'=> true, 'result'=>$useravatar );
	    $out = json_encode($ret);
		return $out;
	}
}