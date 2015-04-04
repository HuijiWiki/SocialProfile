<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */

$wgAjaxExportList[] = 'wfUserSiteFollowsResponse';
$wgAjaxExportList[] = 'wfUserSiteUnfollowsResponse';
function wfUserSiteFollowsResponse( $username, $servername ) {
	global $wgUser, $wgServer, $wgHuijiPrefix;
	$out = 'fail';

	// This feature is only available for logged-in users.
	if ( !$user->isLoggedIn() ) {
		$out = '请登录';
		return $out;
	}

	// No need to allow blocked users to access this page, they could abuse it, y'know.
	if ( $user->isBlocked() ) {
		$out = '您被封禁中';
		return $out;
	}

	// Database operations require write mode
	if ( wfReadOnly() ) {
		$out = '数据库已锁定';
		return $out;
	}

	// Are we even allowed to do this?
	if ( !$user->isAllowed( 'edit' ) ) {
		$out = '请验证邮箱';
		return $out;
	}

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName() && $servername === $wgServer){
		if ($usf->addUserSiteFollow($wgUser, $wgHuijiPrefix) >= 0){
			$out = '取消关注';
		}
	}
		 //TODO: use wfMessage instead of hard code
	return $out;
}
function wfUserSiteUnfollowsResponse( $username, $servername ) {
	global $wgUser, $wgSitename, $wgServer, $wgHuijiPrefix;
	$out = 'fail';

	// This feature is only available for logged-in users.
	if ( !$user->isLoggedIn() ) {
		$out = '请登录';
		return $out;
	}

	// No need to allow blocked users to access this page, they could abuse it, y'know.
	if ( $user->isBlocked() ) {
		$out = '您被封禁中';
		return $out;
	}

	// Database operations require write mode
	if ( wfReadOnly() ) {
		$out = '数据库已锁定';
		return $out;
	}

	// Are we even allowed to do this?
	if ( !$user->isAllowed( 'edit' ) ) {
		$out = '请验证邮箱';
		return $out;
	}

	$usf = new UserSiteFollow();
	if ( $username === $wgUser->getName() && $servername === $wgServer){
		if ($usf->deleteUserSiteFollow($wgUser, $wgHuijiPrefix)){
			$out = '关注'.$wgSitename;
		}
		 //TODO: use wfMessage instead of hard code
	}
	return $out;
}