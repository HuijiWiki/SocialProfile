<?php
/**
 * AJAX functions used by UserSiteFollow extension.
 */
$wgAjaxExportList[] = 'wfUserUserFollowsResponse';
$wgAjaxExportList[] = 'wfUserUserUnfollowsResponse';
$wgAjaxExportList[] = 'wfUserFollowsInfoResponse';
$wgAjaxExportList[] = 'wfUserFollowsRecommend';
$wgAjaxExportList[] = 'wfGetUserFollowing';
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
	if ( $follower === $wgUser->getName() && $followee !== $follower){
		$huijiUser = HuijiUser::newFromUser($wgUser);
		$followee = User::newFromName($followee);
		if ($huijiUser->follow($followee)){
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
	
	if ( $follower === $wgUser->getName() && $followee !== $follower){
		$huijiUser = HuijiUser::newFromUser($wgUser);
		$followee = User::newFromName($followee);
		if ($huijiUser->unfollow($followee)){
			$out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
		}
		 //TODO: use wfMessage instead of hard code
	}
	return $out;
}
function wfUserFollowsInfoResponse( $username ) {
	$user = User::newFromName( $username );
	//No such user
	if ($username == null||$user == null || $user->getId() == 0 ){
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NO_SUCH_USER);
		return $out;
	}
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

	if ( $follower === $wgUser->getName() && $followee !== $follower){
		$huijiUser = HuijiUser::newFromUser($wgUser);
		$followee = User::newFromName($followee);
		if ($huijiUser->follow($followee)){
			$weekRank = UserStats::getUserRank(20,'week');
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
	            $isFollow = $huijiUser->isFollowing($tuser);
	            if( !$isFollow && $value['user_name'] != $wgUser->getName() ){
	                $flres['avatar'] = $value['avatarImage'];
	                $flres['username'] = $value['user_name'];
	                $flres['userurl'] = $value['user_url'];
	                $recommendRes[] = $flres;
	            }         
	        }
	        $n = count($recommendRes);
	        $i = 5;
	        $newUser = isset($recommendRes[$i])?$recommendRes[$i]:null;
    		$res = array('success'=> true, 'result'=>$newUser );
    		$out = json_encode($res);
    		return $out;
		}
	
	}
}

function wfGetUserFollowing( $username ){
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
	$user = User::newFromName($username);
	//No such user
	if ($user == '' || $user->getId() == 0 ){
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NO_SUCH_USER);
		return $out;
	}

	$huijiUser = HuijiUser::newFromUser($user);
	$result = $huijiUser->getFollowingUsers();
    	$ret = array('success'=> true, 'result'=>$result );
    	$out = json_encode($ret);
	return $out;

}
