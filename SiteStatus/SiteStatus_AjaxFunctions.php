<?php
/**
 * AJAX functions used by SiteStatus extension.
 */
$wgAjaxExportList[] = 'wfGetSiteRank';
$wgAjaxExportList[] = 'wfGetSiteFollowedUsers';
$wgAjaxExportList[] = 'wfGetRecommendContent';

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

function wfGetRecommendContent(){
	global $wgUser;
	$recRes = new BootstrapMediaWikiTemplate();
    $block = $recRes->getIndexBlock( '首页/Admin' );
    $pageTitle = Title::newFromText( '首页/Admin' );
    $wgParserOptions = new ParserOptions($wgUser);
    $n = count($block);
    $recContent = array(); 
    for ($i=0; $i < $n; $i++) {
        $contentRes['title'] = $block[$i]->title;
        $contentRes['wikiname'] = $block[$i]->wikiname;
        $contentRes['desc'] = $wgParser->parse( $block[$i]->desc ,$pageTitle ,$wgParserOptions )->getText();
        $contentRes['wikiurl'] = $block[$i]->wikiurl;
        $contentRes['siteurl'] = $block[$i]->siteurl;
        $contentRes['backgroungimg'] = $block[$i]->backgroungimg;
        $recContent[] = $contentRes;
    }
    $result = array_rand( $recContent, 5 );
    $ret = array('success'=> true, 'result'=>$result );
	$out = json_encode($ret);
	return $out;
}