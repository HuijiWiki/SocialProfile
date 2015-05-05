<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */
$wgAjaxExportList[] = 'wfUserSiteFollowsResponse';
$wgAjaxExportList[] = 'wfUserSiteUnfollowsResponse';
$wgAjaxExportList[] = 'wfUserSiteFollowsDetailsResponse';
$wgAjaxExportList[] = 'wfUserFollowsSiteResponse';
function wfUserSiteFollowsResponse( $username, $servername ) {
	
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

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName() ){//&& $servername === $wgServer
		//convert full url to prefix
		$str = substr($servername,7);
		$n = strpos($str, '.huiji.wiki');
		if ($n){
			$servername = substr($str, 0, $n);
		}
		if ($usf->addUserSiteFollow($wgUser, $servername) >= 0){
			$out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
		}
	}
	return $out;
}
function wfUserSiteUnfollowsResponse( $username, $servername ) {
	global $wgUser, $wgSitename, $wgServer, $wgHuijiPrefix;

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

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName()){// && $servername === $wgServer
		$str = substr($servername,7);
		$n=strpos($str,'.huiji.wiki');
		if ($n){
			$servername=substr($str,0,$n);
		}
		if ($usf->deleteUserSiteFollow($wgUser, $servername)){
			$out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
		}
	}
	return $out;
}
function wfUserSiteFollowsDetailsResponse( $user_name,$t_name ) {
	$user_id = User::idFromName($user_name);
	$t_id = User::idFromName($t_name);
	$sites = UserSiteFollow::getFullFollowedSitesDB($user_id,$t_id);
	$ret = array('success'=> true, 'result'=>$sites );
	$out = json_encode($ret);
	return $out;
}

function wfUserFollowsSiteResponse( $user, $site_name ) {
	global $wgUser, $wgSitename, $wgServer, $wgHuijiPrefix;

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

	$str = substr($site_name,7);
	$n=strpos($str,'.huiji.wiki');
	if ($n) 
		$site_name=substr($str,0,$n);
	// $user_id = User::idFromName($user_name);
	// $t_id = User::idFromName($t_name);
	$sites = UserSiteFollow::getUserFollowSite($wgUser, $site_name);
	$ret = array('success'=> true, 'result'=>$sites );
	$out = json_encode($ret);
	return $out;  
}