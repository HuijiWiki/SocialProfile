<?php
/**
 * AJAX functions used by UploadVideos extension.
 */
$wgAjaxExportList[] = 'wfinsertVideoInfo';
$wgAjaxExportList[] = 'wfcheckVideoExist';
$wgAjaxExportList[] = 'wfUploadNewRevision';
$wgAjaxExportList[] = 'wfGetBiliVideoInfo';

function wfinsertVideoInfo( $video_from, $title_str, $video_id, $video_title, $video_player_url, $video_tags, $video_duration ) {
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
	$user_name = $wgUser->getName();
	// return $title_str;
	$title = Title::newFromText( $title_str, NS_FILE );
	$pageRevision = $title->getLatestRevID();
	// $res = array(
	// 		'page_id' => $title->getArticleID(),
	// 		'success' => true,
	// 		);
	// return json_encode( $res );
	$a = UploadVideos::addVideoInfo( $pageRevision, $title->getArticleID(), $video_from, $video_id, $video_title, $video_player_url, $video_tags, $user_name, $video_duration );
		$pageId = $title->getArticleID();
		$article = new WikiFilePage($title);
		$thumSha1 = $article->getFile()->getSha1();
		$video = VideoTitle::newFromId( $pageId );
		$videoRevisionId = $video->getVideoRevisionId();
		$addRevisionBinder = UploadVideos::addRevisionBinder( $thumSha1, $videoRevisionId );
	if ( $a != false ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
	}else{
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_DATABASE_FAILED);
	}
	return $out;
}
//check file isexsit
function wfcheckVideoExist( $video_title ){
	$exist = UploadVideos::checkFile( $video_title );
	if ( count($exist) > 0 ) {
		return 'success';
	}else {
		return 'failed';
	}
}

//upload new revision
function wfUploadNewRevision( $video_from, $video_id, $video_title, $video_player_url, $video_tags, $video_duration ){

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
	$user_name = $wgUser->getName();
	$vision_id = 2;
	$a = UploadVideos::uploadNewVision( $video_title, $vision_id, $video_from, $video_id, $video_player_url, $video_tags, $video_duration, $user_name  );
	if ( $a != false ) {
		$out = ResponseGenerator::getJson(ResponseGenerator::SUCCESS);
	}else{
		$out = ResponseGenerator::getJson(ResponseGenerator::ERROR_DATABASE_FAILED);
	}
	return $out;

}

function wfGetBiliVideoInfo( $video_id, $page_id){
	$app_sec =  '2ad42749773c441109bdc0191257a664';
	$params = array(
		'type' => 'json',
		'id' => $video_id,
		'page' => $page_id,
		'appkey' => '85eb6835b0a1034e',
	);
	ksort($params);
	$data = http_build_query($params);
	$res = $data.'&sign='.md5($data.$app_sec);
	$resp_cid = UploadVideos::urlfetch('http://api.bilibili.com/view?'.$res);
	return $resp_cid;
}