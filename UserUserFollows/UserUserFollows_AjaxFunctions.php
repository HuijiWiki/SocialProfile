<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */
$wgAjaxExportList[] = 'wfUserUserFollowsResponse';
$wgAjaxExportList[] = 'wfUserUserUnfollowsResponse';
$wgAjaxExportList[] = 'wfUserFollowsInfoResponse';
$wgAjaxExportList[] = 'wfUserFollowsRecommend';
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
	$user = User::newFromName( $username );
	$ust = new UserStatus( $user );
	$sites = $ust->getUserAllInfo( );
    $ret = array('success'=> true, 'result'=>$sites );
    $out = json_encode($ret);
		 //TODO: use wfMessage instead of hard code
	return $out;
}

//params follower = wgUser
function wfUserFollowsRecommend( $follower, $followee ){
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
			$weekRank = UserStats::getUserRank(10,'week');
			$monthRank = UserStats::getUserRank(20,'month');
			$totalRank = UserStats::getUserRank(20,'total');
			if ( count($weekRank) >=8 ) {
	            $recommend = $weekRank;
	        }elseif ( count($monthRank) >=8 ) {
	            $recommend = $monthRank;
	        }else{
	            $recommend = $totalRank;
	        }
	        $recommendRes = array();
	        $flres = array();
	        foreach ($recommend as $value) {
	            $tuser = User::newFromName($value['user_name']);
	            $isFollow = $uuf->checkUserUserFollow( $wgUser, $tuser );
	            if( !$isFollow && $value['user_name'] != $wgUser->getName() ){
	                $flres['avatar'] = $value['avatarImage'];
	                $flres['username'] = $value['user_name'];
	                $flres['userurl'] = $value['user_url'];
	                $recommendRes[] = $flres;
	            }         
	        }
	        $n = count($recommendRes);
	        $i = 5;
	        $newUser = $recommendRes[$i];
	        $i++;
    		$res = array('success'=> true, 'result'=>$newUser );
    		return $res;
		}
	}
}