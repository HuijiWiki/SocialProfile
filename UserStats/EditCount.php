<?php
/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

/**
 * For the UserLevels (points) functionality to work, you will need to
 * define $wgUserLevels and require_once() this file in your wiki's
 * LocalSettings.php file.
 */
$wgHooks['NewRevisionFromEditComplete'][] = 'incEditCount';
$wgHooks['ArticleDelete'][] = 'removeDeletedEdits';
$wgHooks['ArticleUndelete'][] = 'restoreDeletedEdits';

/**
 * Updates user's points after they've made an edit in a namespace that is
 * listed in the $wgNamespacesForEditPoints array.
 */
function incEditCount( $article, $revision, $baseRevId ) {
	global $wgUser, $wgNamespacesForEditPoints,$wgMemc,$wgHuijiPrefix;

	// only keep tally for allowable namespaces
	if (
		!is_array( $wgNamespacesForEditPoints ) ||
		in_array( $article->getTitle()->getNamespace(), $wgNamespacesForEditPoints )
	) {
		$stats = new UserStatsTrack( $wgUser->getID(), $wgUser->getName() );
		$stats->incStatField( 'edit' );
	}
	$dbr = wfGetDB( DB_SLAVE );
	$num = SiteStats::edits();
	$sg = SystemGifts::checkEditsCounts($num);
	$usg = new UserSystemGifts( $wgUser->getName() );
	if($sg){
		$usg->sendSystemGift( 17 );
	}
	//10.31 gift-- del next day
	$nowTime = time();
	$starTime = strtotime("2015-10-31 00:00:00");
	$endTime = strtotime("2015-11-01 00:00:00");
	if( $nowTime >= $starTime && $nowTime < $endTime ){
		$dayCount = UserEditBox::getTodayEdit( $wgUser->getId() );
		if ($dayCount == 1) {
			$usg->sendSystemGift( 51 );
		}elseif ($dayCount == 10) {
			$usg->sendSystemGift( 52 );
		}elseif ($dayCount == 31) {
			$usg->sendSystemGift( 53 );
		}
	}
	$key = wfForeignMemcKey( 'huiji', '', 'revision', 'high_edit_site_followed', $wgUser->getName(), $wgHuijiPrefix );
	$wgMemc->incr( $key );
	$key = wfForeignMemcKey( 'huiji', '', 'revision', 'last_edit_user', $article->getTitle()->getArticleId(), $wgHuijiPrefix );
	$wgMemc->delete($key);
	return true;
}

/**
 * Updates user's points after a page in a namespace that is listed in the
 * $wgNamespacesForEditPoints array that they've edited has been deleted.
 */
function removeDeletedEdits( &$article, &$user, &$reason ) {
	global $wgNamespacesForEditPoints,$wgMemc,$wgHuijiPrefix;

	// only keep tally for allowable namespaces
	if (
		!is_array( $wgNamespacesForEditPoints ) ||
		in_array( $article->getTitle()->getNamespace(), $wgNamespacesForEditPoints )
	) {
		$dbr = wfGetDB( DB_MASTER );
		$res = $dbr->select(
			'revision',
			array( 'rev_user_text', 'rev_user', 'COUNT(*) AS the_count' ),
			array( 'rev_page' => $article->getID(), 'rev_user <> 0' ),
			__METHOD__,
			array( 'GROUP BY' => 'rev_user_text' )
		);
		foreach ( $res as $row ) {
			$stats = new UserStatsTrack( $row->rev_user, $row->rev_user_text );
			$stats->decStatField( 'edit', $row->the_count );
			$key = wfForeignMemcKey( 'huiji', '', 'revision', 'high_edit_site_followed', $row->rev_user_text, $wgHuijiPrefix );
			$wgMemc->decr( $key,$row->the_count );
		}
	}
	

	return true;
}

/**
 * Updates user's points after a page in a namespace that is listed in the
 * $wgNamespacesForEditPoints array that they've edited has been restored after
 * it was originally deleted.
 */
function restoreDeletedEdits( &$title, $new ) {
	global $wgNamespacesForEditPoints,$wgMemc,$wgHuijiPrefix;

	// only keep tally for allowable namespaces
	if (
		!is_array( $wgNamespacesForEditPoints ) ||
		in_array( $title->getNamespace(), $wgNamespacesForEditPoints )
	) {
		$dbr = wfGetDB( DB_MASTER );
		$res = $dbr->select(
			'revision',
			array( 'rev_user_text', 'rev_user', 'COUNT(*) AS the_count' ),
			array( 'rev_page' => $title->getArticleID(), 'rev_user <> 0' ),
			__METHOD__,
			array( 'GROUP BY' => 'rev_user_text' )
		);
		foreach ( $res as $row ) {
			$stats = new UserStatsTrack( $row->rev_user, $row->rev_user_text );
			$stats->incStatField( 'edit', $row->the_count );
			$key = wfForeignMemcKey( 'huiji', '', 'revision', 'high_edit_site_followed', $row->rev_user_text, $wgHuijiPrefix );
			$wgMemc->incr( $key, $row->the_count );
		}
	}


	return true;
}
