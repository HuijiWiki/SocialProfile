<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */

$wgAjaxExportList[] = 'wfUserUserFollowsResponse';
$wgAjaxExportList[] = 'wfUserUserUnfollowsResponse';
function wfUserUserFollowsResponse( $follower, $followee ) {
	global $wgUser;
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

	$uuf = new UserUserFollow();
	if ( $follower === $wgUser->getName() && $followee !== $follower){
		if ($uuf->addUserUserFollow($wgUser, User::newFromName($followee)) !== false){
			$out = '取消关注';
		}
	}
		 //TODO: use wfMessage instead of hard code
	return $out;
}
function wfUserUserUnfollowsResponse( $follower, $followee ) {
	global $wgUser;
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

	$uuf = new UserUserFollow();
	if ( $follower === $wgUser->getName() && $followee !== $follower){
		if ($uuf->deleteUserUserFollow($wgUser, User::newFromName($followee))){
			$out = '关注'.$followee;
		}
		 //TODO: use wfMessage instead of hard code
	}
	return $out;
}