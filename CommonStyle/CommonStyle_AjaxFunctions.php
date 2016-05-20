<?php
/**
 * AJAX functions used by CommonStyle extension.
 */
$wgAjaxExportList[] = 'wfUpdateCssStyle';
$wgAjaxExportList[] = 'wfOpenCssStyle';
function wfUpdateCssStyle( $cssContent, $fileName, $cssId ) {
	
	global $wgUser, $wgHuijiPrefix;
	$cssPath = "/var/www/virtual/".$wgHuijiPrefix."/style";

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

    if(!is_dir($cssPath)){
    	mkdir($cssPath, 0777);
    }
    $lessCon = $cssCon = '';
    if ( count($cssContent)>0 && !empty($cssContent) ) {
    	$cssCon = json_encode($cssContent);
    	foreach ($cssContent as $key => $value) {
			$lessCon .= $key.":".$value.";";
    	}	
    }
    file_put_contents($cssPath.'/SiteColor.less', $lessCon); 
	$res = CommonStyle::insertSiteCss( $fileName, $cssCon, $cssId );
	if ($res) {
		$ret = array('result'=> 'true' );
		$out = json_encode($ret);
	}else{
		$ret = array('result'=> 'false' );
		$out = json_encode($ret);
	}
	return $out;
}

function wfOpenCssStyle( $cssId ){

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
	if ( $cssId != null ) {
		$res = CommonStyle::openCssStyle( $cssId );
		if ($res) {
			$out = '{"success": true,"message": "insert success"}';
		}
	}
	return $out;
}