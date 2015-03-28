<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */

$wgAjaxExportList[] = 'wfUserSiteFollowsResponse';
$wgAjaxExportList[] = 'wfUserSiteUnfollowsResponse';
function wfUserSiteFollowsResponse( $username, $servername ) {
	global $wgUser;
	$out = 'fail';

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName() ){
		if ($usf->addUserSiteFollow($wgUser, $servername)){
			$out = '已关注';
		}
	}
		 //TODO: use wfMessage instead of hard code
	return $out;
}
function wfUserSiteUnfollowsResponse( $username, $servername ) {
	global $wgUser, $wgSitename;
	$out = 'fail';

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName() ){
		if ($usf->deleteUserSiteFollow($wgUser, $servername)){
			$out = '关注'.$wgSitename;
		}
		 //TODO: use wfMessage instead of hard code
	}
	return $out;
}