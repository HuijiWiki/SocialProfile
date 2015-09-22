<?php
/**
 * AJAX functions used by SiteStatus extension.
 */
$wgAjaxExportList[] = 'wfGetSiteRank';
$wgAjaxExportList[] = 'wfGetSiteFollowedUsers';

function wfGetSiteRank( ) {
	global $wgUser, $wgHuijiPrefix;

	$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_UNKNOWN);

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NOT_LOGGED_IN);
		return $out;
	}
	$dateArr = array();
	for($k=1;$k<31;$k++){
		$dateArr[]= date('Y-m-d',strtotime("-$k day"));
	}
	$desdateArr = array_reverse($dateArr);
	$res['date'] = $desdateArr;
	foreach ($desdateArr as $key => $value) {
		$daySiteRank = AllSitesInfo::getAllSitesRankData( $wgHuijiPrefix, $value );
		$drank = isset($daySiteRank[0]['site_score'])?$daySiteRank[0]['site_score']:0;
		$result[] = (int)$drank;
	}
	$res['rank'] = $result;
	if ( $res ){
		$ret = array('success'=> true, 'result'=>$res );
		$out = json_encode($ret);
		return $out;
	}
}

function wfGetSiteFollowedUsers(){
	global $wgUser, $wgHuijiPrefix;

	$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_UNKNOWN);

	// This feature is only available for logged-in users.
	if ( !$wgUser->isLoggedIn() ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_NOT_LOGGED_IN);
		return $out;
	}
	$dateArr = array();
	for($k=1;$k<31;$k++){
		$dateArr[]= date('Y-m-d',strtotime("-$k day"));
	}
	$desdateArr = array_reverse($dateArr);
	$res['date'] = $desdateArr;
	foreach ($desdateArr as $key => $value) {
		$dayFollow = UserSiteFollow::getFollowerCountOneday( $wgHuijiPrefix, $value );
		$dfol = (int)isset($dayFollow)?$dayFollow:0;
		$result[] = (int)$dfol;
	}
	$res['FollowCount'] = $result;
	if ( $res ){
		$ret = array('success'=> true, 'result'=>$res );
		$out = json_encode($ret);
		return $out;
	}
}