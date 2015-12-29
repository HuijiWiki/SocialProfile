<?php
/**
 * AJAX functions used by template fork.
 */
$wgAjaxExportList[] = 'wfAddForkCount';
$wgAjaxExportList[] = 'wfAddForkInfo';
$wgAjaxExportList[] = 'wfGetForkCountByPageId';
$wgAjaxExportList[] = 'wfGetForkInfoByPageId';

//add fork count
function wfAddForkCount( $page_id ){
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
	return 'success';
}

//add fork info
function wfAddForkInfo( $page_id, $ns_num, $page_title, $fork_from, $prefix ){
	global $wgUser, $isProduction;
	$fork_user = $wgUser->getName();
	if ($prefix != null) {
		if( $isProduction == true &&( $prefix == 'www' || $prefix == 'home') ){
			$prefix = 'huiji_home';
		}elseif ( $isProduction == true ) {
			$prefix = 'huiji_sites-'.str_replace('.', '_', $prefix);
		}else{
			$prefix = 'huiji_'.str_replace('.', '_', $prefix);
		}
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
	return 'success';
}

//get template fork count by pageid
function wfGetForkCountByPageId( $page_id ){
	$dbr = wfGetDB(DB_SLAVE);
	$res = $dbr->select(
		'template_fork_count',
		array(
			'fork_count'
		),
		array(
			'template_id' => $page_id
		),
		__METHOD__
	);
	$result = 0;
	if( $res !== false ){
		foreach ($res as $value) {
			$result = $value->fork_count;
		}
	}
	return json_encode( $result );
}

//get template fork info by pageid
function wfGetForkInfoByPageId( $page_id ){
	$dbr = wfGetDB(DB_SLAVE);
	$res = $dbr->select(
		'template_fork',
		array(
			'fork_from',
			'fork_user',
			'fork_date',
		),
		array(
			'target_id' => $target_id
		),
		__METHOD__,
		array( 
			'ORDER BY' => 'fork_date DESC'
		)
	);
	$result = array();
	if( $res ){
		foreach ($res as $value) {
			$result['fork_from'] = $value->fork_from;
			$result['fork_user'] = $value->fork_user;
			$result['fork_date'] = $value->fork_date;
		}
	}
	return json_encode( $result );
}

