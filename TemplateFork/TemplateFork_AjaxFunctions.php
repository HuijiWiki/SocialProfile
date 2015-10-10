<?php
/**
 * AJAX functions used by template fork.
 */
$wgAjaxExportList[] = 'wfAddForkCount';
$wgAjaxExportList[] = 'wfAddForkInfo';

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
function wfAddForkInfo( $page_id, $fork_from, $prefix ){
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
	$dbw = wfGetDB(DB_MASTER, array(), $prefix);
	$res = $dbw->insert(
		'template_fork',
		array(
			'template_id' => $page_id,
			'fork_from' => $fork_from,
			'fork_user' => $fork_user,
			'fork_date' => date( 'Y-m-d H:i:s' ),
		),
		__METHOD__
	);
	return 'success';
}

