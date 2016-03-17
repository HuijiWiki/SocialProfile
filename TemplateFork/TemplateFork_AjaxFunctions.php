<?php
/**
 * AJAX functions used by template fork.
 */
$wgAjaxExportList[] = 'wfAddForkCount';
$wgAjaxExportList[] = 'wfAddForkInfo';
$wgAjaxExportList[] = 'wfGetForkInfoByPageId';

//add fork count
function wfAddForkCount( $page_id, $prefix ){
	global $wgMemc;
	$count = AllSitesInfo::getPageForkCount( $page_id );
	$pageCount = (!is_null($count))?$count:0;
	$dbw = wfGetDB( DB_MASTER );
	$dbw->upsert(
		'template_fork_count',
		array(
			'template_id' => $page_id,
			'fork_count' => 1,
		),
		array(
			'template_id' => $page_id,
		),
		array(
			'fork_count' => $pageCount+1,
		),			
		__METHOD__
	);
	$wgMemc->delete( wfForeignMemcKey('huiji','', 'getForkCountByPageId', 'onesite', $page_id, $prefix ) );
	return 'success';
}

//add fork info
function wfAddForkInfo( $page_id, $ns_num, $page_title, $fork_from, $prefix ){
	global $wgUser, $isProduction, $wgMemc;
	$c_prefix = $prefix;
	$fork_user = $wgUser->getName();
	if ($prefix != null) {
		$prefix = WikiSite::DbIdFromPrefix($prefix);
	}
	$page_title = substr(strrchr($page_title, ":"), 1);
	$dbw = wfGetDB(DB_MASTER, array(), $prefix);
	$res_page = $dbw->select(
		'page',
		array(
			'page_id'
		),
		array(
			'page_namespace' => $ns_num,
			'page_title' => $page_title
		),
		__METHOD__
	);
	foreach ($res_page as $value) {
		$res_pageid = $value->page_id;
	}
	$res = $dbw->insert(
		'template_fork',
		array(
			'template_id' => $page_id,
			'target_id' => $res_pageid,
			'fork_from' => $fork_from,
			'fork_user' => $fork_user,
			'fork_date' => date( 'Y-m-d H:i:s' ),
		),
		__METHOD__
	);
	$wgMemc->delete( wfForeignMemcKey('huiji','', 'getInfoByPageId', 'onesite', $res_pageid, $c_prefix ) );
	return 'success';
}

//get template fork info by pageid
function wfGetForkInfoByPageId( $target_id, $prefix ){
	$result = TemplateFork::getForkInfoByPageId( $target_id, $prefix );
	if ( $result != null ) {
		return $result;
	}
}

