<?php
/**
 * AJAX functions used by AdminDashboard extension.
 */
$wgAjaxExportList[] = 'wfGetUserStatusInfo';
$wgAjaxExportList[] = 'wfUpdaSiteDescription';
$wgAjaxExportList[] = 'wfSetSiteProperty';

function wfGetUserStatusInfo( $str, $limit, $continue=0 ){
	global $wgHuijiPrefix, $wgUser, $wgRequest;
	if( !$wgUser->isLoggedIn() ){
		$ret = array('result'=> 'false', 'info'=>'user is not loign' );
	    $out = json_encode($ret);
		return $out;
	}

    $salts = ApiQueryTokens::getTokenTypeSalts();
	$token = $wgUser->getEditToken( $salts['userrights'] , $wgRequest );
	if ( $str === '' ) {
		$HuijiSite = WikiSite::newFromPrefix($wgHuijiPrefix);        
		$resUserArr = $HuijiSite->getFollowers();
		$result = $userInfo = array();
		foreach ( $resUserArr as $key => $value ) {
		    $userObj = HuijiUser::newFromName( $value['user_name'] );
		    $userStats = $userObj->getStats( $wgHuijiPrefix );
		    $result[$value['user_name']] = (int)str_replace(',', '', $userStats['edits']);
		}
		if ( count($result) > 0 ) {
			arsort($result);
			$resPage = array_slice($result, $continue, $limit);
			foreach ( $resPage as $key => $value ) {
				$user = HuijiUser::newFromName( $key );
				$userRight = $user->getEffectiveGroups();
				$result = array();
				if (in_array('staff', $userRight)) {
				    $result[] = 'staff';
				}
				if (in_array('bureaucrat', $userRight)) {
				    $result[] = 'bureaucrat';
				}
				if (in_array('sysop', $userRight)) {
				    $result[] = 'sysop';
				}
				if (in_array('rollback', $userRight)) {
				    $result[] = 'rollback';
				}
				if (in_array('bot', $userRight)) {
				    $result[] = 'bot';
				}
				$userInfo['userid'] = $user->getId();
				$userInfo['name'] = $key;
				$userInfo['img'] = $user->getAvatar()->getAvatarURL();
				$userInfo['editcount'] = $value;
				$userInfo['level'] = $user->getLevel()->getLevelName();
				$userInfo['status'] = $user->isBlocked();
				$userInfo['rights'] = $result;
				$userAllInfo[] = $userInfo;

			}
			$ret = array( 'result'=> 'success', 'users'=>$userAllInfo, 'continue'=>($continue+count($resPage)), 'token'=>$token, 'admin'=>$wgUser->changeableGroups() );
		}else{
			$ret = array( 'result'=> 'false', 'users'=>'no user' );
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
				$user = HuijiUser::newFromId( $value->user_id );
				$userRight = $user->getEffectiveGroups();
				$result = array();
				if (in_array('staff', $userRight)) {
				    $result[] = 'staff';
				}
				if (in_array('bureaucrat', $userRight)) {
				    $result[] = 'bureaucrat';
				}
				if (in_array('sysop', $userRight)) {
				    $result[] = 'sysop';
				}
				if (in_array('rollback', $userRight)) {
				    $result[] = 'rollback';
				}
				if (in_array('bot', $userRight)) {
				    $result[] = 'bot';
				}
				$userIdRes['userid'] = $value->user_id;
				$userIdRes['name'] = $user->getName();
				$userIdRes['img'] = $user->getAvatar()->getAvatarURL();
				$userStats = $user->getStats();
				$userIdRes['editcount'] = $userStats['edits'];
				$userIdRes['level'] = $user->getLevel()->getLevelName();
				$userIdRes['status'] = $user->isBlocked();
				$userIdRes['rights'] = $result;
				$userAllInfo[] = $userIdRes;
			}
			$user_count = count($userAllInfo);
			if ( $user_count > 0 ) {
				$ret = array( 'result'=> 'success', 'users'=>$userAllInfo, 'continue'=>($continue+$user_count), 'token'=>$token, 'admin'=>$wgUser->changeableGroups() );
			}else{
				$ret = array( 'result'=> 'false', 'users'=>'no user' );
			}
		}
	}
    $out = json_encode($ret);
	return $out;
}

function wfUpdaSiteDescription( $desc ){
	global $wgHuijiPrefix, $wgUser;
	if ( !$wgUser->isAllowed( 'AdminDashboard' ) ){
		$ret = array( 'result'=> 'failed', 'reason'=>'not allowed' );
	}else {
		$dbw = wfGetDB(DB_MASTER);
		$dbw->update(
			'domain',
			array(
				'domain_dsp' => $desc
			),
			array(
				'domain_prefix' => $wgHuijiPrefix
			),
			__METHOD__
		);
		$ret = array( 'result'=> 'success' );
	}
	$log = new LogPage( 'AdminDashboard' );
	$log->addEntry(
			'addDescription',
			SpecialPage::getTitleFor('AdminDashboard'),
			wfMessage( 'user-update-description-log-entry',array($wgUser->getName(),$wgHuijiPrefix) )->inContentLanguage()->text(),
			array()
		);
	$out = json_encode($ret);
	return $out;
}

function wfSetSiteProperty( $name, $value ){
	global $wgHuijiPrefix, $wgUser, $wgSiteSettings;
	if ( !$wgUser->isAllowed( 'AdminDashboard' ) ){
		$ret = array( 'result'=> 'failed', 'reason'=>'not allowed' );
		$out = json_encode($ret);
		return $out;
	}
	if ($value != 1 && $value != 0){
		$ret = array( 'result'=> 'failed', 'reason'=>'不合法的值' );
		$out = json_encode($ret);
		return $out;		
	}
	$site = WikiSite::newFromPrefix( $wgHuijiPrefix );
	$rating = $site->getRating();
	if( RatingCompare::$$wgSiteSettings[$name]['level'] <= RatingCompare::$$rating ){
		$res = $site->setProperty( $name, $value );
		$ret = array( 'result'=> 'success' );
	} else {
		$ret = array( 'result'=> 'failed', 'reason'=>'未知选项' );
	}
	$log = new LogPage( 'AdminDashboard' );
	$log->addEntry(
			'setSiteProperty',
			SpecialPage::getTitleFor('AdminDashboard'),
			wfMessage( 'user-set-siteproperty-log-entry',array( $wgUser->getName(),$wgHuijiPrefix,$name,$value ) )->inContentLanguage()->text(),
			array()
		);
	$out = json_encode($ret);		
	return $out;

}