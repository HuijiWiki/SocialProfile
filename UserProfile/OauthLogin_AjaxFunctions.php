<?php
$wgAjaxExportList[] = 'wfCheckOauth';
$wgAjaxExportList[] = 'wfAddInfoToOauth';
$wgAjaxExportList[] = 'wfAddUserOauthCookie';

//check is exist openid
function wfCheckOauth( $openid, $type ){
	$dbr = wfGetDB( DB_SLAVE );
	$res = $dbr->select(
		'oauth',
		array(
			'user_id'
		),
		array(
			'open_id' => $openid,
			'o_type' => $type
		),
		__METHOD__
	);
	$user_id = null;
	if ($res){
		foreach($res as $value){
			$user_id = $value->user_id;
		}
	}
	$result = array('success'=> true, 'result'=>$user_id );
	$out = json_encode($result);
	return $out;
}

//insert into oauth
function wfAddInfoToOauth( $otype, $openid, $userid, $inviteuser, $inviter ){
	global $wgUser;
	$dbw = wfGetDB( DB_MASTER );
	if ( $inviteuser == 1 ) {
		$inviteUser = HuijiUser::newFromName( $inviter );
		$stats = new UserStatsTrack( $inviteUser->getId() );
		$stats->incStatField('referral_complete');
		$u = User::newFromId( $userid );
		$usg = new UserSystemGifts( $u->getName() );
		$usg->sendSystemGift(78);
		$u->setCookies(null,null,true );
		$result = array('success'=> true, 'result'=>'1' );
		$out = json_encode($result);
		return $out;
	}
	$res = $dbw->insert(
		'oauth',
		array(
			'o_type' => $otype,
			'open_id' => $openid,
			'user_id' => $userid,
		),
		__METHOD__
	);
	$u = User::newFromId( $userid );
	$u->setCookies(null,null,true );
	// $u->setSession();
	if($res){
		$result = array('success'=> true, 'result'=>'1' );
		$out = json_encode($result);
	}
	return $out;
}

//wfAddUserOauthCookie
function wfAddUserOauthCookie( $user_id ){
	// session_start();
	if ( $user_id ){
		$u = User::newFromId( $user_id );
		// $uname = $u->getName();
		// $arr = array('wpName'=>$uname);
		// $u->setCookies($arr, null, true);
		$u->setCookies(null,null,true );
	}
	// session_id() = $user_id;
	$res = array('success' => true, 'result'=>'1' );
	$out = json_encode($res);
	return  $out;
}