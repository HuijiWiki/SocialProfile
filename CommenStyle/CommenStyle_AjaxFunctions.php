<?php
/**
 * AJAX functions used by CommenStyle extension.
 */
$wgAjaxExportList[] = 'wfUpdateCssStyle';
$wgAjaxExportList[] = 'wfOpenCssStyle';
function wfUpdateCssStyle( $cssContent, $fileName ) {
	
	global $wgUser, $wgHuijiPrefix;
	$cssPath = "/var/www/virtual/".$wgHuijiPrefix."/skins/bootstrap-mediawiki/css";

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

	$out = $cssContent;
        if(!is_dir($cssPath)){
        	mkdir($cssPath, 0777);
        }
        // $filename = 'test.css';
        file_put_contents($cssPath.'/'.$fileName, $cssContent); 
	$cs = new CommenStyle();
	$isExist = CommenStyle::checkCssFile( $fileName );
	if (!$isExist) {
		CommenStyle::insertSiteCss( $fileName );
		$out = '{"success": true,"message": $cssContent}';
	}else{
		$out = '{"success": false,"message": "file have exist"}';
	}
	return $out;
}

function wfOpenCssStyle( $fileName ){

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
	if ( $fileName != null ) {
		$res = CommenStyle::openCssStyle($fileName);
		if ($res) {
			$out = '{"success": true,"message": "insert success"}';
		}
	}
	return $out;
}