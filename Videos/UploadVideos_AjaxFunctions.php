<?php
/**
 * AJAX functions used by UploadVideos extension.
 */
$wgAjaxExportList[] = 'wfinsertVideoInfo';
$wgAjaxExportList[] = 'wfcheckVideoExist';
$wgAjaxExportList[] = 'wfUploadNewRevision';
$wgAjaxExportList[] = 'wfGetBiliVideoInfo';
$wgAjaxExportList[] = 'wfGet163MusicInfo';

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
	require_once('/var/www/html/Confidential.php');
	$app_sec = Confidential::$bilibili_secret_key; 
	$app_key = Confidential::$bilibili_app_key; 
	$params = array(
		'type' => 'json',
		'id' => $video_id,
		'page' => $page_id,
		'appkey' => $app_key,
	);
	ksort($params);
	$data = http_build_query($params);
	$res = $data.'&sign='.md5($data.$app_sec);
	$resp_cid = UploadVideos::urlfetch('http://api.bilibili.com/view?'.$res);
	return $resp_cid;
}

/**
 * [wfGet163MusicInfo description]
 * @param  [int] $music_id [description]
 * @param  [int] $type     [ album-type:1 song-type:0 playlist-type:2]
 * @return [json]           [music info]
 */
function wfGet163MusicInfo( $music_id, $type ){
	if ( $type == 2 ) {
		$url = "http://music.163.com/api/song/detail/?id=" . $music_id . "&ids=%5B" . $music_id . "%5D";
	    return UploadVideos::curl_get($url);
	}elseif ( $type == 1 ) {
		$url = "http://music.163.com/api/album/" . $music_id;
    	return UploadVideos::curl_get($url);
	}elseif ( $type == 0 ) {
		$url = "http://music.163.com/api/playlist/detail?id=" . $music_id;
    	return UploadVideos::curl_get($url);
	}

}