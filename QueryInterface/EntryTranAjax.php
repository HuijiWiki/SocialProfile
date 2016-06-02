<?php
/**
 * AJAX functions used by EntryTran extension.
 */
require_once("EntryTran.php");
$wgAjaxExportList[] = 'wfGetTran';
$wgAjaxExportList[] = 'wfGetEntry';
function wfGetTran($content, $lang, $site, $offset=0, $size=98 ){
	$res = array();
	$contentArr = explode('|', $content);	
	foreach($contentArr as $item){
		$res[] = EntryTran::getTran($item, $lang, $site, $offset, $size);
	}
	return json_encode($res);
}
function wfGetEntry($content, $lang, $site, $offset=0, $size=98 ){
	$res = array();
	$contentArr = explode('|', $content);	
	foreach($contentArr as $item){
		$res[] = EntryTran::getEntry($item, $lang, $site, $offset, $size);
	}
	return json_encode($res);
}
?>