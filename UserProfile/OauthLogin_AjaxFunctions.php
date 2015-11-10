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
function wfAddInfoToOauth( $otype, $openid, $userid ){
	$dbw = wfGetDB( DB_MASTER );
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
	$u->setCookies();
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