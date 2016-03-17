<?php
/**
 * AJAX functions used by AdminDashboard extension.
 */
$wgAjaxExportList[] = 'wfGetUserStatusInfo';
// $wgAjaxExportList[] = 'wfGetDefaultUserInfo';

function wfGetUserStatusInfo( $str, $limit, $continue = 1 ){
	global $wgHuijiPrefix, $wgUser;
	if( !$wgUser->isLoggedIn() ){
		$ret = array('result'=> 'false', 'info'=>'user is not loign' );
	    $out = json_encode($ret);
		return $out;
	}
	if ( $str === '') {
		$HuijiSite = WikiSite::newFromPrefix($wgHuijiPrefix);        
		$resUserArr = $HuijiSite->getFollowers();
		$result = $userInfo = array();
		foreach ($resUserArr as $key => $value) {
		    $userObj = HuijiUser::newFromName( $value['user_name'] );
		    $userStats = $userObj->getStats( $wgHuijiPrefix );
		    $result[$value['user_name']] = (int)str_replace(',', '', $userStats['edits']);
		}
		if (count($result) > 0) {
			arsort($result);
			$resPage = array_slice($result, $continue, $limit);
			foreach ($resPage as $key => $value) {
				$user = HuijiUser::newFromName( $key );
				$userInfo['userid'] = $user->getId();
				$userInfo['name'] = $key;
				$userInfo['img'] = $user->getAvatar()->getAvatarURL();
				$userInfo['editcount'] = $value;
				$userInfo['level'] = $user->getLevel()->getLevelName();
				$userInfo['status'] = $user->isBlocked();
				$userInfo['rights'] = $user->getEffectiveGroups();
				$userInfo['admin'] = $wgUser->changeableGroups();
				$userAllInfo[] = $userInfo;

			}
			$ret = array('result'=> 'success', 'users'=>$userAllInfo, 'continue'=>($continue+$limit) );
		}else{
			$ret = array('result'=> 'false', 'users'=>'no user' );
		}
		
	}else{
		$str = ucfirst($str);
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'user',
			array( 'user_id' ),
			"user_name like '{$str}%'",
			__METHOD__,
			array(
				'LIMIT' => $limit,
				'OFFSET' => $continue
			)
		);
		$userIdRes = $adminGroup = $userAllInfo = array();
		if ( $res ) {
			foreach ($res as $key => $value) {
				$userIdRes['userid'] = $value->user_id;
				$user = HuijiUser::newFromId( $value->user_id );
				$userIdRes['name'] = $user->getName();
				$userIdRes['img'] = $user->getAvatar()->getAvatarURL();
				$userStats = $user->getStats();
				$userIdRes['editcount'] = $userStats['edits'];
				$userIdRes['level'] = $user->getLevel()->getLevelName();
				$userIdRes['status'] = $user->isBlocked();
				$userIdRes['rights'] = $user->getEffectiveGroups();
				$userIdRes['admin'] = $wgUser->changeableGroups();
				$userAllInfo[] = $userIdRes;
			}
			$user_count = count($userAllInfo);
			if ( $user_count > 0 ) {
				// if ( $user_count > $limit ) {
				// 	$con_user = $userAllInfo[$user_count-1];
				// 	unset( $userAllInfo[$user_count-1] );
				// 	$resUserArr = $userAllInfo;
				$ret = array('result'=> 'success', 'users'=>$userAllInfo, 'continue'=>($continue+$limit) );
				// }else{
				// 	$ret = array('result'=> 'success', 'users'=>$userAllInfo );
				// }
			}else{
				$ret = array('result'=> 'false', 'users'=>'no user' );
			}
		}
	}
    $out = json_encode($ret);
	return $out;
}

// function wfGetDefaultUserInfo( $str, $per_page, $continue ){
// 	global $wgHuijiPrefix, $wgUser;
// 	$page = !isset( $page ) ? 1 : $page;
// 	//remember delete after done
// 	$per_page = 3;
// 	$HuijiSite = WikiSite::newFromPrefix($wgHuijiPrefix);        
// 	$resUserArr = $HuijiSite->getFollowers();
// 	$result = $userInfo = array();
// 	foreach ($resUserArr as $key => $value) {
// 	    $userObj = HuijiUser::newFromName( $value['user_name'] );
// 	    $userStats = $userObj->getStats( $wgHuijiPrefix );
// 	    $result[$value['user_name']] = (int)str_replace(',', '', $userStats['edits']);
// 	}
// 	arsort($result);
// 	$star = $per_page*($page-1);
// 	$resPage = array_slice($result, $star, $per_page);
// 	foreach ($resPage as $key => $value) {
// 		$user = HuijiUser::newFromName( $key );
// 		$userInfo['userid'] = $userResObj->getId();
// 		$userInfo['name'] = $key;
// 		$userInfo['img'] = $user->getAvatar()->getAvatarURL();

// 		$userInfo['editcount'] = $value;
// 		$userInfo['level'] = $user->getLevel()->getLevelName();
// 		$userInfo['status'] = $user->isBlocked();
// 		$userInfo['rights'] = $user->getEffectiveGroups();
// 		$userInfo['admin'] = $wgUser->changeableGroups();
// 		$userAllInfo[] = $userInfo;

// 	}
// 	$ret = array('result'=> 'false', 'users'=>$userAllInfo );
// 	$out = json_encode($ret);
// 	return $out;

// }