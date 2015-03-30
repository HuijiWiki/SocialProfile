<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */

$wgAjaxExportList[] = 'wfUserSiteFollowsResponse';
$wgAjaxExportList[] = 'wfUserSiteUnfollowsResponse';
function wfUserSiteFollowsResponse( $username, $servername ) {
	global $wgUser, $wgServer, $wgHuijiPrefix;
	$out = 'fail';

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName() && $servername === $wgServer){
		if ($usf->addUserSiteFollow($wgUser, $wgHuijiPrefix) >= 0){
			$out = '已关注';
		}
	}
		 //TODO: use wfMessage instead of hard code
	return $out;
}
function wfUserSiteUnfollowsResponse( $username, $servername ) {
	global $wgUser, $wgSitename, $wgServer, $wgHuijiPrefix;
	$out = 'fail';

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName() && $servername === $wgServer){
		if ($usf->deleteUserSiteFollow($wgUser, $wgHuijiPrefix)){
			$out = '关注'.$wgSitename;
		}
		 //TODO: use wfMessage instead of hard code
	}
	return $out;
}