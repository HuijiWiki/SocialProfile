<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */

$wgAjaxExportList[] = 'wfUserSiteFollowsResponse';
$wgAjaxExportList[] = 'wfUserSiteUnfollowsResponse';
function wfUserSiteFollowsResponse(  $response, $username, $servername ) {
	global $wgUser;
	$out = '';

	$usf = new UserSiteFollow( $wgUser->getName() );
	if ( $username === $wgUser ){
		$usf->addUserSiteFollow($wgUser, $servername);
		$out = '已关注'; //TODO: use wfMessage instead of hard code
	}else{
		$out = 'fail';
	}
	return $out;
}
function wfUserSiteUnfollowsResponse( $response, $username, $servername ) {
	global $wgUser, $wgSitename;
	$out = '';

	$usf = new UserSiteFollow( $wgUser->getName() );
	if ( $username === $wgUser ){
		$usf->deleteUserSiteFollow($wgUser, $servername);
		$out = '关注'.$wgSitename; //TODO: use wfMessage instead of hard code
	}else{
		$out = 'fail';
	}

	return $out;
}